<?php
/**
 * Template for a form row containing a select input field rendered by vista_get_template().
 * 
 * @param string $id          The HTML element's ID attribute.
 * @param string $label       The label text for the input field.
 * @param string $name        The HTML element's name attribute.
 * @param string $value       The default or selected value for the select input.
 * @param array  $options     An associative array of select options.
 * @param string $placeholder The placeholder text for the select input.
 * @param string $selected    The value to be marked as selected.
 */

?>

<div class="vista-form-row">
  <div class="vista-form-item">
    <div class="vista-field-select">
      <div class="vista-label">
        <label for="<?= esc_attr($id); ?>"><?= esc_html($label); ?></label>
      </div>
      <div class="vista-input">
        <select id="<?= esc_attr($id); ?>" name="<?= esc_attr($name); ?>">
          <?php foreach ($options as $value) : ?>
            <option value="<?= esc_attr($value); ?>" <?= '' === $value ? 'disabled' : ''; ?> <?= ($value === $selected) ? 'selected' : ''; ?>>
                <?= '' === $value ? esc_html($placeholder) : esc_html($value); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>
</div>