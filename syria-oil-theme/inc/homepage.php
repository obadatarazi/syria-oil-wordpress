<?php
/**
 * Homepage / hero helpers (Theme Options driven).
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hero featured sidebar mode.
 *
 * split — slider + أخبار بارزة list beside it.
 * below — full slider; أخبار بارزة as category section under hero.
 * none  — full slider only; no أخبار بارزة block.
 *
 * @return string split|below|none
 */
function syria_oil_hero_featured_sidebar_mode() {
	$mode = function_exists( 'syria_oil_mod' ) ? (string) syria_oil_mod( 'hero_featured_sidebar', 'below' ) : 'below';
	if ( ! in_array( $mode, array( 'split', 'below', 'none' ), true ) ) {
		$mode = 'below';
	}
	return $mode;
}

/**
 * Whether the أخبار بارزة category section should render on the homepage.
 *
 * @return bool
 */
function syria_oil_show_featured_category_section() {
	return 'below' === syria_oil_hero_featured_sidebar_mode();
}

/**
 * Whether the hero slider should use split layout.
 *
 * @return bool
 */
function syria_oil_hero_uses_split_layout() {
	return 'split' === syria_oil_hero_featured_sidebar_mode();
}

/**
 * Featured category section titles/names to suppress when not in "below" mode.
 *
 * @return string[]
 */
function syria_oil_featured_category_labels() {
	return array( 'أخبار سوريا', 'أخبار بارزة', 'اخبار بارزة', 'Top news', 'Featured' );
}

/**
 * Banner edge decoration mode.
 *
 * @return string none|color|image|both
 */
function syria_oil_banner_edge_mode() {
	$mode = function_exists( 'syria_oil_mod' ) ? (string) syria_oil_mod( 'banner_edge_mode', 'none' ) : 'none';
	if ( ! in_array( $mode, array( 'none', 'color', 'image', 'both' ), true ) ) {
		// Legacy: old flag overlay switcher.
		if ( function_exists( 'syria_oil_mod_enabled' ) && syria_oil_mod_enabled( 'banner_flag_overlay', false ) ) {
			return 'both';
		}
		$mode = 'none';
	}
	return $mode;
}

/**
 * Whether any banner edge decoration is active.
 *
 * @return bool
 */
function syria_oil_banner_has_decoration() {
	return 'none' !== syria_oil_banner_edge_mode();
}

/**
 * Resolve banner edge color from Theme Options preset.
 *
 * @return string Hex color.
 */
function syria_oil_banner_edge_color() {
	$preset = function_exists( 'syria_oil_mod' ) ? (string) syria_oil_mod( 'banner_edge_color_preset', 'primary' ) : 'primary';
	$depth  = function_exists( 'syria_oil_mod' ) ? sanitize_hex_color( (string) syria_oil_mod( 'color_depth', '#321010' ) ) : '#321010';
	$custom = function_exists( 'syria_oil_mod' ) ? sanitize_hex_color( (string) syria_oil_mod( 'banner_edge_color_custom', '#321010' ) ) : '#321010';

	$map = array(
		'primary'   => function_exists( 'syria_oil_mod' ) ? sanitize_hex_color( (string) syria_oil_mod( 'color_primary', '#007a3d' ) ) : '#007a3d',
		'depth'     => $depth ? $depth : '#321010',
		'secondary' => function_exists( 'syria_oil_mod' ) ? sanitize_hex_color( (string) syria_oil_mod( 'color_secondary', '#f7f9fb' ) ) : '#f7f9fb',
		'accent'    => function_exists( 'syria_oil_mod' ) ? sanitize_hex_color( (string) syria_oil_mod( 'color_accent', '#ce1126' ) ) : '#ce1126',
		'ink'       => function_exists( 'syria_oil_mod' ) ? sanitize_hex_color( (string) syria_oil_mod( 'color_ink', '#0f1419' ) ) : '#0f1419',
	);

	if ( 'custom' === $preset && $custom ) {
		return $custom;
	}

	return isset( $map[ $preset ] ) && $map[ $preset ] ? $map[ $preset ] : '#007a3d';
}

/**
 * Banner flag / stripe image URL (Theme Options media or bundled fallback).
 *
 * @return string
 */
function syria_oil_banner_flag_image_url() {
	$id = function_exists( 'syria_oil_mod' ) ? absint( syria_oil_mod( 'banner_flag_image', 0 ) ) : 0;
	if ( $id ) {
		$url = wp_get_attachment_image_url( $id, 'full' );
		if ( $url ) {
			return $url;
		}
	}
	return trailingslashit( get_template_directory_uri() ) . 'assets/images/syria-flag-stripes.svg';
}

/**
 * Banner emblem image URL (Theme Options media or bundled fallback).
 *
 * @return string
 */
function syria_oil_banner_emblem_image_url() {
	$id = function_exists( 'syria_oil_mod' ) ? absint( syria_oil_mod( 'banner_emblem_image', 0 ) ) : 0;
	if ( $id ) {
		$url = wp_get_attachment_image_url( $id, 'full' );
		if ( $url ) {
			return $url;
		}
	}
	return trailingslashit( get_template_directory_uri() ) . 'assets/images/syrian-emblem-2025.png';
}

/**
 * Bundled emblem asset URL (no Theme Options lookup).
 *
 * @return string
 */
function syria_oil_syrian_emblem_bundled_url() {
	return trailingslashit( get_template_directory_uri() ) . 'assets/images/syrian-emblem-2025.png';
}
