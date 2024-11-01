<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if(! function_exists('vista_get_template')) {
  /**
   * Loads a template file from the specified template directory.
   *
   * This function attempts to include the specified template file from the template
   * directory defined by the VISTA__PLUGIN_DIR constant. If the file does not exist,
   * it returns an error message.
   * 
   * Each template includes specifications for the variables used therein.
   *
   * @param string $template_name The name of the template file to load.
   * @param array  $args  Optional. An associative array of variables to pass to the template.
   *
   * @return string|void  The content of the template if it's found, or an exception if the template is missing.
   * 
   * @throws \Exception If the specified template is not found at the given path.
   */
  function vista_get_template(string $template_name, array $args = array()): void {
    $default_path = VISTA__PLUGIN_DIR . 'templates/';
    $template = $default_path . $template_name;

    if (! file_exists( $template )) {
      throw new \Exception('The template file is missing.');
    }

    if (! empty( $args )) {
      extract( $args );
    }

    include $template;
  }
}

// Add the str_contains function from php 8
// based on original work from the PHP Laravel framework
if (!function_exists('str_contains')) {
  function str_contains($haystack, $needle) {
    return $needle !== '' && mb_strpos($haystack, $needle) !== false;
  }
}

if (!function_exists('vista_prepare_field_value')) {
  /**
   * Sanitizes and filters a value, preparing it for a received query parameter
   *
   * @param string $value - Value of the query parameter to be sanitized and filtered
   * 
   * @return Array $value - Array containing the filtered values,
   * (e.g., array( '98877034', '98870614', '98886014') as a result of a string input '98877034,98870614,98886014')
   */
  function vista_prepare_field_value(string $value): array {
    $sanitized_value = sanitize_text_field($value);
    $value = preg_split(
      '/(%2C\+)|(, )|\+|,\s*/',
      $sanitized_value,
      -1,
      PREG_SPLIT_NO_EMPTY
    );

    return $value;
  }
}

if (!function_exists('vista_plugin_url')) {
  /**
   * Helper function to construct relative URLs to files in this plugin.
   * This function converts a path relative to the plugin root directory to
   * a full path, regardless of plugin file renaming.
   * 
   * @param string $path The path to the file, relative to the plugin root directory
   */
  function vista_plugin_url(string $path): string {
    return plugins_url(
      $path, 
      VISTA__PLUGIN_DIR . '/vista'
    );
  }
}

if (!function_exists('vista_safe_redirect')) {
  /**
   * Safely redirects to another page 
   * without the risk of the headers already being sent.
   * @param string $dest The destination URL
   * @param int $status The HTTP status code to send
   */
  function vista_safe_redirect(string $dest, int $status = 302) {
    if ( ! $dest ) {
      return false;
    }

    // Check if headers have already been sent
    if (did_action('wp_loaded') || doing_action('wp_loaded')) {
      // Code copied from body of wp_safe_redirect
      $dest = wp_sanitize_redirect( $dest );
	    $fallback_url = apply_filters( 'wp_safe_redirect_fallback', admin_url(), $status);
	    $dest = wp_validate_redirect($dest, $fallback_url);
      // Uses JS instead of modifying headers
      echo("<script>location.href = '{$dest}'</script>");
    } else { // Headers haven't been sent, can redirect
      wp_safe_redirect($dest, $status);
    }
    exit;
  }
}