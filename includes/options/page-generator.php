<?php
namespace VSTA\Options;

/**
 * Class Generate_Pages
 * 
 * This class is responsible for programmatically creating different types of WordPress pages.
 * 
 * @package VSTA\Options
 */
class Generate_Pages {
  /**
   * Prefix used for option names to store page IDs.
   * @var string
   */
  public const OPTION_PREFIX = 'vistawp_page_id_';

  /**
   * Option name for the 'listings' page.
   * @var string
   */
  public const LISTINGS_OPTION = self::OPTION_PREFIX . 'listings';

  /**
   * Option name for the 'individual' page.
   * @var string
   */
  public const INDIVIDUAL_OPTION = self::OPTION_PREFIX . 'individual';

  /**
   * Option name for the 'openhouse' page.
   * @var string
   */
  public const OPENHOUSE_OPTION = self::OPTION_PREFIX . 'openhouse';
  
  /**
   * An array that maps page types to their respective content templates.
   * @var array
   */
  const PAGE_CONTENTS = [
    self::LISTINGS_OPTION => '[vista_simple_listings theme="light" dest="individual-listing" pagination="yes"]',
    self::INDIVIDUAL_OPTION => '<h2>Property photos and address</h2>
                                [vista_listing_field field=address]
                                [vista_listing_field field=first-photo]
                                <h2>Property info </h2>
                                <p> Status  [vista_listing_field field=status] </p>
                                <p> bedrooms  [vista_listing_field field=bedrooms] </p>
                                <p> bathrooms  [vista_listing_field field=bathrooms] </p>
                                <p> Square-feet [vista_listing_field field=sqft] </p>
                                <p> Lot Size Area [vista_listing_field field=lotSizeArea] </p>
                                <p> Square-foot pricing  [vista_listing_field field=sqftPrice] </p>', // Content template for 'individual' type
    self::OPENHOUSE_OPTION => '[vista_openhouse_list]
                                  [first-photo]
                                  <p>Price $[listPrice]</p>
                                  <h2> Address info </h2>
                                  <h4>[streetNumber] [streetName] <br>
                                    [city], [state] [postalCode]</h4>
                                  <span><strong>Status:</strong> [status]</span>
                                  <h2>Property info</h2>
                                    <p> Beds [bedrooms] </p>
                                    <p> Baths [bathrooms] </p>
                                    <p> Square-feet [sqft] </p>
                                    <h2> Agent info </h2>
                                    <br><p>Listing ID #[listingId]</p>
                                    <p>Listing Courtesy of: [office-servingName]</p>
                                  [/vista_openhouse_list]',
  ];
  
  /**
   * Auto create the specified type of page.
   * 
   * @param string $option_name The option name for the specific page type.
   * @param string $title The title of the page to be created.
   * @return bool|null Returns false if an error occurs, otherwise, no explicit return value.
   */
  public function auto_create_page(string $option_name, string $title): ?bool {
    // Check if the provided option name is valid
    if (!array_key_exists($option_name, self::PAGE_CONTENTS)) {
      \delete_option($option_name); // Delete the option if the option name is invalid
      return false;
    }
    
    $content = self::PAGE_CONTENTS[$option_name];
    
    // Define the parameters for creating the WordPress page
    $page = array(
      'post_title'    => $title,
      'post_content'  => $content,
      'post_status'   => 'publish',
      'post_type'     => 'page',
      'post_parent'   => 0,
      'page_template' => '',
    );
    
    // Try insert page; handle failure
    $page_id = \wp_insert_post($page);    
    if (\is_wp_error($page_id)) {
      \delete_option($option_name); // Delete the option if page creation failed
      return false;
    }
    
    \update_option($option_name, $page_id); // Update the option with the page ID
    return true;
  }
}