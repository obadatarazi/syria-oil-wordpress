<?php
/**
 * 404 template.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<section class="section">
	<div class="container text-center">
		<div class="flag-line" aria-hidden="true" style="margin-inline:auto"><span></span><span></span><span></span></div>
		<h1><?php esc_html_e( 'Page not found', 'syria-oil' ); ?></h1>
		<p class="text-muted" style="max-width:38rem;margin:1rem auto 2rem"><?php esc_html_e( 'The page you were looking for could not be found. It may have moved or no longer exists.', 'syria-oil' ); ?></p>
		<div style="max-width:32rem;margin-inline:auto"><?php get_search_form(); ?></div>
		<p style="margin-top:2rem"><a class="btn btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'syria-oil' ); ?></a></p>
	</div>
</section>

<?php
get_footer();
