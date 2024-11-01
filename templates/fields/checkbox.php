<?php
/**
 * Template for a form row containing a group of checkboxes rendered by vista_get_template().
 *
 * @param string $name      The name attribute for the checkbox group.
 * @param string $title     The title or label for the checkbox group.
 * @param string $prefix    A prefix to add to the IDs of individual checkboxes.
 * @param array  $options   An array of checkbox options where the keys represent labels, and the values represent values.
 */

?>

<div class="vista-form-row">
  <div class="vista-form-item">
    <div class="vista-field-checkbox">
      <div class="vista-label">
        <label for="<?= esc_attr($name); ?>"><?= esc_html($title); ?></label>
      </div>
      <div class="vista-input">
        <?php foreach ($options as $key => $value) : ?>
          <label for="<?= esc_attr($prefix . $value); ?>">
            <input type="checkbox" id="<?= esc_attr($prefix . $value); ?>" name="<?= esc_attr($name); ?>[]" value="<?= esc_attr($value); ?>" <?= (in_array($value, $checked_options)) ? 'checked' : ''; ?> >
            <?= esc_html($key); ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>