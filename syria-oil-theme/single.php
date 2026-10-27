<?php
/**
 * Single post template — matches ministry-portal article-view.html
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'single' ) ) {
	return;
}

get_header();

while ( have_posts() ) :
	the_post();
	$source   = get_post_meta( get_the_ID(), '_syria_oil_source_url', true );
	$post_id  = get_the_ID();
	?>
	<article <?php post_class( 'article-root' ); ?> id="post-<?php echo esc_attr( (string) $post_id ); ?>" data-post-id="<?php echo esc_attr( (string) $post_id ); ?>">
		<section class="article-top">
			<div class="container article-top-inner">
				<?php syria_oil_breadcrumb(); ?>
				<div class="article-meta-row" id="article-meta">
					<?php syria_oil_category_badge(); ?>
					<?php syria_oil_posted_on(); ?>
					<span><?php echo esc_html( get_the_author() ); ?></span>
				</div>
				<h1 id="article-title"><?php the_title(); ?></h1>
				<div class="article-tools">
					<div class="article-tools__primary">
						<button class="btn btn-secondary btn-sm" type="button" onclick="window.print()"><?php esc_html_e( 'Print', 'syria-oil' ); ?></button>
						<?php if ( $source ) : ?>
							<a class="btn btn-secondary btn-sm" href="<?php echo esc_url( $source ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Source', 'syria-oil' ); ?></a>
						<?php endif; ?>
					</div>
					<?php if ( function_exists( 'syria_oil_the_article_font_controls' ) ) : ?>
						<?php syria_oil_the_article_font_controls(); ?>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<section class="section article-main">
			<div class="container layout-sidebar">
				<div class="content-area">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="article-featured">
							<?php
							the_post_thumbnail(
								'syria-oil-hero',
								array(
									'alt'           => the_title_attribute( array( 'echo' => false ) ),
									'loading'       => 'eager',
									'fetchpriority' => 'high',
									'decoding'      => 'async',
								)
							);
							?>
						</div>
					<?php endif; ?>

					<div class="article-prose entry-content">
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

					<?php if ( function_exists( 'syria_oil_the_article_share_bar' ) ) : ?>
						<?php syria_oil_the_article_share_bar( $post_id ); ?>
					<?php endif; ?>

					<div class="article-end">
						<a class="btn btn-secondary" href="<?php echo esc_url( home_url( '/news/' ) ); ?>"><?php esc_html_e( 'Back to news', 'syria-oil' ); ?></a>
						<?php if ( $source ) : ?>
							<a class="btn btn-primary" href="<?php echo esc_url( $source ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View on syria-oil.com', 'syria-oil' ); ?></a>
						<?php endif; ?>
					</div>

					<?php if ( function_exists( 'syria_oil_continuous_reading_enabled' ) && syria_oil_continuous_reading_enabled() ) : ?>
						<div class="article-continuous-controls">
							<button type="button" class="btn btn-secondary article-load-more" data-post-id="<?php echo esc_attr( (string) $post_id ); ?>">
								<?php esc_html_e( 'Load 5 more articles', 'syria-oil' ); ?>
							</button>
						</div>
					<?php endif; ?>

					<?php
					$related = new WP_Query(
						array(
							'posts_per_page'         => 3,
							'post__not_in'           => array( $post_id ),
							'ignore_sticky_posts'    => true,
							'no_found_rows'          => true,
							'category__in'           => wp_list_pluck( get_the_category(), 'term_id' ),
						)
					);
					if ( $related->have_posts() ) :
						?>
						<div class="related-block">
							<h2><?php esc_html_e( 'Related', 'syria-oil' ); ?></h2>
							<div class="grid-3">
								<?php
								while ( $related->have_posts() ) :
									$related->the_post();
									get_template_part( 'template-parts/content', 'card' );
								endwhile;
								wp_reset_postdata();
								?>
							</div>
						</div>
					<?php endif; ?>

					<?php
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</div>

				<aside class="sidebar article-sidebar" aria-label="<?php esc_attr_e( 'Article sidebar', 'syria-oil' ); ?>">
					<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
						<?php dynamic_sidebar( 'sidebar-1' ); ?>
					<?php else : ?>
						<div class="widget widget--latest-news">
							<h3><?php esc_html_e( 'Latest news', 'syria-oil' ); ?></h3>
							<ul class="article-sidebar-list">
								<?php
								$recent = new WP_Query(
									array(
										'posts_per_page'         => 6,
										'post__not_in'           => array( $post_id ),
										'ignore_sticky_posts'    => true,
										'no_found_rows'          => true,
										'update_post_meta_cache' => false,
										'update_post_term_cache' => false,
									)
								);
								while ( $recent->have_posts() ) :
									$recent->the_post();
									printf(
										'<li><a href="%1$s"><span class="article-sidebar-list__title">%2$s</span><time class="article-sidebar-list__date" datetime="%3$s">%4$s</time></a></li>',
										esc_url( get_permalink() ),
										esc_html( wp_trim_words( get_the_title(), 14, '…' ) ),
										esc_attr( get_the_date( 'c' ) ),
										esc_html( function_exists( 'syria_oil_format_post_date' ) ? syria_oil_format_post_date( get_the_ID() ) : get_the_date( 'j M Y' ) )
									);
								endwhile;
								wp_reset_postdata();
								?>
							</ul>
						</div>
					<?php endif; ?>
				</aside>
			</div>
		</section>
	</article>
	<?php if ( function_exists( 'syria_oil_continuous_reading_enabled' ) && syria_oil_continuous_reading_enabled() ) : ?>
		<div class="article-continuous-stream" aria-live="polite"></div>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
