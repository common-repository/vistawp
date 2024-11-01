<?php
namespace VSTA;

defined('ABSPATH') || exit;

use \VSTA\API as API;

/**
 * Abstract class for displaying multiple api objects
 * Child classes must:
 *  - Implement load_api_data, which populates $api_data and $api_headers
 *  - Initialize $params in the constructor
 *  - Call the parent constructor from child constructor with a unique prefix for their shortcodes
 */
abstract class Multiple_Display {

  /**
   * Default number of objects to display per page
   * This is provided as the default 'limit' parameter to the API
   * @var int
   */
  protected const DEFAULT_LIMIT = 20;

  /**
   * Message displayed by url_querystring/the vista_link_query shortcode
   * when the 'page' shortcode attribute is not set
   * @var string
   */
  protected const NO_PAGE_PARAM = "<p>ERROR: 'Page' shortcode attribute must be set.</p>";

  /**
   * Error message displayed by pagination_button and its child shortcodes when the
   * type attribute is not set or is set incorrectly
   * @var string
   */
  protected const NO_TYPE_PARAM = "<p>You must set type=forward or type=back for this pagination button to work</p>";

  /**
   * Populates the array of api_data objects in $this->api_data.
   * Additionally, populates api headers in $this->api_headers
   * Can throw exceptions if one occurs while retreiving API data;
   * the exception message is echoed onto the page by shortcodes
   */
  abstract protected function load_api_data(): void;

  /**
   * Array of API_Data (single child class) objects
   * retrieved from the API
   * @var array<API_Data>
   */
  protected array $api_data = array();

  /**
   * Array of headers returned by the API
   * @var array<string>
   */
  protected array $api_headers = array();

  /**
   * Error message returned by API or NULL if no error
   * @var string
   */
  protected ?string $err;

  /**
   * Interface for getting parameters from the API
   * Should be initialized in constructor of child class
   * @var Listing_Parameters
   */
  protected API\Listing_Parameters $params;

  /**
   * Singleton instances of child classes
   * @var array
   */
  protected static array $instances = array();

  /**
   * Create or return singleton instance of child class
   * @return Multiple_Display Instance of child class
   */
  public static function get_instance(): Multiple_Display {
    $child = get_called_class();
    if (!isset(self::$instances[$child])) {
      self::$instances[$child] = new $child();
    }

    return self::$instances[$child];
  }

  /**
   * Constructor. Contains a bug fix and shortcodes
   * @param string $prefix Prefix to be added after vista_ to all shortcodes
   *                       This prefix prevents children overwriting each other's shortcodes
   */
  protected function __construct(string $prefix) {
    add_shortcode("vista_{$prefix}_paginator", array($this, 'pagination_button'));
    add_shortcode("vista_link_query", array('\\VSTA\\Multiple_Display', 'url_querystring'));
    add_shortcode("vista_{$prefix}_total", array($this, 'total_listings'));
    add_shortcode("vista_{$prefix}_list", array($this, 'display_listings'));
    $this->err = NULL;
    
    // BUG FIX
    // I have no idea what causes this bug or why this fix works. The stack trace does not include this plugin,
    // but the bug only occurs when $_GET['limit'] > 72 (or less, depending on other $_GET params, which I don't understand),
    // and we use $_GET['limit'] as intended in this plugin. This fix diables automatic conversion of smiley faces in text
    // to the corresponding emoji and that fixes the bug. Go figure
    if (isset($_GET['limit']) && intval($_GET['limit']) > 50) // Sanitization unnecessary as field is only checked for intval
      update_option( 'use_smilies', false );
  }

  /**
   * Outputs a link to another page with the current page's query string appended
   * Secures against invalid chars/XSS attacks in the query string, 
   * by only allowing the chars [A-Za-z0-9 ,&=.?%+] to pass
   * @param mixed  $atts    Shortcode attributes. "page" should be set to the slug of the page to link to
   * @param string $content Content of the <a> element. Can be anything
   * @return string <a href="$atts['page']?param=value&param2=value2">$content</a>,
   *                or empty string if no params are set
   */
  public static final function url_querystring($atts, string $content): string {
    // Ensure we have a page shortcode attribute
    if (!isset($atts['page']))
      return self::NO_PAGE_PARAM;
    
      // Only allow [A-Za-z0-9 ,&=?%+] chars in validated string
    $validated = preg_replace("/[^A-Za-z0-9 ,&=?%+]/", '', $_SERVER['QUERY_STRING']);
    
    // Construct returned <a> element
    $query = $validated ? "?" . $validated : "";
    return "<a href='{$atts['page']}$query'>$content</a>";
  }

  /**
   * Outputs HTML which displays a button that paginates returned listings
   * Uses 'limit' and 'offset' parameters to track pagination. Either may be set before this page is displayed.
   * Offset defaults to 0, limit defaults to self::DEFAULT_LIMIT
   * Takes the following attributes in $atts:
   *   type: 'forward' or 'backward', forward links to the next page & backward to the prev page
   * This attribute is required.
   * Two copies of the shortcode should be used to create both a forward & backward button
   * The text content of the button is set by the shortcode content.
   * The button has css class 'vista-listings-paginator'
   * The forward button also has class 'listings-forward', and the backward button 'listings-backward'
   * @param  mixed  $atts     Attributes. Only 'type' is used
   * @param  string $content  Shortcode content, which is displayed inside the button
   * @return string           HTML for the button
   */
  public function pagination_button($atts, string $content): string {
    // Validate attributes
    if (!is_array($atts) || !($atts['type'] == 'forward' || $atts['type'] == 'backward'))
      return self::NO_TYPE_PARAM;

    // Make sure api is called
    $err = $this->ensure_api();
    if ($err)
      return $err;

    // Initialize parameters
    $disabled = ''; // Whether the button is disabled because we have no more listings in this direction
    $link = \get_page_link();
    $listing_count = (int) $this->api_headers['X-Total-Count'][0];
    $offset = intval($_GET['offset'] ?? 0); // Default offset is 0 as this is the start of the list
    $limit = intval($_GET['limit'] ?? self::DEFAULT_LIMIT); // No sanitization here or prev line as only intval is used
    $class = 'vista-listings-paginator ';

    // Assign button params based on type
    if ($atts['type'] == 'forward') {
      $class .= "listings-forward";
      $remainder = $listing_count - ($offset + $limit);
      if ($remainder <= 0)
        $disabled = 'disabled'; // Disable if we can't go further
      $link .= "?offset=" . strval($limit + $offset);
      if ($remainder < $limit) {
        $link .= "&limit=$remainder";
      } else {
        $link .= "&limit=$limit";
      }
    } else {
      $class .= "listings-backward";
      if ($offset == 0)
        $disabled = 'disabled'; // Disable if we can't go further
      $link .= "?offset=" . ($offset - $limit <= 0 ? 0 : $offset - $limit);
      // Only the last page can have <self::DEFAULT_LIMIT results,
      // so previous pages always have self::DEFAULT_LIMIT results
      $link .= "&limit=" . self::DEFAULT_LIMIT;
    }
    // Add other parameters
    foreach ($_GET as $param => $value) {
      // Sanitize variables
      $param = \sanitize_text_field($param);
      $value = \sanitize_text_field($value);
      if ($param == 'offset' || $param == 'limit') continue; // We've already recaclulated & included offset & limit
      $link .= "&$param=$value";
    }

    return "<button class='$class' onclick=\"window.location.href='$link'\" $disabled>$content</button>";
  }

  /**
   * Displays the total number of listings
   * @return string Number of listings, no html
   */
  public function total_listings(): string {
    // Make sure api is called
    if ($this->ensure_api())
      return $this->ensure_api();

    return $this->api_headers['X-Total-Count'][0] ?? '0';
  }

  /**
   * Ensures that the SimplyRETS API has been called
   * and results are available in $this->listings
   * @return string "" if successful, or an error message
   */
  protected final function ensure_api(): string {
    // If an error has been returned by the API, return that message
    if (!is_null($this->err))
      return $this->err;
    else if ($this->api_headers)
      return ""; // If we've already called the API
    // Get listings from API
    try {
      $this->load_api_data();
    } catch (\Exception $e) {
      $this->err = $e->getMessage();
      return $this->err;
    }
    return "";
  }

  /**
   * Outputs HTML on the page for displaying multiple listings.
   * The content of the shortcode is used to determine how to display
   * listings. Shortcode content is repeated for each listing.
   * Shortcode content should contain shortcodes named for listing fields,
   * which are replaced with the value of that field in each listing
   * @param  array  $atts    Shortcode attributes
   * @param  string $content Shortcode content
   * @return string          Display HTML
   */
  public function display_listings($atts, string $content): string {
    // Make sure api is called
    $err = $this->ensure_api();
    if ($err)
      return $err;

    // To store output
    $result = "";

    // Replace shortcodes in each listing & append to result
    foreach($this->api_data as $listing) {
      $result .= $this->replace_shortcodes($content, $listing);
    }

    return $result;
  }

  /**
   * Replaces all shortcodes in an HTML string with their corresponding value from an mls listing
   * Each shortcode must simply be the name of the listing field encased in brackets.
   * Field names are specified in class Single_Listing
   * @param  string   $input  String to have shortcodes replaced
   * @param  API_Data $fields API_Data object to get field values, used to replace shortcodes
   * @return string                 $input with shortcodes swapped for field values in $fields
   */
  protected final function replace_shortcodes(string $input, API_Data $fields): string {
    // Split input by closing brackets.
    // We also need to decode HTML entities in the input string,
    // as WP likes to replace some characters that we don't want replaced
    $tokens = explode(']', html_entity_decode($input));

    // To store output
    $result = "";

    // Parse tokens & replace shortcodes
    foreach ($tokens as $token) {
      // Check if this token contains a shortcode
      if (\str_contains($token, '[')) {
        $split = explode('[', $token);
        $result .= $split[0];
        $result .= $fields->get_field($split[1]);
        if (count($split) > 2) {
          $result .= "ERROR: Extra [ before {$split[2]}";
        }
      } else {
        $result .= $token;
      }
    }

    return $result;
  }

}



 ?>
