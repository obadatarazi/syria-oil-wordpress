<?php
/**
 * Search results template.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Elementor Pro often maps search to archive Theme Builder conditions.
if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'archive' ) ) {
	return;
}

get_header();
?>

<div class="page-hero">
	<div class="container">
		<?php syria_oil_breadcrumb(); ?>
		<h1>
			<?php
			/* translators: %s: search query. */
			printf( esc_html__( 'Search results for: %s', 'syria-oil' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
			?>
		</h1>
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
