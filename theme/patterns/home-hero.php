<?php
/**
 * Title: Home hero
 * Slug: givelifewp/home-hero
 * Categories: banner
 * Inserter: no
 * Description: Headline, intro, calls to action and photo.
 *
 * @package givelifewp
 */

?>
<!-- wp:group {"align":"wide","className":"givelifewp-hero","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide givelifewp-hero"><!-- wp:group {"className":"givelifewp-hero__text","layout":{"type":"default"}} -->
<div class="wp-block-group givelifewp-hero__text"><!-- wp:paragraph {"className":"givelifewp-eyebrow"} -->
<p class="givelifewp-eyebrow">A community nudge to give blood</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"className":"givelifewp-display"} -->
<h1 class="wp-block-heading givelifewp-display">Sometimes a reminder is <em>all it takes.</em></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"givelifewp-lead"} -->
<p class="givelifewp-lead">GiveLifeWP is a non-commercial community project with one hope: that more people give, starting with blood. Clear answers, honest guidance and the occasional gentle nudge, for whenever you’re ready.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"givelifewp-button"} -->
<div class="wp-block-button givelifewp-button"><a class="wp-block-button__link wp-element-button" href="/find-a-donor-service/">Find where to give</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline givelifewp-button"} -->
<div class="wp-block-button is-style-outline givelifewp-button"><a class="wp-block-button__link wp-element-button" href="/first-time/">First time? What to expect</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"givelifewp-hero__media","layout":{"type":"default"}} -->
<div class="wp-block-group givelifewp-hero__media"><?php echo givelifewp_image_block( 'donor-arm', 'givelifewp-drop-mask givelifewp-lcp' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

<!-- wp:paragraph {"className":"givelifewp-badge"} -->
<p class="givelifewp-badge"><strong>1 donation</strong> can help up to <strong>3 people</strong></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
