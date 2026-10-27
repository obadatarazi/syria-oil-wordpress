<?php
/**
 * Front-end asset enqueueing — performance-first.
 *
 * - Critical CSS cascade: fonts (display=swap) → tokens → theme → mobile
 * - Non-critical JS uses defer strategy (never blocks rendering)
 * - Widget CSS/JS only load via Elementor get_style_depends / get_script_depends
 * - Fonts get preconnect + preload for faster LCP text paint
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filemtime-based cache-busting version helper.
 *
 * @param string $relative Asset path relative to theme root.
 * @return string
 */
function syria_oil_asset_version( $relative ) {
	$path = trailingslashit( SYRIA_OIL_DIR ) . ltrim( $relative, '/' );
	return file_exists( $path ) ? (string) filemtime( $path ) : SYRIA_OIL_VERSION;
}

/**
 * Google Fonts stylesheet URL (display=swap baked into the query).
 *
 * Loads Arabic faces + Latin siblings (IBM Plex Sans, DM Sans) so English
 * via TranslatePress does not fall back to a mismatched system font.
 *
 * @return string
 */
function syria_oil_fonts_url() {
	$families = array(
		'family=IBM+Plex+Sans+Arabic:wght@400;500;600;700',
		'family=Noto+Kufi+Arabic:wght@600;700;800',
		'family=IBM+Plex+Sans:wght@400;500;600;700',
		'family=DM+Sans:wght@400;500;600;700',
	);
	return 'https://fonts.googleapis.com/css2?' . implode( '&', $families ) . '&display=swap';
}

/**
 * Enqueue theme styles and deferred scripts.
 */
function syria_oil_enqueue_assets() {
	// Google Fonts — display=swap avoids FOIT; weights trimmed to used ranges.
	wp_enqueue_style(
		'syria-oil-fonts',
		syria_oil_fonts_url(),
		array(),
		null
	);

	$combine      = function_exists( 'syria_oil_mod_enabled' ) && syria_oil_mod_enabled( 'perf_combine_css', false );
	$combined_url = '';

	if ( $combine && function_exists( 'syria_oil_combined_css_url' ) ) {
		$combined_url = syria_oil_combined_css_url();
		if ( ! $combined_url && function_exists( 'syria_oil_rebuild_combined_css' ) ) {
			syria_oil_rebuild_combined_css();
			$combined_url = syria_oil_combined_css_url();
		}
	}

	if ( $combine && $combined_url ) {
		wp_enqueue_style(
			'syria-oil-combined',
			$combined_url,
			array( 'syria-oil-fonts' ),
			(string) filemtime( syria_oil_combined_css_path() )
		);
	} else {
		wp_enqueue_style(
			'syria-oil-tokens',
			SYRIA_OIL_URI . '/assets/css/tokens.css',
			array( 'syria-oil-fonts' ),
			syria_oil_asset_version( 'assets/css/tokens.css' )
		);

		wp_enqueue_style(
			'syria-oil-theme',
			SYRIA_OIL_URI . '/assets/css/theme.css',
			array( 'syria-oil-tokens' ),
			syria_oil_asset_version( 'assets/css/theme.css' )
		);

		wp_enqueue_style(
			'syria-oil-mobile',
			SYRIA_OIL_URI . '/assets/css/mobile.css',
			array( 'syria-oil-theme' ),
			syria_oil_asset_version( 'assets/css/mobile.css' )
		);

		wp_enqueue_style(
			'syria-oil-careers',
			SYRIA_OIL_URI . '/assets/css/careers.css',
			array( 'syria-oil-theme' ),
			syria_oil_asset_version( 'assets/css/careers.css' )
		);

		wp_enqueue_style(
			'syria-oil-style',
			get_stylesheet_uri(),
			array( 'syria-oil-mobile', 'syria-oil-careers' ),
			syria_oil_asset_version( 'style.css' )
		);
	}

	$defer = ! function_exists( 'syria_oil_mod_enabled' ) || syria_oil_mod_enabled( 'perf_defer_js', true );
	$script_args = array( 'in_footer' => true );
	if ( $defer ) {
		$script_args['strategy'] = 'defer';
	}

	// Navigation — defer so it never blocks First Contentful Paint.
	wp_enqueue_script(
		'syria-oil-navigation',
		SYRIA_OIL_URI . '/assets/js/navigation.js',
		array(),
		syria_oil_asset_version( 'assets/js/navigation.js' ),
		$script_args
	);

	wp_enqueue_script(
		'syria-oil-ads-layout',
		SYRIA_OIL_URI . '/assets/js/ads-layout.js',
		array(),
		syria_oil_asset_version( 'assets/js/ads-layout.js' ),
		$script_args
	);

	wp_enqueue_script(
		'syria-oil-lang-toggle',
		SYRIA_OIL_URI . '/assets/js/lang-toggle.js',
		array(),
		syria_oil_asset_version( 'assets/js/lang-toggle.js' ),
		$script_args
	);

	wp_enqueue_script(
		'syria-oil-chip-filter',
		SYRIA_OIL_URI . '/assets/js/chip-filter.js',
		array(),
		syria_oil_asset_version( 'assets/js/chip-filter.js' ),
		$script_args
	);

	if ( syria_oil_mod( 'show_syrian_emblem', false ) ) {
		wp_enqueue_script(
			'syria-oil-emblem',
			SYRIA_OIL_URI . '/assets/js/syrian-emblem.js',
			array(),
			syria_oil_asset_version( 'assets/js/syrian-emblem.js' ),
			$script_args
		);
		wp_localize_script(
			'syria-oil-emblem',
			'syriaOilEmblem',
			array(
				'url' => syria_oil_syrian_emblem_url(),
			)
		);
	}

	wp_localize_script(
		'syria-oil-navigation',
		'syriaOilNav',
		array(
			'submenuToggle' => __( 'Open submenu', 'syria-oil' ),
			'submenuClose'  => __( 'Close submenu', 'syria-oil' ),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// Scroll-reveal: register always; enqueue only when classic templates may use cards,
	// or when Elementor widgets declare the handle via get_script_depends().
	wp_register_style(
		'syria-oil-animations',
		SYRIA_OIL_URI . '/assets/css/animations.css',
		array(),
		syria_oil_asset_version( 'assets/css/animations.css' )
	);
	wp_register_script(
		'syria-oil-scroll-reveal',
		SYRIA_OIL_URI . '/assets/js/scroll-reveal.js',
		array(),
		syria_oil_asset_version( 'assets/js/scroll-reveal.js' ),
		$script_args
	);

	if ( is_home() || is_front_page() || is_archive() || is_search() || is_singular( 'post' ) ) {
		wp_enqueue_style( 'syria-oil-animations' );
		wp_enqueue_script( 'syria-oil-scroll-reveal' );
	}

	// When Arabic UI is forced but WP locale is still LTR, still load rtl.css.
	if ( function_exists( 'syria_oil_is_arabic_ui' ) && syria_oil_is_arabic_ui() && ! is_rtl() ) {
		wp_enqueue_style(
			'syria-oil-rtl',
			get_template_directory_uri() . '/rtl.css',
			array( 'syria-oil-style' ),
			syria_oil_asset_version( 'rtl.css' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'syria_oil_enqueue_assets' );

/**
 * Preload fonts via resource hints only (no hardcoded <link> in wp_head).
 *
 * @param array  $urls          Hint URLs.
 * @param string $relation_type Relation.
 * @return array
 */
function syria_oil_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href'        => 'https://fonts.googleapis.com',
			'crossorigin' => 'anonymous',
		);
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
		if ( ! function_exists( 'syria_oil_mod_enabled' ) || syria_oil_mod_enabled( 'perf_preconnect_eia', true ) ) {
			$urls[] = array(
				'href' => 'https://api.eia.gov',
			);
		}
	}
	if ( 'dns-prefetch' === $relation_type ) {
		if ( ! function_exists( 'syria_oil_mod_enabled' ) || syria_oil_mod_enabled( 'perf_preconnect_eia', true ) ) {
			$urls[] = 'https://api.eia.gov';
		}
	}
	if ( 'preload' === $relation_type ) {
		$urls[] = array(
			'href'        => syria_oil_fonts_url(),
			'as'          => 'style',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'syria_oil_resource_hints', 10, 2 );

/**
 * Enqueue chrome extras only when Customizer toggles enable them.
 */
function syria_oil_enqueue_chrome_extras() {
	$defer = ! function_exists( 'syria_oil_mod_enabled' ) || syria_oil_mod_enabled( 'perf_defer_js', true );
	$script_args = array( 'in_footer' => true );
	if ( $defer ) {
		$script_args['strategy'] = 'defer';
	}

	if ( syria_oil_mod( 'show_back_to_top', true ) ) {
		wp_enqueue_script(
			'syria-oil-back-to-top',
			SYRIA_OIL_URI . '/assets/js/back-to-top.js',
			array(),
			syria_oil_asset_version( 'assets/js/back-to-top.js' ),
			$script_args
		);
	}

	if ( syria_oil_mod( 'show_preloader', true ) ) {
		wp_enqueue_script(
			'syria-oil-preloader',
			SYRIA_OIL_URI . '/assets/js/preloader.js',
			array(),
			syria_oil_asset_version( 'assets/js/preloader.js' ),
			$script_args
		);
	}
}
add_action( 'wp_enqueue_scripts', 'syria_oil_enqueue_chrome_extras', 30 );

/**
 * Fallback: ensure defer on theme scripts for older WP builds that ignore
 * the strategy array in wp_enqueue_script().
 *
 * @param string $tag    Script HTML.
 * @param string $handle Handle.
 * @param string $src    Source URL.
 * @return string
 */
function syria_oil_script_loader_tag( $tag, $handle, $src ) {
	/*
	 * Keep mobile menu + preloader out of LiteSpeed Delay/Combine so toggles
	 * always bind (Delay JS otherwise runs after DOMContentLoaded).
	 */
	$critical = array( 'syria-oil-navigation', 'syria-oil-preloader' );
	if ( in_array( $handle, $critical, true ) ) {
		if ( false === strpos( $tag, 'data-no-defer=' ) ) {
			$tag = str_replace( ' src', ' data-no-defer="1" data-no-delay="1" data-no-optimize="1" src', $tag );
		}
		/* Force real JS MIME if an optimizer already rewrote the type. */
		$tag = preg_replace( '/\stype=(["\'])litespeed\/javascript\1/i', ' type="text/javascript"', $tag );
		return $tag;
	}

	if ( function_exists( 'syria_oil_mod_enabled' ) && ! syria_oil_mod_enabled( 'perf_defer_js', true ) ) {
		return $tag;
	}

	$defer_handles = array(
		'syria-oil-navigation',
		'syria-oil-article-tools',
		'syria-oil-featured-slider',
		'syria-oil-back-to-top',
		'syria-oil-preloader',
		'syria-oil-emblem',
		'syria-oil-news-ticker',
		'syria-oil-faq-accordion',
		'syria-oil-price-ticker',
		'syria-oil-posts-filter',
	);

	/**
	 * Extra script handles that should receive defer when perf_defer_js is on.
	 *
	 * @param string[] $defer_handles Handles.
	 */
	$defer_handles = apply_filters( 'syria_oil_defer_script_handles', $defer_handles );

	if ( ! in_array( $handle, $defer_handles, true ) ) {
		return $tag;
	}
	if ( false !== strpos( $tag, ' defer' ) || false !== strpos( $tag, "strategy='defer'" ) || false !== strpos( $tag, 'strategy="defer"' ) ) {
		return $tag;
	}
	return str_replace( ' src', ' defer src', $tag );
}
add_filter( 'script_loader_tag', 'syria_oil_script_loader_tag', 10, 3 );
