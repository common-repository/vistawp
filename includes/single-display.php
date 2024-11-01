<?php
namespace VSTA;

use \VSTA\API;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Provides a template for displaying single results from the SimplyRETS API,
 * such as single listings and analytics
 *
 * @author Nate Lanza
 * @version 1.0
 */
abstract class Single_Display {

  /**
   * Contains api data, as an object, or error message as a string,
   * or NULL if the API has not been called
   * Only initialized by load_api_data
   * @var API_Data|string|null
   */
  protected $api_data = NULL;

  /**
   * Singleton instances of child classes
   * @var array
   */
  protected static array $instances = array();

  /**
   * Loads data for a single MLS listing by calling the SimplyRETS API
   * Sets $this->api_data to an API_Data child class,
   * or throws an exception if an error occurs.
   */
  abstract protected function load_api_data(): void;

  /**
  * Creates or returns a single instance of child class
  *
  * @return Single_Display A single instance of this class
  */
  public static function get_instance(): Single_Display {
    $child = get_called_class();
    if (!isset(self::$instances[$child])) {
      self::$instances[$child] = new $child();
    }

    return self::$instances[$child];
  }

  /**
   * Marked protected to enforce the singleton design pattern.
   */
  protected function __construct() {
  }

  /**
   * Ensure that the API has been called with load_api_data and handles exceptions
   * @return string Exception message if one occurred, or null if none occurred
   */
  protected final function ensure_api(): ?string {
    // Load data if not already loaded
    if ( is_null($this->api_data) ) {
      try {
        $this->load_api_data();
      } catch (\Exception $e) {
        $this->api_data = $e->getMessage();
        return $e->getMessage();
      }
    // Show error message if error occurred while trying to load in previous shortcode
    } else if ( is_string($this->api_data) ) {
      return $this->api_data;
    }

    // Nothing wrong
    return NULL;
  }

  /**
   * Retrieves data for the listing to be displayed on this page
   * if said data hasn't already been retrieved.
   * Also displays a listing field on the page
   * @param  array  $atts 'field' should be set- this defines which MLS field to display
   * @return string       HTML of field
   */
  public function data_field($atts): string {
    // Error handling
    $err = $this->ensure_api();
    if (!is_null($err))
      return $err;

    // Display listing
    return $this->api_data->display_field($atts);
  }
}

?>
