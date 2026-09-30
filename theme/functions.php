<?php
/**
 * GiveLifeWP theme setup.
 *
 * @package givelifewp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Editor styles, so the front page looks the same in the Site Editor.
 */
function givelifewp_setup() {
	add_editor_style( 'assets/css/front-page.css' );
}
add_action( 'after_setup_theme', 'givelifewp_setup' );

/**
 * Front page styles and motion.
 */
function givelifewp_enqueue_assets() {
	if ( ! is_front_page() ) {
		return;
	}

	$path = get_theme_file_path( 'assets/css/front-page.css' );

	wp_enqueue_style(
		'givelifewp-front-page',
		get_theme_file_uri( 'assets/css/front-page.css' ),
		array(),
		file_exists( $path ) ? (string) filemtime( $path ) : null
	);
}
add_action( 'wp_enqueue_scripts', 'givelifewp_enqueue_assets' );
