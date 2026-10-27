<?php
/**
 * Codestar Framework theme options — Syria Oil Control Center.
 *
 * Option key: syria_oil_options. Sole brand store after migration.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SYRIA_OIL_OPTIONS', 'syria_oil_options' );
define( 'SYRIA_OIL_PAGE_META', '_syria_oil_page_options' );

syria_oil_require( 'inc/theme-options-previews.php' );
syria_oil_require( 'inc/theme-options-admin-i18n.php' );

/**
 * Default values for syria_oil_options (ex-Control Center keys + branding).
 *
 * @return array<string,mixed>
 */
function syria_oil_options_defaults() {
	return array(
		'top_bar_text'                     => '',
		'top_bar_link_label'               => '',
		'top_bar_link_url'                 => '',
		'show_top_bar_date'                => true,
		'search_placeholder'               => '',
		'footer_tagline'                   => '',
		'footer_explore_title'             => '',
		'footer_cats_title'                => '',
		'footer_contact_title'             => '',
		'footer_bottom_text'               => '',
		'footer_sponsors'                  => '',
		'footer_sponsors_title'            => '',
		'contact_email'                    => '',
		'contact_phone'                    => '',
		'contact_page_url'                 => '',
		'about_page_url'                   => '',
		'social_facebook'                  => '',
		'social_x'                         => '',
		'social_youtube'                   => '',
		'social_linkedin'                  => '',
		'social_instagram'                 => '',
		'social_telegram'                  => '',
		'show_header_social'               => true,
		'article_font_scale'               => true,
		'continuous_reading'               => true,
		'show_top_bar'                     => true,
		'color_primary'                    => '#007a3d',
		'color_secondary'                  => '#f7f9fb',
		'maintenance_mode_enabled'         => false,
		'maintenance_mode_title'           => '',
		'maintenance_mode_message'         => '',
		'maintenance_mode_show_countdown'  => false,
		'maintenance_mode_end_time'        => '',
		'maintenance_mode_custom_code'     => '',
		'color_accent'         => '#ce1126',
		'color_depth'          => '#321010',
		'color_ink'            => '#0f1419',
		'font_heading'         => 'Noto Kufi Arabic',
		'font_body'            => 'IBM Plex Sans Arabic',
		'font_scale'           => 'medium',
		'container_width'      => 'boxed',
		'card_image_ratio'     => '16:9',
		'hero_image_ratio'     => '16:9',
		'media_object_fit'     => 'cover',
		'side_ad_max_height'   => 520,
		'show_mobile_ads'      => true,
		'mobile_ads_limit'     => 0,
		'mobile_ad_max_height' => 140,
		'header_style'         => 'standard',
		'footer_columns'       => '3',
		'enable_dark_mode'     => false,
		'show_breadcrumbs'     => true,
		'show_back_to_top'     => true,
		'show_preloader'       => true,
		'banner_flag_overlay'  => false,
		'show_syrian_emblem'   => false,
		'show_footer_oil_pump' => 0,
		'hero_featured_sidebar' => 'below',
		'banner_edge_mode'      => 'none',
		'banner_edge_color_preset' => 'primary',
		'banner_edge_color_custom' => '#321010',
		'banner_flag_image'     => 0,
		'banner_emblem_image'   => 0,
		'force_arabic_rtl'     => true,
		'logo_primary'           => 0,
		'logo_retina'            => 0,
		'logo_dark'              => 0,
		'logo_mobile'            => 0,
		'logo_desktop_height'    => 176,
		'logo_desktop_max_width' => 200,
		'logo_mobile_height'     => 112,
		'logo_mobile_max_width'  => 128,
		'logo_drawer_height'     => 96,
		'logo_drawer_max_width'  => 140,
		'logo_footer_height'     => 88,
		'logo_footer_max_width'  => 140,
		'header_nav_font_size'   => 15,
		'logo_footer_mobile_height'    => 56,
		'logo_footer_mobile_max_width' => 96,
		'favicon'              => 0,
		'widget_defaults'      => array(
			'query_posts_per_page' => 6,
			'ticker_label'         => 'آخر الأخبار',
			'newsletter_title'     => '',
			'newsletter_text'      => '',
			'faq_empty'            => '',
		),
		/* Cache & Performance (Phase 9) */
		'perf_lazy_images'     => true,
		'perf_defer_js'        => true,
		'perf_combine_css'     => false,
		'perf_disable_emojis'  => true,
		'perf_preconnect_eia'  => true,
		/* Careers — use 1/0 for CSF switchers (not PHP booleans) */
		'careers_enabled'      => 1,
		'careers_show_home'    => 1,
		'careers_show_footer'  => 1,
		'careers_show_closed'  => 0,
		'careers_apply_email'  => '',
		'careers_home_limit'   => 3,
		'careers_intro_title'  => 'الوظائف والتوظيف',
		'careers_intro_text'   => 'الوظائف الشاغرة والمزايا ونموذج التقديم.',
		'careers_benefits'     => 'بيئة مهنية وبرامج تدريب ومسارات ترقية. تأمين صحي وبدل مواقع ميدانية وثقافة سلامة أولاً.',
	);
}

/**
 * BC alias used by older includes.
 *
 * @return array
 */
function syria_oil_customizer_defaults() {
	return syria_oil_options_defaults();
}

/**
 * Keys that may be overridden per page/post.
 *
 * @return string[]
 */
function syria_oil_page_override_keys() {
	return array(
		'color_primary',
		'header_style',
		'container_width',
		'show_breadcrumbs',
	);
}

/**
 * Normalize CSF media field (array|int) to attachment ID.
 *
 * @param mixed $value Raw.
 * @return int
 */
function syria_oil_media_id( $value ) {
	if ( is_array( $value ) && isset( $value['id'] ) ) {
		return absint( $value['id'] );
	}
	return absint( $value );
}

/**
 * Raw options array from DB (no defaults merge).
 *
 * @return array<string,mixed>
 */
function syria_oil_get_options_raw() {
	$opts = get_option( SYRIA_OIL_OPTIONS, array() );
	return is_array( $opts ) ? $opts : array();
}

/**
 * Typed getter — options + optional singular overrides. No theme_mod after migrate.
 *
 * @param string $key     Option key.
 * @param mixed  $default Optional override of built-in default.
 * @return mixed
 */
function syria_oil_mod( $key, $default = null ) {
	$defaults = syria_oil_options_defaults();
	if ( null === $default ) {
		$default = array_key_exists( $key, $defaults ) ? $defaults[ $key ] : '';
	}

	$opts  = syria_oil_get_options_raw();
	$value = array_key_exists( $key, $opts ) ? $opts[ $key ] : $default;

	if ( in_array( $key, syria_oil_page_override_keys(), true ) && function_exists( 'is_singular' ) && is_singular() ) {
		$post_id = get_queried_object_id();
		if ( $post_id ) {
			$meta = get_post_meta( $post_id, SYRIA_OIL_PAGE_META, true );
			if ( is_array( $meta ) && array_key_exists( $key, $meta ) && '' !== $meta[ $key ] && null !== $meta[ $key ] ) {
				$value = $meta[ $key ];
			}
		}
	}

	if ( in_array( $key, array( 'logo_primary', 'logo_retina', 'logo_dark', 'logo_mobile', 'favicon' ), true ) ) {
		return syria_oil_media_id( $value );
	}

	return $value;
}

/**
 * Persist a single option key into syria_oil_options (+ logo/favicon WP sync).
 *
 * @param string $key   Key.
 * @param mixed  $value Value.
 */
function syria_oil_set_mod( $key, $value ) {
	$opts         = syria_oil_get_options_raw();
	$opts[ $key ] = $value;
	update_option( SYRIA_OIL_OPTIONS, $opts, false );

	if ( 'logo_primary' === $key ) {
		syria_oil_sync_logo_to_wp( $value );
	}
	if ( 'favicon' === $key ) {
		syria_oil_sync_favicon_to_wp( $value );
	}
}

/**
 * Sync primary logo to WP custom_logo for SEO / get_custom_logo().
 *
 * @param mixed $value Media id or CSF media array.
 */
function syria_oil_sync_logo_to_wp( $value ) {
	$id = syria_oil_media_id( $value );
	if ( $id ) {
		set_theme_mod( 'custom_logo', $id );
	} else {
		remove_theme_mod( 'custom_logo' );
	}
}

/**
 * Sync favicon to site_icon.
 *
 * @param mixed $value Media id or CSF media array.
 */
function syria_oil_sync_favicon_to_wp( $value ) {
	$id = syria_oil_media_id( $value );
	update_option( 'site_icon', $id );
}

/**
 * One-time theme_mods → syria_oil_options migration.
 */
function syria_oil_migrate_theme_mods_to_options() {
	if ( get_option( 'syria_oil_options_migrated' ) ) {
		return;
	}

	$defaults = syria_oil_options_defaults();
	$merged   = syria_oil_get_options_raw();

	foreach ( array_keys( $defaults ) as $key ) {
		if ( 'widget_defaults' === $key || 'logo_primary' === $key || 'favicon' === $key ) {
			continue;
		}
		if ( ! array_key_exists( $key, $merged ) || '' === $merged[ $key ] || null === $merged[ $key ] ) {
			$from_mod = get_theme_mod( $key, null );
			if ( null !== $from_mod ) {
				$merged[ $key ] = $from_mod;
			}
		}
	}

	if ( empty( $merged['logo_primary'] ) ) {
		$custom = absint( get_theme_mod( 'custom_logo' ) );
		if ( $custom ) {
			$merged['logo_primary'] = $custom;
		}
	}
	if ( empty( $merged['favicon'] ) ) {
		$icon = absint( get_option( 'site_icon' ) );
		if ( $icon ) {
			$merged['favicon'] = $icon;
		}
	}

	$merged = wp_parse_args( $merged, $defaults );
	update_option( SYRIA_OIL_OPTIONS, $merged, false );

	syria_oil_sync_logo_to_wp( $merged['logo_primary'] );
	syria_oil_sync_favicon_to_wp( $merged['favicon'] );

	foreach ( array_keys( $defaults ) as $key ) {
		if ( in_array( $key, array( 'widget_defaults', 'logo_primary', 'favicon' ), true ) ) {
			continue;
		}
		remove_theme_mod( $key );
	}

	update_option( 'syria_oil_options_migrated', defined( 'SYRIA_OIL_VERSION' ) ? SYRIA_OIL_VERSION : '1.5.0', false );
}
add_action( 'after_setup_theme', 'syria_oil_migrate_theme_mods_to_options', 5 );

/**
 * Load CSF once (skip if another plugin already loaded it), then register panel.
 */
function syria_oil_load_csf() {
	if ( ! class_exists( 'CSF' ) ) {
		$file = trailingslashit( SYRIA_OIL_DIR ) . 'inc/csf/codestar-framework.php';
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
	if ( class_exists( 'CSF' ) ) {
		syria_oil_register_theme_options();
	}
}
add_action( 'after_setup_theme', 'syria_oil_load_csf', 6 );

/**
 * Register CSF admin panel + sections (idempotent).
 */
function syria_oil_register_theme_options() {
	if ( ! class_exists( 'CSF' ) ) {
		return;
	}
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	add_filter( 'csf_welcome_page', '__return_false' );

	$prefix   = SYRIA_OIL_OPTIONS;
	$defaults = syria_oil_options_defaults();
	$fonts    = syria_oil_font_choices();
	$brand    = __( 'Set brand colors here — not in Elementor → Site Settings → Global Colors.', 'syria-oil' );

	CSF::createOptions(
		$prefix,
		array(
			'framework_title'    => __( 'Syria Oil', 'syria-oil' ) . ' <small>' . esc_html( SYRIA_OIL_VERSION ) . '</small>',
			'framework_class'    => 'syria-oil-csf',
			'theme'              => 'light',
			'menu_title'         => __( 'Syria Oil', 'syria-oil' ),
			'menu_slug'          => 'syria-oil-options',
			'menu_type'          => 'menu',
			'menu_icon'          => 'dashicons-admin-site-alt3',
			'menu_position'      => 59,
			'menu_capability'    => 'manage_options',
			'show_bar_menu'      => false,
			'show_search'        => true,
			'show_reset_all'     => true,
			'show_reset_section' => true,
			'show_footer'        => true,
			'ajax_save'          => true,
			'save_defaults'      => true,
			'form_action'        => '',
			'footer_text'        => __( 'Changes apply after Save. Hard-refresh the front page (Ctrl+Shift+R) to bypass cache.', 'syria-oil' ),
			'footer_credit'      => sprintf(
				/* translators: %s: theme version */
				__( 'Syria Oil Theme %s — built for Elementor & RTL Arabic news.', 'syria-oil' ),
				esc_html( SYRIA_OIL_VERSION )
			),
		)
	);

	/* ---- Overview ---- */
	CSF::createSection(
		$prefix,
		array(
			'id'          => 'overview',
			'title'       => __( 'Overview', 'syria-oil' ),
			'icon'        => 'fas fa-home',
			'description' => __( 'Quick status of your portal settings and shortcuts to common tasks.', 'syria-oil' ),
			'fields'      => array(
				array(
					'type'     => 'callback',
					'function' => 'syria_oil_render_options_dashboard',
				),
			),
		)
	);

	/* ---- General ---- */
	CSF::createSection(
		$prefix,
		array(
			'title'       => __( 'General', 'syria-oil' ),
			'icon'        => 'fas fa-cog',
			'description' => __( 'Site-wide toggles: navigation helpers, preloader, RTL, and legacy decoration switches.', 'syria-oil' ),
			'fields'      => array(
				array(
					'id'      => 'show_breadcrumbs',
					'type'    => 'switcher',
					'title'   => __( 'Show breadcrumbs', 'syria-oil' ),
					'default' => $defaults['show_breadcrumbs'],
				),
				array(
					'id'      => 'show_back_to_top',
					'type'    => 'switcher',
					'title'   => __( 'Show back-to-top button', 'syria-oil' ),
					'default' => $defaults['show_back_to_top'],
				),
				array(
					'id'      => 'show_preloader',
					'type'    => 'switcher',
					'title'   => __( 'Show page preloader', 'syria-oil' ),
					'desc'    => __( 'Black screen with the white logo and the website title until the page finishes loading.', 'syria-oil' ),
					'default' => $defaults['show_preloader'],
				),
				array(
					'id'       => 'banner_flag_overlay',
					'type'     => 'switcher',
					'title'    => __( 'Legacy flag overlay (deprecated)', 'syria-oil' ),
					'subtitle' => __( 'Use Banner tab instead. Kept for old sites only.', 'syria-oil' ),
					'default'  => $defaults['banner_flag_overlay'],
				),
				array(
					'id'       => 'show_syrian_emblem',
					'type'     => 'switcher',
					'title'    => __( 'Show emblem watermark on banners', 'syria-oil' ),
					'subtitle' => __( 'Uses Banner → Emblem image. Off by default for an independent media look.', 'syria-oil' ),
					'default'  => $defaults['show_syrian_emblem'],
				),
				array(
					'id'       => 'show_footer_oil_pump',
					'type'     => 'switcher',
					'title'    => __( 'Show oil pump icon in footer', 'syria-oil' ),
					'subtitle' => __( 'Small decorative pumpjack under the footer logo.', 'syria-oil' ),
					'default'  => $defaults['show_footer_oil_pump'],
				),
				array(
					'id'       => 'force_arabic_rtl',
					'type'     => 'switcher',
					'title'    => __( 'Force Arabic RTL UI', 'syria-oil' ),
					'subtitle' => __( 'Keeps the front end Arabic and right-to-left even if WordPress locale detection fails. Turn off only for a dedicated English front end.', 'syria-oil' ),
					'default'  => $defaults['force_arabic_rtl'],
				),
			),
		)
	);

	/* ---- Branding ---- */
	CSF::createSection(
		$prefix,
		array(
			'title'       => __( 'Branding', 'syria-oil' ),
			'icon'        => 'fas fa-image',
			'description' => __( 'Logos, favicon, and pixel sizes for header, mobile menu, and footer.', 'syria-oil' ),
			'fields'      => array(
				array(
					'type'     => 'callback',
					'function' => 'syria_oil_render_branding_preview_pane',
				),
				array(
					'type'    => 'submessage',
					'style'   => 'info',
					'content' => __( 'Primary logo and favicon are managed here and synced to WordPress Site Identity for SEO plugins.', 'syria-oil' ),
				),
				array(
					'type'    => 'submessage',
					'style'   => 'info',
					'content' => __( 'Logo sizes below are in pixels (px). Change a value → Save → hard-refresh the site. Header, footer, mobile bar, and mobile menu each have their own size.', 'syria-oil' ),
				),
				array(
					'type'    => 'heading',
					'content' => __( 'Logo files', 'syria-oil' ),
				),
				array(
					'id'           => 'logo_primary',
					'type'         => 'media',
					'title'        => __( 'Primary logo', 'syria-oil' ),
					'library'      => 'image',
					'url'          => false,
					'preview_size' => 'medium',
				),
				array(
					'id'           => 'logo_retina',
					'type'         => 'media',
					'title'        => __( 'Retina / 2x logo', 'syria-oil' ),
					'library'      => 'image',
					'url'          => false,
				),
				array(
					'id'           => 'logo_dark',
					'type'         => 'media',
					'title'        => __( 'White logo (footer / preloader)', 'syria-oil' ),
					'desc'         => __( 'Used on the dark footer and the page preloader (preloader background follows Colors → Primary). Upload the white Syria Oil mark.', 'syria-oil' ),
					'library'      => 'image',
					'url'          => false,
				),
				array(
					'id'           => 'logo_mobile',
					'type'         => 'media',
					'title'        => __( 'Mobile logo override', 'syria-oil' ),
					'library'      => 'image',
					'url'          => false,
				),
				array(
					'type'    => 'heading',
					'content' => __( 'Logo sizes (px)', 'syria-oil' ),
				),
				array(
					'id'      => 'logo_desktop_height',
					'type'    => 'number',
					'title'   => __( 'Header logo height — desktop (px)', 'syria-oil' ),
					'desc'    => __( 'Top header on desktop / large screens.', 'syria-oil' ),
					'default' => $defaults['logo_desktop_height'],
					'min'     => 24,
					'max'     => 320,
					'unit'    => 'px',
				),
				array(
					'id'      => 'logo_desktop_max_width',
					'type'    => 'number',
					'title'   => __( 'Header logo max width — desktop (px)', 'syria-oil' ),
					'default' => $defaults['logo_desktop_max_width'],
					'min'     => 24,
					'max'     => 480,
					'unit'    => 'px',
				),
				array(
					'id'      => 'logo_mobile_height',
					'type'    => 'number',
					'title'   => __( 'Header logo height — mobile (px)', 'syria-oil' ),
					'desc'    => __( 'Top header bar on phones and tablets.', 'syria-oil' ),
					'default' => $defaults['logo_mobile_height'],
					'min'     => 24,
					'max'     => 240,
					'unit'    => 'px',
				),
				array(
					'id'      => 'logo_mobile_max_width',
					'type'    => 'number',
					'title'   => __( 'Header logo max width — mobile (px)', 'syria-oil' ),
					'desc'    => __( 'Raise this for tall/stacked logos that look too narrow.', 'syria-oil' ),
					'default' => $defaults['logo_mobile_max_width'],
					'min'     => 24,
					'max'     => 320,
					'unit'    => 'px',
				),
				array(
					'id'      => 'logo_drawer_height',
					'type'    => 'number',
					'title'   => __( 'Mobile menu logo height (px)', 'syria-oil' ),
					'desc'    => __( 'Logo at the top of the open mobile menu drawer.', 'syria-oil' ),
					'default' => $defaults['logo_drawer_height'],
					'min'     => 24,
					'max'     => 280,
					'unit'    => 'px',
				),
				array(
					'id'      => 'logo_drawer_max_width',
					'type'    => 'number',
					'title'   => __( 'Mobile menu logo max width (px)', 'syria-oil' ),
					'default' => $defaults['logo_drawer_max_width'],
					'min'     => 24,
					'max'     => 400,
					'unit'    => 'px',
				),
				array(
					'id'      => 'logo_footer_height',
					'type'    => 'number',
					'title'   => __( 'Footer logo height — desktop (px)', 'syria-oil' ),
					'desc'    => __( 'Logo in the site footer on desktop.', 'syria-oil' ),
					'default' => $defaults['logo_footer_height'],
					'min'     => 24,
					'max'     => 320,
					'unit'    => 'px',
				),
				array(
					'id'      => 'logo_footer_max_width',
					'type'    => 'number',
					'title'   => __( 'Footer logo max width — desktop (px)', 'syria-oil' ),
					'default' => $defaults['logo_footer_max_width'],
					'min'     => 24,
					'max'     => 480,
					'unit'    => 'px',
				),
				array(
					'id'      => 'logo_footer_mobile_height',
					'type'    => 'number',
					'title'   => __( 'Footer logo height — mobile (px)', 'syria-oil' ),
					'default' => $defaults['logo_footer_mobile_height'],
					'min'     => 24,
					'max'     => 240,
					'unit'    => 'px',
				),
				array(
					'id'      => 'logo_footer_mobile_max_width',
					'type'    => 'number',
					'title'   => __( 'Footer logo max width — mobile (px)', 'syria-oil' ),
					'default' => $defaults['logo_footer_mobile_max_width'],
					'min'     => 24,
					'max'     => 320,
					'unit'    => 'px',
				),
				array(
					'id'           => 'favicon',
					'type'         => 'media',
					'title'        => __( 'Favicon / site icon', 'syria-oil' ),
					'library'      => 'image',
					'url'          => false,
				),
			),
		)
	);

	/* ---- Colors ---- */
	CSF::createSection(
		$prefix,
		array(
			'title'       => __( 'Colors', 'syria-oil' ),
			'icon'        => 'fas fa-palette',
			'description' => __( 'Brand palette for the whole site. The live preview below updates as you pick colors.', 'syria-oil' ),
			'fields'      => array(
				array(
					'type'    => 'submessage',
					'style'   => 'warning',
					'content' => $brand,
				),
				array(
					'type'    => 'submessage',
					'style'   => 'info',
					'content' => __( 'Each color below lists where it appears on the live site. Change a color → Save → refresh the front page to see it.', 'syria-oil' ),
				),
				array(
					'id'      => 'color_primary',
					'type'    => 'color',
					'title'   => __( 'Primary — brand green', 'syria-oil' ),
					'subtitle'=> __( 'Main brand color', 'syria-oil' ),
					'desc'    => __( 'Changes: page preloader background · news ticker label · post-card green panels & image overlays · buttons & links · newsletter gradient · news ticker border · spotlight cards · soft green backgrounds · card hover borders. Darker/softer shades are created automatically from this color.', 'syria-oil' ),
					'default' => $defaults['color_primary'],
					'class'   => 'syria-oil-preview-color',
				),
				array(
					'id'      => 'color_secondary',
					'type'    => 'color',
					'title'   => __( 'Secondary — page background', 'syria-oil' ),
					'subtitle'=> __( 'Site canvas', 'syria-oil' ),
					'desc'    => __( 'Changes: overall page background behind sections and cards (the light gray/green canvas of the site).', 'syria-oil' ),
					'default' => $defaults['color_secondary'],
				),
				array(
					'id'      => 'color_accent',
					'type'    => 'color',
					'title'   => __( 'Accent — alert / CTA red', 'syria-oil' ),
					'subtitle'=> __( 'Highlight & urgency', 'syria-oil' ),
					'desc'    => __( 'Changes: “Breaking / urgent” kickers · newsletter subscribe button · flag stripe accent · important call-to-action highlights.', 'syria-oil' ),
					'default' => $defaults['color_accent'],
				),
				array(
					'id'      => 'color_depth',
					'type'    => 'color',
					'title'   => __( 'Depth — burgundy / dark touches', 'syria-oil' ),
					'subtitle'=> __( 'Editorial accent (خمري)', 'syria-oil' ),
					'desc'    => __( 'Changes: news card category tags · featured story accent border · alternating section tint · banner edge when set to Depth. Keeps primary green for buttons and brand; this color adds darker editorial touches.', 'syria-oil' ),
					'default' => $defaults['color_depth'],
					'class'   => 'syria-oil-preview-color',
				),
				array(
					'id'      => 'color_ink',
					'type'    => 'color',
					'title'   => __( 'Ink — body text', 'syria-oil' ),
					'subtitle'=> __( 'Readable text', 'syria-oil' ),
					'desc'    => __( 'Changes: headings and body text on light backgrounds · article titles (non-green panels) · muted labels derived from this color.', 'syria-oil' ),
					'default' => $defaults['color_ink'],
				),
				array(
					'id'      => 'enable_dark_mode',
					'type'    => 'switcher',
					'title'   => __( 'Enable site-wide dark mode', 'syria-oil' ),
					'default' => $defaults['enable_dark_mode'],
				),
				array(
					'type'     => 'callback',
					'function' => 'syria_oil_render_options_preview_pane',
				),
			),
		)
	);

	/* ---- Typography ---- */
	CSF::createSection(
		$prefix,
		array(
			'title'       => __( 'Typography', 'syria-oil' ),
			'icon'        => 'fas fa-font',
			'description' => __( 'Arabic-first fonts, base size, and header navigation text size.', 'syria-oil' ),
			'fields'      => array(
				array(
					'type'     => 'callback',
					'function' => 'syria_oil_render_typography_preview_pane',
				),
				array(
					'type'    => 'submessage',
					'style'   => 'info',
					'content' => __( 'Set fonts here — not in Elementor Site Settings.', 'syria-oil' ),
				),
				array(
					'id'      => 'font_heading',
					'type'    => 'select',
					'title'   => __( 'Heading font', 'syria-oil' ),
					'options' => $fonts,
					'default' => $defaults['font_heading'],
				),
				array(
					'id'      => 'font_body',
					'type'    => 'select',
					'title'   => __( 'Body font', 'syria-oil' ),
					'options' => $fonts,
					'default' => $defaults['font_body'],
				),
				array(
					'id'      => 'font_scale',
					'type'    => 'select',
					'title'   => __( 'Base font size', 'syria-oil' ),
					'options' => array(
						'small'  => __( 'Small', 'syria-oil' ),
						'medium' => __( 'Medium', 'syria-oil' ),
						'large'  => __( 'Large', 'syria-oil' ),
					),
					'default' => $defaults['font_scale'],
				),
				array(
					'id'      => 'header_nav_font_size',
					'type'    => 'number',
					'title'   => __( 'Header menu font size (px)', 'syria-oil' ),
					'subtitle'=> __( 'Main navigation links in the header.', 'syria-oil' ),
					'default' => $defaults['header_nav_font_size'],
					'min'     => 12,
					'max'     => 22,
					'unit'    => 'px',
				),
			),
		)
	);

	/* ---- Layout ---- */
	CSF::createSection(
		$prefix,
		array(
			'title'       => __( 'Layout', 'syria-oil' ),
			'icon'        => 'fas fa-columns',
			'description' => __( 'Container width, card ratios, hero sidebar mode, ads, header & footer grid.', 'syria-oil' ),
			'fields'      => array(
				array(
					'type'     => 'callback',
					'function' => 'syria_oil_render_layout_preview_pane',
				),
				array(
					'id'      => 'container_width',
					'type'    => 'radio',
					'title'   => __( 'Container width', 'syria-oil' ),
					'options' => array(
						'boxed' => __( 'Boxed (1280px)', 'syria-oil' ),
						'full'  => __( 'Full width', 'syria-oil' ),
					),
					'default' => $defaults['container_width'],
				),
				array(
					'id'      => 'card_image_ratio',
					'type'    => 'select',
					'title'   => __( 'Card image ratio', 'syria-oil' ),
					'desc'    => __( 'Forces landscape crop on news cards (CSS object-fit).', 'syria-oil' ),
					'options' => array(
						'2:1'   => '2:1 (' . __( 'shortest', 'syria-oil' ) . ')',
						'16:9'  => '16:9',
						'16:10' => '16:10',
						'3:2'   => '3:2',
						'4:3'   => '4:3 (' . __( 'tallest', 'syria-oil' ) . ')',
					),
					'default' => $defaults['card_image_ratio'],
				),
				array(
					'id'      => 'hero_image_ratio',
					'type'    => 'select',
					'title'   => __( 'Hero / slider image ratio', 'syria-oil' ),
					'options' => array(
						'16:10' => '16:10',
						'16:9'  => '16:9',
						'4:3'   => '4:3',
						'3:2'   => '3:2',
					),
					'default' => $defaults['hero_image_ratio'],
				),
				array(
					'id'      => 'hero_featured_sidebar',
					'type'    => 'select',
					'title'   => __( 'أخبار بارزة بجانب الهيرو', 'syria-oil' ),
					'subtitle'=> __( 'Hero featured sidebar', 'syria-oil' ),
					'desc'    => __( 'شغّل: قائمة بارزة جنب السلايدر. طفي: باب أخبار بارزة تحت الهيرو (الوضع الحالي). بلا: لا تظهر أخبار بارزة كقسم منفصل.', 'syria-oil' ),
					'options' => array(
						'split' => __( 'شغّل - Split بجانب السلايدر', 'syria-oil' ),
						'below' => __( 'طفي - قسم تحت الهيرو', 'syria-oil' ),
						'none'  => __( 'بلا - إخفاء', 'syria-oil' ),
					),
					'default' => $defaults['hero_featured_sidebar'],
				),
				array(
					'id'      => 'media_object_fit',
					'type'    => 'select',
					'title'   => __( 'Image fit', 'syria-oil' ),
					'options' => array(
						'cover'   => __( 'Cover (crop)', 'syria-oil' ),
						'contain' => __( 'Contain (letterbox)', 'syria-oil' ),
					),
					'default' => $defaults['media_object_fit'],
				),
				array(
					'id'      => 'side_ad_max_height',
					'type'    => 'number',
					'title'   => __( 'Side ad max height (px)', 'syria-oil' ),
					'desc'    => __( 'Caps tall portrait banners in the left/right rails.', 'syria-oil' ),
					'default' => $defaults['side_ad_max_height'],
					'min'     => 120,
					'max'     => 900,
					'unit'    => 'px',
				),
				array(
					'id'      => 'show_mobile_ads',
					'type'    => 'switcher',
					'title'   => __( 'Show ads on mobile/tablet', 'syria-oil' ),
					'desc'    => __( 'When side rails are hidden, show matching Side Ads in a strip under the header.', 'syria-oil' ),
					'default' => $defaults['show_mobile_ads'],
				),
				array(
					'id'         => 'mobile_ads_limit',
					'type'       => 'number',
					'title'      => __( 'Mobile ads limit', 'syria-oil' ),
					'desc'       => __( 'How many Side Ads to put in the mobile swipe strip. Use 0 to show all (about two visible; swipe for the rest).', 'syria-oil' ),
					'default'    => $defaults['mobile_ads_limit'],
					'min'        => 0,
					'max'        => 24,
					'dependency' => array( 'show_mobile_ads', '==', '1' ),
				),
				array(
					'id'         => 'mobile_ad_max_height',
					'type'       => 'number',
					'title'      => __( 'Mobile ad max height (px)', 'syria-oil' ),
					'desc'       => __( 'On mobile: Premium under the header, Normal after the hero, Basic near the end of the page. Same Side Ads inventory as the left/right rails.', 'syria-oil' ),
					'default'    => $defaults['mobile_ad_max_height'],
					'min'        => 80,
					'max'        => 280,
					'unit'       => 'px',
					'dependency' => array( 'show_mobile_ads', '==', '1' ),
				),
				array(
					'id'      => 'header_style',
					'type'    => 'select',
					'title'   => __( 'Header style', 'syria-oil' ),
					'options' => array(
						'standard'              => __( 'Standard', 'syria-oil' ),
						'sticky'                => __( 'Sticky', 'syria-oil' ),
						'transparent_over_hero' => __( 'Transparent over hero', 'syria-oil' ),
					),
					'default' => $defaults['header_style'],
				),
				array(
					'type'    => 'content',
					'content' => '<p class="description">' . esc_html__( 'Language toggle (AR/EN) appears in the classic header. If Elementor Theme Builder owns the header, use the [syria_oil_lang_toggle] shortcode.', 'syria-oil' ) . '</p>',
				),
				array(
					'id'          => 'footer_columns',
					'type'        => 'radio',
					'title'       => __( 'Footer columns', 'syria-oil' ),
					'subtitle'    => __( 'Front-end grid only. All four footer widget areas stay available in Appearance → Widgets.', 'syria-oil' ),
					'options'     => array(
						'3' => __( '3 columns', 'syria-oil' ),
						'4' => __( '4 columns', 'syria-oil' ),
					),
					'default'     => $defaults['footer_columns'],
				),
			),
		)
	);

	/* ---- Banner decoration ---- */
	CSF::createSection(
		$prefix,
		array(
			'id'          => 'banner',
			'title'       => __( 'Banner', 'syria-oil' ),
			'icon'        => 'fas fa-flag',
			'description' => __( 'Optional flag stripe, color edge, and emblem watermark on heroes and article headers.', 'syria-oil' ),
			'fields'      => array(
				array(
					'type'     => 'callback',
					'function' => 'syria_oil_render_banner_preview_pane',
				),
				array(
					'type'    => 'submessage',
					'style'   => 'info',
					'content' => __( 'Decorative edge on page heroes, article tops, and Elementor banners. Leave on None for an independent media look.', 'syria-oil' ),
				),
				array(
					'id'      => 'banner_edge_mode',
					'type'    => 'select',
					'title'   => __( 'Banner edge style', 'syria-oil' ),
					'options' => array(
						'none'  => __( 'None', 'syria-oil' ),
						'color' => __( 'Color edge only', 'syria-oil' ),
						'image' => __( 'Flag / image edge only', 'syria-oil' ),
						'both'  => __( 'Color + image', 'syria-oil' ),
					),
					'default' => $defaults['banner_edge_mode'],
				),
				array(
					'id'      => 'banner_edge_color_preset',
					'type'    => 'select',
					'title'   => __( 'Banner edge color', 'syria-oil' ),
					'desc'    => __( 'Used when style is Color or Both.', 'syria-oil' ),
					'options' => array(
						'primary'   => __( 'Primary (brand)', 'syria-oil' ),
						'depth'     => __( 'Depth (burgundy)', 'syria-oil' ),
						'secondary' => __( 'Secondary (background)', 'syria-oil' ),
						'accent'    => __( 'Accent', 'syria-oil' ),
						'ink'       => __( 'Ink (text)', 'syria-oil' ),
						'custom'    => __( 'Custom', 'syria-oil' ),
					),
					'default' => $defaults['banner_edge_color_preset'],
				),
				array(
					'id'      => 'banner_edge_color_custom',
					'type'    => 'color',
					'title'   => __( 'Custom banner edge color', 'syria-oil' ),
					'desc'    => __( 'Applies when edge color is set to Custom.', 'syria-oil' ),
					'default' => $defaults['banner_edge_color_custom'],
				),
				array(
					'id'       => 'banner_flag_image',
					'type'     => 'media',
					'title'    => __( 'Banner flag / stripe image', 'syria-oil' ),
					'subtitle' => __( 'Used when style is Image or Both.', 'syria-oil' ),
					'library'  => 'image',
					'url'      => false,
				),
				array(
					'id'       => 'banner_emblem_image',
					'type'     => 'media',
					'title'    => __( 'Banner emblem image (eagle)', 'syria-oil' ),
					'subtitle' => __( 'Used when Show emblem watermark is on (General tab).', 'syria-oil' ),
					'library'  => 'image',
					'url'      => false,
				),
			),
		)
	);

	/* ---- Top Bar ---- */
	CSF::createSection(
		$prefix,
		array(
			'title'       => __( 'Top Bar', 'syria-oil' ),
			'icon'        => 'fas fa-window-maximize',
			'description' => __( 'Thin announcement strip above the header with optional date and link.', 'syria-oil' ),
			'fields'      => array(
				array(
					'type'     => 'callback',
					'function' => 'syria_oil_render_topbar_preview_pane',
				),
				array(
					'id'      => 'show_top_bar',
					'type'    => 'switcher',
					'title'   => __( 'Show top bar', 'syria-oil' ),
					'default' => $defaults['show_top_bar'],
				),
				array(
					'id'      => 'top_bar_text',
					'type'    => 'text',
					'title'   => __( 'Top bar message', 'syria-oil' ),
					'default' => $defaults['top_bar_text'],
				),
				array(
					'id'      => 'show_top_bar_date',
					'type'    => 'switcher',
					'title'   => __( 'Show today\'s date', 'syria-oil' ),
					'default' => $defaults['show_top_bar_date'],
				),
				array(
					'id'      => 'top_bar_link_label',
					'type'    => 'text',
					'title'   => __( 'Top bar link label', 'syria-oil' ),
					'default' => $defaults['top_bar_link_label'],
				),
				array(
					'id'      => 'top_bar_link_url',
					'type'    => 'text',
					'title'   => __( 'Top bar link URL', 'syria-oil' ),
					'default' => $defaults['top_bar_link_url'],
					'sanitize'=> 'esc_url_raw',
				),
			),
		)
	);

	/* ---- Header ---- */
	CSF::createSection(
		$prefix,
		array(
			'title'  => __( 'Header & Search', 'syria-oil' ),
			'icon'   => 'fas fa-heading',
			'fields' => array(
				array(
					'id'       => 'search_placeholder',
					'type'     => 'text',
					'title'    => __( 'Search placeholder', 'syria-oil' ),
					'subtitle' => __( 'Leave empty to use the default translated string.', 'syria-oil' ),
					'default'  => $defaults['search_placeholder'],
				),
			),
		)
	);

	/* ---- Footer ---- */
	CSF::createSection(
		$prefix,
		array(
			'title'       => __( 'Footer', 'syria-oil' ),
			'icon'        => 'fas fa-shoe-prints',
			'description' => __( 'Tagline, column titles, sponsors row, and copyright note. Column count is set under Layout.', 'syria-oil' ),
			'fields'      => array(
				array(
					'type'     => 'callback',
					'function' => 'syria_oil_render_footer_preview_pane',
				),
				array(
					'id'      => 'footer_tagline',
					'type'    => 'text',
					'title'   => __( 'Footer tagline', 'syria-oil' ),
					'default' => $defaults['footer_tagline'],
				),
				array(
					'id'      => 'footer_explore_title',
					'type'    => 'text',
					'title'   => __( 'Explore column title', 'syria-oil' ),
					'default' => $defaults['footer_explore_title'],
				),
				array(
					'id'      => 'footer_cats_title',
					'type'    => 'text',
					'title'   => __( 'Categories column title', 'syria-oil' ),
					'default' => $defaults['footer_cats_title'],
				),
				array(
					'id'      => 'footer_contact_title',
					'type'    => 'text',
					'title'   => __( 'Contact column title', 'syria-oil' ),
					'default' => $defaults['footer_contact_title'],
				),
				array(
					'id'      => 'footer_bottom_text',
					'type'    => 'text',
					'title'   => __( 'Footer bottom note', 'syria-oil' ),
					'default' => $defaults['footer_bottom_text'],
				),
				array(
					'id'      => 'footer_sponsors_title',
					'type'    => 'text',
					'title'   => __( 'Sponsors row title', 'syria-oil' ),
					'default' => $defaults['footer_sponsors_title'],
				),
				array(
					'id'          => 'footer_sponsors',
					'type'        => 'textarea',
					'title'       => __( 'Sponsors / partners', 'syria-oil' ),
					'desc'        => __( 'One per line: Name or Name|https://example.com', 'syria-oil' ),
					'default'     => $defaults['footer_sponsors'],
					'attributes'  => array( 'rows' => 5 ),
				),
			),
		)
	);

	/* ---- Careers ---- */
	CSF::createSection(
		$prefix,
		array(
			'id'     => 'careers',
			'title'  => __( 'Careers', 'syria-oil' ),
			'icon'   => 'fas fa-briefcase',
			'fields' => array(
				array(
					'type'    => 'heading',
					'content' => __( 'فرص العمل — الإعدادات', 'syria-oil' ),
				),
				array(
					'type'    => 'content',
					'content' => '<p>' . esc_html__( 'إدارة الوظائف من قائمة «فرص العمل» في لوحة التحكم. الطلبات تظهر تحت «طلبات التقديم».', 'syria-oil' ) . '</p>'
						. '<p><a class="button button-primary" href="' . esc_url( admin_url( 'post-new.php?post_type=syria_oil_job' ) ) . '">' . esc_html__( 'إضافة فرصة عمل', 'syria-oil' ) . '</a> '
						. '<a class="button" href="' . esc_url( admin_url( 'edit.php?post_type=syria_oil_job' ) ) . '">' . esc_html__( 'كل الفرص', 'syria-oil' ) . '</a> '
						. '<a class="button" href="' . esc_url( admin_url( 'edit.php?post_type=syria_oil_job_application' ) ) . '">' . esc_html__( 'طلبات التقديم', 'syria-oil' ) . '</a></p>',
				),
				array(
					'id'      => 'careers_enabled',
					'type'    => 'switcher',
					'title'   => __( 'تفعيل وحدة التوظيف', 'syria-oil' ),
					'subtitle'=> __( 'Enable careers module', 'syria-oil' ),
					'default' => $defaults['careers_enabled'],
				),
				array(
					'id'      => 'careers_show_home',
					'type'    => 'switcher',
					'title'   => __( 'إظهار الوظائف في الصفحة الرئيسية', 'syria-oil' ),
					'subtitle'=> __( 'Show jobs on homepage', 'syria-oil' ),
					'default' => $defaults['careers_show_home'],
				),
				array(
					'id'      => 'careers_show_footer',
					'type'    => 'switcher',
					'title'   => __( 'إظهار رابط فرص العمل في الفوتر', 'syria-oil' ),
					'subtitle'=> __( 'Show careers link in footer', 'syria-oil' ),
					'default' => $defaults['careers_show_footer'],
				),
				array(
					'id'      => 'careers_show_closed',
					'type'    => 'switcher',
					'title'   => __( 'إظهار الوظائف المغلقة / المنتهية', 'syria-oil' ),
					'subtitle'=> __( 'Show closed / expired jobs', 'syria-oil' ),
					'default' => $defaults['careers_show_closed'],
				),
				array(
					'id'      => 'careers_apply_email',
					'type'    => 'text',
					'title'   => __( 'بريد استقبال طلبات التقديم', 'syria-oil' ),
					'desc'    => __( 'Receives apply-form notifications. Falls back to Contact email / admin email.', 'syria-oil' ),
					'default' => $defaults['careers_apply_email'],
				),
				array(
					'id'      => 'careers_home_limit',
					'type'    => 'number',
					'title'   => __( 'عدد الوظائف في الرئيسية', 'syria-oil' ),
					'default' => $defaults['careers_home_limit'],
					'min'     => 1,
					'max'     => 12,
					'unit'    => 'jobs',
				),
				array(
					'id'      => 'careers_intro_title',
					'type'    => 'text',
					'title'   => __( 'عنوان صفحة الوظائف', 'syria-oil' ),
					'default' => $defaults['careers_intro_title'],
				),
				array(
					'id'      => 'careers_intro_text',
					'type'    => 'textarea',
					'title'   => __( 'مقدمة صفحة الوظائف', 'syria-oil' ),
					'default' => $defaults['careers_intro_text'],
				),
				array(
					'id'         => 'careers_benefits',
					'type'       => 'textarea',
					'title'      => __( 'نص المزايا', 'syria-oil' ),
					'desc'       => __( 'سطر لكل فكرة. يظهر فوق قائمة الوظائف عند تفعيل المزايا في الوِدجت.', 'syria-oil' ),
					'default'    => $defaults['careers_benefits'],
					'attributes' => array( 'rows' => 4 ),
				),
			),
		)
	);

	/* ---- Contact ---- */
	CSF::createSection(
		$prefix,
		array(
			'title'  => __( 'Contact', 'syria-oil' ),
			'icon'   => 'fas fa-envelope',
			'fields' => array(
				array(
					'id'      => 'contact_email',
					'type'    => 'text',
					'title'   => __( 'Email', 'syria-oil' ),
					'default' => $defaults['contact_email'],
				),
				array(
					'id'      => 'contact_phone',
					'type'    => 'text',
					'title'   => __( 'Phone', 'syria-oil' ),
					'default' => $defaults['contact_phone'],
				),
				array(
					'id'      => 'contact_page_url',
					'type'    => 'text',
					'title'   => __( 'Contact page URL', 'syria-oil' ),
					'default' => $defaults['contact_page_url'],
				),
				array(
					'id'      => 'about_page_url',
					'type'    => 'text',
					'title'   => __( 'About page URL', 'syria-oil' ),
					'default' => $defaults['about_page_url'],
				),
			),
		)
	);

	/* ---- Social ---- */
	CSF::createSection(
		$prefix,
		array(
			'title'  => __( 'Social Links', 'syria-oil' ),
			'icon'   => 'fas fa-share-alt',
			'fields' => array(
				array(
					'id'      => 'social_facebook',
					'type'    => 'text',
					'title'   => __( 'Facebook URL', 'syria-oil' ),
					'default' => $defaults['social_facebook'],
				),
				array(
					'id'      => 'social_x',
					'type'    => 'text',
					'title'   => __( 'X (Twitter) URL', 'syria-oil' ),
					'default' => $defaults['social_x'],
				),
				array(
					'id'      => 'social_youtube',
					'type'    => 'text',
					'title'   => __( 'YouTube URL', 'syria-oil' ),
					'default' => $defaults['social_youtube'],
				),
				array(
					'id'      => 'social_linkedin',
					'type'    => 'text',
					'title'   => __( 'LinkedIn URL', 'syria-oil' ),
					'default' => $defaults['social_linkedin'],
				),
				array(
					'id'      => 'social_instagram',
					'type'    => 'text',
					'title'   => __( 'Instagram URL', 'syria-oil' ),
					'default' => $defaults['social_instagram'],
				),
				array(
					'id'      => 'social_telegram',
					'type'    => 'text',
					'title'   => __( 'Telegram URL', 'syria-oil' ),
					'default' => $defaults['social_telegram'],
				),
				array(
					'id'      => 'show_header_social',
					'type'    => 'switcher',
					'title'   => __( 'Show social icons in header', 'syria-oil' ),
					'default' => $defaults['show_header_social'],
				),
			),
		)
	);

	/* ---- Reading experience ---- */
	CSF::createSection(
		$prefix,
		array(
			'title'  => __( 'Reading', 'syria-oil' ),
			'icon'   => 'fas fa-book-reader',
			'fields' => array(
				array(
					'id'      => 'article_font_scale',
					'type'    => 'switcher',
					'title'   => __( 'Article font size controls', 'syria-oil' ),
					'default' => $defaults['article_font_scale'],
				),
				array(
					'id'      => 'continuous_reading',
					'type'    => 'switcher',
					'title'   => __( 'Continuous reading (load next article)', 'syria-oil' ),
					'default' => $defaults['continuous_reading'],
				),
			),
		)
	);

	/* ---- Oil & Gas Prices ---- */
	$eia_key = (string) get_option( 'syria_oil_eia_api_key', '' );
	CSF::createSection(
		$prefix,
		array(
			'title'  => __( 'Oil & Gas Prices', 'syria-oil' ),
			'icon'   => 'fas fa-chart-line',
			'fields' => array(
				array(
					'type'    => 'submessage',
					'style'   => 'info',
					'content' => __( 'Prices are fetched server-side on a schedule (every 30 minutes) and cached. The API key never reaches the browser.', 'syria-oil' )
						. ' <a href="https://www.eia.gov/opendata/register.php" target="_blank" rel="noopener noreferrer">'
						. esc_html__( 'Register for a free EIA API key', 'syria-oil' )
						. '</a>',
				),
				array(
					'id'         => 'eia_api_key',
					'type'       => 'text',
					'title'      => __( 'EIA API key', 'syria-oil' ),
					'attributes' => array(
						'type'         => 'password',
						'autocomplete' => 'off',
					),
					'default'    => $eia_key,
				),
				array(
					'type'     => 'callback',
					'function' => 'syria_oil_render_prices_refresh_callback',
				),
			),
		)
	);

	/* ---- Widget Defaults ---- */
	$wd = isset( $defaults['widget_defaults'] ) ? $defaults['widget_defaults'] : array();
	CSF::createSection(
		$prefix,
		array(
			'title'  => __( 'Widget Defaults', 'syria-oil' ),
			'icon'   => 'fas fa-puzzle-piece',
			'fields' => array(
				array(
					'type'    => 'submessage',
					'style'   => 'info',
					'content' => __( 'Site-wide defaults for Syria Oil Elementor widgets. Per-widget Elementor settings always win when set.', 'syria-oil' ),
				),
				array(
					'id'      => 'widget_defaults',
					'type'    => 'fieldset',
					'title'   => __( 'Defaults', 'syria-oil' ),
					'fields'  => array(
						array(
							'id'      => 'query_posts_per_page',
							'type'    => 'number',
							'title'   => __( 'Posts per widget (grids / sliders)', 'syria-oil' ),
							'default' => isset( $wd['query_posts_per_page'] ) ? $wd['query_posts_per_page'] : 6,
							'min'     => 1,
							'max'     => 24,
						),
						array(
							'id'      => 'ticker_label',
							'type'    => 'text',
							'title'   => __( 'News ticker label', 'syria-oil' ),
							'desc'    => __( 'Default for new News Ticker widgets (آخر الأخبار). Existing Elementor widgets keep their saved label until re-saved or re-seeded.', 'syria-oil' ),
							'default' => isset( $wd['ticker_label'] ) ? $wd['ticker_label'] : 'آخر الأخبار',
						),
						array(
							'id'      => 'newsletter_title',
							'type'    => 'text',
							'title'   => __( 'Newsletter title default', 'syria-oil' ),
							'default' => '',
						),
						array(
							'id'      => 'newsletter_text',
							'type'    => 'textarea',
							'title'   => __( 'Newsletter text default', 'syria-oil' ),
							'default' => '',
						),
						array(
							'id'      => 'faq_empty',
							'type'    => 'text',
							'title'   => __( 'FAQ empty-state copy', 'syria-oil' ),
							'default' => '',
						),
					),
				),
			),
		)
	);

	/* ---- Cache & Performance ---- */
	$cf_token = (string) get_option( 'syria_oil_cloudflare_api_token', '' );
	$cf_zone  = (string) get_option( 'syria_oil_cloudflare_zone_id', '' );
	CSF::createSection(
		$prefix,
		array(
			'title'  => __( 'Cache & Performance', 'syria-oil' ),
			'icon'   => 'fas fa-bolt',
			'fields' => array(
				array(
					'type'    => 'submessage',
					'style'   => 'info',
					'content' => __( 'This theme does not run a full-page HTML cache. Use Clear Theme Cache for theme/plugin data, and Notify cache plugin to purge to signal WP Rocket, LiteSpeed, Cloudflare, etc.', 'syria-oil' ),
				),
				array(
					'type'     => 'callback',
					'function' => 'syria_oil_render_cache_actions_callback',
				),
				array(
					'type'    => 'subheading',
					'content' => __( 'Front-end optimization', 'syria-oil' ),
				),
				array(
					'id'       => 'perf_lazy_images',
					'type'     => 'switcher',
					'title'    => __( 'Lazy-load images', 'syria-oil' ),
					'subtitle' => __( 'Native loading="lazy" on cards and post thumbnails. Turn off if your CDN already injects lazy-loading.', 'syria-oil' ),
					'default'  => $defaults['perf_lazy_images'],
				),
				array(
					'id'       => 'perf_defer_js',
					'type'     => 'switcher',
					'title'    => __( 'Defer non-critical JS', 'syria-oil' ),
					'subtitle' => __( 'Navigation, ticker, back-to-top, and preloader scripts use defer so they do not block first paint.', 'syria-oil' ),
					'default'  => $defaults['perf_defer_js'],
				),
				array(
					'id'       => 'perf_combine_css',
					'type'     => 'switcher',
					'title'    => __( 'Combine & minify theme CSS', 'syria-oil' ),
					'subtitle' => __( 'Optional. Concatenates theme-owned CSS only (not Elementor). Off by default.', 'syria-oil' ),
					'default'  => $defaults['perf_combine_css'],
				),
				array(
					'id'       => 'perf_disable_emojis',
					'type'     => 'switcher',
					'title'    => __( 'Disable WordPress emoji scripts', 'syria-oil' ),
					'subtitle' => __( 'Removes emoji detection JS/CSS from the front end (low risk).', 'syria-oil' ),
					'default'  => $defaults['perf_disable_emojis'],
				),
				array(
					'id'       => 'perf_preconnect_eia',
					'type'     => 'switcher',
					'title'    => __( 'Preconnect commodity API host', 'syria-oil' ),
					'subtitle' => __( 'dns-prefetch / preconnect for api.eia.gov (server-side feed; small win for admin refresh paths).', 'syria-oil' ),
					'default'  => $defaults['perf_preconnect_eia'],
				),
				array(
					'type'    => 'subheading',
					'content' => __( 'Cloudflare (optional)', 'syria-oil' ),
				),
				array(
					'type'    => 'submessage',
					'style'   => 'normal',
					'content' => __( 'If you use Cloudflare caching, store a token with Zone.Cache Purge permission. Credentials are saved outside the main options array (same pattern as the EIA key) and never printed on the front end.', 'syria-oil' ),
				),
				array(
					'id'         => 'cloudflare_api_token',
					'type'       => 'text',
					'title'      => __( 'Cloudflare API token', 'syria-oil' ),
					'attributes' => array(
						'type'         => 'password',
						'autocomplete' => 'off',
					),
					'default'    => $cf_token,
				),
				array(
					'id'      => 'cloudflare_zone_id',
					'type'    => 'text',
					'title'   => __( 'Cloudflare Zone ID', 'syria-oil' ),
					'default' => $cf_zone,
				),
			),
		)
	);

	/* ---- Maintenance Mode ---- */
	CSF::createSection(
		$prefix,
		array(
			'id'     => 'maintenance',
			'title'  => __( 'Maintenance Mode', 'syria-oil' ),
			'icon'   => 'fas fa-tools',
			'fields' => array(
				array(
					'type'    => 'submessage',
					'style'   => 'warning',
					'content' => __( '⚠ When maintenance mode is enabled, visitors see a maintenance page. Only logged-in administrators can access the site.', 'syria-oil' ),
				),
				array(
					'id'       => 'maintenance_mode_enabled',
					'type'     => 'switcher',
					'title'    => __( 'Enable Maintenance Mode', 'syria-oil' ),
					'subtitle' => __( 'Show maintenance page to visitors. Admins can still access the site.', 'syria-oil' ),
					'text_on'  => __( 'Active', 'syria-oil' ),
					'text_off' => __( 'Disabled', 'syria-oil' ),
					'default'  => false,
				),
				array(
					'type'    => 'subheading',
					'content' => __( 'Maintenance Page Content', 'syria-oil' ),
				),
				array(
					'id'      => 'maintenance_mode_title',
					'type'    => 'text',
					'title'   => __( 'Page title', 'syria-oil' ),
					'default' => __( 'Site Under Maintenance', 'syria-oil' ),
				),
				array(
					'id'      => 'maintenance_mode_message',
					'type'    => 'textarea',
					'title'   => __( 'Message', 'syria-oil' ),
					'subtitle' => __( 'HTML allowed. This message appears below the title.', 'syria-oil' ),
					'default' => __( 'We are currently performing scheduled maintenance. We will be back shortly. Thank you for your patience.', 'syria-oil' ),
				),
				array(
					'type'    => 'subheading',
					'content' => __( 'Countdown Timer (optional)', 'syria-oil' ),
				),
				array(
					'id'       => 'maintenance_mode_show_countdown',
					'type'     => 'switcher',
					'title'    => __( 'Show countdown timer', 'syria-oil' ),
					'subtitle' => __( 'Display live countdown to maintenance end time.', 'syria-oil' ),
					'default'  => false,
				),
				array(
					'id'         => 'maintenance_mode_end_time',
					'type'       => 'datetime',
					'title'      => __( 'Maintenance end time', 'syria-oil' ),
					'subtitle'   => __( 'When maintenance is scheduled to complete. Used for countdown timer.', 'syria-oil' ),
					'dependency' => array( 'maintenance_mode_show_countdown', '==', 'true' ),
					'settings'   => array(
						'dateFormat' => 'Y-m-d',
						'timeFormat' => 'H:i',
					),
				),
				array(
					'type'    => 'subheading',
					'content' => __( 'Styling & Design', 'syria-oil' ),
				),
				array(
					'type'    => 'submessage',
					'style'   => 'info',
					'content' => __( 'The maintenance page automatically uses colors, fonts, and logo from your Brand Settings. No additional styling needed.', 'syria-oil' ),
				),
				array(
					'id'       => 'maintenance_mode_custom_code',
					'type'     => 'code_editor',
					'title'    => __( 'Custom HTML/CSS (optional)', 'syria-oil' ),
					'subtitle' => __( 'Add custom content or styling. Insert HTML that appears below the countdown timer.', 'syria-oil' ),
					'settings' => array(
						'theme' => 'monokai',
						'mode'  => 'htmlmixed',
					),
				),
				array(
					'type'    => 'subheading',
					'content' => __( 'Preview', 'syria-oil' ),
				),
				array(
					'type'    => 'content',
					'content' => '<p>' . esc_html__( 'Preview how the maintenance page looks to visitors:', 'syria-oil' )
						. '</p><p><a class="button button-primary" href="' . esc_url( add_query_arg( 'maintenance_preview', '1', home_url( '/' ) ) ) . '" target="_blank">'
						. esc_html__( 'Preview Maintenance Page', 'syria-oil' ) . '</a></p>',
				),
			),
		)
	);

	/* ---- Import / Export ---- */
	CSF::createSection(
		$prefix,
		array(
			'title'  => __( 'Import / Export', 'syria-oil' ),
			'icon'   => 'fas fa-shield-alt',
			'fields' => array(
				array(
					'type'    => 'subheading',
					'content' => __( 'Demo side ads', 'syria-oil' ),
				),
				array(
					'type'    => 'content',
					'content' => '<p>' . esc_html__( 'Import the bundled left/right banner creatives as Side Ad posts (homepage targeting). You can also do this from Side Ads → Seed demo side ads, or Syria Oil → Setup.', 'syria-oil' )
						. '</p><p><a class="button button-primary" href="' . esc_url( function_exists( 'syria_oil_seed_demo_ads_url' ) ? syria_oil_seed_demo_ads_url( 'options' ) : '#' ) . '">'
						. esc_html__( 'Seed demo side ads', 'syria-oil' ) . '</a> '
						. '<a class="button button-secondary" href="' . esc_url( admin_url( 'edit.php?post_type=syria_oil_ad' ) ) . '">'
						. esc_html__( 'Open Side Ads', 'syria-oil' ) . '</a></p>',
				),
				array(
					'type'    => 'subheading',
					'content' => __( 'Backup', 'syria-oil' ),
				),
				array(
					'type' => 'backup',
				),
			),
		)
	);
}

/**
 * Prices refresh form + cache status (callback field).
 */
function syria_oil_render_prices_refresh_callback() {
	$cached = class_exists( '\Syria_Oil_Widgets\Price_Feed' )
		? \Syria_Oil_Widgets\Price_Feed::get_prices()
		: null;

	echo '<div class="syria-oil-prices-status">';
	echo '<h4>' . esc_html__( 'Cache status', 'syria-oil' ) . '</h4>';
	if ( $cached ) {
		echo '<p>' . esc_html(
			sprintf(
				/* translators: %s: datetime */
				__( 'Last updated: %s', 'syria-oil' ),
				wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $cached['last_updated'] )
			)
		) . '</p>';
	} else {
		echo '<p>' . esc_html__( 'No cached prices yet. Save an API key and click Refresh now.', 'syria-oil' ) . '</p>';
	}

	if ( class_exists( '\Syria_Oil_Widgets\Price_Feed' ) ) {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:8px">';
		echo '<input type="hidden" name="action" value="syria_oil_refresh_prices" />';
		wp_nonce_field( 'syria_oil_refresh_prices' );
		echo '<button type="submit" class="button button-secondary">' . esc_html__( 'Refresh now', 'syria-oil' ) . '</button>';
		echo '</form>';
	} else {
		echo '<p class="description">' . esc_html__( 'Activate Syria Oil Widgets to enable price refresh.', 'syria-oil' ) . '</p>';
	}
	echo '</div>';
}

/**
 * After CSF save: logo sync, EIA key bridge, kit sync, identity hook.
 *
 * @param array $data Saved options.
 */
function syria_oil_options_save_after_identity( $data ) {
	if ( ! is_array( $data ) ) {
		return;
	}

	$bridged = false;

	if ( array_key_exists( 'eia_api_key', $data ) ) {
		update_option( 'syria_oil_eia_api_key', sanitize_text_field( (string) $data['eia_api_key'] ), false );
		unset( $data['eia_api_key'] );
		$bridged = true;
	}

	if ( array_key_exists( 'cloudflare_api_token', $data ) ) {
		update_option( 'syria_oil_cloudflare_api_token', sanitize_text_field( (string) $data['cloudflare_api_token'] ), false );
		unset( $data['cloudflare_api_token'] );
		$bridged = true;
	}

	if ( array_key_exists( 'cloudflare_zone_id', $data ) ) {
		update_option( 'syria_oil_cloudflare_zone_id', sanitize_text_field( (string) $data['cloudflare_zone_id'] ), false );
		unset( $data['cloudflare_zone_id'] );
		$bridged = true;
	}

	if ( $bridged ) {
		update_option( SYRIA_OIL_OPTIONS, $data, false );
	}

	if ( ! empty( $data['logo_primary'] ) || array_key_exists( 'logo_primary', $data ) ) {
		syria_oil_sync_logo_to_wp( isset( $data['logo_primary'] ) ? $data['logo_primary'] : 0 );
	}
	if ( ! empty( $data['favicon'] ) || array_key_exists( 'favicon', $data ) ) {
		syria_oil_sync_favicon_to_wp( isset( $data['favicon'] ) ? $data['favicon'] : 0 );
	}

	if ( function_exists( 'syria_oil_sync_kit_from_options' ) ) {
		syria_oil_sync_kit_from_options();
	}

	$changed = array_keys( $data );
	/**
	 * Fires after identity options are persisted.
	 *
	 * @param array $changed Option keys in the saved payload.
	 */
	do_action( 'syria_oil_identity_updated', $changed );
}
add_action( 'csf_' . SYRIA_OIL_OPTIONS . '_save_after', 'syria_oil_options_save_after_identity' );

/**
 * Bridge widget defaults into Elementor widgets via filter.
 *
 * @param mixed  $value  Default value.
 * @param string $widget Widget get_name().
 * @param string $key    Control key.
 * @return mixed
 */
function syria_oil_filter_widget_default( $value, $widget = '', $key = '' ) {
	$opts = syria_oil_mod( 'widget_defaults', array() );
	if ( ! is_array( $opts ) ) {
		return $value;
	}

	$map = array(
		'query_posts_per_page' => 'query_posts_per_page',
		'label'                => 'ticker_label',
		'title'                => ( false !== strpos( (string) $widget, 'newsletter' ) ) ? 'newsletter_title' : '',
		'text'                 => ( false !== strpos( (string) $widget, 'newsletter' ) ) ? 'newsletter_text' : '',
		'empty_text'           => 'faq_empty',
	);

	if ( isset( $map[ $key ] ) && $map[ $key ] && isset( $opts[ $map[ $key ] ] ) && '' !== $opts[ $map[ $key ] ] ) {
		return $opts[ $map[ $key ] ];
	}

	if ( 'query_posts_per_page' === $key && isset( $opts['query_posts_per_page'] ) ) {
		return absint( $opts['query_posts_per_page'] );
	}

	return $value;
}
add_filter( 'syria_oil_widget_default', 'syria_oil_filter_widget_default', 10, 3 );

/**
 * Enqueue admin live-preview assets on the options page.
 *
 * @param string $hook Hook suffix.
 */
function syria_oil_theme_options_admin_assets( $hook ) {
	if ( false === strpos( $hook, 'syria-oil-options' ) && false === strpos( $hook, 'syria-oil-setup' ) ) {
		return;
	}

	if ( function_exists( 'syria_oil_fonts_url' ) ) {
		wp_enqueue_style( 'syria-oil-options-fonts', syria_oil_fonts_url(), array(), null );
	}

	$admin_css   = 'assets/css/theme-options-admin.css';
	$preview_css = 'assets/css/theme-options-preview.css';
	$setup_css   = 'assets/css/setup-page.css';
	$js          = 'assets/js/theme-options-preview.js';

	if ( false !== strpos( $hook, 'syria-oil-setup' ) ) {
		wp_enqueue_style(
			'syria-oil-setup-page',
			SYRIA_OIL_URI . '/' . $setup_css,
			array(),
			syria_oil_asset_version( $setup_css )
		);
	}

	wp_enqueue_style(
		'syria-oil-options-admin',
		SYRIA_OIL_URI . '/' . $admin_css,
		array(),
		syria_oil_asset_version( $admin_css )
	);
	wp_enqueue_style(
		'syria-oil-options-preview',
		SYRIA_OIL_URI . '/' . $preview_css,
		array( 'syria-oil-options-admin' ),
		syria_oil_asset_version( $preview_css )
	);
	wp_enqueue_script(
		'syria-oil-options-preview',
		SYRIA_OIL_URI . '/' . $js,
		array( 'jquery' ),
		syria_oil_asset_version( $js ),
		true
	);

	if ( function_exists( 'syria_oil_theme_options_preview_config' ) ) {
		wp_localize_script( 'syria-oil-options-preview', 'syriaOilOptionsPreview', syria_oil_theme_options_preview_config() );
	}
}
add_action( 'admin_enqueue_scripts', 'syria_oil_theme_options_admin_assets' );

/**
 * Hide duplicate Site Identity logo/icon once CSF owns branding.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function syria_oil_customize_hide_core_branding( $wp_customize ) {
	$wp_customize->remove_control( 'custom_logo' );
	$wp_customize->remove_control( 'site_icon' );
}
add_action( 'customize_register', 'syria_oil_customize_hide_core_branding', 20 );

/**
 * Native metabox for per-page/post identity overrides (CSF metabox is premium-only).
 */
function syria_oil_register_page_overrides_metabox() {
	foreach ( array( 'page', 'post' ) as $type ) {
		add_meta_box(
			'syria_oil_page_overrides',
			__( 'Syria Oil — Page overrides', 'syria-oil' ),
			'syria_oil_render_page_overrides_metabox',
			$type,
			'side',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'syria_oil_register_page_overrides_metabox' );

/**
 * @param WP_Post $post Post.
 */
function syria_oil_render_page_overrides_metabox( $post ) {
	$meta = get_post_meta( $post->ID, SYRIA_OIL_PAGE_META, true );
	if ( ! is_array( $meta ) ) {
		$meta = array();
	}
	wp_nonce_field( 'syria_oil_page_overrides', 'syria_oil_page_overrides_nonce' );

	$color = isset( $meta['color_primary'] ) ? $meta['color_primary'] : '';
	$header = isset( $meta['header_style'] ) ? $meta['header_style'] : '';
	$container = isset( $meta['container_width'] ) ? $meta['container_width'] : '';
	$crumbs = isset( $meta['show_breadcrumbs'] ) ? $meta['show_breadcrumbs'] : '';

	echo '<p><label>' . esc_html__( 'Primary color (empty = global)', 'syria-oil' ) . '<br />';
	echo '<input type="text" class="widefat" name="syria_oil_page_color_primary" value="' . esc_attr( $color ) . '" placeholder="#007a3d" /></label></p>';

	echo '<p><label>' . esc_html__( 'Header style', 'syria-oil' ) . '<br />';
	echo '<select name="syria_oil_page_header_style" class="widefat">';
	$headers = array(
		''                      => __( '— Use global —', 'syria-oil' ),
		'standard'              => __( 'Standard', 'syria-oil' ),
		'sticky'                => __( 'Sticky', 'syria-oil' ),
		'transparent_over_hero' => __( 'Transparent over hero', 'syria-oil' ),
	);
	foreach ( $headers as $val => $label ) {
		printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $val ), selected( $header, $val, false ), esc_html( $label ) );
	}
	echo '</select></label></p>';

	echo '<p><label>' . esc_html__( 'Container', 'syria-oil' ) . '<br />';
	echo '<select name="syria_oil_page_container_width" class="widefat">';
	$containers = array(
		''      => __( '— Use global —', 'syria-oil' ),
		'boxed' => __( 'Boxed', 'syria-oil' ),
		'full'  => __( 'Full width', 'syria-oil' ),
	);
	foreach ( $containers as $val => $label ) {
		printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $val ), selected( $container, $val, false ), esc_html( $label ) );
	}
	echo '</select></label></p>';

	echo '<p><label>' . esc_html__( 'Breadcrumbs', 'syria-oil' ) . '<br />';
	echo '<select name="syria_oil_page_show_breadcrumbs" class="widefat">';
	$crumb_opts = array(
		''  => __( '— Use global —', 'syria-oil' ),
		'1' => __( 'Show', 'syria-oil' ),
		'0' => __( 'Hide', 'syria-oil' ),
	);
	foreach ( $crumb_opts as $val => $label ) {
		printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $val ), selected( (string) $crumbs, $val, false ), esc_html( $label ) );
	}
	echo '</select></label></p>';
}

/**
 * Save page override metabox.
 *
 * @param int $post_id Post ID.
 */
function syria_oil_save_page_overrides_metabox( $post_id ) {
	if ( ! isset( $_POST['syria_oil_page_overrides_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['syria_oil_page_overrides_nonce'] ) ), 'syria_oil_page_overrides' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$meta = array();
	$color = isset( $_POST['syria_oil_page_color_primary'] ) ? sanitize_hex_color( wp_unslash( $_POST['syria_oil_page_color_primary'] ) ) : '';
	if ( $color ) {
		$meta['color_primary'] = $color;
	}

	$header = isset( $_POST['syria_oil_page_header_style'] ) ? sanitize_text_field( wp_unslash( $_POST['syria_oil_page_header_style'] ) ) : '';
	$header = syria_oil_sanitize_choice( $header, array( 'standard', 'sticky', 'transparent_over_hero' ), '' );
	if ( $header ) {
		$meta['header_style'] = $header;
	}

	$container = isset( $_POST['syria_oil_page_container_width'] ) ? sanitize_text_field( wp_unslash( $_POST['syria_oil_page_container_width'] ) ) : '';
	$container = syria_oil_sanitize_choice( $container, array( 'boxed', 'full' ), '' );
	if ( $container ) {
		$meta['container_width'] = $container;
	}

	$crumbs = isset( $_POST['syria_oil_page_show_breadcrumbs'] ) ? sanitize_text_field( wp_unslash( $_POST['syria_oil_page_show_breadcrumbs'] ) ) : '';
	if ( '0' === $crumbs || '1' === $crumbs ) {
		$meta['show_breadcrumbs'] = ( '1' === $crumbs );
	}

	if ( empty( $meta ) ) {
		delete_post_meta( $post_id, SYRIA_OIL_PAGE_META );
	} else {
		update_post_meta( $post_id, SYRIA_OIL_PAGE_META, $meta );
	}
}
add_action( 'save_post', 'syria_oil_save_page_overrides_metabox' );

/**
 * Hide CSF Tools welcome submenu when free CSF registers it.
 */
function syria_oil_remove_csf_welcome_menu() {
	remove_submenu_page( 'tools.php', 'csf-welcome' );
}
add_action( 'admin_menu', 'syria_oil_remove_csf_welcome_menu', 999 );
