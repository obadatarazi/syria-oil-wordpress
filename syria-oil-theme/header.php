<?php
/**
 * Site header.
 *
 * Renders the theme's default header when Elementor Pro Theme Builder has no
 * custom header assigned. All user-facing strings come from theme_mods /
 * Customizer or bloginfo — nothing is hardcoded for site content.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?><?php echo syria_oil_mod( 'enable_dark_mode', false ) ? ' data-theme="dark"' : ''; ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
		<link rel="pingback" href="<?php echo esc_url( get_bloginfo( 'pingback_url' ) ); ?>" />
	<?php endif; ?>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php if ( syria_oil_mod( 'show_preloader', true ) ) : ?>
	<?php
	$syria_oil_preloader_logo = '';
	$syria_oil_preloader_logo_id = absint( syria_oil_mod( 'logo_dark', 0 ) );
	if ( $syria_oil_preloader_logo_id ) {
		$syria_oil_preloader_logo = wp_get_attachment_image_url( $syria_oil_preloader_logo_id, 'full' );
	}
	if ( ! $syria_oil_preloader_logo && function_exists( 'syria_oil_brand_logo_white_url' ) && is_readable( syria_oil_brand_logo_white_path() ) ) {
		$syria_oil_preloader_logo = syria_oil_brand_logo_white_url();
	}
	if ( ! $syria_oil_preloader_logo ) {
		$syria_oil_preloader_logo_id = absint( syria_oil_mod( 'logo_primary', 0 ) );
		if ( $syria_oil_preloader_logo_id ) {
			$syria_oil_preloader_logo = wp_get_attachment_image_url( $syria_oil_preloader_logo_id, 'full' );
		}
	}
	$syria_oil_preloader_title = get_bloginfo( 'name' );
	?>
	<div class="syria-oil-preloader" id="syria-oil-preloader" aria-hidden="true">
		<div class="syria-oil-preloader__inner">
			<?php if ( $syria_oil_preloader_logo ) : ?>
				<img
					class="syria-oil-preloader__logo"
					src="<?php echo esc_url( $syria_oil_preloader_logo ); ?>"
					alt="<?php echo esc_attr( $syria_oil_preloader_title ); ?>"
					width="200"
					height="200"
					decoding="async"
				/>
			<?php endif; ?>
			<div class="syria-oil-preloader__title"><?php echo esc_html( $syria_oil_preloader_title ); ?></div>
		</div>
	</div>
<?php endif; ?>

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'syria-oil' ); ?></a>

<?php
// Let Elementor Pro render a custom header if one is assigned for this location.
$syria_oil_el_header = ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'header' ) );
$GLOBALS['syria_oil_classic_header'] = ! $syria_oil_el_header;
if ( ! $syria_oil_el_header ) {
	get_template_part( 'template-parts/header' );
}
?>

<div class="site-shell">
	<?php
	if ( function_exists( 'syria_oil_render_ad_rail' ) ) {
		syria_oil_render_ad_rail( 'left' );
	}
	?>
	<div class="site-shell__main">
<main id="main" class="site-main">
	<?php
	if ( function_exists( 'syria_oil_render_premium_ads' ) ) {
		syria_oil_render_premium_ads();
	}
	// Normal strip: JS moves it after the page hero on every template.
	if ( function_exists( 'syria_oil_render_mid_page_ads' ) ) {
		syria_oil_render_mid_page_ads();
	}
	?>
