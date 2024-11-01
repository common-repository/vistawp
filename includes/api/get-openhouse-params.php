<?php

namespace VSTA\API;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


/**
 * Retrieves listing parameters for the SimplyRETS API from $_GET
 *
 * @author Nate Lanza
 */
class Get_Openhouse_Params extends Get_Params {

  /**
   * Initializes a new instance of this class
   */
  public function __construct() {
    parent::__construct();

    /**
     * Maps URL parameters to SimplyRETS API parameters
     */
    $this->mappings = array(
      'vista-offset' => 'offset', // Required for pagination
      'vista-limit' => 'limit', // Required for pagination
      'vista-type' => 'type',
      'vista-listingId' => 'listingId',
      'vista-cities' => 'cities',
      'vista-brokers' => 'brokers',
      'vista-agent' => 'agent',
      'vista-minprice' => 'minprice',
      'vista-startdate' => 'startdate',
      'vista-lastId' => 'lastId',
      'vista-sort' => 'sort',
      'vista-include' => 'include',
      'offset' => 'offset', // Backward Compatibility of Parameters (without prefix "vista-")
      'limit' => 'limit',
      'type' => 'type',
      'listingId' => 'listingId',
      'cities' => 'cities',
      'brokers' => 'brokers',
      'agent' => 'agent',
      'minprice' => 'minprice',
      'startdate' => 'startdate',
      'lastId' => 'lastId',
      'sort' => 'sort',
      'include' => 'include',
    );
  }
}



?>
