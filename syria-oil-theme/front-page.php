<?php
/**
 * Front page — ministry-portal style homepage.
 *
 * If the static front page was built with Elementor, render that layout.
 * While Elementor is open (edit/preview), always call the_content() so the
 * builder can attach. Otherwise show the composed homepage (hero + latest
 * news) when Elementor content is empty, so placeholder text never becomes
 * the whole site home.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$use_elementor = false;
$has_elementor = class_exists( '\Elementor\Plugin' );
$plugin        = $has_elementor ? \Elementor\Plugin::$instance : null;

// Elementor editor/preview must always hit the_content() or it shows
// “content area was not found” on the static front page.
$elementor_editing = (
	$plugin
	&& (
		( isset( $plugin->preview ) && $plugin->preview->is_preview_mode() )
		|| ( isset( $plugin->editor ) && method_exists( $plugin->editor, 'is_edit_mode' ) && $plugin->editor->is_edit_mode() )
	)
);

if ( is_page() && have_posts() ) {
	the_post();
	$page_id = get_the_ID();

	$built_with_elementor = (
		$plugin
		&& $plugin->documents->get( $page_id )
		&& $plugin->documents->get( $page_id )->is_built_with_elementor()
	);

	if ( $elementor_editing || $built_with_elementor ) {
		if ( $elementor_editing ) {
			// Canvas for the builder — never fall back to the classic homepage.
			the_content();
			$use_elementor = true;
		} else {
			ob_start();
			the_content();
			$el_raw  = ob_get_clean();
			$el_text = trim( wp_strip_all_tags( (string) $el_raw ) );
			// Empty / near-empty Elementor pages fall back to the classic homepage on the front.
			if ( '' !== $el_text && strlen( $el_text ) > 20 ) {
				$use_elementor = true;
				echo $el_raw; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}
	rewind_posts();
}

if ( ! $use_elementor ) :
	$news_page = get_page_by_path( 'news' );
	$news_url  = $news_page ? get_permalink( $news_page ) : home_url( '/news/' );

	$latest = new WP_Query(
		array(
			'posts_per_page'         => 7,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => true,
		)
	);

	if ( $latest->have_posts() ) :
		$latest->the_post();
		$hero_id = get_post_thumbnail_id();
		$cats    = get_the_category();
		$cat_name = ! empty( $cats[0] ) ? $cats[0]->name : __( 'Featured', 'syria-oil' );
		?>
		<section class="home-hero">
			<?php if ( $hero_id ) : ?>
				<div class="home-hero-media">
					<?php
					$data = syria_oil_get_attachment_image_data(
						$hero_id,
						'syria-oil-hero',
						array(
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'decoding'      => 'async',
							'alt'           => get_the_title(),
						)
					);
					if ( $data ) {
						syria_oil_print_img( $data['attrs'] );
					}
					?>
				</div>
			<?php else : ?>
				<div class="home-hero-media home-hero-media--fallback" aria-hidden="true"></div>
			<?php endif; ?>
			<div class="home-hero-overlay"></div>
			<div class="container home-hero-content">
				<div class="flag-line" aria-hidden="true"><span></span><span></span><span></span></div>
				<p class="hero-kicker"><?php echo esc_html( $cat_name . ' · ' . ( function_exists( 'syria_oil_format_post_date' ) ? syria_oil_format_post_date() : get_the_date() ) ); ?></p>
				<h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
				<p class="lede"><?php echo esc_html( syria_oil_get_excerpt( 40 ) ); ?></p>
				<div class="hero-actions">
					<a class="btn btn-primary btn-lg" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read the story', 'syria-oil' ); ?></a>
					<a class="btn btn-ghost btn-lg" href="#latest"><?php esc_html_e( 'Latest news', 'syria-oil' ); ?></a>
				</div>
			</div>
		</section>
		<?php
	else :
		?>
		<section class="home-hero home-hero--empty">
			<div class="home-hero-overlay"></div>
			<div class="container home-hero-content">
				<div class="flag-line" aria-hidden="true"><span></span><span></span><span></span></div>
				<h1><?php bloginfo( 'name' ); ?></h1>
				<p class="lede"><?php bloginfo( 'description' ); ?></p>
				<div class="hero-actions">
					<a class="btn btn-primary btn-lg" href="<?php echo esc_url( $news_url ); ?>"><?php esc_html_e( 'Latest news', 'syria-oil' ); ?></a>
				</div>
			</div>
		</section>
		<?php
	endif;
	?>

	<section class="section" id="latest">
		<div class="container">
			<div class="section-head">
				<div>
					<div class="eyebrow"><?php esc_html_e( 'Newsroom', 'syria-oil' ); ?></div>
					<h2><?php esc_html_e( 'Latest news', 'syria-oil' ); ?></h2>
				</div>
				<a class="btn btn-secondary" href="<?php echo esc_url( $news_url ); ?>"><?php esc_html_e( 'All news', 'syria-oil' ); ?></a>
			</div>

			<?php if ( $latest->have_posts() || $latest->post_count > 0 ) : ?>
				<div class="grid-3">
					<?php
					$latest->rewind_posts();
					while ( $latest->have_posts() ) :
						$latest->the_post();
						get_template_part( 'template-parts/content', 'card' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			<?php else : ?>
				<p class="text-muted"><?php esc_html_e( 'No posts yet. Run Appearance → Syria Oil Setup to import news.', 'syria-oil' ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<?php
	$articles = new WP_Query(
		array(
			'posts_per_page'         => 3,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'meta_key'               => '_syria_oil_kind', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'             => 'article', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	$articles_page = get_page_by_path( 'articles' );
	$articles_url  = $articles_page ? get_permalink( $articles_page ) : home_url( '/articles/' );

	if ( $articles->have_posts() ) :
		?>
		<section class="section section--muted">
			<div class="container">
				<div class="section-head">
					<div>
						<div class="eyebrow"><?php esc_html_e( 'Analysis', 'syria-oil' ); ?></div>
						<h2><?php esc_html_e( 'Articles', 'syria-oil' ); ?></h2>
					</div>
					<a class="btn btn-secondary" href="<?php echo esc_url( $articles_url ); ?>"><?php esc_html_e( 'All articles', 'syria-oil' ); ?></a>
				</div>
				<div class="grid-3">
					<?php
					while ( $articles->have_posts() ) :
						$articles->the_post();
						get_template_part( 'template-parts/content', 'card' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
		<?php
	endif;
endif;

get_footer();
