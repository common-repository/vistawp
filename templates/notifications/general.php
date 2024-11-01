<div class="notice is-dismissible notice-<?= esc_attr($type); ?>">
  <div id="vistawp-banner">
    <img height="50" src="<?= esc_url(\vista_plugin_url('img/vista_banner_icon.svg')); ?>">
    <p class="vsta-text-<?= esc_attr($type); ?>"> <?= esc_html($text) ?> </p>
  </div>
</div>