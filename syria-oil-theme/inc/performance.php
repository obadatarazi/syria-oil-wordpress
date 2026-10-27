<?php
/**
 * Performance, Core Web Vitals, and frontend cleanup.
 *
 * - Disable emoji scripts/styles on the frontend
 * - Prefer Elementor DOM-optimization experiments
 * - Shared image attribute helpers (lazy, async, LCP, CLS width/height)
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Strip WordPress emoji scripts/styles when enabled in Cache & Performance.
 * Saves ~10–15 KB and a render-blocking request on every page.
 */
function syria_oil_disable_emojis() {
	if ( function_exists( 'syria_oil_mod_enabled' ) && ! syria_oil_mod_enabled( 'perf_disable_emojis', true ) ) {
		return;
	}

	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	add_filter( 'tiny_mce_plugins', 'syria_oil_disable_emojis_tinymce' );
	add_filter( 'wp_resource_hints', 'syria_oil_disable_emojis_dns_prefetch', 10, 2 );
	add_filter( 'emoji_svg_url', '__return_false' );
}
add_action( 'init', 'syria_oil_disable_emojis' );

/**
 * @param array $plugins TinyMCE plugins.
 * @return array
 */
function syria_oil_disable_emojis_tinymce( $plugins ) {
	return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
}

/**
 * @param array  $urls          URLs to print.
 * @param string $relation_type Relation type.
 * @return array
 */
function syria_oil_disable_emojis_dns_prefetch( $urls, $relation_type ) {
	if ( 'dns-prefetch' !== $relation_type ) {
		return $urls;
	}
	return array_filter(
		$urls,
		static function ( $url ) {
			return false === strpos( $url, 'https://s.w.org/images/core/emoji' );
		}
	);
}

/**
 * Prefer Elementor DOM-optimization experiments as the default state.
 * Site owners can still toggle them off in Elementor → Features.
 */
function syria_oil_enable_elementor_perf_experiments() {
	if ( ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance->experiments ) ) {
		return;
	}

	$experiments = \Elementor\Plugin::$instance->experiments;
	if ( ! method_exists( $experiments, 'set_feature_default_state' ) || ! method_exists( $experiments, 'get_features' ) ) {
		return;
	}

	$features = $experiments->get_features();
	$prefer   = array( 'e_dom_optimization', 'e_optimized_markup', 'optimized_dom_output' );

	foreach ( $prefer as $feature ) {
		if ( isset( $features[ $feature ] ) ) {
			$experiments->set_feature_default_state( $feature, 'active' );
		}
	}
}
add_action( 'elementor/experiments/default-features-registered', 'syria_oil_enable_elementor_perf_experiments' );

/**
 * Fallback: force-active known Elementor performance experiment options once.
 * Runs only if the option has never been set by the site owner.
 */
function syria_oil_seed_elementor_perf_options() {
	if ( ! syria_oil_has_elementor() || get_option( 'syria_oil_perf_seeded' ) ) {
		return;
	}

	$map = array(
		'elementor_experiment-e_dom_optimization'  => 'active',
		'elementor_experiment-e_optimized_markup'  => 'active',
		'elementor_experiment-optimized_dom_output' => 'active',
	);

	foreach ( $map as $option => $value ) {
		if ( false === get_option( $option, false ) ) {
			update_option( $option, $value, false );
		}
	}

	update_option( 'syria_oil_perf_seeded', SYRIA_OIL_VERSION, false );
}
add_action( 'elementor/init', 'syria_oil_seed_elementor_perf_options', 30 );

/**
 * Build safe <img> attributes for a media library attachment.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $size          Registered image size.
 * @param array  $extra         Extra attributes (loading, fetchpriority, class, alt…).
 * @return array{src:string,width:int,height:int,alt:string,attrs:array}|null
 */
function syria_oil_get_attachment_image_data( $attachment_id, $size = 'full', $extra = array() ) {
	$attachment_id = absint( $attachment_id );
	if ( ! $attachment_id ) {
		return null;
	}

	$src = wp_get_attachment_image_src( $attachment_id, $size );
	if ( ! $src || empty( $src[0] ) ) {
		return null;
	}

	$alt = isset( $extra['alt'] ) ? $extra['alt'] : get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
	if ( ! is_string( $alt ) ) {
		$alt = '';
	}

	$attrs = array_merge(
		array(
			'src'      => $src[0],
			'width'    => (int) $src[1],
			'height'   => (int) $src[2],
			'alt'      => $alt,
			'decoding' => 'async',
			'loading'  => 'lazy',
		),
		$extra
	);

	// Explicit dimensions always win for CLS.
	$attrs['width']  = (int) $src[1];
	$attrs['height'] = (int) $src[2];
	$attrs['src']    = $src[0];
	$attrs['alt']    = $alt;

	return array(
		'src'    => $src[0],
		'width'  => (int) $src[1],
		'height' => (int) $src[2],
		'alt'    => $alt,
		'attrs'  => $attrs,
	);
}

/**
 * Echo a safely escaped <img> tag from an attribute map.
 *
 * @param array $attrs Attribute map (must include src).
 */
function syria_oil_print_img( $attrs ) {
	if ( empty( $attrs['src'] ) ) {
		return;
	}

	$allowed = array( 'src', 'width', 'height', 'alt', 'class', 'loading', 'decoding', 'fetchpriority', 'sizes', 'srcset' );
	$html    = '<img';

	foreach ( $allowed as $key ) {
		if ( ! isset( $attrs[ $key ] ) || '' === $attrs[ $key ] ) {
			continue;
		}
		$value = $attrs[ $key ];
		if ( 'src' === $key || 'srcset' === $key ) {
			$html .= ' ' . $key . '="' . esc_url( $value ) . '"';
		} else {
			$html .= ' ' . $key . '="' . esc_attr( $value ) . '"';
		}
	}

	$html .= ' />';
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped per-attr above.
}

/**
 * Default post-thumbnail attributes: lazy + async + CLS dimensions.
 * First-in-content heroes should override with fetchpriority=high / loading=eager.
 *
 * @param array        $attr       Attributes.
 * @param WP_Post|null $attachment Attachment.
 * @param string|array $size       Size.
 * @return array
 */
function syria_oil_post_thumbnail_attrs( $attr, $attachment, $size ) {
	if ( ! is_array( $attr ) ) {
		$attr = array();
	}
	if ( empty( $attr['decoding'] ) ) {
		$attr['decoding'] = 'async';
	}
	$lazy = ! function_exists( 'syria_oil_mod_enabled' ) || syria_oil_mod_enabled( 'perf_lazy_images', true );
	if ( $lazy ) {
		if ( empty( $attr['loading'] ) ) {
			$attr['loading'] = 'lazy';
		}
	} elseif ( empty( $attr['loading'] ) || 'lazy' === $attr['loading'] ) {
		$attr['loading'] = 'eager';
	}
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'syria_oil_post_thumbnail_attrs', 10, 3 );

/**
 * Respect Cache & Performance lazy-load toggle for core img tags.
 *
 * @param bool   $default  Default.
 * @param string $tag_name Tag.
 * @return bool
 */
function syria_oil_lazy_loading_enabled( $default, $tag_name ) {
	if ( 'img' === $tag_name && function_exists( 'syria_oil_mod_enabled' ) && ! syria_oil_mod_enabled( 'perf_lazy_images', true ) ) {
		return false;
	}
	return $default;
}
add_filter( 'wp_lazy_loading_enabled', 'syria_oil_lazy_loading_enabled', 10, 2 );
