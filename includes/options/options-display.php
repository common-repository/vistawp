<?php
namespace VSTA\Options;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use \VSTA as V;

/**
* Adds options pages and defines their display HTML
*/
class Options_Display {
  /**
  * Option name for the listings page type.
  *
  * This constant holds the option name for the listings page type,
  * which is used to store and retrieve the page ID associated with
  * the 'listings' type of page.
  *
  * @var string
  */
  public const LISTINGS_OPTION = Generate_Pages::OPTION_PREFIX . 'listings';
  
  /**
  * Option name for the individual listings page type.
  *
  * This constant holds the option name for the individual listings
  * page type, which is used to store and retrieve the page ID associated
  * with the 'individual' type of page.
  *
  * @var string
  */
  public const INDIVIDUAL_OPTION = Generate_Pages::OPTION_PREFIX . 'individual';
  
  /**
  * Option name for the openhouse page type.
  *
  * This constant holds the option name for the openhouse page type,
  * which is used to store and retrieve the page ID associated with
  * the 'openhouse' type of page.
  *
  * @var string
  */
  public const OPENHOUSE_OPTION = Generate_Pages::OPTION_PREFIX . 'openhouse';
  
  /**
  * Singleton instance of this class
  * @var Options_Display
  */
  private static ?Options_Display $instance = NULL;

  /**
   * The slug that should be used for the main page 
   * when the View menu header is selected.
   * This slug should only be used for an Options_Page.
   * 
   * @var string
   */
  public const MAIN_PAGE_SLUG = 'vista_main';
  
  /**
  * Default capability required to interact with vista settings
  * Individual pages may use this or set their own role requirement
  * @var string
  */
  public const VISTA_CAPABILITY = 'manage_options';
  
  /**
  * This will be a file path to the icon for the
  * vista menu item
  * @var string
  */
  private string $icon_url;
  
  /**
  * Array of Options_Page objects that have been registered
  * with register_settings_page
  * @var array[Options_Page]
  */
  private array $pages;
  
  /**
  * Retrieve the single instance of this class
  * @return Options_Display
  */
  public static function get_instance(): Options_Display {
    if (is_null(self::$instance)) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  /**
  * Protected to enforce singleton pattern
  */
  protected function __construct() {
    add_action('admin_menu', array($this, 'init_settings'));
    $this->icon_url = \vista_plugin_url("/img/dashboard_logo.png");
    
    // Styles for admin dashboard
    V\Main::get_instance()->register_style('vsta_admin', \vista_plugin_url("/css/admin.css"));
    if (\is_admin()) {
      V\Main::get_instance()->enqueue_style('vsta_admin');
    }
  }
  
  /**
  * Registers a settings page, to be included under the Vista menu header.
  * If the slug is 'vista_main', this will be the default selected page
  * @param Options_Page $page  Class representing the page
  */
  public function register_settings_page(Options_Page $page): void {
    $this->pages[] = $page;
  }
  
  /**
  * Initializes the settings page and all subpages
  * These are registered by other classes and this is called when admin_menu
  */
  public function init_settings(): void {
    \add_menu_page(
      "Vista",
      "Vista",
      self::VISTA_CAPABILITY,
      self::MAIN_PAGE_SLUG,
      array(
        $this,
        'main_page'
      ),
      $this->icon_url,
      8,
    );
    
    // This bug only occurs in very specific circumstances,
    // so this check is rarely necessary, but it's here just in case
    if (empty($this->pages)) {
      return;
    }

    foreach($this->pages as $page) {
      \add_submenu_page(
        self::MAIN_PAGE_SLUG,
        $page->page_title(),
        $page->menu_title(),
        $page->get_capability(),
        $page->get_slug(),
        $page->display_func(),
        $page->get_position(),
      );
    }
  }
  
  /**
  * Creates the three default pages
  * and displays a banner indicating success or failure
  */
  private function create_pages(): void {
    $listings_page = new Generate_Pages();
    $allPagesCreated = true;
    
    // Try to create pages
    if (isset($_POST['generate_pages'])) {
      $allPagesCreated &= $listings_page->auto_create_page(Options_Display::LISTINGS_OPTION, 'Listings');
      $allPagesCreated &= $listings_page->auto_create_page(Options_Display::INDIVIDUAL_OPTION, 'Individual Listing');
      // $allPagesCreated &= $listings_page->auto_create_page(Options_Display::OPENHOUSE_OPTION, 'Openhouses');
      
      if ($allPagesCreated) {
        $notification = Notifications_Banner::create('success','Success All Pages Created');
        $notification->display_notification();
      } else {
        $notification = Notifications_Banner::create('error','Error Some Pages Failed to Create');
        $notification->display_notification();
      }
    }
  }
  
  /**
  * Outputs text for the default Vista admin page
  * This should be overridden by registering a function
  * with slug set to MAIN_PAGE_SLUG
  */
  public function main_page(): void {
    $this->create_pages();

    ob_start();
      \vista_get_template(
        'pages/main_page.php',
        array(
          'listings' => get_option(Options_Display::LISTINGS_OPTION),
          'individual' => get_option(Options_Display::INDIVIDUAL_OPTION),
        ),
      );

    echo ob_get_clean();
  }
}