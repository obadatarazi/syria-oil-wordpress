<?php
/**
 * Parse display strings like "1,482+" or "$72.50" into counter parts.
 *
 * @package Syria_Oil_Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Split a formatted numeric string into target / prefix / suffix / decimals.
 *
 * @param string $raw Raw value from editor (e.g. "1,482+", "72.5%", "USD 12.30").
 * @return array{target:float,prefix:string,suffix:string,decimals:int,grouping:bool}|null
 */
function syria_oil_widgets_parse_counter_value( $raw ) {
	$raw = trim( (string) $raw );
	if ( '' === $raw ) {
		return null;
	}

	if ( ! preg_match( '/^(.*?)([-+]?\d[\d,]*(?:\.\d+)?)(.*)$/u', $raw, $m ) ) {
		return null;
	}

	$num_raw  = $m[2];
	$grouping = ( false !== strpos( $num_raw, ',' ) );
	$clean    = str_replace( ',', '', $num_raw );
	$decimals = 0;
	if ( false !== strpos( $clean, '.' ) ) {
		$parts    = explode( '.', $clean, 2 );
		$decimals = isset( $parts[1] ) ? strlen( $parts[1] ) : 0;
	}

	return array(
		'target'   => (float) $clean,
		'prefix'   => $m[1],
		'suffix'   => $m[3],
		'decimals' => $decimals,
		'grouping' => $grouping,
	);
}

/**
 * Format a western (en-US) decimal for price displays / counter targets.
 *
 * @param float $value    Number.
 * @param int   $decimals Decimal places.
 * @return string
 */
function syria_oil_widgets_format_western_number( $value, $decimals = 2 ) {
	return number_format( (float) $value, (int) $decimals, '.', ',' );
}
