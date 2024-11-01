<?php
namespace VSTA\Options;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Represents a submenu page under the Vista menu header
 * Automatically registered with Options_Display when instantiated
 */
class Options_Page {

  // These match the constructor params, check that documentation
  private string $title;
  private string $menu;
  private string $capability;
  private string $slug;
  private $display;
  private int $position;

  /**
   * Creates a new options_page and registers it with Options_Display
   * @param string   $title       Page title (displays at the top of the admin page)
   * @param string   $menu        Menu title (displays on the side menu)
   * @param string   $capability  Capability required to use this page
   * @param string   $slug        Slug
   * @param callable $display     Function to display this menu page. This should directly output the HTML via echo
   *                              or otherwise, as opposed to returning it
   * @param int      $position    Order in the submenu list
   */
  public function __construct(
    string $title,
    string $menu,
    string $capability,
    string $slug,
    callable $display,
    int $position
  ) {
    $this->title = $title;
    $this->menu = $menu;
    $this->capability = $capability;
    $this->slug = $slug;
    $this->display = $display;
    $this->position = $position;
  }

  /**
   * Registers this settings page with Options_Display
   * so that the new settings page is created on startup
   */
  public function register(): void {
    Options_Display::get_instance()->register_settings_page($this);
  }

  /**
   * Get the page title for this page
   */
  public function page_title(): string {
    return $this->title;
  }

  /**
   * Get the menu title for this page
   */
  public function menu_title(): string {
    return $this->menu;
  }

  /**
   * Get the slug for this page.
   * If the slug is Options_Display::MAIN_PAGE_SLUG, this page will be selected
   * by default when a user clicks the vista menu header
   * Every slug must be unique
   */
  public function get_slug(): string {
    return $this->slug;
  }

  /**
   * Get the position (order in the menu) for this page
   * @return int Position
   */
  public function get_position(): int {
    return $this->position;
  }

  /**
   * Returns the capability required to view this menu page
   * @return string Capability
   */
  public function get_capability(): string {
    return $this->capability;
  }

  /**
   * Outputs the HTML for this settings page
   */
  public function display_func(): callable {
    return $this->display;
  }

}

?>
