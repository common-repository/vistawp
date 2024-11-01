<?php
namespace VSTA\Openhouses;

defined('ABSPATH') || exit;

use \VSTA\API as API;

/**
 * Creates a shortcode for displaying a single open house
 */
class Open_House_Display extends \VSTA\Single_Display {

  /**
   * Message to display when the "openhouse" GET param is not set
   * @var string
   */
  protected const NO_PARAM_MSG = '<p>Open house ID must be in the URL parameter "openhouse"</p>';

  /**
   * Retrieves data for the open house from the API and stores it
   * in $this->api_data
   */
  protected function load_api_data(): void {
    if (!isset($_GET['openhouse'])) {
      throw new \Exception(self::NO_PARAM_MSG);
    }

    // Set up API call
    $api_call = new API\RETS_API(
      API\RETS_API::CALL_TYPES['SINGLE_OPEN_HOUSE'],
      \sanitize_text_field($_GET['openhouse']),
    );

    // Make sure necessary scripts & styles are enqueued
    \VSTA\Main::get_instance()->enqueue_style('slideshow-styles');
    \VSTA\Main::get_instance()->enqueue_script('slideshow-js');

    $this->api_data = new Open_House($api_call->get_response());
  }

  /**
   * Registers shortcodes
   * Protected to enforce singleton
   */
  protected function __construct() {
    parent::__construct();
    add_shortcode('vista_openhouse_field', array($this, 'data_field'));
  }

}







 ?>
