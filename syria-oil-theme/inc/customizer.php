<?php
/**
 * Customizer leftovers — social helper + hard cutover (Control Center removed).
 *
 * Brand identity lives in Syria Oil → Theme Options (CSF / syria_oil_options).
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Closure factory for whitelist sanitize callbacks (kept for BC).
 *
 * @param array $allowed Allowed values.
 * @param mixed $default Default.
 * @return callable
 */
function syria_oil_sanitize_choice_cb( $allowed, $default ) {
	return function ( $value ) use ( $allowed, $default ) {
		return syria_oil_sanitize_choice( $value, $allowed, $default );
	};
}

/**
 * Social network map (option key => label + mark).
 *
 * @return array<string,array{label:string,mark:string}>
 */
function syria_oil_social_networks_map() {
	return array(
		'social_facebook'  => array( 'label' => __( 'Facebook', 'syria-oil' ), 'icon' => 'facebook' ),
		'social_x'         => array( 'label' => __( 'X', 'syria-oil' ), 'icon' => 'x' ),
		'social_youtube'   => array( 'label' => __( 'YouTube', 'syria-oil' ), 'icon' => 'youtube' ),
		'social_linkedin'  => array( 'label' => __( 'LinkedIn', 'syria-oil' ), 'icon' => 'linkedin' ),
		'social_instagram' => array( 'label' => __( 'Instagram', 'syria-oil' ), 'icon' => 'instagram' ),
		'social_telegram'  => array( 'label' => __( 'Telegram', 'syria-oil' ), 'icon' => 'telegram' ),
	);
}

/**
 * Render social links from options (skips empty URLs).
 *
 * @param string $class Wrapper class.
 */
function syria_oil_render_social_links( $class = 'social-row' ) {
	$map   = syria_oil_social_networks_map();
	$links = array();
	foreach ( $map as $mod => $meta ) {
		$url = syria_oil_mod( $mod );
		if ( $url ) {
			$icon = function_exists( 'syria_oil_get_social_icon_svg' ) ? syria_oil_get_social_icon_svg( $meta['icon'] ) : esc_html( $meta['icon'] );
			$links[] = sprintf(
				'<a href="%1$s" target="_blank" rel="noopener noreferrer" aria-label="%2$s">%3$s</a>',
				esc_url( $url ),
				esc_attr( $meta['label'] ),
				$icon
			);
		}
	}

	if ( empty( $links ) ) {
		return;
	}

	echo '<div class="' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'Social links', 'syria-oil' ) . '">';
	echo implode( '', $links ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	echo '</div>';
}

/**
 * Compact social icons for header / top bar.
 */
function syria_oil_render_header_social_links() {
	if ( ! syria_oil_mod_enabled( 'show_header_social', true ) ) {
		return;
	}
	syria_oil_render_social_links( 'header-social-row' );
}
