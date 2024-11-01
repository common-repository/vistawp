<?php
/**
 * Template for a form row containing a number input field rendered by vista_get_template().
 *
 * @param string $id          The HTML element's ID attribute.
 * @param string $label       The label text for the input field.
 * @param string $name        The HTML element's name attribute.
 * @param string $value       The default value for the number input.
 * @param string $placeholder The placeholder text for the number input.
 */

?>

<div class="vista-form-row">
  <div class="vista-form-item">
    <div class="vista-field-number">
      <div class="vista-label">
        <label for="<?= esc_attr($id); ?>"><?= esc_html($label); ?></label>
      </div>
      <div class="vista-input">
        <input type="number" step="1000" min="0" id="<?= esc_attr($id); ?>" name="<?= esc_attr($name); ?>" value="<?= esc_attr($value); ?>" placeholder="<?= esc_attr($placeholder); ?>">
      </div>
    </div>
  </div>
</div>