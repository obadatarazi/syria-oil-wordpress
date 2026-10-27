<?php
/**
 * Inline SVG icons for social + share actions.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render an inline SVG icon.
 *
 * @param string $slug Icon slug.
 * @return string Markup (empty if unknown).
 */
function syria_oil_get_social_icon_svg( $slug ) {
	$slug = sanitize_key( $slug );
	$paths = array(
		'facebook'  => 'M14 8.5h-2.3V7c0-.7.1-1.2.4-1.5.3-.4.9-.6 1.7-.6.5 0 1.1.1 1.5.2V3.9c-.4-.1-1-.2-1.8-.2-1.8 0-3 .5-3.7 1.4-.6.8-.9 2-.9 3.5v1.9H7v2.8h2.9V21h3.5v-9.7H16l.3-2.8H13.4V8.5z',
		'x'         => 'M16.6 5h2.5l-5.4 6.2L20 19h-2.5l-4.5-5.2L7.8 19H5.3l5.8-6.6L5 5h2.6l4.1 4.7L16.6 5zm-.9 12.2h1.4L8.4 6.7H7l8.7 10.5z',
		'youtube'   => 'M10 8.5l5.5 3.1L10 14.7V8.5zM9.2 6.3h5.6c1.4 0 2.5 1.1 2.5 2.5v6.4c0 1.4-1.1 2.5-2.5 2.5H9.2C7.8 17.7 6.7 16.6 6.7 15.2V8.8c0-1.4 1.1-2.5 2.5-2.5z',
		'linkedin'  => 'M7 9.5H4.5V19H7V9.5zM5.8 4a1.5 1.5 0 100 3 1.5 1.5 0 000-3zM9 9.5H11.5V11h.1c.3-.6 1.2-1.2 2.5-1.2 2.7 0 3.2 1.8 3.2 4.1V19H13.2v-4.6c0-1.1 0-2.5-1.5-2.5-1.5 0-1.7 1.2-1.7 2.4V19H9V9.5z',
		'instagram' => 'M12 8.2a3.8 3.8 0 100 7.6 3.8 3.8 0 000-7.6zm0 6.1a2.3 2.3 0 110-4.6 2.3 2.3 0 010 4.6zm4.8-6.3a.9.9 0 11-1.8 0 .9.9 0 011.8 0zM7.8 5.5h8.4A2.3 2.3 0 0118.5 7.8v8.4a2.3 2.3 0 01-2.3 2.3H7.8a2.3 2.3 0 01-2.3-2.3V7.8a2.3 2.3 0 012.3-2.3zm8.4 1.8H7.8a.5.5 0 00-.5.5v8.4c0 .3.2.5.5.5h8.4c.3 0 .5-.2.5-.5V7.8a.5.5 0 00-.5-.5z',
		'telegram'  => 'M5.8 11.2L18.5 6c.6-.2 1.1.1.9.9l-2.2 9.8c-.2.5-.5.6-.9.4l-2.4-1.8-1.2 1.2c-.1.1-.2.2-.5.2l.2-3.3 8.8-8c.1-.1 0-.2-.1-.1l-10.8 6.8-3.5-1.1c-.5-.2-.5-.7.1-.9z',
		'whatsapp'  => 'M12 4.5a7.5 7.5 0 00-6.5 11.2l-.6 2.4 2.5-.7A7.5 7.5 0 1012 4.5zm0 1.4a6.1 6.1 0 016 6.4l.1.5-.3-.1a6.1 6.1 0 01-5.8-6.8zm-2.8 3.2c.1-.2.3-.3.5-.3.1 0 .2 0 .4.1.2.1.5.4.6.8.1.5.3 1.8-.6 2.2-.3.1-.8.2-1.3-.1-.3-.2-.9-.5-1.5-1.1-.6-.5-1-1.1-1.1-1.4-.1-.3 0-.7.2-.9.1-.2.3-.5.4-.6.1-.1.2-.2.3-.2.1 0 .2 0 .2.1l1 1z',
		'copy'      => 'M8.5 4.5h6.5a1.5 1.5 0 011.5 1.5v6.5H15V6a1 1 0 00-1-1H8.5V4.5zM6.5 7h6.5A1.5 1.5 0 0114.5 8.5v6.5A1.5 1.5 0 0113 16.5H6.5A1.5 1.5 0 015 15V8.5A1.5 1.5 0 016.5 7zm0 1.5a.5.5 0 00-.5.5v6.5c0 .3.2.5.5.5h6.5c.3 0 .5-.2.5-.5V8.5a.5.5 0 00-.5-.5H6.5z',
		'print'     => 'M7.5 5.5V4h9v1.5h-9zM6 8h12a1.5 1.5 0 011.5 1.5V14H17v5.5H7V14H4.5V9.5A1.5 1.5 0 016 8zm9.5 2H8.5v1h7V10zm-4.5 5v2h4v-2H11z',
	);

	if ( ! isset( $paths[ $slug ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="so-icon so-icon--%1$s" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path fill="currentColor" d="%2$s"/></svg>',
		esc_attr( $slug ),
		esc_attr( $paths[ $slug ] )
	);
}

/**
 * Echo icon markup.
 *
 * @param string $slug Icon slug.
 */
function syria_oil_the_social_icon_svg( $slug ) {
	echo syria_oil_get_social_icon_svg( $slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built safely above.
}
