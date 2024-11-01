<?php
namespace VSTA\Openhouses;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use \VSTA\Listings as L;
/**
 * Represents a single open house, retrieved from the API
 */
class Open_House extends \VSTA\API_Data {

  /**
   * Message to display when $this->timezone is not a valid timezone
   * @var string
   */
  protected const BAD_TZ_MSG = "Timezone not recognized";

  /**
   * The listing that this openhouse is for
   * Data for this is returned by the API
   * @var Single_Listing
   */
  protected ?L\Single_Listing $listing;

  /**
   * Timezone to output datetime fields in
   * If null, UTC is used
   * @var string
   */
  protected ?string $timezone;

  /**
   * Initializes mappings to process fields
   */
  protected function init_mappings(): void {
    $this->mappings = array(
      "listing" => array($this, 'listing_data'),
      "startTime" => array($this, 'convert_time'),
      "endTime" => array($this, 'convert_time'),
    );
    $this->number_fields = array(

    );
  }

  /**
   * Constructor
   * @param array $api_data    Deserialized single open house data
   *                           from the API
   * @param int   $time_offset Time zone to use for datetime fields.
   *                           If not set, the wp timezone is used
   */
  public function __construct(array $api_data, ?string $timezone = NULL) {
    $this->timezone = $timezone ?? \wp_timezone_string();
    parent::__construct($api_data);
  }

  /**
   * Designed to be called by a shortcode which should display listing fields
   * Overrides parent; but has same function except calling child's get_field
   * @param  mixed  $atts Shortcode attributes. 'field' should be set, and determines which
   *                      field of the listing to display
   * @return string       Field to display, as HTML, or error message if $atts['field'] is not set
   */
  public function display_field($atts): string {
    // Validate args
    if (!is_array($atts) || !isset($atts['field'])) {
      return self::NO_FIELD_MSG;
    }

    return $this->get_field($atts['field']);
  }

  /**
   * Returns the value of a stored field for display
   * Designed to be called by a shortcode
   * Overrides parent func
   * @param  mixed $atts Shortcode attributes. 'field' should be set, and determines which
   *                     field of the listing to display
   * @return string      Field value, or the default error message
   */
  public function get_field(?string $field): string {
    $field = strtolower($field);
    if (is_null($field))
      return parent::NULL_MSG;
    if (isset( $this->fields[$field] )) {
      return $this->fields[$field];
    } else if (isset($this->listing)) {
      return $this->listing->get_field($field);
    } else {
      return parent::ERROR;
    }
  }

  /**
   * Converts a datetime field to a readable format and stores by name
   * @param string  $name   Field name
   * @param string  $value  Field value (Y-m-d\TH:i:s.vP)
   */
  protected function convert_time(string $name, ?string $value): void {
    // Create datetime object & check for error
    $datetime = \DateTime::createFromFormat(\DateTimeInterface::RFC3339, $value);
    if ($datetime === false) {
      $datetime = \DateTime::createFromFormat(\DateTimeInterface::RFC3339_EXTENDED, $value);
      if ($datetime === false) {
        $this->try_process_field($name, $value);
        return;
      }
    }

    // Convert timezone
    if (!is_null($this->timezone))
      try {
        $datetime->setTimezone(new \DateTimeZone($this->timezone));
      } catch (\Exception $e) {
        $this->try_process_field($name, self::BAD_TZ_MSG);
        return;
      }

    $this->try_process_field($name, $datetime->format("M j, Y: g:i A"));
  }

  /**
   * Processes all the listing data into $this->listing
   * @param string $name   Field name (listing)
   * @param array $value   Field value (listing data)
   */
  protected function listing_data(string $name, ?array $value): void {
    if (!is_array($value))
      return;

    $this->listing = new L\Single_Listing($value);
  }



}




 ?>
