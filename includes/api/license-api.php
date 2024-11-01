<?php
namespace VSTA\API;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class License_API {

 /**
  * Handles interactions with the License Key API,
  * currently implemented by Easy Digital Downloads
  *
  * @author Nate Lanza
  */

  /**
   * Message to display if the user didn't enter a license key
   * @var string
   */
  protected const NO_KEY_MSG = "Please enter a license key";

  /**
   * Exception message for frontend display if an API error occurs
   * @var string
   */
  protected const API_ERR_MSG = 'Bad API Response';

  /**
   * URL of the remote site, where EDD/software licensing is installed
   * @var string
   */
  protected const REMOTE_URL = 'https://vistawp.com';

  /**
   * Maps error response fields from the EDD API to human-readable
   * error messages for display on the frontend
   * @var array
   */
  private const ERR_MAP = array(
    "missing" => "Could not activate license: License key doesn't exist",
    "missing_url" => "SITE ERROR: URL not provided. Please contact vista support",
    "license_not_activable" => "Could not activate license: Attempting to activate a bundle's parent license",
    "disabled" => "Could not activate license: License key revoked",
    "no_activations_left" => "Could not activate license: No activations left",
    "expired" => "Could not activate license: License has expired",
    "key_mismatch" => "Could not activate license: License is not valid for the subscription tier you selected",
    "invalid_item_id" => "Could not activate license: License is not valid for the subscription tier you selected",
    "item_name_mismatch" => "Could not activate license: License is not valid for the subscription tier you selected",
  );

  /**
   * License key
   * @var string
   */
  private string $key;

  /**
   * ID of the vista product on the remote site
   * @var string
   */
  private string $item_id;

  /**
   * URL of this site
   * Retrieved from get_site_url
   * @var string
   */
  private string $url;

  /**
   * New instance of this class
   * @param string $license_key License key to use for API calls
   *                            If this is "" or NULL, API calls will not be made but return as failed
   * @param string $item_id     ID of the product on the vistawp site
   */
  public function __construct(?string $license_key, string $item_id) {
    $this->key = $license_key;
    $this->item_id = $item_id;
    $this->url = \get_site_url();
  }

  /**
   * Checks whether the license key is activated
   * If $this->key evaluates to false, false is returned without calling API
   * @return bool True if activated, else false
   */
  public function check(): bool {
    if (!$this->key)
      return false;
    $response = $this->call_api('check_license');
    if (empty($response['success']))
      return false;

    return $response['success'];
  }

  /**
   * Activates the license key
   * @return string NULL if no error, or an error message if an error occurs
   */
  public function activate(): ?string {
    if (!$this->key)
      return self::NO_KEY_MSG; // For frontend display
    $response = $this->call_api('activate_license');
    if (!isset($response['success']))
      throw new \Exception(self::API_ERR_MSG);

    if ($response['success'])
      return NULL;
    else {
      return self::ERR_MAP[$response['error']];
    }

  }


  /**
   * Calls the API with a given request type
   * @param  string $type Call type- see options (edd_action param) at
   *                      @link https://docs.easydigitaldownloads.com/article/1072-software-licensing-api
   * @return array  Response as array, or empty array if $this->key is not set
   */
  protected function call_api(string $type): array {
    $args = array(
      'timeout' => 15,
      'sslverify' => true,
    );

    $remote_url = self::REMOTE_URL . "?edd_action=$type&item_id={$this->item_id}&license={$this->key}&url={$this->url}";


    $response = \wp_remote_post( $remote_url, $args );

    if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
      throw new \Exception(self::API_ERR_MSG);
    }

    return json_decode( wp_remote_retrieve_body( $response ), true ) ?? array();
  }


}



 ?>