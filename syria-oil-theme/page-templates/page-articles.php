<?php
/**
 * Template Name: مقالات (شبكة)
 * Articles listing — matches ministry-portal pages/blog.html
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

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
		echo $el_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor HTML.
	}

	if ( ! $is_elementor ) :
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
		<?php
	endif;

	if ( ! $has_widget_grid ) :
		?>
	<section class="section">
		<div class="container layout-sidebar">
			<div class="content-area">
				<?php
				$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
				$query = new WP_Query(
					array(
						'post_type'           => 'post',
						'post_status'         => 'publish',
						'posts_per_page'      => 12,
						'paged'               => $paged,
						'ignore_sticky_posts' => true,
						'meta_key'            => '_syria_oil_kind', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'meta_value'          => 'article', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					)
				);

				if ( ! $query->have_posts() ) {
					$query = new WP_Query(
						array(
							'post_type'           => 'post',
							'post_status'         => 'publish',
							'posts_per_page'      => 12,
							'paged'               => $paged,
							'ignore_sticky_posts' => true,
						)
					);
				}
				?>
				<?php if ( $query->have_posts() ) : ?>
					<div class="grid-2" id="blog-grid">
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
							'total'     => (int) $query->max_num_pages,
							'current'   => $paged,
							'type'      => 'list',
							'prev_text' => '&laquo;',
							'next_text' => '&raquo;',
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

			<aside class="sidebar" aria-label="<?php esc_attr_e( 'Sidebar', 'syria-oil' ); ?>">
				<div class="widget">
					<h3><?php esc_html_e( 'Latest news', 'syria-oil' ); ?></h3>
					<ul>
						<?php
						$side = new WP_Query(
							array(
								'posts_per_page' => 6,
								'no_found_rows'  => true,
							)
						);
						while ( $side->have_posts() ) :
							$side->the_post();
							printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( get_permalink() ), esc_html( get_the_title() ) );
						endwhile;
						wp_reset_postdata();
						?>
					</ul>
				</div>
			</aside>
		</div>
	</section>
		<?php
	endif;
endwhile;

get_footer();
