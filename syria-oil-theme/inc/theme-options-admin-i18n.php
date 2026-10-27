<?php
/**
 * Force Arabic + RTL on Syria Oil Theme Options admin screen.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current admin screen is Syria Oil options.
 *
 * @return bool
 */
function syria_oil_is_theme_options_admin_screen() {
	if ( ! is_admin() ) {
		return false;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	return 'syria-oil-options' === $page;
}

/**
 * Arabic string map for theme options UI.
 *
 * @return array<string,string>
 */
function syria_oil_theme_options_ar_strings() {
	static $map = null;
	if ( null !== $map ) {
		return $map;
	}
	$file = SYRIA_OIL_DIR . '/languages/theme-options-ar.php';
	$map  = file_exists( $file ) ? include $file : array();
	return is_array( $map ) ? $map : array();
}

/**
 * @param string $locale Locale.
 * @return string
 */
function syria_oil_theme_options_admin_locale( $locale ) {
	if ( syria_oil_is_theme_options_admin_screen() ) {
		return 'ar';
	}
	return $locale;
}
add_filter( 'determine_locale', 'syria_oil_theme_options_admin_locale', 20 );
add_filter( 'locale', 'syria_oil_theme_options_admin_locale', 20 );

/**
 * @param string $translated Translated.
 * @param string $text       Original.
 * @param string $domain     Domain.
 * @return string
 */
function syria_oil_theme_options_admin_gettext( $translated, $text, $domain ) {
	if ( 'syria-oil' !== $domain || ! syria_oil_is_theme_options_admin_screen() ) {
		return $translated;
	}
	$map = syria_oil_theme_options_ar_strings();
	return isset( $map[ $text ] ) ? $map[ $text ] : $translated;
}
add_filter( 'gettext', 'syria_oil_theme_options_admin_gettext', 20, 3 );

/**
 * @param string $classes Body classes.
 * @return string
 */
function syria_oil_theme_options_admin_body_class( $classes ) {
	if ( syria_oil_is_theme_options_admin_screen() ) {
		$classes .= ' so-admin-rtl rtl syria-oil-options-screen';
	}
	return $classes;
}
add_filter( 'admin_body_class', 'syria_oil_theme_options_admin_body_class' );

/**
 * Mark CSF wrapper RTL for preview layout.
 */
function syria_oil_theme_options_admin_rtl_attr() {
	if ( ! syria_oil_is_theme_options_admin_screen() ) {
		return;
	}
	echo "<script>document.documentElement.setAttribute('dir','rtl');document.documentElement.setAttribute('lang','ar');</script>\n";
}
add_action( 'admin_head', 'syria_oil_theme_options_admin_rtl_attr', 1 );
