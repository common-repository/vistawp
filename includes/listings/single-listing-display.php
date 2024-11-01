<?php

namespace VSTA\Listings;

use \VSTA\API as API;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Registers single listing shortcodes & implements other functionality
 * for displaying single listings
 *
 * @author Nate Lanza
 */
class Single_Listing_Display extends \VSTA\Single_Display {

  /**
   * Error message to display when the 'listing' GET param is not set
   * @var string
   */
  protected const PARAM_ERR_MSG = "<p>Listing MLS ID must be in the URL parameter 'listing'</p>";

  /**
   * Error message to display when an API error occurs
   * @var string
   */
  protected const API_ERR_MSG = "<p>Unable to retrieve listing</p>";

  /**
   * Registers shortcodes
   * Marked protected to enforce the singleton design pattern.
   */
  protected function __construct() {
    parent::__construct();
    add_shortcode('vista_listing_field', array($this, 'data_field'));
    \VSTA\Main::get_instance()->register_style('slideshow-styles', \vista_plugin_url('/css/slideshow.css'));  
    \VSTA\Main::get_instance()->register_script('slideshow-js', \vista_plugin_url('js/slideshow.js'));  
  }

  /**
   * Loads data for a single MLS listing
   * Sets $this->api_data to a Single_Listing object populated with api data,
   * or a string error message if an error occurs
   */
  protected function load_api_data(): void {
    if ( !isset($_GET['listing']) ) {
      throw new \Exception(self::PARAM_ERR_MSG);
    }

    // Set up API call
    $api_call = new API\RETS_API(
      API\RETS_API::CALL_TYPES["SINGLE_PROPERTY"],
      \sanitize_text_field($_GET['listing'])
    );
    // Include rooms
    $api_call->add_param("include", "rooms");

    // Make API call
    try {
      $api_call->get_response();
    } catch (\Exception $e) {
      throw new \Exception(self::API_ERR_MSG);
    }

    // Make sure necessary scripts & styles are enqueued
  \VSTA\Main::get_instance()->enqueue_style('slideshow-styles');
    \VSTA\Main::get_instance()->enqueue_script('slideshow-js');

    // Pass off displaying individual fields to Single_Listing
    $this->api_data = new Single_Listing($api_call->get_response());
  }
}

?>