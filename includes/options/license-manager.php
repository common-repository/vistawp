<?php
namespace VSTA\Options;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use \VSTA as V;

/**
 * Manages the user's license key, including registering the settings page with Options_Display,
 * updating the database option based on the settings page, and providing
 * an API for other classes to retrieve the license key and pricing tier
 */
class License_Manager {

  /**
   * Name of the option in the WP database that stores the license key
   * @var string
   */
  private const LICENSE_KEY_OPTION = 'vista_license_key';

  /**
   * Name of the option in the WP database that stores the pricing tier
   * @var string
   */
  private const TIER_OPTION = 'vista_tier';

  /**
   * Name of the option in the WP database that stores whether the license is valid
   * @var string
   */
  private const VALID_OPTION = 'vista_license_valid';

  /**
   * If the user should be able to set the pricing tier in the admin panel,
   * set this to NULL and use the below two constants to set up choices.
   * Otherwise, if this is set to an int or string, that will be used
   * as the pricing tier ID/product ID when calling the EDD API on vistawp.com
   * 
   * @var string|int
   */
  private const TIER = 23071;

  /**
   * Maps names of pricing tiers to their item ID in EDD on vistawp.com
   * Names are used to populate the settings page dropdown,
   * IDs are used to validate the license key with the API.
   * @var array<string>
   */
  private const TIER_ID = array(
    "VistaWP Monthly Subscription" => "23071",
    "Vista Yearly Subscription" => "20670",
  );

  /**
   * Maps string tier names to int levels
   * Other classes in this plugin may use these levels 
   * to determine whether certain features are accessible to the user,
   * although tiered features currently are not implemented.
   * @var array<int>
   */
  private const TIER_LEVEL = array(
    "VistaWP Monthly Subscription" => 1,
    "Vista Yearly Subscription" => 1,
  );

  /**
   * Singleton instance of this class
   */
  private static ?License_Manager $instance = NULL;

  /**
   * License key
   * @var string
   */
  private ?string $key;

  /**
   * The pricing tier of this license
   * Possible options are set as keys in self::TIER_ID
   * @var string
   */
  private ?string $tier;

  /**
   * Validity of this license
   * True if license has been activated successfully, else false
   * @var bool
   */
  private bool $valid;

  /**
   * Retrieve the singleton instance of this class
   * Initializes the class if not already initialized
   * @return License_Manager Singleton class instance
   */
  public static function get_instance(): License_Manager {
    if (is_null(self::$instance)) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * Sets key from vista_license_key option
   * Sets tier from vista_tier option
   * Registers options page
   */
  protected function __construct() {
    $this->key = \get_option(self::LICENSE_KEY_OPTION, NULL);
    $this->tier = \get_option(self::TIER_OPTION, NULL);
    $this->valid = (bool)\get_option(self::VALID_OPTION, false);

    V\Main::get_instance()->register_style(
      'vsta_admin', 
      \vista_plugin_url("/css/admin.css")
    );

    if (\is_admin()) {
      V\Main::get_instance()->enqueue_style('vsta_admin');
    }

    $page = new Options_Page(
      "License Key",
      "License",
      Options_Display::VISTA_CAPABILITY,
      "vista_license",
      array($this, 'settings_page'),
      12,
    );
    $page->register();
  }

  /**
   * Processes submission of the license key form.
   * 
   * Starts by checking for the deactivation button- 
   * if found, clears the license key and redirects to the same page.
   * 
   * Otherwise, checks if a new license key is set and takes action based on tier:
   * If self::TIER is not set:
   * - Will not update if POST params vsta-license-key and vsta-tier are not both set.
   * - Will not update if vsta-tier is not a key in TIER_ID.
   * If self::TIER is set, will update only if $_POST['vsta-license-key'] is set
   * 
   * Once the above conditionals are satisfied, sanitizes fields and passes
   * them to api_verify to validate the key with the API and potentially update
   */
  protected function maybe_update() {
    // Clear license key if the user clicks the "Clear" button
    if (isset($_POST['deactivate']) && $_POST['deactivate'] === '1') {
      $this->clear_key();
      // Redirect to the same page after clearing the license key
      \vista_safe_redirect(\esc_url($_SERVER['REQUEST_URI']));
      exit;
    }

    // Otherwise, update the license key if the user submits the form
    if (
      (isset($_POST['vsta-license-key']) && !is_null(self::TIER)) ||
      (isset($_POST['vsta-license-key']) &&
      isset($_POST['vsta-tier']) &&
      array_key_exists($_POST['vsta-tier'], self::TIER_ID))
    ) {
      // Data sanitization
      $key = \sanitize_key($_POST['vsta-license-key']);
      if (is_null(self::TIER)) {
        $tier = \sanitize_text_field($_POST['vsta-tier']);
      } else {
        $tier = NULL;
      }
      
      // Validate with API. This outputs the result html to the page
      $this->api_verify($key, $tier);
    }
  }

  /**
   * Validates the key with the API
   * and outputs HTML to the page indicating the result.
   * Sets $this->valid (and the corresponding WP setting),
   * as well as $this->key and $this->tier if the key is valid.
   * @param string $key License key to validate
   * @param ?string $tier Pricing tier name, or NULL to use self::TIER instead
   * @throws InvalidArgumentException if !self::TIER && !$tier
   * @throws InvalidArgumentException if $tier is set but not a key in self::TIER_ID
   * @return bool True if the key is valid, else false
   */
  protected function api_verify(string $key, ?string $tier): bool {
    if (!self::TIER && !$tier) {
      throw new \InvalidArgumentException(
        "Must provide a tier if self::TIER is not set"
      );
    }
    if ($tier && !array_key_exists($tier, self::TIER_ID)) {
      throw new \InvalidArgumentException(
        "Invalid tier: " . $tier
      );
    }

    // Here we retrive the tier ID from the mapping, 
    // if a single tier is not hardcoded
    $api = new \VSTA\API\License_API(
      $key, 
      self::TIER ?? self::TIER_ID[$tier]
    );
    $result = $api->activate();

    // API returns null for success; update options accordingly
    if (is_null($result)) {
      $this->set_valid_key($key);
      $this->set_tier($tier);
      return true;
    } else {
      ?>
      <div class="notice notice-error">
        Error: <?php echo esc_html($result) 
      ?></div><?php

      $this->clear_key();
      return false;
    }
  }

  /**
   * Outputs HTML to display the settings page for the license key
   */
  public function settings_page(): void {
    ?><br><?php
    $this->maybe_update();
    $licenseKey = $this->get_key();
    $licenseStatus = $licenseKey ? 'Active' : 'Disabled';
    ?>

    <div class="vsta-settings-header"> 
      <img width="125" src="
        <?php echo esc_html(\vista_plugin_url('/img/vista_logo.png')); ?>
      " >
      <div class="vsta-license-link">
        <a href="https://vistawp.com#pricing">https://vistawp.com</a>
      </div>
    </div>

    <div class="vsta-settings-container">
    <h2 class="vsta-settings-h2">License Settings</h2>
    <br>
    <p class="vsta-settings-text">
      To see your MLS data, you will need an active license key 
      and your MLS connected with VistaWP.
    </p>
    <br>

    <form method="post" id="vsta-setttings-form">
      <label class="vsta-license-label" for="vsta-license-key">License Key</label>
      <input
        class="regular-text"
        name="vsta-license-key"
        id="vsta-license-key"
        type="text"
        value="<?php echo esc_html($this->get_key()); ?>"
      >

      <?php
      // If the tier is hardcoded, don't show the dropdown. Otherwise,
      // this displays a dropdown to select the user's pricing tier 
      if (is_null(self::TIER)): 
      ?>
      <br>
      <label class="vista-license-label" for="vsta-tier">Subscription Tier</label>
      <select name="vsta-tier" id="vsta-tier" required>
        <?php
        foreach (self::TIER_ID as $name => $unused) {
          if ($name == $this->get_tier()) {
            echo "<option selected>" . esc_html($name) . "</option>";
          } else {
            echo  "<option>" . esc_html($name) . "</option>" ;
          }
        }
         ?>
      </select>
      <?php endif; ?>

      <input type="submit" class="vsta-btn-go" name="submit" value="Activate">
      <button 
        class="vsta-btn-danger" 
        id="vsta-deactivate-btn" 
        name="deactivate" 
        value="1">Clear</button>
      <label class="vsta-license-label">
        Clear your license key to use dummy data.
      </label>
    </form>
    <br>
    <p class="vsta-settings-text">License Status
      <span class="vsta-<?php echo esc_html(strtolower($licenseStatus)); ?> "> 
        <?php echo esc_html($licenseStatus); ?> 
      </span> 
    </p> 

    <br><br>
    <h2 class="vsta-settings-h2">Don't have a license?</h2>
    <br>
    <p class="vsta-settings-text">
      You'll need to setup an account and get your license key 
      <span><a href="https://vistawp.com/">Sign up here</a></span>
    </p>
    <br><br>
    <p class="vsta-settings-text">
      <a href="https://vistawp.com/support/">Need Help?</a>
    </p>
  </div>

  <?php
  }

  /**
   * Sets the license key and marks it as valid.
   * This key should have already been validated with the API.
   * @param string $key License key
   */
  private function set_valid_key(string $key): void {
    $this->key = $key;
    $this->valid = true;
    \update_option(self::LICENSE_KEY_OPTION, $key);
    \update_option(self::VALID_OPTION, true);
  }

  /**
   * Clears the license key and marks it as invalid,
   * and clears the tier.
   */
  private function clear_key(): void {
    $this->key = NULL;
    $this->valid = false;
    $this->tier = NULL;
    \delete_option(self::LICENSE_KEY_OPTION);
    \delete_option(self::VALID_OPTION);
    \delete_option(self::TIER_OPTION);
  }

  /**
   * Sets the tier of this license
   * @param ?string $tier Tier name- if null, does nothing
   */
  private function set_tier(?string $tier): void {
    if (is_null($tier))
      return;
      
    $this->tier = $tier;
    \update_option(self::TIER_OPTION, $tier);
  }

  /**
   * Return the license key
   * Returns NULL if the license key has not been set
   * @return string License key, or NULL if not set
   */
  public function get_key(): ?string {
    return $this->key;
  }

  /**
   * @return bool True if the current license key is valid, else false
   */
  public function is_valid(): bool {
    return $this->valid;
  }

  /**
   * Gets the level of the pricing tier of this subscription.
   * Mappings from tiers to levels are found in TIER_LEVEL.
   * Currently unused.
   * @return int Pricing tier level
   */
  public function get_tier(): int {
    if (!$this->valid)
      return 0;
    return self::TIER ?? 
      (is_null($this->tier) ? 0 : self::TIER_LEVEL[$this->tier]);
  }
}