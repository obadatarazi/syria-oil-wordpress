<?php
/**
 * Theme migration utilities for users switching from other themes.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Export current theme settings (for backup before switching themes).
 *
 * @return array
 */
function syria_oil_export_theme_data() {
	return array(
		'theme_mods'         => get_theme_mods(),
		'menu_locations'     => get_theme_mod( 'nav_menu_locations' ),
		'custom_logo'        => get_theme_mod( 'custom_logo' ),
		'site_icon'          => get_option( 'site_icon' ),
		'blogname'           => get_option( 'blogname' ),
		'blogdescription'    => get_option( 'blogdescription' ),
		'permalink_structure' => get_option( 'permalink_structure' ),
		'show_on_front'      => get_option( 'show_on_front' ),
		'page_on_front'      => get_option( 'page_on_front' ),
		'page_for_posts'     => get_option( 'page_for_posts' ),
		'exported_at'        => time(),
	);
}

/**
 * Try to recover menu assignments from previous theme.
 *
 * Maps common menu location names to Syria Oil locations.
 */
function syria_oil_recover_menu_locations() {
	$prev_locations = get_option( 'syria_oil_previous_menu_locations' );
	if ( ! $prev_locations || ! is_array( $prev_locations ) ) {
		return;
	}
	
	// Mapping: previous location name → Syria Oil location.
	$map = array(
		// Common names → our names.
		'primary'       => 'primary',
		'main'          => 'primary',
		'header'        => 'primary',
		'top'           => 'primary',
		'footer'        => 'footer',
		'footer-menu'   => 'footer',
		'mobile'        => 'mobile_bottom',
		'mobile-menu'   => 'mobile_bottom',
	);
	
	$new_locations = get_theme_mod( 'nav_menu_locations', array() );
	
	foreach ( $prev_locations as $old_loc => $menu_id ) {
		$new_loc = $map[ $old_loc ] ?? null;
		if ( $new_loc && empty( $new_locations[ $new_loc ] ) ) {
			$new_locations[ $new_loc ] = $menu_id;
		}
	}
	
	set_theme_mod( 'nav_menu_locations', $new_locations );
}

/**
 * Admin tool: Download theme settings as JSON backup.
 */
function syria_oil_handle_export_settings() {
	if ( ! current_user_can( 'export' ) ) {
		wp_die( esc_html__( 'You do not have permission to export data.', 'syria-oil' ) );
	}
	
	check_admin_referer( 'syria_oil_export_settings' );
	
	$data = syria_oil_export_theme_data();
	$json = wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
	
	$filename = 'syria-oil-settings-' . gmdate( 'Y-m-d-H-i-s' ) . '.json';
	
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Expires: 0' );
	
	echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}
add_action( 'admin_post_syria_oil_export_settings', 'syria_oil_handle_export_settings' );

/**
 * Attempt menu recovery after theme switch.
 */
add_action( 'after_switch_theme', 'syria_oil_recover_menu_locations', 15 );
