<?php
namespace VSTA\Forms;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * VistaWP_Basic_Form class handles the rendering and submission of a basic form.
 * It generates a shortcode [vistawp_basic_form] that can be used to display the form on a page.
 * The form includes various input fields for search queries, property types, price range, beds, and baths.
 *
 * @author VistaWP
 * @version 2.0
 */
class Basic extends Form {
  /**
   * The shortcode tag for this form.
   */
  private const SHORTCODE_TAG = "vista_basic_form";

  /**
   * The id of the form.
   */
  private const FORM_ID = "vista-basic-form";

  public function __construct() {
    parent::__construct();
  }

  /**
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
    $query_value = isset($_GET['vista-q']) ? \sanitize_text_field($_GET['vista-q']) : '';
    $minprice = isset($_GET['vista-minprice']) ? \sanitize_text_field($_GET['vista-minprice']) : '';
    $maxprice = isset($_GET['vista-maxprice']) ? \sanitize_text_field($_GET['vista-maxprice']) : '';
    $checked_options = isset($_GET['vista-type']) ? \array_map('sanitize_text_field', $_GET['vista-type']) : array();
    $selected_bed = isset($_GET['vista-minbeds']) ? \sanitize_text_field($_GET['vista-minbeds']) : '';
    $selected_bath = isset($_GET['vista-minbaths']) ? \sanitize_text_field($_GET['vista-minbaths']) : '';
    
    // Set form element values
    $this->form_header(self::FORM_ID, \sanitize_text_field($atts['dest'] ?? ''));
    $this->maybe_add_status($atts);
    $this->text_field('q', 'vista-q', 'Location', $query_value, 'City, Zip Code, or Street Name');
    $this->number_field('minprice', 'vista-minprice', 'Minimum Price', $minprice, '$ Min Price...');
    $this->number_field('maxprice', 'vista-maxprice', 'Maximum Price', $maxprice, '$ Max Price...');
    $this->checkbox_field(
      'Property Type ',
      'ptype-',
      'vista-type',
      array(
        'Residential' => 'residential',
        'Condominium' => 'condominium',
        'Multifamily' => 'multifamily',
        'Land' => 'land',
      ),
      $checked_options,
    );

    // For the minbeds and minbaths selects
    $options = array('', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10');
    $this->select_field('minbeds', 'vista-minbeds', 'Minimum Beds', $options, 'How many beds?', $selected_bed);
    $this->select_field('minbaths', 'vista-minbaths', 'Minimum Baths', $options, 'How many bathrooms?', $selected_bath);
    $this->submit_button('Submit');
    $this->form_footer();

    return ob_get_clean();
  }
}