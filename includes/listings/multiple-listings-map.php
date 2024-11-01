<?php

namespace VSTA\Listings;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use \VSTA\API as API;


/**
 * A class which can display multiple listings on a map
 * Extends the functionality of Multiple_Listings_Display.
 * Since Multiple_Listings_Display is a singleton, so is this class.
 * One class cannot be instantiated if the other has already been.
 *
 * @author Nate Lanza
 */
class Map extends Multiple_Listings_Display {

  /**
   * True if $this->display_map has been called.
   * Prevents multiple calls to display_map, to avoid multiple maps on one page
   * Initialized to false
   * @var bool
   */
  protected bool $called;

  /**
   * Image to display for 2nd and further instances of the [vista_listings_map] shortcode
   * on a page. This shortcode is limited to one per page.
   * @var string
   */
  protected const ONE_MAP_MSG = "<p>Cannot display more than one map per page</p>";

  /**
   * Marked protected to enforce singleton design pattern
   */
  protected function __construct() {
    parent::__construct();
    add_shortcode('vista_listings_map', array($this, 'display_map'));

    // Register styles
    \VSTA\Main::get_instance()->register_style('vsta_leaflet', \vista_plugin_url('/css/leaflet.css'));
    
    \VSTA\Main::get_instance()->register_style('vsta_map_styles', \vista_plugin_url('/css/listing-map.css'));
    

    // Register scripts- some are external, the last script depends on them
    \VSTA\Main::get_instance()->register_script('vsta_leaflet', \vista_plugin_url('js/leaflet.js'));
    

    \VSTA\Main::get_instance()->register_script(
      'vsta_listing_map',
      \vista_plugin_url('/js/map.js'),
      array('vsta_leaflet', 'jquery')
    );
    
    
    
    // A specific css file breaks the map, we need to filter it out
    add_filter( 'style_loader_src', function($href) {
      if (\str_contains($href, 'http://www.idxhome.com/service/resources/dist/wordpress/bundle.css')) {
        return false;
      }

      return $href;
    });

    $this->called = false;
  }

  /**
   * Displays multiple listings as a list & map
   * Called by the [vista_listings_map] shortcode
   * The content of the shortcode is used to determine how to display
   * selected listings under the map.
   * @param  array  $atts    Shortcode attributes
   * @param  string $content Shortcode content
   *
   * @return string HTML
   */
  public final function display_map($atts, string $content): string {
    if ($this->called)
      return self::ONE_MAP_MSG;

    // Make sure api is called
    if ($this->ensure_api())
      return $this->ensure_api();

    // To store output
    $result = "";

    // Encode listings for JS
    $listings = array();
    foreach ($this->api_data as $listing) {
      $listing_array = $listing->get_fields();
      // Remove html fields
      $listings[] = $listing_array;
    }
    $result .= "<span id='vsta-listing-data' style='display: none;' data-listings='" . esc_attr(json_encode($listings)) . "'></span>";

    // Create map container
    $result .= "<div id='vsta-listing-map'></div>";
    // Create container to display listing info
    $result .= "<div id='vsta-listing-map-info'>" . $this->fields_to_spans($content) . "</div>";

    // Enqueue map script & styles
    \VSTA\Main::get_instance()->enqueue_script('vsta_listing_map');
    \VSTA\Main::get_instance()->enqueue_style('vsta_leaflet');
    \VSTA\Main::get_instance()->enqueue_style('vsta_map_styles');

    $this->called = true;
    return $result;
  }

  /**
   * Replaces shortcodes (text wrapped in [] brackets) with <span> elements.
   * Each span element has id vsta-map-info-[text], where [text] is the shortcode tag with no brackets
   * Each span element has class vsta-map-field.
   * The vsta_listing_map script fills each span with info from a listing
   * @param  string $html  text to replace shortcodes in
   * @return string        $html with shortcodes replaced by <span> elements
   */
  protected final function fields_to_spans(string $html): string {
    // Split input by closing brackets.
    // We also need to decode HTML entities in the input string,
    // as WP likes to replace some characters that we don't want replaced
    $tokens = explode(']', html_entity_decode($html));

    // To store output
    $result = "";

    // Parse tokens & replace shortcodes
    foreach ($tokens as $token) {
      // Check if this token contains a shortcode
      if (\str_contains($token, '[')) {
        $split = explode('[', $token);
        $result .= $split[0];
        $result .= "<span class='vsta-map-field' id='vsta-map-info-{$split[1]}'></span>";
      } else {
        $result .= $token;
      }
    }

    return $result;
  }


}








 ?>