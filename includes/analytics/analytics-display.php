<?php
namespace VSTA\Analytics;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use \VSTA\API as API;

/**
 * For displaying analytics from the SimplyRETS API
 *
 * @author Nate Lanza
 */
class Analytics_Display extends \VSTA\Single_Display {

  /**
   * Message to display if an API error occurs
   * @var string
   */
  protected const API_ERR_MSG = "<p>Unable to retrieve analytics</p>";

  /**
   * Interface for getting parameters from the API
   * @var Listing_Parameters
   */
  private ?API\Listing_Parameters $params;

  /**
   * Registers shortcodes
   * Marked protected to enforce the singleton design pattern.
   */
  protected function __construct() {
    parent::__construct();
    $this->params = new API\Get_Analytics_Params();
    add_shortcode("vista_analytics_field", array($this, 'data_field'));
  }

  /**
   * OVERRIDE
   * Loads data for an analytics query
   * Sets $this->api_data to an Analytics object populated with api data,
   * or a string error message if an error occurs
   */
  protected function load_api_data(): void {
    // Set up API call
    $api_call = new API\RETS_API(API\RETS_API::CALL_TYPES["ANALYTICS"]);

    // Add parameters
    foreach ($this->params->get_params() as $key => $value) {
      if ( !is_null($key) && !is_null($value) )
        $api_call->add_param($key, $value);
    }

    // Make API call
    try {
      $api_call->get_response();
    } catch (\Exception $e) {
      throw new \Exception(self::API_ERR_MSG);
    }

    // Pass off displaying individual fields to Analytics
    $this->api_data = new Analytics($api_call->get_response());
  }

}




 ?>
