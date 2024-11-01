<?php
namespace VSTA\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles the rendering and submission of a basic form.
 * It generates a shortcode [vista_search_form] 
 * that can be used to display the form on a page.
 * The form only includes one input field for search queries
 *
 * @author VistaWP
 * @version 2.0
 */
class Search extends Form {

    public function __construct() {
        parent::__construct();
    }

    /**
     * The shortcode tag for this form.
     */
    private const SHORTCODE_TAG = "vista_search_form";

    /**
     * The id of the form.
     */
    private const FORM_ID = "vista-search-form";

    /**
     * Return the shortcode tag for this form.
     * @return string The shortcode tag
     */
    public function get_tag(): string {
        return self::SHORTCODE_TAG;
    }

    /**
     * Render the shortcode.
     * This method generates the HTML markup for the form.
     *
     * @param array $atts The shortcode attributes.
     * @return string The HTML markup for the form.
     */
    public function render($atts): string {
      ob_start();
      
      // Retrieve preloaded URL query value
      $value = isset($_GET['vista-q']) ? \sanitize_text_field($_GET['vista-q']) : '';
      
      // Set form element values
      $this->form_header(self::FORM_ID, \sanitize_text_field($atts['dest'] ?? ''));
      $this->maybe_add_status($atts);      
      $this->text_field('q', 'vista-q', 'Location', $value, 'City, Zip Code, or Street Name');
      $this->submit_button('Submit');
      $this->form_footer();
      
      return ob_get_clean();
    }
}