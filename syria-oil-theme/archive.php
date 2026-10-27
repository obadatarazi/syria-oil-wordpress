<?php
/**
 * Archive template (categories, tags, taxonomies, dates, author).
 *
 * Responsive .grid-3 of cards with pagination. Elementor Pro can override the
 * archive location; this is the classic fallback.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'archive' ) ) {
	return;
}

get_header();
?>

<div class="page-hero">
	<div class="container">
		<?php syria_oil_breadcrumb(); ?>
		<h1><?php the_archive_title(); ?></h1>
		<?php if ( get_the_archive_description() ) : ?>
			<div class="archive-description"><?php the_archive_description(); ?></div>
		<?php endif; ?>
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
			the_posts_pagination( array(
				'mid_size'  => 1,
				'prev_text' => __( '&laquo; Previous', 'syria-oil' ),
				'next_text' => __( 'Next &raquo;', 'syria-oil' ),
			) );
			?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
