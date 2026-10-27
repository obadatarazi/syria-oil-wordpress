<?php
/**
 * Syria Oil theme bootstrap.
 *
 * Loads modular includes for setup, asset enqueueing, Customizer, Elementor
 * integration, and One-Click Demo Import support.
 *
 * Child-theme ready: syria_oil_require() prefers the stylesheet (child) path
 * when a matching file exists, so overrides never require editing the parent.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

if ( ! defined( 'SYRIA_OIL_VERSION' ) ) {
	define( 'SYRIA_OIL_VERSION', '1.17.9' );
}

/** Parent theme directory (never changes when a child theme is active). */
define( 'SYRIA_OIL_DIR', get_template_directory() );
define( 'SYRIA_OIL_URI', get_template_directory_uri() );

/**
 * Require a theme include file, preferring a child-theme override when present.
 *
 * @param string $relative Path relative to the theme root.
 */
function syria_oil_require( $relative ) {
	$relative = ltrim( $relative, '/' );
	$child    = trailingslashit( get_stylesheet_directory() ) . $relative;
	$parent   = trailingslashit( get_template_directory() ) . $relative;

	if ( file_exists( $child ) && $child !== $parent ) {
		require_once $child;
		return;
	}
	if ( file_exists( $parent ) ) {
		require_once $parent;
	}
}

syria_oil_require( 'inc/setup.php' );
syria_oil_require( 'inc/i18n.php' );
syria_oil_require( 'inc/enqueue.php' );
syria_oil_require( 'inc/performance.php' );
syria_oil_require( 'inc/homepage.php' );
syria_oil_require( 'inc/identity.php' );
syria_oil_require( 'inc/theme-options.php' );
syria_oil_require( 'inc/ads.php' );
syria_oil_require( 'inc/careers.php' );
syria_oil_require( 'inc/cache.php' );
syria_oil_require( 'inc/customizer.php' );
syria_oil_require( 'inc/social-icons.php' );
syria_oil_require( 'inc/template-tags.php' );
syria_oil_require( 'inc/class-bottom-nav-walker.php' );
syria_oil_require( 'inc/portal-import.php' );
syria_oil_require( 'inc/production-categories.php' );
syria_oil_require( 'inc/starter-content.php' );
syria_oil_require( 'inc/migration-tools.php' );
syria_oil_require( 'inc/upgrade-1.17.0.php' );
syria_oil_require( 'inc/article-tools.php' );
syria_oil_require( 'inc/maintenance-mode.php' );
syria_oil_require( 'inc/elementor.php' );
syria_oil_require( 'inc/elementor-page-seed.php' );
syria_oil_require( 'inc/elementor-theme-builder-seed.php' );
syria_oil_require( 'inc/demo-import.php' );
