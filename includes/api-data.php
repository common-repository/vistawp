<?php
namespace VSTA;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * An object which stores data from a SimplyRETS API Call
 *
 * @author Nate Lanza
 * @link https://maderightmedia.com
 * @version 1.0
 */
abstract class API_Data {

  /**
   * Non-specific error message. More specific messages below are preferred
   * @var string
   */
  protected const ERROR = "Not available";

  /**
   * Error message to show when a field's value is NULL or evaluates to false
   * @var string
   */
  protected const NULL_MSG = "None";

  /**
   * Error message to show when a field's name is not set in $this->fields
   * @var string
   */
  protected const MISS_MSG = "Field not found";

  /**
   * Message to display when a user fails to enter the 'field' shortcode attribute
   * @var [type]
   */
  protected const NO_FIELD_MSG =
    'You must specify the listing field to display using the "field" shortcode attribute';

  /**
   * Maps fields to calculated HTML values
   * @var array
   */
  protected array $fields;

  /**
   * Maps field names (in the API request) to callable functions (within this class)
   * which process the field into html, which is then placed into $fields.
   * Should be initialized by init_mappings
   * @var array
   */
  protected array $mappings;

  /**
   * A list of fields that try_process_field should try to format
   * as numbers, with commas and (for non-ints) 2 decimals.
   * Should be initialized by init_mappings
   * @var array
   */
  protected array $number_fields;

  /**
   * Initializes mappings in $this->mappings
   * Each mapping has the name of a field from the API call as key,
   * mapped to a callable function in this class which can process
   * the data contained in that field and store it in $this->fields
   * as HTML which can be echoed onto the page
   *
   * Also initializes mappings in $this->number_fields
   */
  abstract protected function init_mappings(): void;

  /**
   * Creates a new API_Data object
   * Uses the mappings created by init_mappings to process API fields into
   * internal fields, which can be retrieved with get_field
   * @param array  $api_data  Deserialized data from the API. Should contain a single MLS listing
   */
  public function __construct(array $api_data) {
    // Prep mappings
    $this->init_mappings();

    // Process MLS fields into HTML
    foreach ($api_data as $fieldname => $fieldvalue) {
      // If we don't have this field mapped, process it as a text field if possible
      if ( !isset($this->mappings[$fieldname]) ) {
        // Try to process each field
        $this->try_process_field($fieldname, $fieldvalue);
        // We process the field if possible or leave it be.
        // Either way, an error is thrown if we don't continue
        continue;
      }

      // Process the field by calling its processing function & passing in the field name/value
      try {
        call_user_func($this->mappings[$fieldname], $fieldname, $fieldvalue);
      } catch (\Exception $e) {
        $this->process_text_field($fieldname, self::ERROR);
      }

    }
  }

  /**
   * Designed to be called by a shortcode which should display listing fields
   * @param  mixed  $atts Shortcode attributes. 'field' should be set, and determines which
   *                      field of the listing to display
   * @return string       Field to display, as HTML, or error message if $atts['field'] is not set
   */
  public function display_field($atts): string {
    // Validate args
    if (!is_array($atts) || !isset($atts['field'])) {
      return self::NO_FIELD_MSG;
    }

    return $this->get_field($atts['field']);
  }

  /**
   * Gets the value of a stored listing field
   * @param  string $field Name of field
   * @return string        Field value
   */
  public function get_field(string $field): string {
    // Convert
    $field = strtolower($field);
    // Validate field
    if (!isset( $this->fields[$field] )) {
      return self::MISS_MSG;
    }

    return strval($this->fields[$field]);
  }

  /**
   * Stores a text field in $fields for later retrieval
   * @param  string $name  Field name
   * @param  string $value Field content
   */
  protected final function process_text_field(string $name, ?string $value): void {
    $this->fields[strtolower($name)] = $value ?? self::NULL_MSG;
  }

  /**
   * Processes an associative array of sub-fields (mapped fieldname => fieldvalue) into $this->fields
   * Each field is named in $fields as it is named in the associative array, with an optional prefix appended
   * @param string $name   Name of the field in the API that contains this array.
   *                       Parameter exists for compatability with init_mappings, but is not used
   * @param array  $fields The associative array. All values must be strings or ints
   * @param string $prefix Optional prefix to be appended to each field's API name before storing it in $fields
   */
  protected final function process_fields_by_name(string $name, ?array $fields, string $prefix = ""): void {
    // Process all fields
    if(is_array($fields)) {
      foreach ($fields as $fieldname => $fieldvalue) {
        // Try to process each field
        $this->try_process_field($fieldname, $prefix . $fieldvalue);
        }
      }
    }
  /**
   * Tries to process a field as text, double, float, or int.
   * If the field can be processed, it is added to $this->fields
   * and function return true. If field is null, it is added to $this->fields
   * with the value "Field not included in this listing", and function returns true.
   * If the field is a resource, array, or object, it is not added to $this->fields and
   * function returns false
   * @param  string $name Field name
   * @param  mixed $value Field value. If this is null or == false,
   *                      parent::NULL_MSG will be used for the field
   * @return bool         Whether the field was added to $this->fields
   */
  protected final function try_process_field(string $name, $value): bool {
    // Check if value is null
    if (is_null($value)) {
      $this->process_text_field($name, self::NULL_MSG);
      return true;
    // Check if value can be processed by process_text_field
    } else if ( !is_array($value) && !is_object($value) && !is_resource($value) ) {
      // Format numbers
      if (is_numeric($value) && in_array($name, $this->number_fields))
        $value = is_int($value) ? number_format($value) : number_format($value, 2);

      $this->process_text_field($name, $value);
      return true;
    }

    return false;
  }

}




 ?>
