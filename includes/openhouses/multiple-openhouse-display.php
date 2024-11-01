<?php

namespace VSTA\Openhouses;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use \VSTA as V;
use \VSTA\API as API;

/**
 * Handles displaying multiple listings, mainly by registering the [mls_listings] shortcode
 *
 * @author Nate Lanza
 */
class Multiple_Openhouse_Display extends V\Multiple_Display {

  /**
   * Exception message for frontend display when no open houses match a query
   * @var string
   */
  protected const NO_RESULTS_MSG = "No open houses matched your query";

  /**
   * Creates a new instance of this class
   * Marked protected to enforce the singleton design pattern
   */
  protected function __construct() {
    parent::__construct('openhouse');
    $this->params = new API\Get_Openhouse_Params();
  }

  /**
   * Populates $api_data and $api_headers with data from the api
   * Exceptions from the API call are passed through
   */
  protected function load_api_data(): void {
    // Set up API call
    $api_call = new API\RETS_API(API\RETS_API::CALL_TYPES["OPEN_HOUSES"]);

    // Add parameters
    $params = $this->params->get_params();
    foreach ($params as $key => $value) {
      if ( !is_null($key) && !is_null($value) )
        $api_call->add_param($key, $value);
    }
    // Required for accurate pagination
    $api_call->add_param('count', 'true');
    if ( !array_key_exists('limit', $params) ) {
      $api_call->add_param('limit', parent::DEFAULT_LIMIT);
    }


    // Try api call and validate data
    $data = $api_call->get_response();
    if (!is_array($data) || count($data) < 1)
      throw new \Exception(self::NO_RESULTS_MSG);

    // Store all returned listings
    foreach ($data as $listing) {
      $this->api_data[] = new Open_House($listing);
    }
    $this->api_headers = $api_call->get_headers();
  }
}

 ?>
