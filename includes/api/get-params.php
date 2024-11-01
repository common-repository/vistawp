<?php

namespace VSTA\API;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


/**
 * Provides a template for retrieving
 *  parameters for the SimplyRETS API from $_GET
 *
 * @author Nate Lanza
 */
abstract class Get_Params implements Listing_Parameters {

  /**
   * Maps $_GET field names to query parameter names
   * Should be assigned by child class
   * @var array
   */
  protected array $mappings;

  /**
   * Initializes a new instance of this class
   */
  public function __construct() { }


  /**
   * Retrieves query parameters from $_GET
   * Child classes must implement $mappings as an associative array for
   * this method to function properly.
   * If a value retrieved from $_GET contains '%2C+' (the URL encoding of ', '), the '%2C+' will be removed
   * and the $_GET value will be encoded as the array result of explode('%2C+', $value);
   * @return array parameters as an associative array
   */
  public final function get_params(): array {
    $params = array();
    // Loop through mappings, retrieve each mapped field
    foreach ($this->mappings as $getName => $paramName) {

      // Make sure mapped field has content
      if (empty($_GET[$getName]))
        continue;

      // Maybe split to array
      if (is_string($_GET[$getName])) {
        // Sanitize input
        $field = \sanitize_text_field($_GET[$getName]);
        
        // Split parameter if necessary
        $param = preg_split("/(%2C\+)|(, )|\+/", $field);
        if ($param === false)
          $param = $field;
      } else {
        $param = $_GET[$getName];
      }
      
      // We take different actions based on whether the parameter is an array or string
      if (is_array($param)) {
        if (!isset($params[$paramName])) {
          // No previous values, simply set the param
          $params[$paramName] = $param;
        // We need to create an array with the old string value
        } else if (is_string($params[$paramName])) {
          $param[] = $params[$paramName];
          $params[$paramName] = $param;
        // We need to add the new values to the array
        } else if (is_array($params[$paramName])) {
          foreach ($param as $value)
            $params[$paramName][] = $value;
        }
      } else if (is_string($param)) {
        // We need to combine the old string and the new into an array
        if (!isset($params[$paramName]))
          // No previous value, simply set the param
          $params[$paramName] = $param;
        else if (is_string($params[$paramName]))
          $params[$paramName] = array($params[$paramName], $param);
        // We need to add the new value to the old array
        else if (is_array($params[$paramName]))
          $params[$paramName][] = $param;
      }
    }

    return $params;
  }

}
