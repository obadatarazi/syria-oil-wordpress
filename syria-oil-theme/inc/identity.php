<?php
/**
 * Site identity CSS — sole writer of :root / --e-global-* from syria_oil_options.
 *
 * Never reads Elementor kit document. Kit sync on options save is fail-soft
 * and optional; front-end correctness does not depend on kit DB state.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whitelist sanitizer for select/radio controls.
 *
 * @param mixed $value   Raw value.
 * @param array $allowed Allowed values.
 * @param mixed $default Fallback.
 * @return mixed
 */
function syria_oil_sanitize_choice( $value, $allowed = array(), $default = '' ) {
	$value = is_string( $value ) || is_numeric( $value ) ? (string) $value : '';
	if ( in_array( $value, array_map( 'strval', (array) $allowed ), true ) ) {
		return $value;
	}
	return $default;
}

/**
 * Font family choices for non-technical owners.
 *
 * Arabic + Latin siblings are listed so the same pick works in both
 * languages (browser falls through to glyphs that exist).
 *
 * @return array
 */
function syria_oil_font_choices() {
	return array(
		'Noto Kufi Arabic'     => 'Noto Kufi Arabic',
		'IBM Plex Sans Arabic' => 'IBM Plex Sans Arabic',
		'IBM Plex Sans'        => 'IBM Plex Sans',
		'DM Sans'              => 'DM Sans',
		'system-ui'            => __( 'System UI', 'syria-oil' ),
	);
}

/**
 * Build a bilingual font stack (Arabic face + Latin sibling + fallbacks).
 *
 * @param string $primary Primary family name from options.
 * @param string $role    heading|body.
 * @return string
 */
function syria_oil_font_stack( $primary, $role = 'body' ) {
	if ( 'system-ui' === $primary ) {
		return 'system-ui, -apple-system, "Segoe UI", Tahoma, sans-serif';
	}

	$pairs = array(
		'IBM Plex Sans Arabic' => array( 'IBM Plex Sans Arabic', 'IBM Plex Sans' ),
		'IBM Plex Sans'        => array( 'IBM Plex Sans', 'IBM Plex Sans Arabic' ),
		'Noto Kufi Arabic'     => array( 'Noto Kufi Arabic', 'IBM Plex Sans', 'IBM Plex Sans Arabic' ),
		'DM Sans'              => array( 'DM Sans', 'IBM Plex Sans', 'IBM Plex Sans Arabic' ),
	);

	$stack = isset( $pairs[ $primary ] ) ? $pairs[ $primary ] : array( $primary, 'IBM Plex Sans', 'IBM Plex Sans Arabic' );
	$quoted = array();
	foreach ( $stack as $family ) {
		$quoted[] = '"' . $family . '"';
	}
	$quoted[] = ( 'heading' === $role ) ? 'sans-serif' : 'Tahoma, sans-serif';

	return implode( ', ', $quoted );
}

/**
 * Clamp a Theme Options logo size (px).
 *
 * @param mixed $value Raw option.
 * @param int   $min   Minimum px.
 * @param int   $max   Maximum px.
 * @param int   $fallback Default when empty/invalid.
 * @return int
 */
function syria_oil_clamp_logo_px( $value, $min, $max, $fallback ) {
	$n = absint( $value );
	if ( $n < 1 ) {
		$n = (int) $fallback;
	}
	if ( $n < (int) $min ) {
		$n = (int) $min;
	}
	if ( $n > (int) $max ) {
		$n = (int) $max;
	}
	return $n;
}

/**
 * Build CSS custom-property map from options (no kit read-back).
 *
 * @return array<string,string>
 */
function syria_oil_get_identity_css_vars() {
	$primary   = sanitize_hex_color( syria_oil_mod( 'color_primary', '#007a3d' ) );
	$secondary = sanitize_hex_color( syria_oil_mod( 'color_secondary', '#f7f9fb' ) );
	$accent    = sanitize_hex_color( syria_oil_mod( 'color_accent', '#ce1126' ) );
	$depth     = sanitize_hex_color( syria_oil_mod( 'color_depth', '#321010' ) );
	$ink       = sanitize_hex_color( syria_oil_mod( 'color_ink', '#0f1419' ) );

	if ( ! $primary ) {
		$primary = '#007a3d';
	}
	if ( ! $secondary ) {
		$secondary = '#f7f9fb';
	}
	if ( ! $accent ) {
		$accent = '#ce1126';
	}
	if ( ! $depth ) {
		$depth = '#321010';
	}
	if ( ! $ink ) {
		$ink = '#0f1419';
	}

	$heading = syria_oil_mod( 'font_heading', 'Noto Kufi Arabic' );
	$body    = syria_oil_mod( 'font_body', 'IBM Plex Sans Arabic' );
	$fonts   = array_keys( syria_oil_font_choices() );
	if ( ! in_array( $heading, $fonts, true ) ) {
		$heading = 'Noto Kufi Arabic';
	}
	if ( ! in_array( $body, $fonts, true ) ) {
		$body = 'IBM Plex Sans Arabic';
	}

	$scale = syria_oil_mod( 'font_scale', 'medium' );
	$root  = array(
		'small'  => '15px',
		'medium' => '16px',
		'large'  => '18px',
	);
	$root_size = isset( $root[ $scale ] ) ? $root[ $scale ] : '16px';

	$container     = syria_oil_mod( 'container_width', 'boxed' );
	$container_max = ( 'full' === $container ) ? '100%' : '1280px';

	$heading_stack = syria_oil_font_stack( $heading, 'heading' );
	$body_stack    = syria_oil_font_stack( $body, 'body' );

	$ratio_map = array(
		'2:1'   => '2 / 1',
		'16:9'  => '16 / 9',
		'16:10' => '16 / 10',
		'3:2'   => '3 / 2',
		'4:3'   => '4 / 3',
	);
	$card_ratio_key = syria_oil_mod( 'card_image_ratio', '16:9' );
	$hero_ratio_key = syria_oil_mod( 'hero_image_ratio', '16:9' );
	$card_ratio     = isset( $ratio_map[ $card_ratio_key ] ) ? $ratio_map[ $card_ratio_key ] : '16 / 9';
	$hero_ratio     = isset( $ratio_map[ $hero_ratio_key ] ) ? $ratio_map[ $hero_ratio_key ] : '16 / 9';
	$object_fit     = syria_oil_mod( 'media_object_fit', 'cover' );
	$object_fit     = in_array( $object_fit, array( 'cover', 'contain' ), true ) ? $object_fit : 'cover';
	$side_ad_max    = absint( syria_oil_mod( 'side_ad_max_height', 520 ) );
	if ( $side_ad_max < 120 ) {
		$side_ad_max = 120;
	}
	if ( $side_ad_max > 900 ) {
		$side_ad_max = 900;
	}
	$mobile_ad_max = absint( syria_oil_mod( 'mobile_ad_max_height', 140 ) );
	if ( $mobile_ad_max < 80 ) {
		$mobile_ad_max = 80;
	}
	if ( $mobile_ad_max > 280 ) {
		$mobile_ad_max = 280;
	}

	$logo_mobile_h = syria_oil_clamp_logo_px( syria_oil_mod( 'logo_mobile_height', 80 ), 24, 240, 80 );
	$logo_mobile_w = syria_oil_clamp_logo_px( syria_oil_mod( 'logo_mobile_max_width', 88 ), 24, 320, 88 );
	$logo_desktop_h = syria_oil_clamp_logo_px( syria_oil_mod( 'logo_desktop_height', 104 ), 24, 320, 104 );
	$logo_desktop_w = syria_oil_clamp_logo_px( syria_oil_mod( 'logo_desktop_max_width', 120 ), 24, 480, 120 );
	$logo_drawer_h = syria_oil_clamp_logo_px( syria_oil_mod( 'logo_drawer_height', 96 ), 24, 280, 96 );
	$logo_drawer_w = syria_oil_clamp_logo_px( syria_oil_mod( 'logo_drawer_max_width', 120 ), 24, 400, 140 );
	$logo_footer_h = syria_oil_clamp_logo_px( syria_oil_mod( 'logo_footer_height', 72 ), 24, 320, 72 );
	$logo_footer_w = syria_oil_clamp_logo_px( syria_oil_mod( 'logo_footer_max_width', 120 ), 24, 480, 120 );
	$logo_footer_mobile_h = syria_oil_clamp_logo_px( syria_oil_mod( 'logo_footer_mobile_height', 56 ), 24, 240, 56 );
	$logo_footer_mobile_w = syria_oil_clamp_logo_px( syria_oil_mod( 'logo_footer_mobile_max_width', 96 ), 24, 320, 96 );
	$nav_font             = absint( syria_oil_mod( 'header_nav_font_size', 15 ) );
	if ( $nav_font < 12 ) {
		$nav_font = 12;
	}
	if ( $nav_font > 22 ) {
		$nav_font = 22;
	}

	return array(
		'--color-primary'                             => $primary,
		'--color-accent'                              => $accent,
		'--color-depth'                               => $depth,
		'--color-ink'                                 => $ink,
		'--color-bg'                                  => $secondary,
		'--color-surface'                             => '#ffffff',
		'--font-display'                              => $heading_stack,
		'--font-ar'                                   => $body_stack,
		'--font-body'                                 => $body_stack,
		'--font-en'                                   => '"IBM Plex Sans", "DM Sans", "Segoe UI", system-ui, sans-serif',
		'--font-size-root'                            => $root_size,
		'--container'                                 => $container_max,
		'--card-media-ratio'                          => $card_ratio,
		'--hero-media-ratio'                          => $hero_ratio,
		'--media-object-fit'                          => $object_fit,
		'--side-ad-max-height'                        => $side_ad_max . 'px',
		'--mobile-ad-max-height'                      => $mobile_ad_max . 'px',
		'--logo-desktop-height'                       => $logo_desktop_h . 'px',
		'--logo-desktop-max-width'                    => $logo_desktop_w . 'px',
		'--logo-mobile-height'                        => $logo_mobile_h . 'px',
		'--logo-mobile-max-width'                     => $logo_mobile_w . 'px',
		'--logo-drawer-height'                        => $logo_drawer_h . 'px',
		'--logo-drawer-max-width'                     => $logo_drawer_w . 'px',
		'--logo-footer-height'                        => $logo_footer_h . 'px',
		'--logo-footer-max-width'                     => $logo_footer_w . 'px',
		'--logo-footer-mobile-height'                 => $logo_footer_mobile_h . 'px',
		'--logo-footer-mobile-max-width'              => $logo_footer_mobile_w . 'px',
		'--nav-font-size'                             => $nav_font . 'px',
		'--e-global-color-primary'                    => $primary,
		'--e-global-color-secondary'                  => $secondary,
		'--e-global-color-text'                       => $ink,
		'--e-global-color-accent'                     => $accent,
		'--e-global-typography-primary-font-family'   => ( 'system-ui' === $heading ) ? 'system-ui' : $heading,
		'--e-global-typography-secondary-font-family' => ( 'system-ui' === $body ) ? 'system-ui' : $body,
	);
}

/**
 * Sole theme writer of :root / --e-global-* CSS variables.
 */
function syria_oil_print_identity_css() {
	$vars  = syria_oil_get_identity_css_vars();
	$lines = array();
	foreach ( $vars as $name => $value ) {
		$lines[] = sprintf( '%s: %s;', esc_html( $name ), str_replace( array( ';', '{' ), '', $value ) );
	}

	$primary = isset( $vars['--color-primary'] ) ? $vars['--color-primary'] : '#007a3d';
	$accent  = isset( $vars['--color-accent'] ) ? $vars['--color-accent'] : '#ce1126';
	$depth   = isset( $vars['--color-depth'] ) ? $vars['--color-depth'] : '#321010';
	$ink     = isset( $vars['--color-ink'] ) ? $vars['--color-ink'] : '#0f1419';

	/*
	 * Derived brand tokens — change Primary / Accent / Depth in Theme Options and
	 * cards, overlays, newsletter, borders, and soft surfaces follow.
	 */
	$derived = array(
		"--color-primary-dark: color-mix(in srgb, {$primary} 72%, #000);",
		"--color-primary-soft: color-mix(in srgb, {$primary} 12%, #fff);",
		"--color-primary-soft-mid: color-mix(in srgb, {$primary} 18%, #eef2f6);",
		"--color-depth-dark: color-mix(in srgb, {$depth} 78%, #000);",
		"--color-depth-soft: color-mix(in srgb, {$depth} 9%, #fff);",
		"--color-depth-soft-border: color-mix(in srgb, {$depth} 18%, #e2e8f0);",
		"--color-border-hover: color-mix(in srgb, {$primary} 32%, #e2e8f0);",
		"--color-surface-tint: color-mix(in srgb, {$depth} 5%, #fff);",
		"--color-surface-tint-border: color-mix(in srgb, {$depth} 14%, #e2e8f0);",
		"--color-card-overlay: color-mix(in srgb, var(--color-primary-dark) 92%, transparent);",
		"--color-media-fallback-glow: color-mix(in srgb, {$primary} 12%, transparent);",
		"--color-accent-soft: color-mix(in srgb, {$accent} 18%, transparent);",
		"--color-accent-soft-border: color-mix(in srgb, {$accent} 35%, transparent);",
		"--color-accent-soft-text: color-mix(in srgb, {$accent} 28%, #fff);",
		"--color-on-primary: #ffffff;",
		"--color-on-primary-muted: color-mix(in srgb, #fff 78%, transparent);",
		"--color-on-primary-soft: color-mix(in srgb, #fff 18%, transparent);",
		"--color-on-primary-soft-hover: color-mix(in srgb, #fff 22%, transparent);",
		"--color-on-primary-border: color-mix(in srgb, #fff 35%, transparent);",
		"--color-on-primary-ghost: color-mix(in srgb, #fff 12%, transparent);",
		"--sow-nl-from: var(--color-primary-dark);",
		"--sow-nl-to: var(--color-primary);",
		"--sow-ticker-label-bg: var(--color-primary);",
		"--sow-ticker-bg: var(--color-ink);",
		"--sow-ticker-fg: var(--color-on-primary);",
		"--shadow-focus: 0 0 0 3px color-mix(in srgb, {$primary} 28%, transparent);",
		"--color-muted: color-mix(in srgb, {$ink} 42%, #94a3b8);",
	);

	$css  = ":root {\n  " . implode( "\n  ", $lines ) . "\n  " . implode( "\n  ", $derived ) . "\n}\n";
	$css .= "html { font-size: var(--font-size-root, 16px); }\n";
	$css .= "body { font-family: var(--font-body, var(--font-ar)); }\n";
	$css .= ".layout-full .container { max-width: 100%; }\n";
	$css .= ".header-style-sticky .site-header { position: sticky; top: 0; z-index: 100; }\n";
	$css .= ".footer-cols-3 .footer-grid--4 .footer-col:nth-child(n+5) { display: none; }\n";

	if ( function_exists( 'syria_oil_banner_has_decoration' ) && syria_oil_banner_has_decoration() ) {
		$css .= syria_oil_banner_decoration_css();
	} elseif ( syria_oil_mod( 'banner_flag_overlay', false ) ) {
		$css .= syria_oil_flag_banner_overlay_css();
	}

	if ( syria_oil_mod( 'show_syrian_emblem', false ) ) {
		$css .= syria_oil_syrian_emblem_css();
	}

	wp_add_inline_style( 'syria-oil-tokens', $css );
}
add_action( 'wp_enqueue_scripts', 'syria_oil_print_identity_css', 25 );

/**
 * Public URL for the bundled New Syrian Emblem (2025).
 *
 * @return string
 */
function syria_oil_syrian_emblem_url() {
	return syria_oil_banner_emblem_image_url();
}

/**
 * Print the New Syrian Emblem image (decorative).
 *
 * @param string $class Extra CSS classes.
 */
function syria_oil_the_syrian_emblem( $class = 'so-emblem-mark' ) {
	if ( ! syria_oil_mod( 'show_syrian_emblem', false ) ) {
		return;
	}
	printf(
		'<img class="%1$s" src="%2$s" alt="%3$s" width="160" height="120" loading="lazy" decoding="async" aria-hidden="true" />',
		esc_attr( $class ),
		esc_url( syria_oil_syrian_emblem_url() ),
		esc_attr__( 'New Syrian Emblem', 'syria-oil' )
	);
}

/**
 * Public URL for the bundled oil pumpjack SVG.
 *
 * @return string
 */
function syria_oil_oil_pump_url() {
	return trailingslashit( get_template_directory_uri() ) . 'assets/images/oil-pumpjack.svg';
}

/**
 * Print a small oil pumpjack mark (decorative).
 *
 * @param string $class Extra CSS classes.
 */
function syria_oil_the_oil_pump( $class = 'so-oil-pump' ) {
	printf(
		'<img class="%1$s" src="%2$s" alt="" width="48" height="48" loading="lazy" decoding="async" aria-hidden="true" />',
		esc_attr( $class ),
		esc_url( syria_oil_oil_pump_url() )
	);
}

/**
 * Filesystem path for the bundled Syria Oil brand logo.
 *
 * @return string
 */
function syria_oil_brand_logo_path() {
	return trailingslashit( SYRIA_OIL_DIR ) . 'assets/images/syria-oil-logo.png';
}

/**
 * Public URL for the bundled Syria Oil brand logo.
 *
 * @return string
 */
function syria_oil_brand_logo_url() {
	return trailingslashit( get_template_directory_uri() ) . 'assets/images/syria-oil-logo.png';
}

/**
 * Filesystem path for the white brand logo (footer / preloader).
 *
 * @return string
 */
function syria_oil_brand_logo_white_path() {
	return trailingslashit( SYRIA_OIL_DIR ) . 'assets/images/syria-oil-logo-white.png';
}

/**
 * Public URL for the white brand logo.
 *
 * @return string
 */
function syria_oil_brand_logo_white_url() {
	return trailingslashit( get_template_directory_uri() ) . 'assets/images/syria-oil-logo-white.png';
}

/**
 * Sideload a bundled logo file into the Media Library (reuses by option key).
 *
 * @param string $path Absolute file path.
 * @param string $filename Upload basename.
 * @param string $title Attachment title.
 * @param string $option_key Option storing attachment ID.
 * @return int Attachment ID or 0.
 */
function syria_oil_sideload_logo_file( $path, $filename, $title, $option_key ) {
	if ( ! is_readable( $path ) ) {
		return 0;
	}

	$existing = absint( get_option( $option_key, 0 ) );
	if ( $existing && get_post( $existing ) && 'attachment' === get_post_type( $existing ) ) {
		return $existing;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$bits = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( false === $bits ) {
		return 0;
	}

	$upload = wp_upload_bits( $filename, null, $bits );
	if ( ! empty( $upload['error'] ) || empty( $upload['file'] ) ) {
		return 0;
	}

	$mime = 'image/png';
	if ( preg_match( '/\.svg$/i', $filename ) ) {
		$mime = 'image/svg+xml';
	} elseif ( preg_match( '/\.jpe?g$/i', $filename ) ) {
		$mime = 'image/jpeg';
	}

	$attach_id = wp_insert_attachment(
		array(
			'post_mime_type' => $mime,
			'post_title'     => $title,
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$upload['file']
	);

	if ( is_wp_error( $attach_id ) || ! $attach_id ) {
		return 0;
	}

	$meta = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
	wp_update_attachment_metadata( $attach_id, $meta );
	update_post_meta( $attach_id, '_wp_attachment_image_alt', __( 'Syria Oil', 'syria-oil' ) );
	update_option( $option_key, (int) $attach_id, false );

	return (int) $attach_id;
}

/**
 * Sideload the bundled Syria Oil brand logo into the Media Library.
 *
 * @return int Attachment ID or 0.
 */
function syria_oil_sideload_brand_logo() {
	return syria_oil_sideload_logo_file(
		syria_oil_brand_logo_path(),
		'syria-oil-logo.png',
		__( 'Syria Oil Logo', 'syria-oil' ),
		'syria_oil_brand_logo_attachment_id'
	);
}

/**
 * Sideload the white logo (footer / preloader).
 *
 * @return int Attachment ID or 0.
 */
function syria_oil_sideload_brand_logo_white() {
	return syria_oil_sideload_logo_file(
		syria_oil_brand_logo_white_path(),
		'syria-oil-logo-white.png',
		__( 'Syria Oil Logo (white)', 'syria-oil' ),
		'syria_oil_brand_logo_white_attachment_id'
	);
}

/**
 * Ensure Theme Options primary/dark logos use the bundled Syria Oil brand marks.
 *
 * Favicon stays on the Syrian emblem (better at small sizes).
 *
 * @param bool $force Overwrite existing logo settings.
 * @return int Primary attachment ID or 0.
 */
function syria_oil_seed_brand_logo( $force = false ) {
	$attach = syria_oil_sideload_brand_logo();
	$white  = syria_oil_sideload_brand_logo_white();
	if ( ! $attach && ! $white ) {
		return 0;
	}

	$logo = function_exists( 'syria_oil_mod' ) ? absint( syria_oil_mod( 'logo_primary', 0 ) ) : 0;
	$dark = function_exists( 'syria_oil_mod' ) ? absint( syria_oil_mod( 'logo_dark', 0 ) ) : 0;

	if ( function_exists( 'syria_oil_set_mod' ) ) {
		if ( $attach && ( $force || ! $logo ) ) {
			syria_oil_set_mod( 'logo_primary', $attach );
		}
		if ( $white && ( $force || ! $dark || $dark === $logo ) ) {
			// Prefer white mark for footer / preloader (dark backgrounds).
			syria_oil_set_mod( 'logo_dark', $white );
		} elseif ( $attach && ( $force || ! $dark ) ) {
			syria_oil_set_mod( 'logo_dark', $attach );
		}
	} elseif ( $attach && ( $force || ! $logo ) ) {
		set_theme_mod( 'custom_logo', $attach );
	}

	update_option( 'syria_oil_brand_logo_seeded', time(), false );
	update_option( 'syria_oil_white_logo_seeded_v1', '1', false );
	return $attach ? $attach : $white;
}

/**
 * One-time: attach white logo for footer/preloader on existing sites.
 */
function syria_oil_maybe_seed_white_logo() {
	if ( get_option( 'syria_oil_white_logo_seeded_v1' ) ) {
		return;
	}
	if ( ! is_admin() && ! doing_action( 'after_switch_theme' ) ) {
		return;
	}
	if ( ! current_user_can( 'upload_files' ) && ! doing_action( 'after_switch_theme' ) ) {
		return;
	}
	$white = syria_oil_sideload_brand_logo_white();
	if ( $white && function_exists( 'syria_oil_set_mod' ) ) {
		syria_oil_set_mod( 'logo_dark', $white );
	}
	update_option( 'syria_oil_white_logo_seeded_v1', '1', false );
}
add_action( 'after_switch_theme', 'syria_oil_maybe_seed_white_logo', 21 );
add_action( 'admin_init', 'syria_oil_maybe_seed_white_logo', 36 );

/**
 * One-time brand logo seed (replaces emblem-as-header-logo once).
 */
function syria_oil_maybe_seed_brand_logo() {
	if ( get_option( 'syria_oil_brand_logo_seeded' ) ) {
		return;
	}
	if ( ! is_admin() && ! doing_action( 'after_switch_theme' ) ) {
		return;
	}
	if ( ! current_user_can( 'upload_files' ) && ! doing_action( 'after_switch_theme' ) ) {
		return;
	}
	// Force once so sites previously seeded with the emblem get the brand logo in the header.
	syria_oil_seed_brand_logo( true );
}
add_action( 'after_switch_theme', 'syria_oil_maybe_seed_brand_logo', 20 );
add_action( 'admin_init', 'syria_oil_maybe_seed_brand_logo', 35 );

/**
 * Seed banner flag + emblem images into Theme Options.
 *
 * @param bool $force Overwrite existing media fields.
 */
function syria_oil_seed_banner_images( $force = false ) {
	$flag = syria_oil_sideload_logo_file(
		trailingslashit( SYRIA_OIL_DIR ) . 'assets/images/syria-flag-stripes.svg',
		'syria-flag-stripes.svg',
		__( 'Syria flag stripes', 'syria-oil' ),
		'syria_oil_flag_attachment_id'
	);
	$emblem = syria_oil_sideload_syrian_emblem();

	if ( ! function_exists( 'syria_oil_set_mod' ) ) {
		return;
	}

	$cur_flag   = absint( syria_oil_mod( 'banner_flag_image', 0 ) );
	$cur_emblem = absint( syria_oil_mod( 'banner_emblem_image', 0 ) );

	if ( $flag && ( $force || ! $cur_flag ) ) {
		syria_oil_set_mod( 'banner_flag_image', $flag );
	}
	if ( $emblem && ( $force || ! $cur_emblem ) ) {
		syria_oil_set_mod( 'banner_emblem_image', $emblem );
	}
}

/**
 * One-time banner image seed.
 */
function syria_oil_maybe_seed_banner_images() {
	if ( get_option( 'syria_oil_banner_images_seeded_v1' ) ) {
		return;
	}
	if ( ! is_admin() && ! doing_action( 'after_switch_theme' ) ) {
		return;
	}
	if ( ! current_user_can( 'upload_files' ) && ! doing_action( 'after_switch_theme' ) ) {
		return;
	}
	syria_oil_seed_banner_images( false );
	update_option( 'syria_oil_banner_images_seeded_v1', '1', false );
}
add_action( 'after_switch_theme', 'syria_oil_maybe_seed_banner_images', 22 );

/**
 * Sideload the bundled Syrian emblem into the Media Library.
 *
 * @return int Attachment ID or 0.
 */
function syria_oil_sideload_syrian_emblem() {
	$path = trailingslashit( SYRIA_OIL_DIR ) . 'assets/images/syrian-emblem-2025.png';
	if ( ! is_readable( $path ) ) {
		return 0;
	}

	$existing = absint( get_option( 'syria_oil_emblem_attachment_id', 0 ) );
	if ( $existing && get_post( $existing ) && 'attachment' === get_post_type( $existing ) ) {
		return $existing;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$bits = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( false === $bits ) {
		return 0;
	}

	$upload = wp_upload_bits( 'syrian-emblem-2025.png', null, $bits );
	if ( ! empty( $upload['error'] ) || empty( $upload['file'] ) ) {
		return 0;
	}

	$attach_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/png',
			'post_title'     => __( 'New Syrian Emblem', 'syria-oil' ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$upload['file']
	);

	if ( is_wp_error( $attach_id ) || ! $attach_id ) {
		return 0;
	}

	$meta = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
	wp_update_attachment_metadata( $attach_id, $meta );
	update_post_meta( $attach_id, '_wp_attachment_image_alt', __( 'New Syrian Emblem', 'syria-oil' ) );
	update_option( 'syria_oil_emblem_attachment_id', (int) $attach_id, false );

	return (int) $attach_id;
}

/**
 * Ensure Theme Options logo + WP site icon use the Syrian emblem.
 *
 * @param bool $force Overwrite existing logo/favicon.
 * @return int Attachment ID or 0.
 */
function syria_oil_seed_emblem_identity( $force = false ) {
	$attach = syria_oil_sideload_syrian_emblem();
	if ( ! $attach ) {
		return 0;
	}

	$logo = function_exists( 'syria_oil_mod' ) ? absint( syria_oil_mod( 'logo_primary', 0 ) ) : 0;
	$icon = absint( get_option( 'site_icon' ) );

	if ( $force || ! $logo ) {
		if ( function_exists( 'syria_oil_set_mod' ) ) {
			syria_oil_set_mod( 'logo_primary', $attach );
		} else {
			set_theme_mod( 'custom_logo', $attach );
		}
	}

	if ( $force || ! $icon ) {
		if ( function_exists( 'syria_oil_set_mod' ) ) {
			syria_oil_set_mod( 'favicon', $attach );
		} else {
			update_option( 'site_icon', $attach );
		}
	}

	update_option( 'syria_oil_emblem_identity_seeded', time(), false );
	return $attach;
}

/**
 * One-time identity seed after theme activation / admin load.
 */
function syria_oil_maybe_seed_emblem_identity() {
	if ( get_option( 'syria_oil_emblem_identity_seeded' ) ) {
		return;
	}
	if ( ! is_admin() && ! doing_action( 'after_switch_theme' ) ) {
		return;
	}
	if ( ! current_user_can( 'upload_files' ) && ! doing_action( 'after_switch_theme' ) ) {
		return;
	}
	syria_oil_seed_emblem_identity( false );
}
add_action( 'after_switch_theme', 'syria_oil_maybe_seed_emblem_identity' );
add_action( 'admin_init', 'syria_oil_maybe_seed_emblem_identity', 30 );

/**
 * Fallback favicon when WP site_icon is empty.
 */
function syria_oil_fallback_site_icon() {
	if ( absint( get_option( 'site_icon' ) ) ) {
		return;
	}
	$url = syria_oil_syrian_emblem_url();
	printf(
		'<link rel="icon" href="%1$s" type="image/png" sizes="any" />' . "\n" .
		'<link rel="apple-touch-icon" href="%1$s" />' . "\n",
		esc_url( $url )
	);
}
add_action( 'wp_head', 'syria_oil_fallback_site_icon', 2 );
add_action( 'admin_head', 'syria_oil_fallback_site_icon', 2 );

/**
 * Configurable banner edge decoration (color / flag image).
 *
 * @return string
 */
function syria_oil_banner_decoration_css() {
	$mode  = function_exists( 'syria_oil_banner_edge_mode' ) ? syria_oil_banner_edge_mode() : 'none';
	$color = function_exists( 'syria_oil_banner_edge_color' ) ? syria_oil_banner_edge_color() : '#007a3d';
	$flag  = function_exists( 'syria_oil_banner_flag_image_url' ) ? syria_oil_banner_flag_image_url() : '';
	$flag  = esc_url( $flag );

	$use_color = in_array( $mode, array( 'color', 'both' ), true );
	$use_image = in_array( $mode, array( 'image', 'both' ), true );

	$edge_color = $use_color ? $color : 'transparent';
	$image_rule = '';
	if ( $use_image && $flag ) {
		$image_rule = "url(\"{$flag}\")";
	}

	$before_bg = array();
	if ( $use_color ) {
		$before_bg[] = "linear-gradient(90deg, {$edge_color} 0%, {$edge_color} 18%, transparent 18%)";
	}
	if ( $use_image && $image_rule ) {
		$before_bg[] = "linear-gradient(90deg, rgba(0,0,0,0.35) 0%, transparent 22%), {$image_rule}";
	}
	$before_bg[] = 'linear-gradient(180deg, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0.25) 45%, rgba(0,0,0,0.65) 100%)';

	$before_layers = implode( ', ', $before_bg );

	return <<<CSS
.so-banner-decor .article-top,
.so-banner-decor .page-hero,
.so-banner-decor .sow-page-hero,
.so-banner-decor .so-banner,
.so-banner-decor .elementor-section.so-banner {
  position: relative;
  isolation: isolate;
  overflow: hidden;
  color: #fff !important;
}
.so-banner-decor .article-top::before,
.so-banner-decor .page-hero::before,
.so-banner-decor .sow-page-hero::before,
.so-banner-decor .so-banner::before,
.so-banner-decor .elementor-section.so-banner::before {
  content: "";
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background: {$before_layers};
  background-size: cover, auto 100%, cover;
  background-repeat: no-repeat;
  background-position: left center, left center, center;
}
.so-banner-decor .article-top > *:not(.so-emblem-mark),
.so-banner-decor .page-hero > *:not(.so-emblem-mark),
.so-banner-decor .sow-page-hero > *:not(.so-emblem-mark),
.so-banner-decor .so-banner > .elementor-container,
.so-banner-decor .elementor-section.so-banner > .elementor-container {
  position: relative;
  z-index: 1;
}
CSS;
}

/**
 * New Syria flag (green / white / black + red stars) as banner overlay CSS.
 *
 * @return string
 */
function syria_oil_flag_banner_overlay_css() {
	return <<<'CSS'
/* Full-bleed banners — same width on every page */
.page-hero,
.article-top,
.sow-page-hero,
.so-banner,
.elementor-section.so-banner,
.so-flag-banners .page-hero,
.so-flag-banners .article-top,
.so-flag-banners .sow-page-hero,
.so-flag-banners .so-banner,
.so-flag-banners .elementor-section.so-banner {
  width: 100% !important;
  max-width: none !important;
  margin-inline: 0 !important;
  box-sizing: border-box;
  left: auto;
  right: auto;
}
/* Elementor boxed sections used as banners still span the viewport */
body .elementor-section.so-banner.elementor-section-boxed,
body .elementor-top-section.so-banner,
body .elementor-section.sow-page-hero {
  width: 100% !important;
  max-width: 100% !important;
}
body .elementor-section.so-banner > .elementor-container,
body .elementor-section.sow-page-hero > .elementor-container {
  max-width: var(--container, 1200px) !important;
  width: 100% !important;
  margin-inline: auto !important;
}

/* New Syria flag + dark readability scrim (white text stays readable on white stripe) */
.so-flag-banners .article-top,
.so-flag-banners .page-hero,
.so-flag-banners .sow-page-hero,
.so-flag-banners .so-banner,
.so-flag-banners .elementor-section.so-banner {
  position: relative;
  isolation: isolate;
  overflow: hidden;
  background-color: #003820 !important;
  background-image: none !important;
  color: #fff !important;
}
.so-flag-banners .article-top::before,
.so-flag-banners .page-hero::before,
.so-flag-banners .sow-page-hero::before,
.so-flag-banners .so-banner::before,
.so-flag-banners .elementor-section.so-banner::before {
  content: "";
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background:
    linear-gradient(
      180deg,
      rgba(0, 40, 24, 0.82) 0%,
      rgba(0, 35, 22, 0.78) 38%,
      rgba(8, 18, 14, 0.88) 62%,
      rgba(5, 8, 10, 0.92) 100%
    ),
    linear-gradient(
      180deg,
      #006b35 0%,
      #007a3d 33.333%,
      #e8ece9 33.333%,
      #e8ece9 66.666%,
      #111111 66.666%,
      #0a0a0a 100%
    );
  opacity: 1;
}
/* Three red stars — SVG so all three always show */
.so-flag-banners .article-top::after,
.so-flag-banners .page-hero::after,
.so-flag-banners .sow-page-hero::after,
.so-flag-banners .so-banner::after,
.so-flag-banners .elementor-section.so-banner::after {
  content: "";
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  z-index: 0;
  pointer-events: none;
  width: min(42vw, 14rem);
  height: clamp(1.35rem, 4.2vw, 2.35rem);
  opacity: 0.55;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 180 40' fill='%23ce1126'%3E%3Cpath d='M20 2l4.5 13.8H39l-11.7 8.5 4.5 13.8L20 29.6 8.3 38.1l4.5-13.8L1 15.8h14.5z'/%3E%3Cpath d='M90 2l4.5 13.8H109l-11.7 8.5 4.5 13.8L90 29.6 78.3 38.1l4.5-13.8L71 15.8h14.5z'/%3E%3Cpath d='M160 2l4.5 13.8H179l-11.7 8.5 4.5 13.8L160 29.6 148.3 38.1l4.5-13.8L141 15.8h14.5z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: center;
  background-size: contain;
  filter: drop-shadow(0 0 1px rgba(0,0,0,0.35));
}
.so-flag-banners .article-top > *:not(.so-emblem-mark),
.so-flag-banners .page-hero > *:not(.so-emblem-mark),
.so-flag-banners .sow-page-hero > *:not(.so-emblem-mark),
.so-flag-banners .so-banner > .elementor-container,
.so-flag-banners .elementor-section.so-banner > .elementor-container {
  position: relative;
  z-index: 1;
}
/* High-contrast banner type */
.so-flag-banners .page-hero h1,
.so-flag-banners .article-top h1,
.so-flag-banners .sow-page-hero h1,
.so-flag-banners .so-banner h1,
.so-flag-banners .page-hero .elementor-heading-title,
.so-flag-banners .article-top .elementor-heading-title {
  color: #ffffff !important;
  text-shadow:
    0 1px 2px rgba(0, 0, 0, 0.75),
    0 2px 14px rgba(0, 0, 0, 0.45);
}
.so-flag-banners .page-hero p,
.so-flag-banners .article-top p,
.so-flag-banners .sow-page-hero p,
.so-flag-banners .sow-page-hero .lede,
.so-flag-banners .page-hero .breadcrumb,
.so-flag-banners .page-hero .breadcrumb a,
.so-flag-banners .page-hero .breadcrumb [aria-current="page"],
.so-flag-banners .article-top .breadcrumb,
.so-flag-banners .article-top .breadcrumb a,
.so-flag-banners .article-top .breadcrumb [aria-current="page"],
.so-flag-banners .sow-page-hero .breadcrumb,
.so-flag-banners .sow-page-hero .breadcrumb a,
.so-flag-banners .sow-page-hero .breadcrumb .sep,
.so-flag-banners .sow-page-hero .breadcrumb [aria-current="page"],
.so-flag-banners .article-meta-row {
  color: rgba(255, 255, 255, 0.95) !important;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.55);
}
CSS;
}

/**
 * New Syrian Emblem watermark + footer mark CSS.
 *
 * @return string
 */
function syria_oil_syrian_emblem_css() {
	return <<<'CSS'
/* New Syrian Emblem — physical LEFT always (RTL-safe; do not use inline-start) */
body.so-syrian-emblem img.so-emblem-mark {
  position: absolute !important;
  top: 50% !important;
  left: 2.5% !important;
  right: auto !important;
  inset-inline-start: auto !important;
  inset-inline-end: auto !important;
  margin: 0 !important;
  transform: translateY(-50%) !important;
  width: clamp(100px, 18vw, 180px) !important;
  max-width: 28% !important;
  height: auto !important;
  opacity: 0.85 !important;
  z-index: 0 !important;
  pointer-events: none !important;
  filter: drop-shadow(0 2px 10px rgba(0, 0, 0, 0.4));
}
body.so-syrian-emblem .article-top,
body.so-syrian-emblem .page-hero,
body.so-syrian-emblem .sow-page-hero,
body.so-syrian-emblem .so-banner,
body.so-syrian-emblem .elementor-section.so-banner,
body.so-syrian-emblem .elementor-location-archive > .elementor-section:first-child,
body.so-syrian-emblem .elementor-location-single > .elementor-section:first-child {
  position: relative !important;
  overflow: hidden;
}
body.so-syrian-emblem .footer-emblem {
  display: block;
  width: 72px;
  height: auto;
  margin-bottom: 0.85rem;
  opacity: 0.9;
  position: static !important;
}
body.so-syrian-emblem .footer-emblem img,
body.so-syrian-emblem .footer-emblem__img {
  position: static !important;
  width: 100% !important;
  max-width: none !important;
  height: auto !important;
  opacity: 1 !important;
  transform: none !important;
  left: auto !important;
  top: auto !important;
  filter: none;
}
@media (max-width: 767px) {
  /* Keep emblem in the black band / corner so it never covers the three stars */
  body.so-syrian-emblem img.so-emblem-mark {
    width: 56px !important;
    max-width: 22% !important;
    opacity: 0.65 !important;
    left: 0.5rem !important;
    right: auto !important;
    top: auto !important;
    bottom: 0.45rem !important;
    transform: none !important;
  }
}
CSS;
}

/**
 * Fail-soft options → Elementor kit sync lives in inc/elementor.php (syria_oil_sync_kit_from_options).
 * Front end brand colors/fonts come from theme CSS vars, not the Elementor kit.
 */

/**
 * BC alias.
 */
function syria_oil_sync_kit_from_customizer() {
	if ( function_exists( 'syria_oil_sync_kit_from_options' ) ) {
		syria_oil_sync_kit_from_options();
	}
}

/**
 * Admin notices: options panel is brand source; kit sync failure + retry.
 */
function syria_oil_identity_admin_notices() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	$show   = $screen && in_array(
		$screen->id,
		array(
			'themes',
			'dashboard',
			'toplevel_page_syria-oil-options',
			'syria-oil-options_page_syria-oil-setup',
			'toplevel_page_elementor',
			'elementor_page_elementor-settings',
		),
		true
	);

	if ( $show && function_exists( 'syria_oil_has_elementor' ) && syria_oil_has_elementor() ) {
		echo '<div class="notice notice-info is-dismissible"><p>';
		echo esc_html__( 'Syria Oil: set brand colors and fonts in Syria Oil → Theme Options — not in Elementor → Site Settings → Global Colors.', 'syria-oil' );
		echo '</p></div>';
	}

	if ( get_transient( 'syria_oil_kit_sync_failed' ) ) {
		$retry_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=syria_oil_sync_elementor_kit' ),
			'syria_oil_sync_elementor_kit'
		);
		echo '<div class="notice notice-warning is-dismissible"><p>';
		echo esc_html__( 'Brand colors were saved in Theme Options and are applied on the front end via theme CSS. Elementor Site Kit sync failed — Elementor editor global colors may be out of date until you retry.', 'syria-oil' );
		echo ' <a href="' . esc_url( $retry_url ) . '">' . esc_html__( 'Retry Elementor Kit Sync', 'syria-oil' ) . '</a>';
		echo '</p></div>';
	}
}
add_action( 'admin_notices', 'syria_oil_identity_admin_notices' );
