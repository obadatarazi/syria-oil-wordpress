<?php
/**
 * One Click Demo Import (OCDI) support.
 *
 * Registers the demo package and wires after-import actions (menus, front page,
 * Elementor kit). Requires the free "One Click Demo Import" plugin. All hooks
 * no-op safely if the plugin is absent.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Locate a published page by title without relying on deprecated APIs.
 *
 * @param string $title Page title.
 * @return WP_Post|null
 */
function syria_oil_get_page_by_title( $title ) {
	global $wpdb;

	$title = (string) $title;
	if ( '' === $title ) {
		return null;
	}

	// Prefer WP_Query title support (WP 6.2+).
	$query = new WP_Query(
		array(
			'post_type'              => 'page',
			'title'                  => $title,
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'orderby'                => 'post_date ID',
			'order'                  => 'ASC',
		)
	);

	if ( ! empty( $query->posts ) && isset( $query->posts[0]->post_title ) && $query->posts[0]->post_title === $title ) {
		return $query->posts[0];
	}

	// Fallback for installs where the title query var is ignored.
	$page_id = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'page' AND post_status = 'publish' ORDER BY ID ASC LIMIT 1",
			$title
		)
	);

	return $page_id ? get_post( $page_id ) : null;
}

/**
 * Register the importable demo package.
 *
 * Only attaches local file paths that actually exist so OCDI does not warn
 * about missing widgets.wie / customizer.dat / preview assets.
 *
 * @return array
 */
function syria_oil_ocdi_import_files() {
	$demos_uri = trailingslashit( SYRIA_OIL_URI ) . 'demos/';
	$dir       = trailingslashit( SYRIA_OIL_DIR ) . 'demos/';

	$package = array(
		'import_file_name' => __( 'Syria Oil — Default Demo', 'syria-oil' ),
		'import_notice'    => __( 'Imports starter pages and posts. Menus and Customizer defaults are applied automatically after import. Tip: you can also use Tools → Syria Oil Starter.', 'syria-oil' ),
		'preview_url'      => 'https://www.linkedin.com/in/obada-tarazi',
	);

	$content = $dir . 'syria-oil-content.xml';
	if ( file_exists( $content ) ) {
		$package['local_import_file'] = $content;
	}

	$widgets = $dir . 'widgets.wie';
	if ( file_exists( $widgets ) ) {
		$package['local_import_widget_file'] = $widgets;
	}

	$customizer = $dir . 'customizer.dat';
	if ( file_exists( $customizer ) ) {
		$package['local_import_customizer_file'] = $customizer;
	}

	$preview = $dir . 'preview.png';
	if ( file_exists( $preview ) ) {
		$package['import_preview_image_url'] = $demos_uri . 'preview.png';
	}

	return array( $package );
}
add_filter( 'ocdi/import_files', 'syria_oil_ocdi_import_files' );
add_filter( 'pt-ocdi/import_files', 'syria_oil_ocdi_import_files' ); // Legacy hook name.

/**
 * Resolve a demo page by trying several known titles (EN + AR).
 *
 * @param string[] $titles Candidate titles.
 * @return WP_Post|null
 */
function syria_oil_get_demo_page( array $titles ) {
	foreach ( $titles as $title ) {
		$page = syria_oil_get_page_by_title( $title );
		if ( $page ) {
			return $page;
		}
	}
	return null;
}

/**
 * After content import: assign menus, set static front page, import the
 * Elementor kit/templates if present.
 *
 * @param array $selected_import The chosen import definition.
 */
function syria_oil_ocdi_after_import( $selected_import ) {
	unset( $selected_import );

	// Build / refresh menus, pages, sample posts, Customizer chrome.
	if ( function_exists( 'syria_oil_install_starter_content' ) ) {
		syria_oil_install_starter_content( true );
	}

	if ( function_exists( 'syria_oil_seed_demo_side_ads' ) ) {
		syria_oil_seed_demo_side_ads( true );
	}

	// Assign navigation menus by English or Arabic names (belt & suspenders).
	$locations = get_theme_mod( 'nav_menu_locations', array() );

	$menu_map = array(
		'primary'       => array( 'القائمة الرئيسية', 'Primary Menu' ),
		'footer'        => array( 'قائمة التذييل', 'Footer Menu' ),
		'mobile_bottom' => array( 'شريط الجوال السفلي', 'Mobile Bottom Bar' ),
	);

	foreach ( $menu_map as $location => $names ) {
		foreach ( $names as $name ) {
			$menu = get_term_by( 'name', $name, 'nav_menu' );
			if ( $menu ) {
				$locations[ $location ] = (int) $menu->term_id;
				break;
			}
		}
	}
	set_theme_mod( 'nav_menu_locations', $locations );

	$blog = syria_oil_get_demo_page( array( 'الأخبار', 'News' ) );
	if ( $blog ) {
		update_option( 'page_for_posts', (int) $blog->ID );
	}
	update_option( 'show_on_front', 'posts' );
	update_option( 'page_on_front', 0 );

	// 3. Import the Elementor kit JSON if bundled and Elementor is active.
	$kit_file = trailingslashit( SYRIA_OIL_DIR ) . 'demos/elementor/kit.json';
	if ( file_exists( $kit_file ) && did_action( 'elementor/loaded' ) ) {
		do_action( 'syria_oil_import_elementor_kit', $kit_file );
	}
}
add_action( 'ocdi/after_import', 'syria_oil_ocdi_after_import' );
add_action( 'pt-ocdi/after_import', 'syria_oil_ocdi_after_import' );

// Cleaner import UX: hide OCDI branding and skip thumbnail regeneration.
add_filter( 'ocdi/disable_pt_branding', '__return_true' );
add_filter( 'ocdi/regenerate_thumbnails_in_content_import', '__return_false' );
