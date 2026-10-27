<?php
/**
 * Syria Oil Theme Options — dashboard + live preview panes (CSF admin).
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Current option label for dashboard chips.
 *
 * @param string $key   Option key.
 * @param array  $map   value => label.
 * @param string $fallback Fallback label.
 * @return string
 */
function syria_oil_options_dashboard_label( $key, $map, $fallback = '' ) {
	$value = function_exists( 'syria_oil_mod' ) ? syria_oil_mod( $key, '' ) : '';
	if ( isset( $map[ $value ] ) ) {
		return (string) $map[ $value ];
	}
	return $fallback ? $fallback : (string) $value;
}

/**
 * Safe admin URL to edit the static front page in Elementor.
 *
 * @return string
 */
function syria_oil_options_dashboard_home_edit_url() {
	$front_id = (int) get_option( 'page_on_front' );
	if ( $front_id > 0 ) {
		return admin_url( 'post.php?post=' . $front_id . '&action=elementor' );
	}
	return admin_url( 'edit.php?post_type=page' );
}

/**
 * Overview tab — welcome hero + quick status + links.
 */
function syria_oil_render_options_dashboard() {
	$primary   = function_exists( 'syria_oil_mod' ) ? (string) syria_oil_mod( 'color_primary', '#007a3d' ) : '#007a3d';
	$hero      = syria_oil_options_dashboard_label(
		'hero_featured_sidebar',
		array(
			'split' => __( 'Split — beside hero', 'syria-oil' ),
			'below' => __( 'Below hero', 'syria-oil' ),
			'none'  => __( 'Hidden', 'syria-oil' ),
		),
		__( 'Below hero', 'syria-oil' )
	);
	$banner    = syria_oil_options_dashboard_label(
		'banner_edge_mode',
		array(
			'none'  => __( 'None', 'syria-oil' ),
			'color' => __( 'Color edge', 'syria-oil' ),
			'image' => __( 'Image edge', 'syria-oil' ),
			'both'  => __( 'Color + image', 'syria-oil' ),
		),
		__( 'None', 'syria-oil' )
	);
	$container = syria_oil_options_dashboard_label(
		'container_width',
		array(
			'boxed' => __( 'Boxed 1280px', 'syria-oil' ),
			'full'  => __( 'Full width', 'syria-oil' ),
		),
		__( 'Boxed', 'syria-oil' )
	);
	$home      = home_url( '/' );
	$widgets   = admin_url( 'plugins.php' );
	$elementor = admin_url( 'admin.php?page=elementor' );

	echo '<div class="so-admin-dashboard" style="--so-dash-primary:' . esc_attr( $primary ) . '">';
	echo '<div class="so-admin-dashboard__hero">';
	echo '<div class="so-admin-dashboard__hero-text">';
	echo '<p class="so-admin-dashboard__eyebrow">' . esc_html__( 'Syria Oil Control Center', 'syria-oil' ) . '</p>';
	echo '<h2 class="so-admin-dashboard__title">' . esc_html__( 'Design your news portal from one place', 'syria-oil' ) . '</h2>';
	echo '<p class="so-admin-dashboard__lead">' . esc_html__( 'Branding, colors, typography, layout, and banners live here — not in Elementor Site Settings. Save any tab, then hard-refresh the front page to confirm.', 'syria-oil' ) . '</p>';
	echo '<div class="so-admin-dashboard__actions">';
	echo '<a class="button button-primary button-hero" href="' . esc_url( $home ) . '" target="_blank" rel="noopener">' . esc_html__( 'View site', 'syria-oil' ) . '</a> ';
	echo '<a class="button button-secondary" href="' . esc_url( admin_url( 'customize.php' ) ) . '">' . esc_html__( 'Customizer', 'syria-oil' ) . '</a>';
	echo '</div></div>';
	echo '<div class="so-admin-dashboard__hero-art" aria-hidden="true">';
	echo '<div class="so-admin-dashboard__hero-card"><span></span><span></span><span></span></div>';
	echo '</div></div>';

	echo '<div class="so-admin-dashboard__grid">';
	$chips = array(
		array( 'label' => __( 'Primary color', 'syria-oil' ), 'value' => $primary, 'swatch' => $primary ),
		array( 'label' => __( 'Hero sidebar', 'syria-oil' ), 'value' => $hero ),
		array( 'label' => __( 'Banner edge', 'syria-oil' ), 'value' => $banner ),
		array( 'label' => __( 'Container', 'syria-oil' ), 'value' => $container ),
	);
	foreach ( $chips as $chip ) {
		echo '<div class="so-admin-dashboard__chip">';
		echo '<span class="so-admin-dashboard__chip-label">' . esc_html( $chip['label'] ) . '</span>';
		echo '<span class="so-admin-dashboard__chip-value">';
		if ( ! empty( $chip['swatch'] ) ) {
			echo '<span class="so-admin-dashboard__chip-swatch" style="background:' . esc_attr( $chip['swatch'] ) . '"></span>';
		}
		echo esc_html( $chip['value'] ) . '</span></div>';
	}
	echo '</div>';

	echo '<div class="so-admin-dashboard__links">';
	$links = array(
		array( __( 'Edit homepage', 'syria-oil' ), syria_oil_options_dashboard_home_edit_url(), 'dashicons-layout' ),
		array( __( 'Side ads', 'syria-oil' ), admin_url( 'edit.php?post_type=syria_oil_ad' ), 'dashicons-megaphone' ),
		array( __( 'Menus', 'syria-oil' ), admin_url( 'nav-menus.php' ), 'dashicons-menu' ),
		array( __( 'Widgets plugin', 'syria-oil' ), $widgets, 'dashicons-admin-plugins' ),
	);
	foreach ( $links as $link ) {
		echo '<a class="so-admin-dashboard__link" href="' . esc_url( $link[1] ) . '">';
		echo '<span class="dashicons ' . esc_attr( $link[2] ) . '"></span>';
		echo esc_html( $link[0] ) . '</a>';
	}
	echo '</div></div>';
}

/**
 * Colors tab live preview.
 */
function syria_oil_render_options_preview_pane() {
	echo '<div class="so-preview so-preview--colors" id="syria-oil-options-preview">';
	echo '<div class="so-preview__head">';
	echo '<span class="so-preview__badge">' . esc_html__( 'Live preview', 'syria-oil' ) . '</span>';
	echo '<p class="so-preview__title">' . esc_html__( 'Where each color appears on the site', 'syria-oil' ) . '</p>';
	echo '</div>';

	echo '<ul class="so-preview__legend">';
	echo '<li><span class="so-preview__swatch so-preview__swatch--primary"></span>' . esc_html__( 'Primary — ticker, cards, buttons', 'syria-oil' ) . '</li>';
	echo '<li><span class="so-preview__swatch so-preview__swatch--secondary"></span>' . esc_html__( 'Secondary — page canvas', 'syria-oil' ) . '</li>';
	echo '<li><span class="so-preview__swatch so-preview__swatch--accent"></span>' . esc_html__( 'Accent — urgent / CTA', 'syria-oil' ) . '</li>';
	echo '<li><span class="so-preview__swatch so-preview__swatch--depth"></span>' . esc_html__( 'Depth — burgundy card touches', 'syria-oil' ) . '</li>';
	echo '<li><span class="so-preview__swatch so-preview__swatch--ink"></span>' . esc_html__( 'Ink — body text', 'syria-oil' ) . '</li>';
	echo '</ul>';

	echo '<div class="so-preview__mock so-preview__mock--colors">';
	echo '<div class="so-preview__post-card" aria-hidden="true">';
	echo '<div class="so-preview__post-media"><span class="so-preview__post-caption">' . esc_html__( 'Image overlay', 'syria-oil' ) . '</span></div>';
	echo '<div class="so-preview__post-body"><span class="so-preview__post-badge">' . esc_html__( 'Category', 'syria-oil' ) . '</span>';
	echo '<strong>' . esc_html__( 'News card panel', 'syria-oil' ) . '</strong></div></div>';
	echo '<div class="so-preview__article-card">';
	echo '<strong class="so-preview__heading">' . esc_html__( 'عنوان الخبر الرئيسي', 'syria-oil' ) . '</strong>';
	echo '<p class="so-preview__body">' . esc_html__( 'نص المقال على خلفية الصفحة — يتغيّر مع حجم الخط والألوان.', 'syria-oil' ) . '</p>';
	echo '<span class="so-preview__btn">' . esc_html__( 'اقرأ المزيد', 'syria-oil' ) . '</span> ';
	echo '<span class="so-preview__accent">' . esc_html__( 'عاجل', 'syria-oil' ) . '</span>';
	echo '</div></div></div>';
}

/**
 * Branding tab — header + footer logo mockup.
 */
function syria_oil_render_branding_preview_pane() {
	$logo_id = function_exists( 'syria_oil_mod' ) ? absint( syria_oil_mod( 'logo_primary', 0 ) ) : 0;
	$logo    = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
	$dark_id = function_exists( 'syria_oil_mod' ) ? absint( syria_oil_mod( 'logo_dark', 0 ) ) : 0;
	$dark    = $dark_id ? wp_get_attachment_image_url( $dark_id, 'medium' ) : $logo;

	echo '<div class="so-preview so-preview--branding" id="syria-oil-branding-preview"';
	echo ' data-logo="' . esc_url( $logo ) . '" data-logo-dark="' . esc_url( $dark ) . '">';
	echo '<div class="so-preview__head">';
	echo '<span class="so-preview__badge">' . esc_html__( 'Live preview', 'syria-oil' ) . '</span>';
	echo '<p class="so-preview__title">' . esc_html__( 'Logo sizes in header, menu & footer', 'syria-oil' ) . '</p>';
	echo '</div>';

	echo '<div class="so-preview__device-tabs" role="tablist" aria-label="' . esc_attr__( 'Preview device', 'syria-oil' ) . '">';
	echo '<button type="button" class="is-active" data-device="desktop">' . esc_html__( 'Desktop', 'syria-oil' ) . '</button>';
	echo '<button type="button" data-device="mobile">' . esc_html__( 'Mobile', 'syria-oil' ) . '</button>';
	echo '</div>';

	echo '<div class="so-preview__branding-mock" data-device="desktop">';
	echo '<div class="so-preview__header-bar">';
	echo '<div class="so-preview__logo-wrap so-preview__logo-wrap--header">';
	echo '<img class="so-preview__logo so-preview__logo--header" src="' . esc_url( $logo ) . '" alt="" />';
	echo '<span class="so-preview__logo-fallback">' . esc_html__( 'Logo', 'syria-oil' ) . '</span>';
	echo '</div>';
	echo '<nav class="so-preview__nav" aria-hidden="true"><span>' . esc_html__( 'الرئيسية', 'syria-oil' ) . '</span><span>' . esc_html__( 'أخبار', 'syria-oil' ) . '</span><span>' . esc_html__( 'تحليلات', 'syria-oil' ) . '</span></nav>';
	echo '</div>';
	echo '<div class="so-preview__footer-bar">';
	echo '<div class="so-preview__logo-wrap so-preview__logo-wrap--footer">';
	echo '<img class="so-preview__logo so-preview__logo--footer" src="' . esc_url( $dark ? $dark : $logo ) . '" alt="" />';
	echo '<span class="so-preview__logo-fallback so-preview__logo-fallback--dark">' . esc_html__( 'Logo', 'syria-oil' ) . '</span>';
	echo '</div>';
	echo '<p class="so-preview__footer-note">' . esc_html__( 'Footer uses white logo when uploaded', 'syria-oil' ) . '</p>';
	echo '</div></div></div>';
}

/**
 * Typography tab preview.
 */
function syria_oil_render_typography_preview_pane() {
	echo '<div class="so-preview so-preview--typography" id="syria-oil-typography-preview">';
	echo '<div class="so-preview__head">';
	echo '<span class="so-preview__badge">' . esc_html__( 'Live preview', 'syria-oil' ) . '</span>';
	echo '<p class="so-preview__title">' . esc_html__( 'Arabic headings & body with your font choices', 'syria-oil' ) . '</p>';
	echo '</div>';
	echo '<div class="so-preview__type-sample">';
	echo '<p class="so-preview__type-kicker">' . esc_html__( 'قطاع النفط والغاز', 'syria-oil' ) . '</p>';
	echo '<h3 class="so-preview__type-heading">' . esc_html__( 'سوريا تعلن عن خطة جديدة لتطوير حقول الغاز', 'syria-oil' ) . '</h3>';
	echo '<p class="so-preview__type-body">' . esc_html__( 'يعرض هذا النص كيف ستظهر العناوين والفقرات على الموقع. غيّر خط العناوين، خط النص، الحجم، أو حجم قائمة الهيدر ثم احفظ.', 'syria-oil' ) . '</p>';
	echo '<div class="so-preview__type-nav">' . esc_html__( 'الرئيسية', 'syria-oil' ) . ' · ' . esc_html__( 'الأسعار', 'syria-oil' ) . ' · ' . esc_html__( 'الوظائف', 'syria-oil' ) . '</div>';
	echo '</div></div>';
}

/**
 * Layout tab — hero sidebar modes.
 */
function syria_oil_render_layout_preview_pane() {
	echo '<div class="so-preview so-preview--layout" id="syria-oil-layout-preview">';
	echo '<div class="so-preview__head">';
	echo '<span class="so-preview__badge">' . esc_html__( 'Live preview', 'syria-oil' ) . '</span>';
	echo '<p class="so-preview__title">' . esc_html__( 'Hero & أخبار بارزة layout', 'syria-oil' ) . '</p>';
	echo '</div>';
	echo '<div class="so-preview__layout-modes">';
	foreach ( array( 'split', 'below', 'none' ) as $mode ) {
		$labels = array(
			'split' => __( 'Split — rail beside slider', 'syria-oil' ),
			'below' => __( 'Below — category section', 'syria-oil' ),
			'none'  => __( 'Hidden — no separate block', 'syria-oil' ),
		);
		echo '<div class="so-preview__layout-card" data-mode="' . esc_attr( $mode ) . '">';
		echo '<div class="so-preview__layout-diagram so-preview__layout-diagram--' . esc_attr( $mode ) . '" aria-hidden="true">';
		echo '<span class="so-preview__layout-hero"></span>';
		if ( 'split' === $mode ) {
			echo '<span class="so-preview__layout-rail"></span>';
		}
		if ( 'below' === $mode ) {
			echo '<span class="so-preview__layout-below"></span>';
		}
		echo '</div>';
		echo '<p class="so-preview__layout-label">' . esc_html( $labels[ $mode ] ) . '</p>';
		echo '</div>';
	}
	echo '</div></div>';
}

/**
 * Banner tab preview.
 */
function syria_oil_render_banner_preview_pane() {
	$flag = function_exists( 'syria_oil_banner_flag_image_url' ) ? syria_oil_banner_flag_image_url() : '';
	$emblem = function_exists( 'syria_oil_banner_emblem_image_url' ) ? syria_oil_banner_emblem_image_url() : '';

	echo '<div class="so-preview so-preview--banner" id="syria-oil-banner-preview"';
	echo ' data-flag="' . esc_url( $flag ) . '" data-emblem="' . esc_url( $emblem ) . '">';
	echo '<div class="so-preview__head">';
	echo '<span class="so-preview__badge">' . esc_html__( 'Live preview', 'syria-oil' ) . '</span>';
	echo '<p class="so-preview__title">' . esc_html__( 'Banner edge & emblem watermark', 'syria-oil' ) . '</p>';
	echo '</div>';
	echo '<div class="so-preview__banner-stage">';
	echo '<div class="so-preview__banner-edge" aria-hidden="true"></div>';
	echo '<div class="so-preview__banner-body">';
	echo '<span class="so-preview__banner-emblem" aria-hidden="true"></span>';
	echo '<h4 class="so-preview__banner-title">' . esc_html__( 'عنوان الصفحة أو المقال', 'syria-oil' ) . '</h4>';
	echo '<p class="so-preview__banner-meta">' . esc_html__( 'Breadcrumb · Category', 'syria-oil' ) . '</p>';
	echo '</div></div></div>';
}

/**
 * Top Bar tab preview.
 */
function syria_oil_render_topbar_preview_pane() {
	$sample_date = date_i18n( get_option( 'date_format' ) );
	echo '<div class="so-preview so-preview--topbar" id="syria-oil-topbar-preview" data-sample-date="' . esc_attr( $sample_date ) . '">';
	echo '<div class="so-preview__head">';
	echo '<span class="so-preview__badge">' . esc_html__( 'Live preview', 'syria-oil' ) . '</span>';
	echo '<p class="so-preview__title">' . esc_html__( 'Thin strip above the header', 'syria-oil' ) . '</p>';
	echo '</div>';
	echo '<div class="so-preview__topbar-stage">';
	echo '<div class="so-preview__topbar-inner">';
	echo '<div class="so-preview__topbar-meta">';
	echo '<span class="so-preview__topbar-text">' . esc_html__( 'رسالة الشريط العلوي', 'syria-oil' ) . '</span>';
	echo '<span class="so-preview__topbar-date">' . esc_html( $sample_date ) . '</span>';
	echo '</div>';
	echo '<a class="so-preview__topbar-link" href="#">' . esc_html__( 'رابط', 'syria-oil' ) . '</a>';
	echo '</div>';
	echo '<p class="so-preview__topbar-hidden">' . esc_html__( 'Top bar is hidden — enable “Show top bar” below.', 'syria-oil' ) . '</p>';
	echo '</div></div>';
}

/**
 * Footer tab preview.
 */
function syria_oil_render_footer_preview_pane() {
	$logo_id = function_exists( 'syria_oil_mod' ) ? absint( syria_oil_mod( 'logo_dark', 0 ) ) : 0;
	if ( ! $logo_id ) {
		$logo_id = function_exists( 'syria_oil_mod' ) ? absint( syria_oil_mod( 'logo_primary', 0 ) ) : 0;
	}
	$logo = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
	$cols = function_exists( 'syria_oil_mod' ) ? (string) syria_oil_mod( 'footer_columns', '3' ) : '3';

	echo '<div class="so-preview so-preview--footer" id="syria-oil-footer-preview"';
	echo ' data-logo="' . esc_url( $logo ) . '" data-cols="' . esc_attr( $cols ) . '">';
	echo '<div class="so-preview__head">';
	echo '<span class="so-preview__badge">' . esc_html__( 'Live preview', 'syria-oil' ) . '</span>';
	echo '<p class="so-preview__title">' . esc_html__( 'Footer grid, sponsors & bottom note', 'syria-oil' ) . '</p>';
	echo '</div>';
	echo '<div class="so-preview__footer-stage">';
	echo '<div class="so-preview__footer-grid so-preview__footer-grid--' . esc_attr( $cols ) . '">';
	echo '<div class="so-preview__footer-brand-col">';
	echo '<div class="so-preview__footer-logo-wrap">';
	echo '<img class="so-preview__footer-logo" src="' . esc_url( $logo ) . '" alt="" />';
	echo '<span class="so-preview__logo-fallback so-preview__logo-fallback--dark">' . esc_html__( 'Logo', 'syria-oil' ) . '</span>';
	echo '</div>';
	echo '<p class="so-preview__footer-tagline">' . esc_html__( 'شعار الموقع أو وصف قصير', 'syria-oil' ) . '</p>';
	echo '<span class="so-preview__footer-pump" aria-hidden="true" title="' . esc_attr__( 'Oil pump icon', 'syria-oil' ) . '">⛽</span>';
	echo '</div>';
	echo '<div class="so-preview__footer-col"><h5 class="so-preview__footer-col-title so-preview__footer-explore">' . esc_html__( 'Explore', 'syria-oil' ) . '</h5><ul><li>…</li><li>…</li></ul></div>';
	echo '<div class="so-preview__footer-col"><h5 class="so-preview__footer-col-title so-preview__footer-cats">' . esc_html__( 'Categories', 'syria-oil' ) . '</h5><ul><li>…</li><li>…</li></ul></div>';
	if ( '4' === $cols ) {
		echo '<div class="so-preview__footer-col so-preview__footer-col--widget"><h5 class="so-preview__footer-col-title">Widget</h5><ul><li>…</li></ul></div>';
	}
	echo '<div class="so-preview__footer-col"><h5 class="so-preview__footer-col-title so-preview__footer-contact">' . esc_html__( 'Contact', 'syria-oil' ) . '</h5><ul><li>✉ …</li><li>☎ …</li></ul></div>';
	echo '</div>';
	echo '<div class="so-preview__footer-sponsors-wrap">';
	echo '<h5 class="so-preview__footer-sponsors-title">' . esc_html__( 'الشركات الداعمة والراعية', 'syria-oil' ) . '</h5>';
	echo '<div class="so-preview__footer-sponsors-row"><span>…</span><span>…</span><span>…</span></div>';
	echo '</div>';
	echo '<div class="so-preview__footer-bottom-text">© …</div>';
	echo '</div></div>';
}

/**
 * Preview config for admin JS.
 *
 * @return array<string,mixed>
 */
function syria_oil_theme_options_preview_config() {
	$logo_id = function_exists( 'syria_oil_mod' ) ? absint( syria_oil_mod( 'logo_primary', 0 ) ) : 0;
	$dark_id = function_exists( 'syria_oil_mod' ) ? absint( syria_oil_mod( 'logo_dark', 0 ) ) : 0;

	return array(
		'defaults' => array(
			'color_primary'   => '#007a3d',
			'color_secondary' => '#f7f9fb',
			'color_accent'    => '#ce1126',
			'color_depth'     => '#321010',
			'color_ink'       => '#0f1419',
		),
		'logoUrl'     => $logo_id ? (string) wp_get_attachment_image_url( $logo_id, 'medium' ) : '',
		'logoDarkUrl' => $dark_id ? (string) wp_get_attachment_image_url( $dark_id, 'medium' ) : '',
		'flagUrl'     => function_exists( 'syria_oil_banner_flag_image_url' ) ? (string) syria_oil_banner_flag_image_url() : '',
		'emblemUrl'   => function_exists( 'syria_oil_banner_emblem_image_url' ) ? (string) syria_oil_banner_emblem_image_url() : '',
		'footerDefaults' => array(
			'explore'  => __( 'Explore', 'syria-oil' ),
			'cats'     => __( 'Categories', 'syria-oil' ),
			'contact'  => __( 'Contact', 'syria-oil' ),
			'sponsors' => __( 'الشركات الداعمة والراعية', 'syria-oil' ),
		),
	);
}
