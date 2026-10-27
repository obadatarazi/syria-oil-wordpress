<?php
/**
 * Plugin Name: Syria Oil Widgets
 * Plugin URI:  https://www.linkedin.com/in/obada-tarazi
 * Description: Portable Elementor widgets for the Syria Oil portal (Featured Slider, Card Grid, News Ticker, Spotlight/Rail, FAQ, Newsletter, Price Ticker).
 * Version:     1.14.1
 * Author:      Obada Al Tarazi
 * Author URI:  https://www.linkedin.com/in/obada-tarazi
 * Text Domain: syria-oil-widgets
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package Syria_Oil_Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SYRIA_OIL_WIDGETS_VERSION', '1.14.1' );
define( 'SYRIA_OIL_WIDGETS_FILE', __FILE__ );
define( 'SYRIA_OIL_WIDGETS_DIR', dirname( __FILE__ ) );
define( 'SYRIA_OIL_WIDGETS_URL', plugins_url( '', __FILE__ ) );

/**
 * Theme-overridable Elementor control default.
 *
 * @param string $widget_name Widget get_name().
 * @param string $key         Control key.
 * @param mixed  $fallback    Built-in default.
 * @return mixed
 */
function syria_oil_widgets_default( $widget_name, $key, $fallback ) {
	return apply_filters( 'syria_oil_widget_default', $fallback, $widget_name, $key );
}

/**
 * Shared production-category aliases for widgets (theme map when available).
 *
 * @return array<string,array<int,string>>
 */
function syria_oil_widgets_category_aliases() {
	if ( function_exists( 'syria_oil_production_category_aliases' ) ) {
		return syria_oil_production_category_aliases();
	}
	return array(
		'أخبار سوريا'       => array( 'أخبار سوريا', 'أخبار بارزة', 'اخبار بارزة' ),
		'أخبار بارزة'       => array( 'أخبار سوريا', 'أخبار بارزة', 'اخبار بارزة' ),
		'نفط وغاز عالمي'    => array( 'أخبار النفط والغاز العالمي', 'أخبار النفط العالمي' ),
		'نفط وغاز عربي'     => array( 'أخبار النفط والغاز العربي' ),
		'المحروقات'         => array( 'المحروقات' ),
		'قرارات الوزارة'    => array( 'قرارات وأخبار وزارة النفط' ),
		'شركات عاملة'       => array( 'الشركات العاملة في سوريا' ),
		'Top news'          => array( 'أخبار بارزة' ),
		'Global oil & gas'  => array( 'أخبار النفط والغاز العالمي', 'أخبار النفط العالمي' ),
		'Arab oil & gas'    => array( 'أخبار النفط والغاز العربي' ),
		'Fuels'             => array( 'المحروقات' ),
	);
}

/**
 * Resolve a WordPress category from a widget title, name, or production alias.
 *
 * @param string $name Label or category name.
 * @return WP_Term|null
 */
function syria_oil_widgets_resolve_category( $name ) {
	if ( function_exists( 'syria_oil_resolve_category_term' ) ) {
		return syria_oil_resolve_category_term( $name );
	}
	$name = trim( (string) $name );
	if ( '' === $name ) {
		return null;
	}
	$aliases = syria_oil_widgets_category_aliases();
	$try     = array( $name );
	if ( isset( $aliases[ $name ] ) ) {
		$try = array_merge( $try, $aliases[ $name ] );
	}
	foreach ( $try as $candidate ) {
		$term = get_term_by( 'name', $candidate, 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			return $term;
		}
	}
	return null;
}

/**
 * Localize common English UI strings saved in Elementor (e.g. Read more → Arabic).
 *
 * @param string $text Raw button/label text.
 * @return string
 */
function syria_oil_widgets_localize_button( $text ) {
	$text = is_string( $text ) ? trim( $text ) : '';
	if ( '' === $text ) {
		return $text;
	}

	$keys = array(
		'Read more',
		'Read the story',
		'All news',
		'Breaking',
		'Latest News',
		'Subscribe',
		'Latest news',
		'Newsroom',
		'Contact us',
		'About the portal',
		'Search',
		'Menu',
		'More',
		'Prices',
		'Prices updating…',
		'Prices updating...',
		'Blog',
		'Analysis',
		'Articles',
		'All articles',
		'(Untitled)',
	);

	if ( ! in_array( $text, $keys, true ) ) {
		return $text;
	}

	if ( function_exists( 'syria_oil_ui' ) ) {
		return syria_oil_ui( $text, 'syria-oil-widgets' );
	}

	$translated = __( $text, 'syria-oil-widgets' ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
	return $translated;
}

/**
 * Load plugin translations (.mo / .l10n.php).
 */
function syria_oil_widgets_load_textdomain() {
	load_plugin_textdomain( 'syria-oil-widgets', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	$locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
	if ( ! $locale || 0 !== strpos( $locale, 'ar' ) ) {
		return;
	}

	$candidates = array(
		SYRIA_OIL_WIDGETS_DIR . '/languages/syria-oil-widgets-' . $locale . '.l10n.php',
		SYRIA_OIL_WIDGETS_DIR . '/languages/syria-oil-widgets-' . $locale . '.mo',
		SYRIA_OIL_WIDGETS_DIR . '/languages/syria-oil-widgets-ar.l10n.php',
		SYRIA_OIL_WIDGETS_DIR . '/languages/syria-oil-widgets-ar.mo',
	);

	foreach ( $candidates as $file ) {
		if ( is_readable( $file ) ) {
			unload_textdomain( 'syria-oil-widgets' );
			load_textdomain( 'syria-oil-widgets', $file );
			return;
		}
	}
}
add_action( 'plugins_loaded', 'syria_oil_widgets_load_textdomain', 1 );

require_once SYRIA_OIL_WIDGETS_DIR . '/includes/counter-helpers.php';
require_once SYRIA_OIL_WIDGETS_DIR . '/includes/class-assets.php';
require_once SYRIA_OIL_WIDGETS_DIR . '/includes/newsletter/class-subscribers.php';
require_once SYRIA_OIL_WIDGETS_DIR . '/includes/newsletter/class-campaigns.php';
require_once SYRIA_OIL_WIDGETS_DIR . '/includes/newsletter/class-mailer.php';
require_once SYRIA_OIL_WIDGETS_DIR . '/includes/newsletter/class-admin.php';
require_once SYRIA_OIL_WIDGETS_DIR . '/includes/newsletter/class-bootstrap.php';
require_once SYRIA_OIL_WIDGETS_DIR . '/includes/class-newsletter-form.php';
require_once SYRIA_OIL_WIDGETS_DIR . '/includes/class-price-api.php';
require_once SYRIA_OIL_WIDGETS_DIR . '/includes/class-price-feed.php';
require_once SYRIA_OIL_WIDGETS_DIR . '/includes/class-plugin.php';

register_activation_hook( __FILE__, array( 'Syria_Oil_Widgets\\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Syria_Oil_Widgets\\Plugin', 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		Syria_Oil_Widgets\Plugin::instance()->init();
	}
);
