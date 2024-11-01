<?php
namespace VSTA\API;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Retrieves parameters for the SimplyRETS API from $_GET
 *
 * @author Nate Lanza
 */
class Get_Analytics_Params extends Get_Params {

  /**
   * Initializes a new instance of this class
   */
  public function __construct() {
    parent::__construct();

    $this->mappings = array(
      'vista-offset' => 'offset',
      'vista-limit' => 'limit',
      'vista-q' => 'q',
      'vista-status' => 'status',
      'vista-type' => 'type',
      'vista-subtype' => 'subtype',
      'vista-agent' => 'agent',
      'vista-salesAgent' => 'salesAgent',
      'vista-brokers' => 'brokers',
      'vista-minprice' => 'minprice',
      'vista-maxprice' => 'maxprice',
      'vista-minarea' => 'minarea',
      'vista-maxarea' => 'maxarea',
      'vista-minbaths' => 'minbaths',
      'vista-maxbaths' => 'maxbaths',
      'vista-minbeds' => 'minbeds',
      'vista-maxbeds' => 'maxbeds',
      'vista-maxdom' => 'maxdom',
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
      'vista-counties' => 'counties',
      'vista-points' => 'points',
      'vista-idx' => 'idx',
      'vista-include' => 'include',
      'vista-sort' => 'sort',
      'vista-count' => 'count',
      'offset' => 'offset', // Backward Compatibility of Parameters (without prefix "vista-")
      'limit' => 'limit',
      'q' => 'q',
      'status' => 'status',
      'type' => 'type',
      'subtype' => 'subtype',
      'agent' => 'agent',
      'salesAgent' => 'salesAgent',
      'brokers' => 'brokers',
      'minprice' => 'minprice',
      'maxprice' => 'maxprice',
      'minarea' => 'minarea',
      'maxarea' => 'maxarea',
      'minbaths' => 'minbaths',
      'maxbaths' => 'maxbaths',
      'minbeds' => 'minbeds',
      'maxbeds' => 'maxbeds',
      'maxdom' => 'maxdom',
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
      'counties' => 'counties',
      'points' => 'points',
      'idx' => 'idx',
      'include' => 'include',
      'sort' => 'sort',
      'count' => 'count',
    );
  }

}





 ?>
