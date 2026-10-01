<?php
/**
 * GiveLifeWP theme setup.
 *
 * @package givelifewp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_theme_file_path( 'inc/seed.php' );

/**
 * Editor styles, so pages look the same in the Site Editor.
 */
function givelifewp_setup() {
	add_editor_style( 'assets/css/site.css' );
}
add_action( 'after_setup_theme', 'givelifewp_setup' );

/**
 * Site styles and motion.
 */
function givelifewp_enqueue_assets() {
	$path = get_theme_file_path( 'assets/css/site.css' );

	wp_enqueue_style(
		'givelifewp-site',
		get_theme_file_uri( 'assets/css/site.css' ),
		array(),
		file_exists( $path ) ? (string) filemtime( $path ) : null
	);
}
add_action( 'wp_enqueue_scripts', 'givelifewp_enqueue_assets' );

/**
 * Pattern category for the full-page starter content.
 */
function givelifewp_pattern_categories() {
	register_block_pattern_category(
		'givelifewp-pages',
		array( 'label' => __( 'GiveLifeWP pages', 'givelifewp' ) )
	);
}
add_action( 'init', 'givelifewp_pattern_categories', 9 );
