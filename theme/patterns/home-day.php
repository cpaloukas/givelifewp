<?php
/**
 * Title: Home: what happens on the day
 * Slug: givelifewp/home-day
 * Categories: text
 * Inserter: no
 * Description: Photo with the four steps of a donation.
 *
 * @package givelifewp
 */

?>
<!-- wp:group {"align":"wide","className":"givelifewp-split","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide givelifewp-split"><?php echo givelifewp_image_block( 'platelet-donation', 'givelifewp-rounded' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

<!-- wp:group {"className":"givelifewp-split__text","layout":{"type":"default"}} -->
<div class="wp-block-group givelifewp-split__text"><!-- wp:paragraph {"className":"givelifewp-eyebrow"} -->
<p class="givelifewp-eyebrow">On the day</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"givelifewp-h2"} -->
<h2 class="wp-block-heading givelifewp-h2">An hour, a snack, and <em>a real difference.</em></h2>
<!-- /wp:heading -->

<!-- wp:list {"ordered":true,"className":"givelifewp-steps"} -->
<ol class="wp-block-list givelifewp-steps"><!-- wp:list-item -->
<li><strong>Register.</strong> Bring photo ID.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Quick private check.</strong> A few questions and a finger prick for iron.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Give.</strong> A brief pinch, then about 10 minutes.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Rest and snack.</strong> Ten minutes with a drink and a snack.</li>
<!-- /wp:list-item --></ol>
<!-- /wp:list -->

<!-- wp:paragraph {"className":"givelifewp-more"} -->
<p class="givelifewp-more"><a href="/first-time/">First time? Everything to expect</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
