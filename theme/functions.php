<?php
/**
 * GiveLifeWP theme setup.
 *
 * @package givelifewp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_theme_file_path( 'inc/media.php' );
require_once get_theme_file_path( 'inc/seed.php' );

/**
 * Editor styles, so pages look the same in the Site Editor. Page excerpts
 * are the intro line under each page title.
 */
function givelifewp_setup() {
	add_editor_style( 'assets/css/site.css' );
	add_post_type_support( 'page', 'excerpt' );
}
add_action( 'after_setup_theme', 'givelifewp_setup' );

/**
 * Site styles are small, so they're inlined (minified) in <head>: no
 * render-blocking request before the first paint.
 */
function givelifewp_enqueue_assets() {
	$path = get_theme_file_path( 'assets/css/site.min.css' );
	if ( ! file_exists( $path ) ) {
		$path = get_theme_file_path( 'assets/css/site.css' );
	}

	wp_register_style( 'givelifewp-site', false, array(), wp_get_theme()->get( 'Version' ) );
	wp_enqueue_style( 'givelifewp-site' );
	wp_add_inline_style( 'givelifewp-site', (string) file_get_contents( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}
add_action( 'wp_enqueue_scripts', 'givelifewp_enqueue_assets' );

/**
 * Preload the heading font: it draws the largest text on every page.
 */
function givelifewp_preload_fonts() {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( get_theme_file_uri( 'assets/fonts/Vollkorn-VariableFont_wght.woff2' ) )
	);
}
add_action( 'wp_head', 'givelifewp_preload_fonts', 1 );

/**
 * Page intros come from hand-written excerpts only, never auto-generated.
 */
add_filter(
	'render_block_core/post-excerpt',
	function ( $html ) {
		return ( is_page() && ! has_excerpt() ) ? '' : $html;
	}
);

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

