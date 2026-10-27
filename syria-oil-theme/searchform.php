<?php
/**
 * Custom search form matching the design system's .search-box.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form role="search" method="get" class="search-box" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<?php
	$placeholder = function_exists( 'syria_oil_mod' ) ? syria_oil_mod( 'search_placeholder' ) : '';
	if ( '' === $placeholder ) {
		$placeholder = __( 'Search the news…', 'syria-oil' );
	}
	?>
	<?php $syria_oil_search_id = 'search-field-' . uniqid(); ?>
	<label class="sr-only" for="<?php echo esc_attr( $syria_oil_search_id ); ?>"><?php esc_html_e( 'Search for:', 'syria-oil' ); ?></label>
	<input id="<?php echo esc_attr( $syria_oil_search_id ); ?>" type="search" class="search-field" name="s" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" />
	<button class="btn btn-primary" type="submit"><?php esc_html_e( 'Search', 'syria-oil' ); ?></button>
</form>
