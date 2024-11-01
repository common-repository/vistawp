<?php

namespace VSTA\Listings;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use \VSTA as V;
use \VSTA\API as API;

/**
 * Handles displaying multiple listings, mainly by registering the [mls_listings] shortcode
 *
 * @author Nate Lanza
 */
class Multiple_Listings_Display extends V\Multiple_Display {

  /**
   * Exception message for frontend display when no listings are returned by the API
   * @var string
   */
  protected const NO_LISTINGS_MSG = "No listings matched your query";

  /**
   * Creates a new instance of this class
   * Marked protected to enforce the singleton design pattern
   */
  protected function __construct() {
    parent::__construct('listings');
    $this->params = new API\Get_Listing_Params();
    // Register scripts and styles for slideshow
  \VSTA\Main::get_instance()->register_style('slideshow-styles', \vista_plugin_url('/css/slideshow.css' ));  
  \VSTA\Main::get_instance()->register_script('slideshow-js', \vista_plugin_url('/js/slideshow.js'));

  }

  /**
   * Populates $api_data and $api_headers with data from the api
   * Exceptions from the API call are passed through
   */
  protected function load_api_data(): void {
    // Set up API call
    $api_call = new API\RETS_API(API\RETS_API::CALL_TYPES["PROPERTY_LISTINGS"]);

    // Add parameters
    foreach ($this->params->get_params() as $key => $value) {
      if ( !is_null($key) && !is_null($value) )
        $api_call->add_param($key, $value);
    }
    // Required for pagination
    $api_call->add_param("count", "true");
    if ( !array_key_exists('limit', $this->params->get_params()) ) {
      $api_call->add_param('limit', parent::DEFAULT_LIMIT);
    }

    // Try api call and validate data
    $data = $api_call->get_response();
    if (!is_array($data) || count($data) < 1)
      throw new \Exception(self::NO_LISTINGS_MSG);

    // Make sure slideshow scripts & styles are enqueued
    \VSTA\Main::get_instance()->enqueue_style('slideshow-styles');
    \VSTA\Main::get_instance()->enqueue_script('slideshow-js');

    // Store all returned listings
    foreach ($data as $listing) {
      $this->api_data[] = new Single_Listing($listing);
    }
    $this->api_headers = $api_call->get_headers();
  }
}

 ?>