<?php

namespace VSTA\Listings;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class responsible for constructing listings using simple shortcodes.
 *
 * @package Listings
 */
class Simple_Listings {
  
  /**
   * Singleton instances of child classes
   * @var array
   */
  protected static array $instances = array();
  
  /**
   * Create or return singleton instance of child class
   * @return Simple_Listings Instance of child class
   */
  public static function get_instance(): Simple_Listings {
    static $instance;

    return $instance ??= new self();
  }

  /**
   * Constructor of the Simple_Listings class
   */
  protected function __construct() {
    add_shortcode('vista_simple_listings', array($this, 'simple_listings_builder'));

    // Register the 'simple-listings-styles' stylesheet with the Main class of the VSTA namespace
    $this->enqueue_styles();
  }

  /**
   * Builds and renders simple listings with shortcodes.
   * 
   * Description: defines a shortcode that contains the shortcodes of [vista_listings_list] and prints them using the do_shortcode() function.
   * It is possible to pass attribute parameters to filter themes or elements of the listings.
   * 
   * Example: [vista_simple_listings theme="dark" ...]
   * 
   * @param mixed $atts      - Array of attributes provided by the shortcode (optional)
   * 
   * Attributes accepted:
   * 
   * - theme: Determines the appearance of the listing. Valid values are "light" or "dark"
   * 
   * - dest: The slug or relative URL of the property detail page. Requires a slash at the beginning and end.
   * 
   * - pagination: Whether to show pagination options. Valid values are "yes" or "no"
   * 
   * @param string $content  - String containing the content of the shortcode (optional)
   * 
   * @return string          - The rendered HTML output of the simple listings with shortcodes processed.
   */
  public function simple_listings_builder($atts, string $content = ''): string {
    $default_atts = array(
      'theme' => 'light',
      'dest' => '/individual-listing/',
      'pagination' => 'yes',
    );

    // Validate the default fields
    $atts = \shortcode_atts(
      $default_atts,
      $atts,
    );
    
    // Checks if the string starts with a slash
    if (substr($atts['dest'], 0, 1) !== '/') {
      $atts['dest'] = '/' . $atts['dest'];
    }

    // Add slash to end if not exists
    $atts['dest'] = \trailingslashit($atts['dest']);

    ob_start();
      \vista_get_template(
        "shortcodes/simple-listings.php",
        array(
          'theme' => \esc_attr($atts['theme']),
          'dest' => \esc_url($atts['dest']),
          'pagination' => \sanitize_text_field($atts['pagination']),
        ),
      );

    $listings_shortcodes = ob_get_clean();
    
    return \do_shortcode($listings_shortcodes);
  }

  /**
   * Enqueue the 'simple-listings-styles' stylesheet on the front-end.
   */
  private function enqueue_styles() {
    \VSTA\Main::get_instance()->register_style('simple-listings-styles', \vista_plugin_url('/css/simple-listings.css'));
    \VSTA\Main::get_instance()->enqueue_style('simple-listings-styles');
  }
}