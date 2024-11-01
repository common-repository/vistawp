<?php

namespace VSTA\API;

use \VSTA\Options as OPT;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Calls and returns information from the SimplyRETS API
 * Uses the wp option 'vsta_api_err_time' to defeat error message caching on the server
 *
 * @author Nate Lanza
 * @link https://docs.simplyrets.com/api/index.html SimplyRETS API docs
 */
class RETS_API {
  /**
   * Remote URL, where the RETS API is located
   * @var string
   */
  private const REMOTE_URL = 'https://vistawp.com/wp-json/vista/api/v1/rets';

  /**
   * Option name for the time of the last error returned by the API
   * Used for cache busting when trying to resubmit a license key
   * @var string
   */
  private const ERR_TIME_SETTING = 'vsta_api_err_time';

  /**
   * RETS_API_Exception thrown due to error returned by API, if one occurred
   * @var RETS_API_Exception
   */
  private ?RETS_API_Exception $err;

  /**
   * Parameters used to filter request results
   * @var array
   */
  private array $params;

  /**
   * First part of the query string, containing mandatory parameters-
   * 'endpoint', 'license', and potentially 'objectID'
   * @var string
   */
  private string $query;

  /**
   * Response given by the API after get_response() is called once
   * @var array
   */
  private ?array $response;

  /**
   * Headers returned by the API after get_response() is called once
   * @var array
   */
  private ?array $headers;

  /**
   * Types of API call which this class can make.
   * Elements of this array should be fed to the third constructor parameter
   * @var array
   */
  public const CALL_TYPES = array(
    'PROPERTY_LISTINGS' => 'properties',
    'SINGLE_PROPERTY' => 'property',
    'OPEN_HOUSES' => 'openhouses',
    'SINGLE_OPEN_HOUSE' => 'openhouse',
    'AGENTS' => 'agents',
    'ANALYTICS' => 'analytics',
    'SINGLE_ANALYTICS' => 'analytic'
  );

  /**
   * Valid call types for a single listing request. Used in constructor to validate params
   * @var array
   */
  private const SINGLE_CALL_TYPES = array('property', 'openhouse', 'analytic');

  /**
   * Creates a new instance of self
   * @param string $license   License key, from VistaWP site/Easy Digital Downloads
   *                          If this is set to NULL, demo data will be returned from the API
   * @param string $type      Type of API call to make. Must be in self::CALL_TYPES
   * @param string $single_ID (Optional) MLS ID or OpenHouse key to use if this request is for a single listing
   *
   * @throws InvalidArgumentException If a single ID is not set with a type in SINGLE_CALL_TYPES
   */
  public function __construct(string $type, string $single_ID = "") {
    // Validate args
    if (in_array($type, self::SINGLE_CALL_TYPES) && $single_ID == "") {
      throw new \InvalidArgumentException("Must set single_ID param with type $type");
    }
    if (!in_array($type, self::CALL_TYPES)) {
      throw new \InvalidArgumentException("Bad call type: $type");
    }
      
    // Init object fields
    $this->response = null;
    $this->headers = null;
    $this->err = null;

    // Set up query string
    if (in_array($type, self::SINGLE_CALL_TYPES)) {
      $this->query = "?endpoint=$type&objectID=$single_ID";
    } else {
      $this->query = "?endpoint=$type";
    }

    // Init parameters
    $this->params = array();
  }


  /**
   * Adds an parameter to the request before the request is sent
   * The user of this class is responsible for ensuring validity of parameters;
   * this class does not attempt to verify that parameters match the API specifications.
   * Invalid parameters will result in an error on API call
   * Valid params are specified in @link https://docs.simplyrets.com/api/index.html
   *
   * @throws Exception If get_response() has already been called
   * @param string $name  Name of parameter
   * @param mixed $value  Value of parameter. Can be a string for a single parameter value
   *                      or an array for multiple values
   */
  public function add_param(string $name, $value): void {
    // Make sure we haven't already called the API
    if ($this->response) {
      throw new \Exception("API has already been called with this object, more parameters cannot be added");
    }

    // Set parameter
    // We take different actions based on whether the parameter is an array or string
    if (is_array($value)) {
      // Make sure we don't have any spaces
      $newvals = array();
      foreach ($value as $param)
        $newvals[] = str_replace(" ", "+", $param);
      $value = $newvals;
      if (!isset($this->params[$name])) {
        // No previous values, simply set the param
        $this->params[$name] = $value;
      // We need to create an array with the old string value
      } else if (is_string($this->params[$name])) {
        $value[] = $this->params[$name];
        $this->params[$name] = $value;
      // We need to add the new values to the array
      } else if (is_array($this->params[$name])) {
        foreach ($param as $value)
          $this->params[$name][] = $value;
      }
    } else if (is_string($value)) {
      // Make sure we don't have any spaces
      $value = str_replace(" ", "+", $value);
      // We need to combine the old string and the new into an array
      if (!isset($this->params[$name]))
        // No previous value, simply set the param
        $this->params[$name] = $value;
      else if (is_string($this->params[$name]))
        $this->params[$name] = array($this->params[$name], $value);
      // We need to add the new value to the old array
      else if (is_array($this->params[$name]))
        $this->params[$name][] = $value;
    }
  }

  /**
   * Returns the query string for this request.
   * This is not the final query string- it only contains
   * the parameters added with add_param().
   * More parameters may be added in get_response()
   * @return string Query string for this request
   */
  private function get_query_args(): string {
    // Query string is already initialized in constructor with ?endpoint=
    $paramstring = "";
    foreach ($this->params as $name => $value) {
      if (is_string($value)) {
        $paramstring .= "&$name=$value";
      } else if (is_array($value)) {
        foreach ($value as $val) {
          $paramstring .= "&$name=$val";
        }
      }
    }
    return $paramstring;
  }

  /**
   * Sets the err_time option to the current time-
   * called right after an API error is returned
   */
  private function set_err_time(): void {
    \update_option(self::ERR_TIME_SETTING, time());
  }

  /**
   * Clears the err_time option-
   * called right after a successful API call
   */
  private function clear_err_time(): void {
    \update_option(self::ERR_TIME_SETTING, "");
  }

  /**
   * If the last API call failed, returns the time of the error.
   * Otherwise, returns null. Used to decide whether to bust the cache
   * @return string|null Time of the last error, or null if no error
   */
  private function maybe_get_err_time(): ?string {
    $errtime = \get_option(self::ERR_TIME_SETTING, "");
    if (is_numeric($errtime)) { // Indicates that the previous call (at $errtime) failed
      if (time() - $errtime < 86400) { // The failed call occurred within the last 24 hours
        // Errtime was set after the last call, so we can use it to defeat the cache
        return $errtime;
      } else { // The failed call occurred more than 24 hours ago, so no need to defeat the cache
        \update_option(self::ERR_TIME_SETTING, "");
      }
    }
    return null;
  }

  /**
   * Handles errors from the API by setting error time, setting the err field,
   * and throwing an exception.
   * @param string $message Error message to set in the exception
   * @throws RETS_API_Exception Exception with the given message
   */
  private function handle_error(string $message): void {
    $this->set_err_time();
    $this->err = new RETS_API_Exception($message);
    throw $this->err;
  }

  /**
   * Retrieves a response from the SimplyRETS API based on the type set in the constructor
   * and added parameters.
   *
   * @throws Exception If an error occurs on API call
   * @return array Response, deserialized from JSON into an array.
   *               @see https://docs.simplyrets.com/api/index.html for fields
   */
  public function get_response(): array {   
    // See if we've already gotten a response
    if (!is_null($this->response)) {
      return $this->response;
    }
    // See if we already have an error
    if (!is_null($this->err)) {
      throw $this->err;
    }

    // Get query string
    $query_args = $this->get_query_args();
    // See if we need to defeat the cache
    $errtime = $this->maybe_get_err_time();
    if ($errtime) {
      $query_args .= "&nocache=$errtime";
    }

    // Set the URL w/ constructor query template & query args
    $url = self::REMOTE_URL . "{$this->query}{$query_args}";
    // Set up the POST data with auth and license key
    $license  = OPT\License_Manager::get_instance()->get_key();
    $args = array(
      'headers' => array(
          'Authorization' => 'Basic ' . base64_encode( 'vista:vista' ),
        ),
        'body' => array(
          'key' => $license,
            )
    ); 
    
    // Execute API call
    $response = \wp_remote_post($url, $args); 
    // Check for errors
    if (\is_wp_error($response)) {
        $this->handle_error("API Error {$response->get_error_code()} occurred: " . 
                            $response->get_error_message());
    }
    // Decode response
    $payload = json_decode($response['body'], true);

    // Check for server error
    if (isset($payload['error'])) {
      $this->handle_error("Error returned by API: " . $payload['message']);
    } else if (!isset($payload['headers']) || !isset($payload['body'])) {
      $this->handle_error("Malformed response from VistaWP server: " . 
                          "missing headers or body");
    } else {
        $this->clear_err_time();
    }
  
    $this->response = $payload['body'] ?? array();
    $this->headers = $payload['headers'] ?? array();
    return $this->response;
  }

  /**
   * Get headers from the API response
   * @return array All headers included in the response from the SimplyRETS API
   */
  public function get_headers(): array {
    if (is_null($this->headers)) {
      $this->get_response();
    }
    return $this->headers;
  }
}

/**
 * Custom class for exceptions thrown during API calls
 * from the RETS_API class
 */
class RETS_API_Exception extends \Exception {}