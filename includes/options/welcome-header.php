<?php

namespace VSTA\Options;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
* Display the welcome header in the WordPress admin area
*/
class Welcome_Header {
  
  /**
   * Create or return singleton instance of child class
   * @return Welcome_Header Instance of child class
   */
  public static function get_instance(): Welcome_Header {
    static $instance;

    return $instance ??= new self();
  }
  
  /**
  * Creates a new instance of this class
  * Marked protected to enforce the singleton design pattern
  */
  public function __construct() {
    $main = \VSTA\Main::get_instance();
    // Enqueue the 'welcome-styles' CSS file for the plugin
    $main->register_style('welcome-styles', \vista_plugin_url('/css/welcome.css')); 
    $main->enqueue_style('welcome-styles');
    
    // Checks if the plugin is activated and runs a function to see if the welcome header has been displayed
    \add_action('admin_notices', array($this, 'start_header'), 99);

    // Runs a function to reset the welcome header when the plugin is deactivated
    // deactivate hook has to be run from the main php file 
    \register_deactivation_hook($main->get_vista_file(), array($this, 'reset_header'));
  }

  /**
   * Resets the welcome header when the plugin is deactivated
   */
  public function reset_header() {
    delete_option('vistawp_welcome_header_displayed');
  }
  
  /**
  * Starts the header display process.
  *
  * If the welcome header has not been displayed, it will
  * instantiate the Welcome_Header class and call the display_welcome_header() method
  * to show the welcome header. After displaying the header, it will set the option to
  * indicate that the welcome header has been displayed.
  *
  **/
  public function start_header() {
    // Check if the Vista plugin has an active class and the welcome header has not been displayed
    if(!get_option('vistawp_welcome_header_displayed')){      
      // Call the display_welcome_header() method
      $this->display_welcome_header();
      
      // Set the option to indicate that the welcome header has been displayed
      update_option('vistawp_welcome_header_displayed', true);
    }
  }
  
  /**
  * Displays the welcome header
  */
  private function display_welcome_header() {
    $notification = Notifications_Banner::create('success', '', 'welcome');
    $notification->display_notification();
  }
}