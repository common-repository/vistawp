<?php

namespace VSTA\API;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


/**
 * Retrieves listing parameters for the SimplyRETS API from $_GET
 *
 * @author Nate Lanza
 */
class Get_Listing_Params extends Get_Params {

  /**
   * Initializes a new instance of this class
   */
  public function __construct() {
    parent::__construct();

    $this->mappings = self::get_param_list();
  }
  
  /**
   * Gets the default query parameters for a listing
   *
   * @return Array
   */
  public static function get_param_list() {
    return array(
      'vista-offset' => 'offset', // Required for pagination
      'vista-limit' => 'limit', // Required for pagination
      'vista-q' => 'q',
      'vista-status' => 'status',
      'vista-type' => 'type',
      'vista-subtype' => 'subtype',
      'vista-subTypeText' => 'subTypeText',
      'vista-agent' => 'agent',
      'vista-salesAgent' => 'salesAgent',
      'vista-brokers' => 'brokers',
      'vista-specialListingConditions' => 'specialListingConditions',
      'vista-ownership' => 'ownership',
      'vista-minprice' => 'minprice',
      'vista-maxprice' => 'maxprice',
      'vista-minarea' => 'minarea',
      'vista-maxarea' => 'maxarea',
      'vista-minbaths' => 'minbaths',
      'vista-maxbaths' => 'maxbaths',
      'vista-minbeds' => 'minbeds',
      'vista-maxbeds' => 'maxbeds',
      'vista-maxdom' => 'maxdom',
      'vista-minlistdate' => 'minlistdate',
      'vista-maxlistdate' => 'maxlistdate',
      'vista-minyear' => 'minyear',
      'vista-maxyear' => 'maxyear',
      'vista-minacres' => 'minacres',
      'vista-maxacres' => 'maxacres',
      'vista-minGarageSpaces' => 'minGarageSpaces',
      'vista-maxGarageSpaces' => 'maxGarageSpaces',
      'vista-lastId' => 'lastId',
      'vista-postalCodes' => 'postalCodes',
      'vista-features' => 'features',
      'vista-exteriorFeatures' => 'exteriorFeatures',
      'vista-water' => 'water',
      'vista-neighborhoods' => 'neighborhoods',
      'vista-cities' => 'cities',
      'vista-state' => 'state',
      'vista-counties' => 'counties',
      'vista-points' => 'points',
      'vista-idx' => 'idx',
      'vista-include' => 'include',
      'vista-sort' => 'sort',
      'vista-count' => 'count',
      'vista-listing_ids' => 'listing_ids',
      'vista-mls_area' => 'mls_area',
      'offset' => 'offset', // Backward Compatibility of Parameters (without prefix "vista-")
      'limit' => 'limit',
      'q' => 'q',
      'status' => 'status',
      'type' => 'type',
      'subtype' => 'subtype',
      'subTypeText' => 'subTypeText',
      'agent' => 'agent',
      'salesAgent' => 'salesAgent',
      'brokers' => 'brokers',
      'specialListingConditions' => 'specialListingConditions',
      'ownership' => 'ownership',
      'minprice' => 'minprice',
      'maxprice' => 'maxprice',
      'minarea' => 'minarea',
      'maxarea' => 'maxarea',
      'minbaths' => 'minbaths',
      'maxbaths' => 'maxbaths',
      'minbeds' => 'minbeds',
      'maxbeds' => 'maxbeds',
      'maxdom' => 'maxdom',
      'minlistdate' => 'minlistdate',
      'maxlistdate' => 'maxlistdate',
      'minyear' => 'minyear',
      'maxyear' => 'maxyear',
      'minacres' => 'minacres',
      'maxacres' => 'maxacres',
      'minGarageSpaces' => 'minGarageSpaces',
      'maxGarageSpaces' => 'maxGarageSpaces',
      'lastId' => 'lastId',
      'postalCodes' => 'postalCodes',
      'features' => 'features',
      'exteriorFeatures' => 'exteriorFeatures',
      'water' => 'water',
      'neighborhoods' => 'neighborhoods',
      'cities' => 'cities',
      'state' => 'state',
      'counties' => 'counties',
      'points' => 'points',
      'idx' => 'idx',
      'include' => 'include',
      'sort' => 'sort',
      'count' => 'count',
      'listing_ids' => 'listing_ids',
      'mls_area' => 'mls_area',
    );
  }

}