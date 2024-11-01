<?php
namespace VSTA\Forms;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Basis for the different form types
 * Registers the shortcode and associates it with the render_shortcode method,
 * and registers and enqueues form styles
 *
 * @author VistaWP
 * @version 1.0
 */
abstract class Form {

  /**
   * The default separator for array values in a query string
   */
  public const DEFAULT_SEPARATOR = "%2C+";

  /**
   * Render the shortcode.
   * This method generates the HTML markup for the form.
   * Generally, this method should start with a call to
   * ob_start() and return the result of ob_get_clean(),
   * since most of the helper functions for rending the form
   * output HTML directly.
   * 
   * @param $atts The shortcode attributes
   *
   * @return string The HTML markup for the form.
   */
  abstract function render($atts): string;

  /**
   * Get the shortcode tag for this form
   * @return string The shortcode tag
   */
  abstract function get_tag(): string;

  /**
   * Registers the stylesheet and shortcode
   * @param string $shortcode_tag The shortcode tag to be used to display the form
   */
  public function __construct() {
    // Register the shortcode and associate it with the render_shortcode method of this class
    \add_shortcode($this->get_tag(), array($this, 'shortcode_handler'));

    // Register the 'forms-styles' stylesheet with the Main class of the VSTA namespace
    \VSTA\Main::get_instance()->register_style('forms-styles', \vista_plugin_url('/css/forms.css'));
  }

  /**
   * Shortcode callback. Enqueues styles and calls the render method
   */
  public function shortcode_handler($atts): string {
    $this->enqueue_styles();
    return $this->render($atts);
  }

  /**
   * Enqueue the 'forms-styles' stylesheet on the front-end.
   */
  private function enqueue_styles() {
    \VSTA\Main::get_instance()->enqueue_style('forms-styles');
  }

  /**
   * Outputs the HTML for the form header,
   * and gives the <form> tag the specified id
   * 
   * @param string $id   The id to give the <form> tag
   * @param string $dest The URL to submit the form to. esc_attr() is run on this value before outputting,
   *                     so query params cannot be included.
   */
  protected function form_header(string $id, string $dest) {
    ?>
    <form method="GET" id="<?php echo \esc_attr($id); ?>" action="<?php echo \esc_attr($dest) ?>">
      <div class="vista-form-container">
    <?php
  }

  /**
   * Adds a hidden field setting status to active if the allStatus attribute is not set.
   * This should be added to all forms which want to have a default status=Active.
   * 
   * @param $atts The shortcode attributes- should be passed from the param to render().
   *              If the 'allStatus' field is set in $atts, this function does nothing.
   */
  protected function maybe_add_status($atts) {
    if (is_array($atts) && !empty($atts['allStatus'])) {
      return; // We don't want to add the hidden field if allStatus is set
    } else {
      ?>
      <input type="hidden" name="vista-status" value="Active">
      <?php
    }
  }

  /**
   * Outputs the HTML for the form footer
   */
  protected function form_footer() {
    ?>
      </div>
    </form>
    <?php
  }

  /**
   * Outputs the HTML for a text input field
   * @param string $id The id to give the <input> tag
   * @param string $name The name to give the <input> tag
   * @param string $label The label for the field
   * @param string $value The value to give the <input> tag
   * @param string $placeholder The placeholder to give the <input> tag
   */
  protected function text_field(
    string $id,
    string $name,
    string $label,
    string $value = '',
    string $placeholder = ''
  ) {
    ob_start();
      \vista_get_template(
        'fields/text-field.php',
        array(
          'id' => $id,
          'name' => $name,
          'label' => $label,
          'value' => $value,
          'placeholder' => $placeholder,
        ),
      );

    echo ob_get_clean();
  }

  /**
   * Outputs HTML for the submit button
   * @param string $text The text to display on the button
  */
  protected function submit_button(string $text) {
    echo sprintf(
      '<div class="vista-form-row-button-sm"><div class="vista-form-item"><button type="submit" value="Submit">%s</button></div></div>',
      esc_html($text)
    );
  }

  /**
   * Outputs HTML for a <select> field
   * The options are specified as an array of key => value mappings,
   * and one can be selected by default.
   * 
   * @param string $id The id to give the <select> tag
   * @param string $name The name to give the <select> tag
   * @param string $label The label for the field
   * @param array $options The options to display in the multiselect.
   *                       Each entry should be a key => value mapping,
   *                       where the key is displayed to the user and
   *                       the value is the value of the <option> tag
   * @param string $selected The value of option to select by default
   */
  protected function select_field(
    string $id,
    string $name,
    string $label,
    array $options,
    string $placeholder = '',
    string $selected = ''
  ) {
    ob_start();
      \vista_get_template(
        'fields/select.php',
        array(
          'id' => $id,
          'name' => $name,
          'label' => $label,
          'options' => $options,
          'placeholder' => $placeholder,
          'selected' => $selected,
        ),
      );

    echo ob_get_clean();
  }

  /**
   * Outputs HTML for a series of checkboxes whose results
   * are grouped into an array. The options are specified as
   * an array of key => value mappings, where the key is displayed
   * to the user as the label and the value is the value of the checkbox.
   * The ID of each checkbox is the prefix followed by the value.
   * 
   * @param string $title The title to use for the field label
   * @param string $prefix The prefix to use for the ID of each checkbox
   * @param string $name The name of the field, which will be the key in $_POST
   * @param array $options The options to display in the multiselect.
   * @param array $checked_options The value of option to checked by default
   */
  protected function checkbox_field(
    string $title,
    string $prefix,
    string $name,
    array $options,
    array $checked_options = array()
  ) {
    ob_start();
      \vista_get_template(
        'fields/checkbox.php',
        array(
          'title' => $title,
          'prefix' => $prefix,
          'name' => $name,
          'options' => $options,
          'checked_options' => $checked_options,
        ),
      );

    echo ob_get_clean();
  }

  /**
   * Outputs HTML for a number field
   * @param string $id The id to give the <input> tag
   * @param string $name The name to give the <input> tag
   * @param string $label The label for the field
   * @param string $value The value to give the <input> tag
   * @param string $placeholder The placeholder to give the <input> tag
   */
  protected function number_field(
    string $id,
    string $name,
    string $label,
    string $value = '',
    string $placeholder = ''
  ) {
    ob_start();
      \vista_get_template(
        'fields/number-field.php',
        array(
          'id' => $id,
          'name' => $name,
          'label' => $label,
          'value' => $value,
          'placeholder' => $placeholder,
        ),
      );

    echo ob_get_clean();
  }
}