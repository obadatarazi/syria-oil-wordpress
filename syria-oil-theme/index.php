<?php
/**
 * Fallback template for the blog / posts index.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$posts_page_id = (int) get_option( 'page_for_posts' );
$hero_title    = ( is_home() && $posts_page_id ) ? get_the_title( $posts_page_id ) : __( 'Latest News', 'syria-oil' );
$hero_lede     = '';
if ( is_home() && $posts_page_id ) {
	$hero_lede = (string) get_post_field( 'post_excerpt', $posts_page_id );
}
if ( '' === trim( $hero_lede ) ) {
	$hero_lede = __( 'The newest coverage from across the Syrian oil & gas sector.', 'syria-oil' );
}
?>

<div class="page-hero">
	<div class="container">
		<?php syria_oil_breadcrumb(); ?>
		<h1><?php echo esc_html( $hero_title ); ?></h1>
		<p><?php echo esc_html( $hero_lede ); ?></p>
	</div>
</div>

<section class="section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="grid-3">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'card' );
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 1,
					'prev_text' => __( '&laquo; Previous', 'syria-oil' ),
					'next_text' => __( 'Next &raquo;', 'syria-oil' ),
				)
			);
			?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
