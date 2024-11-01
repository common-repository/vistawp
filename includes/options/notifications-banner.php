<?php

namespace VSTA\Options;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Class Notifications_Banner
 *
 * Display notification banners in the WordPress admin area
 */
class Notifications_Banner {
  /** 
   * @var Notifications_Banner[] Array to store instances of the class. 
   */
  private static $instances = array();
  
  /** 
   * @var string The type of the notification. 
   */
  private $type;
  
  /** 
   * @var string The text content of the notification. 
   */
  private $text;
  
  /** 
   * @var string This refers to a pre-defined structure or format that will be used to generate and deliver notifications.
   * It typically includes essential elements such as notification $type and body content ($text).
   */
  private $template;
  
  /**
   * @var array The types of notifications that can be displayed.
   * Derived from WP's admin notice CSS classes.
   */ 
  private static array $types = array(
    'success',
    'warning',
    'error',
    'info'
  );
  
  /**
   * @var array The types of templates that can be displayed.
   * Array containing notification template options located in the `templates/notifications` folder
   */ 
  private static array $templates = array(
    'general',
    'welcome',
  );

  /**
   * Notifications_Banner constructor.
   *
   * @param string $type The type of the notification.
   * @param string $text The text content of the notification.
   * @param string $template The template of the notification.
   */
  protected function __construct(string $type, string $text, string $template) {
    $this->type = $type;
    $this->text = $text;
    $this->template = $template;
    
    // Enqueue the 'notification-styles' CSS file for the banners
    \VSTA\Main::get_instance()->register_style('notification-styles', 
      \vista_plugin_url('/css/admin.css')
    );
    \VSTA\Main::get_instance()->enqueue_style('notification-styles');
  }
  
  /**
   * Create an instance of the class with the specified type and text.
   *
   * @param string $type The type of the notification.
   *                     Can be any of the options in $this->types.
   * @param string $text The text content of the notification.
   *
   * @param string $template The template of the notification (optional).
   *                         The default value is 'general' if no other template is specified.
   * 
   * @return Notifications_Banner Created instance of Notifications_Banner.
   */
  public static function create(string $type, string $text, string $template = 'general'): Notifications_Banner {
    // Check if the type is valid
    if (!in_array($type, self::$types)) {
      throw new \InvalidArgumentException("Invalid notification type: $type");
    }
    
    // Check if the template name is valid
    if (!in_array($template, self::$templates)) {
      throw new \InvalidArgumentException("Invalid notification template: $template");
    }

    // Create new instance and return
    $instance = new self($type, $text, $template);
    self::$instances[] = $instance;
    return $instance;
  }
  
  /**
   * Get all the notification banners that have been displayed.
   *
   * @return Notifications_Banner[] Array of displayed notification banners.
   */
  public function get_displayed_banners(): array {
    return self::$instances;
  }

  /**
   * Display the notification banner.
   * Public for callback use, should only be called by WordPress.
   */
  public function display_notification() {
    ob_start();
      \vista_get_template(
        'notifications/'. $this->template .'.php',
        array(
          'type' => $this->type,
          'text' => $this->text,
        ),
      );

    echo ob_get_clean();
  }
}