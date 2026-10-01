<?php
/**
 * One-time content setup: creates the core pages from theme patterns, the
 * blog page, and a first reminder post. Never overwrites existing content.
 *
 * @package givelifewp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const GIVELIFEWP_SEED_VERSION = 2;

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
 * Creates a published page unless one with this slug exists. Returns its ID.
 *
 * @param string $slug    Page slug.
 * @param string $title   Page title.
 * @param string $content Block markup.
 * @return int
 */
function givelifewp_ensure_page( $slug, $title, $content = '' ) {
	$page = get_page_by_path( $slug );
	if ( $page ) {
		return (int) $page->ID;
	}
	return (int) wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_content' => $content,
		)
	);
}

/**
 * Runs once per seed version.
 */
function givelifewp_seed_content() {
	if ( (int) get_option( 'givelifewp_seeded', 0 ) >= GIVELIFEWP_SEED_VERSION ) {
		return;
	}
	// Simple lock so two simultaneous requests don't both seed.
	if ( ! add_option( 'givelifewp_seed_lock', time(), '', false ) ) {
		return;
	}

	// Content comes from the theme's own pattern files, so skip kses.
	kses_remove_filters();

	givelifewp_ensure_page( 'give-blood', __( 'Give blood', 'givelifewp' ), givelifewp_pattern_content( 'givelifewp/page-give-blood' ) );
	givelifewp_ensure_page( 'first-time', __( 'First time? What to expect', 'givelifewp' ), givelifewp_pattern_content( 'givelifewp/page-first-time' ) );
	givelifewp_ensure_page( 'find-a-donor-service', __( 'Find where to give', 'givelifewp' ), givelifewp_pattern_content( 'givelifewp/page-find-a-donor-service' ) );
	$home = givelifewp_ensure_page( 'home', __( 'Home', 'givelifewp' ) );
	$news = givelifewp_ensure_page( 'news', __( 'News and reminders', 'givelifewp' ) );

	// Static front page plus a blog page, unless someone already chose otherwise.
	if ( 'posts' === get_option( 'show_on_front' ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home );
		update_option( 'page_for_posts', $news );
	}

	if ( ! get_page_by_path( 'is-it-time-to-give-again', OBJECT, 'post' ) ) {
		$category = term_exists( 'reminders', 'category' );
		if ( ! $category ) {
			$category = wp_insert_term( __( 'Reminders', 'givelifewp' ), 'category', array( 'slug' => 'reminders' ) );
		}
		wp_insert_post(
			array(
				'post_type'     => 'post',
				'post_status'   => 'publish',
				'post_name'     => 'is-it-time-to-give-again',
				'post_title'    => __( 'Is it time to give again?', 'givelifewp' ),
				'post_content'  => givelifewp_pattern_content( 'givelifewp/post-reminder' ),
				'post_category' => is_wp_error( $category ) ? array() : array( (int) $category['term_id'] ),
			)
		);
	}

	// Move untouched WordPress sample content to the trash (restorable).
	foreach ( array( array( 'hello-world', 'post' ), array( 'sample-page', 'page' ) ) as $sample ) {
		$post = get_page_by_path( $sample[0], OBJECT, $sample[1] );
		if ( $post && $post->post_modified_gmt === $post->post_date_gmt ) {
			wp_trash_post( $post->ID );
		}
	}

	// Replace the host's placeholder site title.
	if ( false !== stripos( (string) get_option( 'blogname' ), 'super fast WordPress' ) ) {
		update_option( 'blogname', 'GiveLifeWP' );
		update_option( 'blogdescription', __( 'Give blood. Give hope. Give life!', 'givelifewp' ) );
	}

	kses_init_filters();
	update_option( 'givelifewp_seeded', GIVELIFEWP_SEED_VERSION );
	delete_option( 'givelifewp_seed_lock' );
}
add_action( 'init', 'givelifewp_seed_content', 99 );
