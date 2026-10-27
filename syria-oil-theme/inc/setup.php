<?php
/**
 * Theme setup: supports, menus, image sizes, Elementor breakpoints.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme supports.
 */
function syria_oil_setup() {
	// Make theme available for translation.
	load_theme_textdomain( 'syria-oil', SYRIA_OIL_DIR . '/languages' );

	// WordPress 6.5+ JIT loading can miss bind-mounted / symlinked theme
	// language files — force-load Arabic when the site locale is Arabic.
	syria_oil_load_locale_translations();

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array(
		'height'      => 112,
		'width'       => 90,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
		'navigation-widgets',
	) );

	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-line-height' );
	add_theme_support( 'custom-spacing' );

	// Elementor / Elementor Pro Theme Builder (header/footer/single/archive).
	add_theme_support( 'elementor' );
	add_theme_support( 'elementor-pro' );

	// Navigation menus mirroring the ministry-portal structure.
	register_nav_menus( array(
		'primary'       => __( 'Primary Menu', 'syria-oil' ),
		'footer'        => __( 'Footer Menu', 'syria-oil' ),
		'mobile_bottom' => __( 'Mobile Bottom Bar', 'syria-oil' ),
	) );

	// Content-tuned image sizes for hero + card grids.
	set_post_thumbnail_size( 800, 500, true );          // Card media (16:10-ish).
	add_image_size( 'syria-oil-hero', 1920, 1080, true ); // Featured slider hero.
	add_image_size( 'syria-oil-card', 800, 450, true );  // Grid cards (16:9).
	add_image_size( 'syria-oil-rail', 320, 240, true );  // Small rail teasers.
}
add_action( 'after_setup_theme', 'syria_oil_setup' );

/**
 * Force-load syria-oil-{locale}.mo / .l10n.php when JIT registration is not enough.
 */
function syria_oil_load_locale_translations() {
	$locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
	if ( ! $locale || 0 !== strpos( $locale, 'ar' ) ) {
		return;
	}

	$candidates = array(
		SYRIA_OIL_DIR . '/languages/syria-oil-' . $locale . '.l10n.php',
		SYRIA_OIL_DIR . '/languages/syria-oil-' . $locale . '.mo',
		SYRIA_OIL_DIR . '/languages/syria-oil-ar.l10n.php',
		SYRIA_OIL_DIR . '/languages/syria-oil-ar.mo',
	);

	foreach ( $candidates as $file ) {
		if ( is_readable( $file ) ) {
			unload_textdomain( 'syria-oil' );
			load_textdomain( 'syria-oil', $file );
			return;
		}
	}
}
add_action( 'init', 'syria_oil_load_locale_translations', 0 );

/**
 * Content width for embeds.
 */
function syria_oil_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'syria_oil_content_width', 1280 );
}
add_action( 'after_setup_theme', 'syria_oil_content_width', 0 );

/**
 * Register sidebar / widget areas.
 */
function syria_oil_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Article Sidebar', 'syria-oil' ),
		'id'            => 'sidebar-1',
		'description'   => __( 'Sidebar shown on single posts and archives.', 'syria-oil' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	$footer_areas = array(
		'footer-1' => __( 'Footer Column 1', 'syria-oil' ),
		'footer-2' => __( 'Footer Column 2', 'syria-oil' ),
		'footer-3' => __( 'Footer Column 3', 'syria-oil' ),
		'footer-4' => __( 'Footer Column 4', 'syria-oil' ),
	);

	foreach ( $footer_areas as $id => $name ) {
		register_sidebar(
			array(
				'name'          => $name,
				'id'            => $id,
				'description'   => __( 'Footer widget area (always registered; column count is controlled in Customizer).', 'syria-oil' ),
				'before_widget' => '<div id="%1$s" class="footer-col-widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3>',
				'after_title'   => '</h3>',
			)
		);
	}
}
add_action( 'widgets_init', 'syria_oil_widgets_init' );

/**
 * Add a body class so the CSS can reserve space for the mobile bottom nav
 * only when that menu is actually assigned.
 *
 * @param array $classes Body classes.
 * @return array
 */
function syria_oil_body_classes( $classes ) {
	if ( has_nav_menu( 'mobile_bottom' ) ) {
		$classes[] = 'has-bottom-nav';
	}

	$header_style = function_exists( 'syria_oil_mod' ) ? syria_oil_mod( 'header_style', 'standard' ) : 'standard';
	$allowed      = array( 'standard', 'sticky', 'transparent_over_hero' );
	if ( ! in_array( $header_style, $allowed, true ) ) {
		$header_style = 'standard';
	}
	$classes[] = 'header-style-' . $header_style;

	$container = function_exists( 'syria_oil_mod' ) ? syria_oil_mod( 'container_width', 'boxed' ) : 'boxed';
	$classes[] = ( 'full' === $container ) ? 'layout-full' : 'layout-boxed';

	$footer_cols = function_exists( 'syria_oil_mod' ) ? (string) syria_oil_mod( 'footer_columns', '3' ) : '3';
	$classes[]   = ( '4' === $footer_cols ) ? 'footer-cols-4' : 'footer-cols-3';

	if ( function_exists( 'syria_oil_mod' ) && syria_oil_mod( 'enable_dark_mode', false ) ) {
		$classes[] = 'syria-oil-dark';
	}

	if ( function_exists( 'syria_oil_banner_has_decoration' ) && syria_oil_banner_has_decoration() ) {
		$classes[] = 'so-banner-decor';
	} elseif ( function_exists( 'syria_oil_mod' ) && syria_oil_mod( 'banner_flag_overlay', false ) ) {
		$classes[] = 'so-flag-banners';
	}

	if ( function_exists( 'syria_oil_mod' ) && syria_oil_mod( 'show_syrian_emblem', false ) ) {
		$classes[] = 'so-syrian-emblem';
	}

	if ( function_exists( 'syria_oil_hero_uses_split_layout' ) && syria_oil_hero_uses_split_layout() ) {
		$classes[] = 'so-hero-split-active';
	}

	/* Full-width main column when no side ads match this page. */
	if ( function_exists( 'syria_oil_get_all_side_ads' ) && ! empty( syria_oil_get_all_side_ads() ) ) {
		$classes[] = 'has-side-ads';
	} else {
		$classes[] = 'no-side-ads';
	}

	return $classes;
}
add_filter( 'body_class', 'syria_oil_body_classes' );

/**
 * One-time: repair post_date values that contain raw ISO/RSS strings.
 */
function syria_oil_migrate_corrupt_post_dates() {
	if ( get_option( 'syria_oil_post_dates_v1' ) ) {
		return;
	}

	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$ids = $wpdb->get_col(
		"SELECT ID FROM {$wpdb->posts}
		WHERE post_type IN ('post','page')
		AND ( post_date LIKE '%T%' OR CHAR_LENGTH(post_date) > 19 )"
	);

	if ( is_array( $ids ) ) {
		foreach ( $ids as $id ) {
			$id   = (int) $id;
			$post = get_post( $id );
			if ( ! $post || ! function_exists( 'syria_oil_get_post_timestamp' ) ) {
				continue;
			}
			$ts = syria_oil_get_post_timestamp( $post );
			wp_update_post(
				array(
					'ID'            => $id,
					'post_date'     => wp_date( 'Y-m-d H:i:s', $ts, wp_timezone() ),
					'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $ts ),
				)
			);
		}
	}

	$date_format = (string) get_option( 'date_format', '' );
	if ( in_array( $date_format, array( 'c', 'r', 'U', DATE_ATOM, DATE_RFC2822 ), true ) ) {
		update_option( 'date_format', 'j F Y' );
	}

	update_option( 'syria_oil_post_dates_v1', SYRIA_OIL_VERSION, false );
}
add_action( 'init', 'syria_oil_migrate_corrupt_post_dates', 20 );

/**
 * One-time: stop routing new uploads into legacy wp-content/uploads/image/.
 */
function syria_oil_migrate_legacy_upload_dir() {
	if ( get_option( 'syria_oil_legacy_upload_dir_v1' ) ) {
		return;
	}

	$upload_path = (string) get_option( 'upload_path', '' );
	if ( '' !== $upload_path && false !== strpos( $upload_path, 'image' ) ) {
		update_option( 'upload_path', '' );
		update_option( 'upload_url_path', '' );
	}

	update_option( 'syria_oil_legacy_upload_dir_v1', SYRIA_OIL_VERSION, false );
}
add_action( 'init', 'syria_oil_migrate_legacy_upload_dir', 20 );
