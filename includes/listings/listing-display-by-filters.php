<?php

namespace VSTA\Listings;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use \VSTA as V;
use \VSTA\API as API;

/**
 * Responsible for constructing the shortcode that queries a 
 * listing based on the parameters passed through the shortcode
 *
 * @package Listing
 */
class Listing_Display_By_Filters extends V\Multiple_Display {

  /**
   * Error message to display when an API error occurs
   * @var string
   */
  protected const API_ERR_MSG = "<p>Unable to retrieve listing</p>";
  
  /**
   * Exception message for frontend display when no listings are returned by the API
   * @var string
   */
  protected const NO_LISTINGS_MSG = "No listings matched your query";
  
  /**
   * Class variable that contains the query parameters through the shortcode
   * @var array
   */
  protected array $args = array();
  
  /**
   * Constructor of the Listing_Display_By_Filters class
   */
  protected function __construct() {
    add_shortcode( 'vista_listing_filter', array( $this, 'listing_by_filter' ) );
    $this->err = NULL;
  }
  
  /**
   * Filter the listing results by query parameters
   * Through this function, it's possible to pass query parameters to the shortcode and generate more customized listings. 
   * Example: [vista_listing_filter listing_id=xxxxxx ...]
   * And within this, other shortcodes are added to determine the content of each record
   * 
   * @param Array $atts      - Array of attributes provided by the shortcode 
   * @param string $content  - String containing the content of the shortcode (optional)
   * 
   * @return string $result  - String containing the result of the requested shortcodes
   */
  public function listing_by_filter(array $atts, string $content = ''): string {
    // params by default empty value
    $params = array_fill_keys(
      array_keys(
        API\Get_Listing_Params::get_param_list()
      ),
      ''
    );
    
    // Validate the default fields
    $atts = \shortcode_atts(
      $params,
      $atts,
    );

    foreach ($atts as $att => $value) {
      // Prepare field values and sanitize them
      $this->args[ $att ] = \vista_prepare_field_value($value);
    }

    $err = $this->ensure_api();
    if ($err) {
      return $err;
    }

    $result = '';
    foreach($this->api_data as $listing) {
      $result .= $this->replace_shortcodes($content, $listing);
    }

    return $result;
  }
  
  /**
   * Populates $api_data and $api_headers with data from the api
   * Exceptions from the API call are passed through
   */
  protected function load_api_data(): void {
    // Set up API call
    $api_call = new API\RETS_API(API\RETS_API::CALL_TYPES[ 'PROPERTY_LISTINGS' ]);

    // Set search parameters
    foreach ($this->args as $arg_key => $arg_value) {
      array_walk($this->args[ $arg_key ], function($value) use ($api_call, $arg_key) {
        if ('listing_ids' == $arg_key || 'mls_area' == $arg_key) {
          $api_call->add_param('q', $value);
        } else {
          // If it's not a special case, it is added by default
          $api_call->add_param(
            $arg_key,
            $value
          );
        }
      });
    }

    // Try api call and validate data
    $data = $api_call->get_response();
    if (!is_array($data) || count($data) < 1)
      throw new \Exception(self::NO_LISTINGS_MSG);

    // Store all returned listings
    foreach ($data as $listing) {
      $this->api_data[] = new Single_Listing($listing);
    }

    $this->api_headers = $api_call->get_headers();
  }
}