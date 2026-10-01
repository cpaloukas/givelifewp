<?php
/**
 * Theme photos: imported once into the media library so WordPress makes the
 * responsive sizes (served as WebP), srcset and lazy loading for us.
 *
 * @package givelifewp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Photos shipped with the theme, by key.
 *
 * @return array<string, array{file: string, alt: string}>
 */
function givelifewp_photos() {
	return array(
		'donor-arm'         => array(
			'file' => 'donor-arm.jpg',
			'alt'  => __( 'A donor giving blood, with a nurse in white gloves at the needle', 'givelifewp' ),
		),
		'blood-bags'        => array(
			'file' => 'blood-bags.jpg',
			'alt'  => __( 'Rows of donated blood bags hanging in a blood bank', 'givelifewp' ),
		),
		'donation-needle'   => array(
			'file' => 'donation-needle.jpg',
			'alt'  => __( 'A nurse in blue gloves preparing a donor’s arm, with a blood pressure cuff on', 'givelifewp' ),
		),
		'volunteer-bag'     => array(
			'file' => 'volunteer-bag.jpg',
			'alt'  => __( 'A full bag of whole blood labelled volunteer donor', 'givelifewp' ),
		),
		'platelet-donation' => array(
			'file' => 'platelet-donation.jpg',
			'alt'  => __( 'A donor giving platelets, squeezing a soft ball while the machine collects', 'givelifewp' ),
		),
		'site-icon'         => array(
			'file' => 'site-icon.png',
			'alt'  => __( 'GiveLifeWP icon: a heart in a drop of blood', 'givelifewp' ),
		),
		'blood-bus'         => array(
			'file' => 'blood-bus.jpg',
			'alt'  => __( 'A mobile blood donation bus parked in a city street', 'givelifewp' ),
		),
	);
}

/**
 * Serve generated image sizes as WebP.
 */
add_filter(
	'image_editor_output_format',
	function ( $formats ) {
		$formats['image/jpeg'] = 'image/webp';
		return $formats;
	}
);

/**
 * Imports any theme photo not yet in the media library.
 */
function givelifewp_import_photos() {
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$ids = (array) get_option( 'givelifewp_media', array() );
	foreach ( givelifewp_photos() as $key => $photo ) {
		if ( ! empty( $ids[ $key ] ) && get_post( $ids[ $key ] ) ) {
			continue;
		}
		$source = get_theme_file_path( 'assets/img/' . $photo['file'] );
		if ( ! file_exists( $source ) ) {
			continue;
		}
		$upload = wp_upload_bits( 'givelifewp-' . $photo['file'], null, file_get_contents( $source ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( ! empty( $upload['error'] ) ) {
			continue;
		}
		$id = wp_insert_attachment(
			array(
				'post_mime_type' => wp_check_filetype( $photo['file'] )['type'],
				'post_title'     => $photo['alt'],
				'post_status'    => 'inherit',
			),
			$upload['file']
		);
		if ( is_wp_error( $id ) || ! $id ) {
			continue;
		}
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
		update_post_meta( $id, '_wp_attachment_image_alt', $photo['alt'] );
		$ids[ $key ] = $id;
	}
	update_option( 'givelifewp_media', $ids );
}

/**
 * Attachment ID for a theme photo key, or 0.
 *
 * @param string $key Photo key.
 * @return int
 */
function givelifewp_photo_id( $key ) {
	$ids = (array) get_option( 'givelifewp_media', array() );
	return (int) ( $ids[ $key ] ?? 0 );
}

/**
 * Core Image block markup for a theme photo, so it stays editable.
 *
 * @param string $key       Photo key.
 * @param string $class     Extra class names.
 * @param string $size      Image size slug.
 * @return string
 */
function givelifewp_image_block( $key, $class = '', $size = 'large' ) {
	$id     = givelifewp_photo_id( $key );
	$photos = givelifewp_photos();
	if ( ! $id || ! isset( $photos[ $key ] ) ) {
		return '';
	}
	$src = wp_get_attachment_image_url( $id, $size );
	$alt = esc_attr( $photos[ $key ]['alt'] );

	$attrs = array(
		'id'              => $id,
		'sizeSlug'        => $size,
		'linkDestination' => 'none',
	);
	if ( $class ) {
		$attrs['className'] = $class;
	}

	return sprintf(
		"<!-- wp:image %s -->\n<figure class=\"wp-block-image size-%s%s\"><img src=\"%s\" alt=\"%s\" class=\"wp-image-%d\"/></figure>\n<!-- /wp:image -->",
		wp_json_encode( $attrs ),
		esc_attr( $size ),
		$class ? ' ' . esc_attr( $class ) : '',
		esc_url( $src ),
		$alt,
		$id
	);
}

/**
 * Rewrites an <img> tag's loading hints and sizes.
 *
 * @param string $html  Block HTML.
 * @param bool   $lcp   Whether this image is the largest paint (load first).
 * @param string $sizes Value for the sizes attribute.
 * @return string
 */
function givelifewp_img_hints( $html, $lcp, $sizes ) {
	return preg_replace_callback(
		'/<img\s[^>]*>/',
		function ( $m ) use ( $lcp, $sizes ) {
			$tag = preg_replace( '/\s(loading|fetchpriority|decoding|sizes)="[^"]*"/', '', $m[0] );
			$add = $lcp ? ' fetchpriority="high"' : ' loading="lazy"';
			$add .= ' decoding="async"';
			if ( $sizes ) {
				$add .= ' sizes="' . esc_attr( $sizes ) . '"';
			}
			return preg_replace( '/^<img/', '<img' . $add, $tag );
		},
		$html,
		1
	);
}

/**
 * Real display sizes for theme images, so browsers fetch the right file, and
 * only the hero (or a page's header photo) gets high priority.
 */
add_filter(
	'render_block_core/image',
	function ( $html, $block ) {
		$class = $block['attrs']['className'] ?? '';
		if ( false !== strpos( $class, 'givelifewp-lcp' ) ) {
			return givelifewp_img_hints( $html, true, '(min-width: 900px) 34rem, 92vw' );
		}
		if ( false !== strpos( $class, 'givelifewp-inline-photo' ) ) {
			return givelifewp_img_hints( $html, false, '(min-width: 720px) 620px, 92vw' );
		}
		if ( false !== strpos( $class, 'givelifewp-rounded' ) ) {
			return givelifewp_img_hints( $html, false, '(min-width: 900px) 40vw, 92vw' );
		}
		return $html;
	},
	20,
	2
);

add_filter(
	'render_block_core/post-featured-image',
	function ( $html, $block, $instance ) {
		// Only the current page's own header photo loads first; cards in lists are lazy.
		$post_id = (int) ( $instance->context['postId'] ?? 0 );
		if ( ! is_singular() || get_queried_object_id() !== $post_id ) {
			return givelifewp_img_hints( $html, false, '(min-width: 900px) 24rem, 92vw' );
		}
		return givelifewp_img_hints( $html, true, '(min-width: 900px) 26rem, 92vw' );
	},
	20,
	3
);
