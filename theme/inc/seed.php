<?php
/**
 * Content setup: creates the core pages from theme patterns, the blog page,
 * a first reminder post and the theme photos. Pages are refreshed from the
 * patterns on new seed versions only while nobody has edited them.
 *
 * @package givelifewp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const GIVELIFEWP_SEED_VERSION = 6;

/**
 * Pattern content by slug, or an empty string.
 *
 * @param string $slug Pattern slug.
 * @return string
 */
function givelifewp_pattern_content( $slug ) {
	$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( $slug );
	return $pattern['content'] ?? '';
}

/**
 * Creates a page, or refreshes it if it still matches what we last seeded.
 * Returns its ID.
 *
 * @param array $args slug, title, content, excerpt, photo.
 * @return int
 */
function givelifewp_sync_page( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'content' => '',
			'excerpt' => '',
			'photo'   => '',
		)
	);
	$page = get_page_by_path( $args['slug'] );
	$data = array(
		'post_title'   => $args['title'],
		'post_content' => $args['content'],
		'post_excerpt' => $args['excerpt'],
	);

	if ( ! $page ) {
		$id = (int) wp_insert_post(
			wp_slash(
				$data + array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_name'   => $args['slug'],
				)
			)
		);
	} else {
		$id     = (int) $page->ID;
		$stored = get_post_meta( $id, '_givelifewp_seed_hash', true );
		$edited = $stored
			? md5( $page->post_content ) !== $stored
			: $page->post_modified_gmt !== $page->post_date_gmt;
		if ( ! $edited ) {
			wp_update_post( wp_slash( $data + array( 'ID' => $id ) ) );
		}
	}

	if ( $id ) {
		update_post_meta( $id, '_givelifewp_seed_hash', md5( get_post_field( 'post_content', $id, 'raw' ) ) );
		if ( $args['photo'] && ! has_post_thumbnail( $id ) && givelifewp_photo_id( $args['photo'] ) ) {
			set_post_thumbnail( $id, givelifewp_photo_id( $args['photo'] ) );
		}
	}
	return $id;
}

/**
 * Runs once per seed version.
 */
function givelifewp_seed_content() {
	if ( (int) get_option( 'givelifewp_seeded', 0 ) >= GIVELIFEWP_SEED_VERSION ) {
		return;
	}
	// Simple lock so two simultaneous requests don't both seed.
	$lock = (int) get_option( 'givelifewp_seed_lock', 0 );
	if ( $lock && $lock > time() - 300 ) {
		return;
	}
	update_option( 'givelifewp_seed_lock', time(), false );

	givelifewp_import_photos();
	if ( (int) get_option( 'givelifewp_seeded', 0 ) < 6 ) {
		givelifewp_regenerate_theme_images();
	}

	// Content comes from the theme's own pattern files, so skip kses.
	kses_remove_filters();

	givelifewp_sync_page(
		array(
			'slug'    => 'give-blood',
			'title'   => __( 'Give blood', 'givelifewp' ),
			'excerpt' => __( 'Blood can’t be made in a lab. Every bag in every hospital was given by a person who took an hour out of their day. Here’s why it matters, and how you can be one of them.', 'givelifewp' ),
			'content' => givelifewp_pattern_content( 'givelifewp/page-give-blood' ),
			'photo'   => 'blood-bags',
		)
	);
	givelifewp_sync_page(
		array(
			'slug'    => 'first-time',
			'title'   => __( 'First time? What to expect', 'givelifewp' ),
			'excerpt' => __( 'Nervous? That’s normal, and the staff have seen it all before. Here’s what really happens, so nothing comes as a surprise.', 'givelifewp' ),
			'content' => givelifewp_pattern_content( 'givelifewp/page-first-time' ),
			'photo'   => 'donation-needle',
		)
	);
	givelifewp_sync_page(
		array(
			'slug'    => 'find-a-donor-service',
			'title'   => __( 'Find where to give', 'givelifewp' ),
			'excerpt' => __( 'Booking, eligibility checks and donation sessions all live with your official blood service. Find yours below.', 'givelifewp' ),
			'content' => givelifewp_pattern_content( 'givelifewp/page-find-a-donor-service' ),
			'photo'   => 'blood-bus',
		)
	);
	$home = givelifewp_sync_page(
		array(
			'slug'  => 'home',
			'title' => __( 'Home', 'givelifewp' ),
		)
	);
	$news = givelifewp_sync_page(
		array(
			'slug'    => 'news',
			'title'   => __( 'News and reminders', 'givelifewp' ),
			'excerpt' => __( 'Gentle nudges to give, and what’s new at GiveLifeWP.', 'givelifewp' ),
		)
	);

	// Static front page plus a blog page, unless someone already chose otherwise.
	if ( 'posts' === get_option( 'show_on_front' ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home );
		update_option( 'page_for_posts', $news );
	}

	$reminder = get_page_by_path( 'is-it-time-to-give-again', OBJECT, 'post' );
	if ( ! $reminder ) {
		$category = term_exists( 'reminders', 'category' );
		if ( ! $category ) {
			$category = wp_insert_term( __( 'Reminders', 'givelifewp' ), 'category', array( 'slug' => 'reminders' ) );
		}
		$reminder_id = wp_insert_post(
			wp_slash(
				array(
					'post_type'     => 'post',
					'post_status'   => 'publish',
					'post_name'     => 'is-it-time-to-give-again',
					'post_title'    => __( 'Is it time to give again?', 'givelifewp' ),
					'post_content'  => givelifewp_pattern_content( 'givelifewp/post-reminder' ),
					'post_category' => is_wp_error( $category ) ? array() : array( (int) $category['term_id'] ),
				)
			)
		);
	} else {
		$reminder_id = $reminder->ID;
	}
	if ( $reminder_id && ! has_post_thumbnail( $reminder_id ) && givelifewp_photo_id( 'volunteer-bag' ) ) {
		set_post_thumbnail( $reminder_id, givelifewp_photo_id( 'volunteer-bag' ) );
	}

	// Move untouched WordPress sample content to the trash (restorable).
	foreach ( array( array( 'hello-world', 'post' ), array( 'sample-page', 'page' ) ) as $sample ) {
		$post = get_page_by_path( $sample[0], OBJECT, $sample[1] );
		if ( $post && $post->post_modified_gmt === $post->post_date_gmt ) {
			wp_trash_post( $post->ID );
		}
	}

	// Site icon: WordPress makes the favicon and home screen sizes from it.
	if ( ! get_option( 'site_icon' ) && givelifewp_photo_id( 'site-icon' ) ) {
		update_option( 'site_icon', givelifewp_photo_id( 'site-icon' ) );
	}

	// Replace the host's placeholder site title.
	if ( false !== stripos( (string) get_option( 'blogname' ), 'super fast WordPress' ) ) {
		update_option( 'blogname', 'GiveLifeWP' );
		update_option( 'blogdescription', __( 'Give blood. Give hope. Give life.', 'givelifewp' ) );
	}
	if ( 'Give blood. Give hope. Give life!' === get_option( 'blogdescription' ) ) {
		update_option( 'blogdescription', __( 'Give blood. Give hope. Give life.', 'givelifewp' ) );
	}

	kses_init_filters();
	update_option( 'givelifewp_seeded', GIVELIFEWP_SEED_VERSION );
	delete_option( 'givelifewp_seed_lock' );
}
add_action( 'init', 'givelifewp_seed_content', 99 );
