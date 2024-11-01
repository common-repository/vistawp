<?php

namespace VSTA\API;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * A class which implements this API must be able to retrieve parameters for the SimplyRETS API
 * from an external sources, such as $_GET or $_POST, and output them as an associative array,
 * which maps $paramName => $paramValue.
 * $paramValue can be an array to include multiple values for one parameter
 *
 * @author Nate Lanza
 */
interface Listing_Parameters {

  /**
   * Returns parameters for the SimplyRETS API in the format described in this interface's comment
   * @return array Parameters retrieved from the URL
   */
  public function get_params(): array;

}

 ?>
