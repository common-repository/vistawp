<?php
/**
 * Template for the shotcode simple listings, returned by vista_get_template().
 *
 * @param string $theme      - Name of the theme to display.
 * @param string $pagination - Show pagination options "yes" or "no".
 * @param string $dest       - Slug for the page that displays the individual property details (requires a slash at the beginning and end).
 * 
 */

?>

<!-- show pagination  -->
<?php if($pagination == 'yes') : ?>
<div class="vista-sl-pagination">
  <div class="vista-sl-results">
    <label class="vista-sl-<?=$theme?>-results-label">[vista_listings_total] results</label>
  </div>
  <div class="vista-sl-<?=$theme?>-prev">
    [vista_listings_paginator type=backward]Prev[/vista_listings_paginator]
  </div>
  <div class="vista-sl-<?=$theme?>-next">
    [vista_listings_paginator type=forward]Next[/vista_listings_paginator]
  </div>
</div>
<?php endif; ?>

<div class="vista-sl-container">
  [vista_listings_list]
  <div class="vista-sl-card vista-sl-<?=$theme?>-card">
    <div class="vista-sl-photo">
      <a href="<?=$dest . '?listing='?>[mlsId]" class="vista-sl-photo-link">
        [first-photo]
      </a>
    </div>
    
    <div class="vista-sl-<?=$theme?>-address">
      <a href="<?=$dest . '?listing='?>[mlsId]" class="vista-sl-address-link">
        <h2>[address]</h2>
      </a>
    </div>

    <div class="vista-sl-<?=$theme?>-price">
      <p>$[listPrice]</p>
    </div>

    <div class="vista-sl-info">
      <div class="vista-sl-<?=$theme?>-beds">
        <p>[bedrooms]</p><p>Beds</p>
      </div>
      <div class="vista-sl-<?=$theme?>-baths">
        <p>[baths]</p><p>Baths</p>
      </div>
      <div class="vista-sl-<?=$theme?>-sqft">
        <p>[sqft]</p><p>Sq. Ft.</p>
      </div>
    </div>

    <div class="vista-sl-agent-info">
      <div class="vista-sl-<?=$theme?>-listingid">
        <p>ID: #[listingId]</p>
      </div>
      <div class="vista-sl-<?=$theme?>-status">
        <p>Status: [status]</p>
      </div>
    </div>

    <div class="vista-sl-<?=$theme?>-btn">
      <a href="<?= \get_home_url() . $dest . '?listing='?>[mlsId]" class="vista-sl-<?=$theme?>-link">View Property</a>
    </div>

  </div>
  [/vista_listings_list]
  
</div>

<!-- show pagination  -->
<?php if($pagination == 'yes') : ?>
<div class="vista-sl-pagination">
  <div class="vista-sl-results">
    <label class="vista-sl-<?=$theme?>-results-label">[vista_listings_total] results</label>
  </div>
  <div class="vista-sl-<?=$theme?>-prev">
    [vista_listings_paginator type=backward]Prev[/vista_listings_paginator]
  </div>
  <div class="vista-sl-<?=$theme?>-next">
    [vista_listings_paginator type=forward]Next[/vista_listings_paginator]
  </div>
</div>
<?php endif; ?>