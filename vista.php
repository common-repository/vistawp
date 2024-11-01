<?php
/**
* Plugin Name: VistaWP
* Description: Retrieves and displays real estate listings
* Version: 1.4.1
* Author: VistaWP
* Author URI: https://vistawp.com/
* License: GPL2
*/

namespace VSTA;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// general constants
define( 'VISTA__PLUGIN_VERSION', '1.4.1' );
define( 'VISTA__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VISTA__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'VISTA__PLUGIN_TEXTDOMAIN', 'vistawp' );

/**
* Entry class for Vista plugin
* Handles loading of other classes
* Also provides an interface for loading scripts & styles
* which guarantees that they are loaded/enqueued after the wp_enqueue_scripts hook
*
* @author VistaWP
* @link https://vistawp.com/
* @version 1.4.1
*/
class Main {

  /**
   * Singleton instance of this class
   * @var Main
   */
  private static ?Main $instance = null;

  /**
  * Creates or returns a single instance of this class
  * @return Main A single instance of this class
  */
  public static function get_instance(): Main {
    if (is_null(self::$instance)) {
      self::$instance = new self();
      self::$instance->init_classes();
    }

    return self::$instance;
  }

  /**
  * Create a new instance of this class, register scripts, styles, hooks, & shortcodes,
  * & load plugin files. Marked private to enforce the singleton design pattern.
  */
  private function __construct() {
    // Load files
    $this->includes();

    // Script/style loader hook
    \add_action('wp_enqueue_scripts', array($this, 'scripts_styles'));
    \add_action('admin_enqueue_scripts', array($this, 'scripts_styles'));
  }

  /**
   * Runs all sub-classes after the constructor returns
   * Called from get_instance
   */
  private function init_classes(): void {
    Listings\Simple_Listings::get_instance();
    Listings\Listing_Display_By_Filters::get_instance();
    Listings\Single_Listing_Display::get_instance();
    Listings\Map::get_instance();
    Analytics\Analytics_Display::get_instance();
    Options\Options_Display::get_instance();
    Options\License_Manager::get_instance();
    Options\Welcome_Header::get_instance();
    Openhouses\Open_House_Display::get_instance();
    Openhouses\Multiple_Openhouse_Display::get_instance();
    new Forms\Basic();
    new Forms\Advanced();
    new Forms\Search();
  }

  /**
  * Load all classes
  */
  public function includes() {
    // INTERFACE FILES:
    // API parameter interface
    if ( !interface_exists("\\VSTA\\API\\Listing_Parameters") ) {
      require_once 'includes/api/listing-parameters.php';
    }
    // Abstract class for api data objects
    if ( !class_exists("\\VSTA\\API_Data") ) {
      require_once 'includes/api-data.php';
    }
    // Abstract class for displaying single api data objects
    if ( !class_exists("\\VSTA\\Single_Display") ) {
      require_once 'includes/single-display.php';
    }
    // Abstract class for displaying multiple api data objects
    if ( !class_exists("\\VSTA\\Multiple_Display")) {
      require_once 'includes/multiple-display.php';
    }
    // Abstract class for $_GET API parameters
    if ( !class_exists("\\VSTA\\API\\Get_Params")) {
      require_once 'includes/api/get-params.php';
    }

    // CLASS FILES:
    // For displaying simple listings
    if( !class_exists( "\\VSTA\\Simple_Listings" ) ) {
      require_once 'includes/listings/simple-listings.php';
    }
    
    // For displaying multiple listings by filter
    if( !class_exists( "\\VSTA\\Listing_Display_By_Filters" ) ) {
      require_once 'includes/listings/listing-display-by-filters.php';
    }
    
    // For displaying single listings
    if ( !class_exists("\\VSTA\\Listings\\Single_Listing_Display") ) {
      require_once 'includes/listings/single-listing-display.php';
    }
    // For SimplyRETS API calls
    if ( !class_exists("\\VSTA\\API\\RETS_API") ) {
      require_once 'includes/api/RETS-API.php';
    }
    // For representing single Listings
    if ( !class_exists("\\VSTA\\Listings\\Single_Listing") ) {
      require_once 'includes/listings/single-listing.php';
    }
    // For getting api listing params
    if ( !class_exists("\\VSTA\\API\\Get_Listing_Params") ) {
      require_once 'includes/api/get-listing-params.php';
    }
    // For getting api analytics params
    if ( !class_exists("\\VSTA\\API\\Get_Analytics_Params") ) {
      require_once 'includes/api/get-analytics-params.php';
    }
    // For getting api openhouses params
    if ( !class_exists("\\VSTA\\API\\Get_Openhouse_Params") ) {
      require_once 'includes/api/get-openhouse-params.php';
    }
    // For displaying multiple listings
    if ( !class_exists("\\VSTA\\Listings\\Multiple_Listings_Display") ) {
      require_once 'includes/listings/multiple-listings-display.php';
    }
    // For representing analytics
    if ( !class_exists("\\VSTA\\Analytics\\Analytics") ) {
      require_once 'includes/analytics/analytics.php';
    }
    // For displaying analytics
    if ( !class_exists("\\VSTA\\Analytics\\Analytics_Display") ) {
      require_once 'includes/analytics/analytics-display.php';
    }
    // For displaying listings on a map
    if ( !class_exists("\\VSTA\\Listings\\Map") ) {
      require_once 'includes/listings/multiple-listings-map.php';
    }
    
    // Open house object
    if ( !class_exists("\\VSTA\\Openhouse\\Open_House") ) {
      require_once 'includes/openhouses/open-house.php';
    }
    // Single Open house display
    if ( !class_exists("\\VSTA\\Openhouse\\Open_House_Display")) {
      require_once "includes/openhouses/open-house-display.php";
    }
    // Multiple open house display
    if ( !class_exists("\\VSTA\\Openhouse\\Multiple_Openhouse_Display") ) {
      require_once "includes/openhouses/multiple-openhouse-display.php";
    }
        
    // Abstract class for options pages
    if ( !class_exists("\\VSTA\\Options\\Options_Page") ) {
      require_once 'includes/options/options-page.php';
    }
    // For adding & displaying admin pages
    if ( !class_exists("\\VSTA\\Options\\Options_Display") ) {
      require_once 'includes/options/options-display.php';
    }
    // License key API caller
    if ( !class_exists("\\VSTA\\API\\License_API") ) {
      require_once 'includes/api/license-api.php';
    }
    // License key manager
    if ( !class_exists("\\VSTA\\Options\\License_Manager") ) {
      require_once 'includes/options/license-manager.php';
    }
    // Welcome Header file 
    if ( !class_exists("\\VSTA\\Options\\Welcome_Header") ) {
      require_once "includes/options/welcome-header.php";
    }
    // Notifications Banner
    if ( !class_exists("\\VSTA\\Options\\Notifications_Banner") ) {
      require_once "includes/options/notifications-banner.php";
    }
    // Class for auto-generated pages 
    if ( !class_exists("\\VSTA\\Options\\Generate_Pages") ) {
      require_once 'includes/options/page-generator.php';
    }

    // Abstract class for forms
    if ( !class_exists("\\VSTA\\Forms\\Form") ) {
      require_once 'includes/forms/form.php';
    }
    // Class for Advanced form 
    if ( !class_exists("\\VSTA\\Forms\\Advanced") ) {
      require_once 'includes/forms/advanced.php';
    }
    // Class for basic form 
    if ( !class_exists("\\VSTA\\Forms\\Basic") ) {
      require_once 'includes/forms/basic.php';
    }
    // Class for search form 
    if ( !class_exists("\\VSTA\\Forms\\Search") ) {
      require_once 'includes/forms/search.php';
    }
    
    // Misc functions
    require_once 'includes/functions.php';
  }

  /**
   * All scripts registered with register_scripts
   * Each array entry maps to an array with 3 fields: name, path, deps
   * @var array
   */
  private array $registered_scripts = array();

  /**
   * All styles registered with register_styles
   * Each array entry maps style name => style path
   * @var array
   */
  private array $registered_styles = array();

  /**
   * All scripts to be enqueued
   * Array of strings
   * @var array[string]
   */
  private array $script_queue = array();

  /**
   * All styles to be enqueued
   * Array of strings
   * @var array[string]
   */
  private array $style_queue = array();

  /**
   * Returns the path to the main plugin file, for use in function calls
   * in other files which require the path to the main plugin file
   * @return string Path to main plugin file
   */
  public function get_vista_file(): string {
    return __FILE__;
  }

  /**
   * Registers/Enqueues any scripts or styles fed to the below four functions
   * Called by wp_enqueue_scripts hook.
   */
  public function scripts_styles(): void {
    // Register scripts
    foreach ($this->registered_scripts as $script) {
      \wp_register_script($script['name'], $script['path'], $script['deps']);
    }
    // Register styles
    foreach ($this->registered_styles as $name => $path) {
      \wp_register_style($name, $path);
    }

    // Enqueue scripts
    foreach ($this->script_queue as $name) {
      \wp_enqueue_script($name);
    }
    // Enqueue styles
    foreach ($this->style_queue as $name) {
      \wp_enqueue_style($name);
    }
  }

  /**
   * Registers a script with WP.
   * If a script with the same name has already been registered,
   * that script will be overwritten
   * @param string $name  Script name, for enqueueing
   * @param string $path  Path to .js file
   * @param array  $deps  Names of regisered scripts which this depends on
   */
  public function register_script(string $name, string $path, array $deps = NULL): void {
    if (\did_action('wp_enqueue_scripts') || \did_action('admin_enqueue_scripts'))
      \wp_register_script($name, $path, $deps);
    else {
      $this->registered_scripts[] = array(
        'name' => $name,
        'path' => $path,
        'deps' => $deps,
      );
    }
  }

  /**
   * Registers a style with WP.
   * If a style with the same name has already been registered,
   * that style will be overwritten
   * @param string $name  Style name, for enqueueing
   * @param string $path  Path to .css file
   */
  public function register_style(string $name, string $path): void {
    if (\did_action('wp_enqueue_scripts') || \did_action('admin_enqueue_scripts'))
      \wp_register_style($name, $path);
    else
      $this->registered_styles[$name] = $path;
  }

  /**
   * Queues a previously registered script to be loaded with the page
   * @param string $name  Script name. Must have previously been registered with register_script
   */
  public function enqueue_script(string $name): void {
    if (\did_action('wp_enqueue_scripts') || \did_action('admin_enqueue_scripts'))
      \wp_enqueue_script($name);
    else
      $this->script_queue[] = $name;
  }

  /**
   * Queues a previously registered style to be loaded with the page
   * @param string $name  Style name. Must have previously been registered with register_style
   */
  public function enqueue_style(string $name): void {
    if (\did_action('wp_enqueue_scripts') || \did_action('admin_enqueue_scripts')) {
      \wp_enqueue_style($name);
    } else {
      $this->style_queue[] = $name;
    }
  }
}

// Init hooks
add_action('plugins_loaded', '\VSTA\vista_init', 12);
function vista_init() {
  // This is a bugfix for wp_redirect not firing properly inside this plugin,
  // probably because the redirect gets canceled when things are added to the output buffer.
  ob_clean();
  ob_start();

  // Init plugin with failsafe block for initialization errors
  try {
    \VSTA\Main::get_instance();
  } catch (\Exception $e) {
    $GLOBALS['vista_error_message'] = $e->getMessage();
    \add_action('admin_notices', '\VSTA\vista_fallback_error', 11);
    \add_action('wp_loaded', '\VSTA\deactivate_vista', 12);
  }
  
  // This is a courtesy to other developers, 
  // to prevent this plugin adding to the output buffer accidentally.
  ob_end_clean();
}

/**
 * Deactivates the plugin- used in case of fatal error.
 * Must be called after the plugins_loaded hook
 */
function deactivate_vista(): void {
  // Sometimes the file with this function isn't included, so include it
  require_once ABSPATH . 'wp-admin/includes/plugin.php';
  \deactivate_plugins(plugin_basename(__FILE__));
}

/**
 * Display an admin error with customizable message.
 * This should only be used in the event that the plugin
 * encounters a fatal error. 
 * Otherwise, the Notifications_Banner class should be used.
 * The message is retrieved from the global vista_error_message variable.
 */
function vista_fallback_error(): void {
 ?>
 <div class="error notice">
    <p>
      The VistaWP plugin has encountered a fatal error and self-deactivated. 
      Error message: <?php echo $GLOBALS['vista_error_message']; ?>
    </p>
 </div>
 <?php
}