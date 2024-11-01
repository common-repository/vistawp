<?php
/**
 * Template for the main page of the VistaWP plugin, returned by vista_get_template().
 *
 * @param string $listings      The value returned by the get_option() function if the option exists.
 * @param string $individual    The value returned by the get_option() function if the option exists.
 * 
 */

?>

<!-- Start of HTML content for the default page -->
<div class="vsta-settings-header">
  <img width="125" src="<?php echo esc_html(\vista_plugin_url('/img/vista_logo.png')); ?>">
  <div class="vsta-license-link"><a href="https://vistawp.com">https://vistawp.com</a></div>
</div>

<div class="vsta-settings-container">
  <h1 class="vsta-settings-h1">Welcome to VistaWP!</h1><br />
  <!-- Step 1 -->
  <p class="vsta-settings-steps">Step 1</p>
  <h2 class="vsta-settings-h2">Plugin Overview</h2>
  <p class="vsta-settings-text">Watch the video below to get a basic overview of how vista works</p>
  <iframe width="560" height="315" src="https://www.youtube.com/embed/JbPKnsMac_Q" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
  <br /><br />

  <!-- Step 2 -->
  <p class="vsta-settings-steps">Step 2</p>
  <h2 class="vsta-settings-h2">Create Necessary Pages</h2>
  <p class="vsta-settings-text">By clicking the button below, the <b>essential basic pages</b> will be created. <br/>
    <small>(Note: If you click it more than once, another set of identical pages will be generated. You may need to delete any unnecessary ones <br />
    and ensure that the slugs are correct: 'listings' and 'individual-listing').</small><br /><br />
    The following pages will be generated: <br />
    - <b>Listings</b> (property listings) <br />
    - <b>Individual Listing</b> (Individual Property): This page will display results when viewed using <br />
    the 'view property' button on the previous page (refer to video in step 1)
  </p><br />

  <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
    <input type="submit" name="generate_pages" value="Generate Vista Pages" class="button-primary vsta-gen-btn">
  </form>
  <br />
  
  <!-- Display generated page link -->
<?php
  $message = '';
  if ($listings && $individual) {
    $message = '<a href="edit.php?post_type=page" target="_blank">View the generated pages</a>.';
  } else {
    $message = 'The page was not generated -';
    // Each variable is checked to determine which page was not created
    $message .= empty($listings) ? ' <b>Listings Page</b> -' : '';
    $message .= empty($individual) ? ' <b>Individual Listings Page</b>' : '';
  }

  echo sprintf(
    '<p class="vsta-settings-text">%s</p>',
    wp_kses($message, 'post')
  );
?>

  <br /><br />
  <!-- Step 3 -->
  <p class="vsta-settings-steps">Step 3</p>
  <h2 class="vsta-settings-h2">Set up the Search Form</h2>
  <p class="vsta-settings-text">Now, simply <b>add a property search form to any page</b>, like the Home page, by inserting one of these shortcodes. <br />
    <small>(Note:  Ensure consistent page names to maintain link functionality. Refer to step 1 video).</small>
  </p><br />
  <div class="vsta-forms-display">
    <div>
      <code>[vista_basic_form dest="/listings/"]</code>
    </div>
    <div>
      <code>[vista_advanced_form dest="/listings/"]</code>
    </div>
    <div>
      <code> [vista_search_form dest="/listings/"] </code>
    </div>
  </div>
  <br />

  <div class="vsta-settings-info">
    <h2 class="vsta-settings-h2">All good? Need a license or MLS help? Reach out!</h2>
    <p class="vsta-settings-text">Got questions on connecting your MLS or snagging a VistaWP license?<br />
      Hit us up! Whether it's plugin quirks or just a chat, we're here for you. We can even jump on a video call if needed.
    </p>
    
    <p class="vsta-settings-text">
      <a href="https://vistawp.com/contact/" target="_blank">Contact us</a><br />
      <a href="https://vistawp.com">Check out our website</a><br />
      <a href="https://vistawp.com/docs/" target="_blank">Documentation</a><br />
    </p>
  </div>
</div>