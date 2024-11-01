<?php
namespace VSTA\Listings;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Stores data for a single RETS listing, populated from an API call,
 * and sets up the data to be displayed using a shortcode
 *
 * @author VistaWP
 * @link https://maderightmedia.com
 * @version 1.0
 */
class Single_Listing extends \VSTA\API_Data {

  /**
   * Initializes mappings in $this->mappings
   * Each mapping has the name of a field from the API call as key,
   * mapped to a callable function in this class which can process
   * the data contained in that field and store it in $this->fields
   * as HTML which can be echoed onto the page
   */
  protected function init_mappings(): void {
    $this->mappings = array(
      'property' => array($this, 'process_property_data'),
      'office' => array($this, 'process_office_data'),
      'address' => array($this, 'process_address'),
      'school' => array($this, 'process_school_data'),
      'mls' => array($this, 'process_fields_by_name'),
      'association' => array($this, 'process_hoa_info'),
      'photos' => array($this, 'process_photos'),
      'geo' => array($this, 'process_fields_by_name'),
      'tax' => array($this, 'process_fields_by_name'),
      'virtualTourUrl' => array($this, 'process_tour'),
      'modified' => array($this, 'process_modified'),
    );

    $this->number_fields = array(
      'listPrice',
      'sqft',
      'sqftPrice',
    );
  }

  /**
   * Creates a new Single_Listing object
   * @param array  $api_data  Deserialized data from the API. Should contain a single MLS listing
   */
  public function __construct(array $api_data) {
    // Call parent
    parent::__construct($api_data);

    // Register scripts/styles
    // Yes, this runs every time we initialize a new Single_Listing object,
    // but don't optimize that unless necessary
    \VSTA\Main::get_instance()->register_style('slideshow-styles', \vista_plugin_url('css/slideshow.css'));
    \VSTA\Main::get_instance()->register_script('slideshow-js', \vista_plugin_url('js/slideshow.js'));
    \VSTA\Main::get_instance()->enqueue_style('slideshow-styles');
    \VSTA\Main::get_instance()->enqueue_script('slideshow-js');

    // Create price/sqft field
    $this->price_sqft_field($api_data['listPrice'], $api_data['property']['area']);
    $this->view_listing_btn($api_data['mlsId']);
  }

  /**
   * Gets all fields in this listings
   * Fields are stored in an associative array, mapped $fieldname => $fieldvalue
   * Each $fieldvalue is the HTML required to display the field on a page.
   * Exact contents differ per field
   * @return array Fields, as an associative array mapped $fieldname => $fieldvalueHTML
   */
  public final function get_fields(): array {
    return $this->fields;
  }

  /**
   * Creates a sqftPrice field in $fields which represents the price/sq ft of the listing.
   * If either param is null, the sqftPrice field is set to the default error message
   * @param int $price  Price of the listing
   * @param int $sqft   Square footage of the listing
   */
  protected function price_sqft_field(?int $price, ?int $sqft): void {
    if (!empty($price) && !empty($sqft)) {
      try {
        $this->try_process_field('sqftPrice', $price / $sqft);

      } catch(\Exception $e) {
        $this->try_process_field('sqftPrice', parent::ERROR);
      }
    } else {
      $this->try_process_field( 'sqftPrice', NULL );
    }

  }

 /**
  * Creates a field 'viewButton' which contains an <a> element with class 'vista-view-listing-button'.
  * This <a> links to /individual-listing/?listing=$mlsID, where $mlsID is replaced with the parameter
  * @param  string $mlsID  The mlsId field for this listing, from the SimplyRETS API
  */
  protected function view_listing_btn(?string $mlsID): void {
    if (is_null($mlsID)) {
      $this->try_process_field('viewButton', NULL);
    }

    $individual_listing_url = \get_home_url() . "/individual-listing/?listing={$mlsID}";
    $this->try_process_field(
      'viewButton',
      "<a href='{$individual_listing_url}' class='vista-view-listing-button'>View Listing</a>"
    );
  }

  /**
   * Processes the 'modified' field into a human-readable date format,
   * which is encoded into two fields: 'last-modified-time' and 'last-modified-date'
   * @param  string $name     Field name
   * @param  array  $value    Field value
   */
  protected function process_modified(string $name, ?string $value): void {
    if (is_null($value)) {
      $this->try_process_field('last-modified-time', NULL);
      $this->try_process_field('last-modified-date', NULL);
    } else {
      try {
        $datetime = new \DateTime((string)$value);
        $this->try_process_field('last-modified-date', $datetime->format('F j, Y'));
        $this->try_process_field('last-modified-time', $datetime->format('g:i:s A'));
      } catch (\Exception $e) {
        $this->try_process_field('last-modified-time', parent::ERROR);
        $this->try_process_field('last-modified-date', parent::ERROR);
      }
    }
  }

/**
 * Process the data from the property field returned by the API call
 * and stores the data in $fields
 * All text fields are stored by name. Parking is stored as three fields.
 * Rooms is stored as a count in 'rooms' and in rooms-info
 * @param string $name  Field name
 * @param array $value Field value
 */
  protected function process_property_data(string $name, array $value): void {
    // Remove non-textual fields
    $parking = $value['parking'];
    unset($value['parking']);
    $rooms = $value['rooms'] ?? "No rooms found";
    unset($value['rooms']);

    // Process square footage
    $this->try_process_field('sqft', $value['area']);
    unset($value['area']);

    // Set baths
    $this->try_process_field('baths', $value['bathsFull'] + (.5 * $value['bathsHalf']));

    // Set non-textual fields
    $this->try_process_field('parking-leased', $parking['leased']);
    $this->try_process_field('parking-spaces', $parking['spaces']);
    $this->try_process_field('parking-description', $parking['description']);

    // Make sure we have room data, show an error otherise
    $this->fields['rooms-info'] = '';
    if (is_string($rooms)) {
      $this->try_process_field('rooms', 0);
      $this->try_process_field('rooms-info', $rooms);
    } else if (is_array($rooms)) {
      $this->try_process_field('rooms', count($rooms));
      foreach ($rooms as $room) {
        $this->fields['rooms-info'] .= "<p>" . $room['typeText'] . "</p>";
      }
    } else if (is_null($rooms)) {
      $this->try_process_field('rooms', NULL);
      $this->try_process_field('rooms-info', NULL);
    } else {
      $this->try_process_field('rooms', 0);
      $this->try_process_field('rooms-info', parent::ERROR);
    }

    // Process textual fields
    $this->process_fields_by_name($name, $value);
  }

  /**
   * Processes data from the office field into $fields
   * Each sub-field is named and stored with the prefix office-
   * @param  string   $name  Field name
   * @param  array $value Field value
   */
  protected function process_office_data(string $name, ?array $value): void {
    if (is_null($value)) {
      $this->try_process_field('office-email', NULL);
      $this->try_process_field('office-phone', NULL);
      $this->try_process_field('office-cell', NULL);
      $this->try_process_field('office-name', NULL);
      $this->try_process_field('office-servingName', NULL);
      $this->try_process_field('office-brokerid', NULL);
    } else {
      if (empty($value['contact'])) {
        $this->try_process_field('office-email', NULL);
        $this->try_process_field('office-phone', NULL);
        $this->try_process_field('office-cell', NULL);
      } else {
        $this->try_process_field('office-email', $value['contact']['email']);
        $this->try_process_field('office-phone', $value['contact']['office']);
        $this->try_process_field('office-cell', $value['contact']['cell']);
      }
      $this->try_process_field('office-name', $value['name']);
      $this->try_process_field('office-servingName', $value['servingName']);
      $this->try_process_field('office-brokerid', $value['brokerid']);
    }

  }

  /**
   * Processes agent data
   * All fields in the 'contact' array have the prefix "agent-contact-"
   * All other fields are processed into $fields with the prefix agent-
   * @param string $name  Field name
   * @param array $value  Field value
   */
  protected function process_agent(string $name, ?array $value): void {
    if (isset($value))
      $this->process_fields_by_name($name, $value['contact'], "agent-contact-");
    unset($value['contact']);

    // Process other fields
    $this->process_fields_by_name($name, $value, 'agent-');
  }

  /**
   * Processes data from the address field into $fields
   * Simply takes address->full from the api call fields
   * @param string   $name  Field name
   * @param array $value Field value
   */
  protected function process_address(string $name, ?array $value): void {
    // Create full address line
    $unit = $value['unit'] ? "Unit {$value['unit']}, " : "";
    $this->try_process_field('address', "{$value['streetNumberText']} {$value['streetName']}, {$unit}{$value['city']},
      {$value['state']} {$value['postalCode']}");

	// Process other fields by name
	$this->process_fields_by_name($name, $value);
  }

  /**
   * Processes data from the school field into $fields.
   * Adds all sub-fields to $fields by name, except district, which is changed to school-district
   * @param string   $name  Field name
   * @param array $value Field value
   */
  protected function process_school_data(string $name, ?array $value): void {
    if( isset( $value[ 'district' ] ) ) {
      // Process district field
      $this->try_process_field( 'school-district', $value[ 'district' ] );
      unset( $value[ 'district' ] );
    }

    // Process other fields
    $this->process_fields_by_name($name, $value);
  }

  /**
   * Processes data from the association field
   * @param string   $name  Field name
   * @param array $value Field value
   */
  protected function process_hoa_info(string $name, ?array $value): void {
    $this->process_fields_by_name($name, $value, "hoa-");
  }

  /**
   * Processes the photos field. Photos are outputted as a list of
   * <img> elements, with no other elements in between. Each img element
   * has the class vista-listing-photo.
   * Also adds the field 'first-photo', which simply contains the first img element in the 'photos' field
   * @param string   $name  Field name
   * @param array    $value Field value
   */
  protected function process_photos(string $name, ?array $value): void {
    // Process 10 photos as shortcode if possible
    $photos_fields = array(
      'first-photo',
      'second-photo',
      'third-photo',
      'fourth-photo',
      'fifth-photo',
      'sixth-photo',
      'seventh-photo',
      'eighth-photo',
      'ninth-photo',
      'tenth-photo',
    );
    
    // Handle null photos
    if (is_null($value)) {
      $this->try_process_field('photos', NULL);

      return;
    }
    
    $photo_urls = $value;    
    for ($index=0, $count = count($photos_fields); $index < $count; $index++) {
      // Defines the #-photo <img> shortcode
      $this->try_process_field(
        $photos_fields[$index],
        isset($photo_urls[$index]) ? "<img class='vista-lead-photo' src='". esc_url($photo_urls[$index]) ."' alt='Property cover photo' loading='lazy'> " : NULL
      );
      
      // Defines the #-photo-url shortcode
      $this->try_process_field(
        $photos_fields[$index] . '-url',
        isset($photo_urls[$index]) ? esc_url($photo_urls[$index]) : NULL
      );
      
      // Defines the #-photo-url-non-protocol shortcode
      $this->try_process_field(
        $photos_fields[$index] . '-url-non-protocol',
        isset($photo_urls[$index]) ? str_replace('https://', '', esc_url($photo_urls[$index])) : NULL
      );
    }

    // Set slideshow
    $slideshow = '';

    // Loops through the slideshow and adds `<img>` elements to the `$fields` array
    foreach ($value as $photo) {
      $display_default_slide = '';
      if (empty($slideshow)) {
        $display_default_slide = 'vista-display-default-slide';
      }

      $slideshow .= "<div class='vista-slide-item vista-display-fade {$display_default_slide}'><img class='vista-listing-photo' src='". esc_url($photo) ."' alt='Property photo' loading='lazy'></div>";
    }

    // Create a container for the slideshow and add previous/next buttons
    $slideshow_container = <<<EOD
<div id='vista-slide-number'></div>
<div class='vista-slideshow-container'>
   %s
  <a class='vista-grid-prev' onclick='plusSlides(-1)'>&#10094;</a>
  <a class='vista-grid-next' onclick='plusSlides(1)'>&#10095;</a>
</div>
EOD;

    $this->try_process_field(
      'photos',
      sprintf(
        $slideshow_container,
        $slideshow
      )
    );
  }

  /**
   * Process the virtualTourUrl field into an unstyled <a> element with class vista-tour-button
   * that links to the virtual tour URL
   * If no URL is provided for this listing, returns a "No virtual tour available" message
   * @param string $name  Field name (virtualTourUrl)
   * @param array $url   Field value (tour url). Null if no tour available
   */
  protected function process_tour(string $name, ?string $url): void {
    if (filter_var($url, FILTER_VALIDATE_URL))
      $this->try_process_field('virtualTourUrl', "<a class='vista-tour-button' href='$url'>Virtual Tour</a>");
    else
      $this->try_process_field('virtualTourUrl', "No virtual tour available");
  }
}