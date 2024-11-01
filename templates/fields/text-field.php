<?php
/**
 * Template for a form row with a text input field rendered by vista_get_template().
 *
 * @param string $id          The HTML ID attribute for the input field.
 * @param string $name        The HTML name attribute for the input field.
 * @param string $label       The label text for the input field.
 * @param string $value       The value of the input field.
 * @param string $placeholder The placeholder text for the input field.
 */

?>

<div class="vista-form-row">
  <div class="vista-form-item">
    <div class="vista-field-text">
      <div class="vista-label">
        <label for="<?= esc_attr($id); ?>"><?= esc_html($label); ?></label>
      </div>
      <div class="vista-input">
        <input type="text" id="<?= esc_attr($id); ?>" name="<?= esc_attr($name); ?>" value="<?= esc_attr($value); ?>" placeholder="<?= esc_attr($placeholder); ?>">
      </div>
    </div>
  </div>
</div>