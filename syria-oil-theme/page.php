<?php
/**
 * Page template — ministry-portal style page-hero + section content.
 *
 * News / Articles slugs always get a post grid (even if Elementor only
 * printed a short intro), so /news/ and /articles/ are never empty.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$slug         = get_post_field( 'post_name', get_the_ID() );
	$listing_slug = in_array( $slug, array( 'news', 'articles', 'blog', 'اخبار', 'الأخبار', 'مقالات', 'المقالات' ), true );
	$is_elementor = (
		class_exists( '\Elementor\Plugin' )
		&& \Elementor\Plugin::$instance->documents->get( get_the_ID() )
		&& \Elementor\Plugin::$instance->documents->get( get_the_ID() )->is_built_with_elementor()
	);

	$el_html         = '';
	$has_widget_grid = false;
	if ( $is_elementor ) {
		ob_start();
		the_content();
		$el_html         = ob_get_clean();
		$has_widget_grid = ( false !== strpos( $el_html, 'so-card-grid' ) || false !== strpos( $el_html, 'elementor-widget-syria_oil' ) );
		echo $el_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		?>
		<div class="page-hero">
			<div class="container">
				<?php syria_oil_breadcrumb(); ?>
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( ! $listing_slug ) : ?>
		<section class="section">
			<div class="container">
				<div class="entry-content portal-page-content">
					<?php
					the_content();
					wp_link_pages(
						array(
							'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'syria-oil' ),
							'after'  => '</div>',
						)
					);
					?>
				</div>
				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</div>
		</section>
		<?php endif; ?>
		<?php
	}

	if ( $listing_slug && ! $has_widget_grid ) :
		$is_articles = in_array( $slug, array( 'articles', 'blog', 'مقالات', 'المقالات' ), true );
		$paged       = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		$args        = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 12,
			'paged'               => $paged,
			'ignore_sticky_posts' => true,
		);
		if ( $is_articles ) {
			$args['meta_key']   = '_syria_oil_kind'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['meta_value'] = 'article'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		}
		$query = new WP_Query( $args );
		if ( $is_articles && ! $query->have_posts() ) {
			unset( $args['meta_key'], $args['meta_value'] );
			$query = new WP_Query( $args );
		}
		?>
		<section class="section">
			<div class="container">
				<?php if ( $query->have_posts() ) : ?>
					<div class="grid-2">
						<?php
						while ( $query->have_posts() ) :
							$query->the_post();
							get_template_part( 'template-parts/content', 'card' );
						endwhile;
						?>
					</div>
					<?php
					$pagination = paginate_links(
						array(
							'total'   => (int) $query->max_num_pages,
							'current' => $paged,
							'type'    => 'list',
						)
					);
					if ( $pagination ) {
						echo wp_kses_post( $pagination );
					}
					wp_reset_postdata();
					?>
				<?php else : ?>
					<?php get_template_part( 'template-parts/content', 'none' ); ?>
				<?php endif; ?>
			</div>
		</section>
		<?php
	endif;
endwhile;

get_footer();
