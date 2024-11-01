<?php
namespace VSTA\Analytics;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Represents information from a query to the Analytics portion of the SimplyRETS API
 *
 * @author Nate Lanza
 */
class Analytics extends \VSTA\API_Data {

  /**
   * Error message to output when 0 results match a query
   * @var string
   */
  protected const NO_ANALYTICS_MSG = "No analytics matched your query";

  /**
   * OVERRIDE METHOD
   * Initializes mappings in $this->mappings
   * Each mapping has the name of a field from the API call as key,
   * mapped to a callable function in this class which can process
   * the data contained in that field and store it in $this->fields
   * as HTML which can be echoed onto the page.
   */
  protected function init_mappings(): void {
    $this->mappings = array(
      'areaDistribution' => array($this, 'process_area_distribution'),
    );

    $this->number_fields = array(
      'sqftPrice',
      'avgPrice',
    );
  }

  /**
   * Constructor. Calls parent
   * @param array $api_data Deserialized data from the API. Should contain analytics data
   */
  public function __construct(array $api_data) {
    parent::__construct($api_data);

    // Make price/sqft field
    $this->price_sqft($api_data['avgPrice'], $api_data['avgLivingArea']);
    // Show no results message if we have no results
    if ($api_data['totalCount'] == 0) {
      array_walk($this->fields, function(array &$fields, $index){
        $fields[$index] = self::NO_ANALYTICS_MSG;
      });
    }
  }

  /**
   * Adds a field called sqftPrice which represents the average price/sqft for all listings retrieved
   * @param  float $price  Average price
   * @param  float $sqft   Average square footage
   */
  protected function price_sqft(?float $price, ?float $sqft): void {
    if ($sqft == 0)
      $this->try_process_field('sqftPrice', NULL);
    else
      $this->try_process_field('sqftPrice', $price / $sqft);
  }

  /**
   * Processes the areaDistribution field into $this->fields, as
   * a table of areas & counts
   * @param string $name  Field name
   * @param string $value Field value
   */
  protected function process_area_distribution(string $name, ?array $value): void {
    // Init table
    $result =
    "<table class='vista-area-distribution'>
      <thead>
        <tr>
          <th>Area</th>
          <th>Listings</th>
        </tr>
      </thead>
      <tbody>
    ";

    // Add rows to table
    foreach ($value as $area => $count) {
      $result .=
      "<tr>
        <td>$area</td>
        <td>$count</td>
      </tr>";
    }

    // Close table
    $result .= "</tbody></table>";

    // Add field
    $this->try_process_field($name, $result);
  }

}











 ?>
