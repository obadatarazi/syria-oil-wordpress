<?php
/**
 * Article UX — share bar, font scale, continuous reading.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether article font-scale controls are enabled.
 *
 * @return bool
 */
function syria_oil_article_font_scale_enabled() {
	return function_exists( 'syria_oil_mod_enabled' ) ? syria_oil_mod_enabled( 'article_font_scale', true ) : true;
}

/**
 * Whether continuous reading is enabled.
 *
 * @return bool
 */
function syria_oil_continuous_reading_enabled() {
	return function_exists( 'syria_oil_mod_enabled' ) ? syria_oil_mod_enabled( 'continuous_reading', true ) : true;
}

/**
 * Share destinations for a post.
 *
 * @param int $post_id Post ID.
 * @return array<int,array<string,string>>
 */
function syria_oil_article_share_links( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$url     = rawurlencode( get_permalink( $post_id ) );
	$title   = rawurlencode( wp_strip_all_tags( get_the_title( $post_id ) ) );

	$links = array(
		array(
			'id'    => 'facebook',
			'label' => __( 'Facebook', 'syria-oil' ),
			'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . $url,
			'mark'  => 'f',
		),
		array(
			'id'    => 'x',
			'label' => __( 'X', 'syria-oil' ),
			'url'   => 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title,
			'mark'  => 'X',
		),
		array(
			'id'    => 'telegram',
			'label' => __( 'Telegram', 'syria-oil' ),
			'url'   => 'https://t.me/share/url?url=' . $url . '&text=' . $title,
			'mark'  => 'TG',
		),
		array(
			'id'    => 'linkedin',
			'label' => __( 'LinkedIn', 'syria-oil' ),
			'url'   => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $url,
			'mark'  => 'in',
		),
		array(
			'id'    => 'whatsapp',
			'label' => __( 'WhatsApp', 'syria-oil' ),
			'url'   => 'https://wa.me/?text=' . $title . '%20' . $url,
			'mark'  => 'WA',
		),
	);

	return apply_filters( 'syria_oil_article_share_links', $links, $post_id );
}

/**
 * Render share + print toolbar below article content.
 *
 * @param int $post_id Post ID.
 */
function syria_oil_the_article_share_bar( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$links   = syria_oil_article_share_links( $post_id );
	?>
	<div class="article-share-bar" aria-label="<?php esc_attr_e( 'Share this article', 'syria-oil' ); ?>">
		<span class="article-share-bar__label"><?php esc_html_e( 'Share', 'syria-oil' ); ?></span>
		<div class="article-share-bar__links">
			<?php foreach ( $links as $link ) : ?>
				<a class="article-share-btn article-share-btn--<?php echo esc_attr( $link['id'] ); ?>" href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $link['label'] ); ?>">
					<?php syria_oil_the_social_icon_svg( $link['id'] ); ?>
				</a>
			<?php endforeach; ?>
			<button type="button" class="article-share-btn article-share-btn--copy" data-copy-url="<?php echo esc_url( get_permalink( $post_id ) ); ?>" aria-label="<?php esc_attr_e( 'Copy link', 'syria-oil' ); ?>">
				<?php syria_oil_the_social_icon_svg( 'copy' ); ?>
			</button>
			<button type="button" class="article-share-btn article-share-btn--print" onclick="window.print()" aria-label="<?php esc_attr_e( 'Print', 'syria-oil' ); ?>">
				<?php syria_oil_the_social_icon_svg( 'print' ); ?>
			</button>
		</div>
	</div>
	<?php
}

/**
 * Font size controls for article prose.
 */
function syria_oil_the_article_font_controls() {
	if ( ! syria_oil_article_font_scale_enabled() ) {
		return;
	}
	?>
	<div class="article-font-controls" aria-label="<?php esc_attr_e( 'Text size', 'syria-oil' ); ?>">
		<button type="button" class="article-font-btn" data-font-step="-1" aria-label="<?php esc_attr_e( 'Decrease text size', 'syria-oil' ); ?>">A−</button>
		<button type="button" class="article-font-btn" data-font-step="0" aria-label="<?php esc_attr_e( 'Reset text size', 'syria-oil' ); ?>">A</button>
		<button type="button" class="article-font-btn" data-font-step="1" aria-label="<?php esc_attr_e( 'Increase text size', 'syria-oil' ); ?>">A+</button>
	</div>
	<?php
}

/**
 * REST: next post for continuous reading.
 */
function syria_oil_register_article_rest_routes() {
	register_rest_route(
		'syria-oil/v1',
		'/next-post/(?P<id>\d+)',
		array(
			'methods'             => 'GET',
			'callback'            => 'syria_oil_rest_next_posts',
			'permission_callback' => '__return_true',
			'args'                => array(
				'id'      => array(
					'validate_callback' => function ( $param ) {
						return is_numeric( $param ) && $param > 0;
					},
				),
				'count'   => array(
					'default'           => 1,
					'sanitize_callback' => 'absint',
				),
				'exclude' => array(
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'syria_oil_register_article_rest_routes' );

/**
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function syria_oil_rest_next_posts( $request ) {
	$post_id = absint( $request['id'] );
	$post    = get_post( $post_id );
	if ( ! $post || 'publish' !== $post->post_status ) {
		return new WP_Error( 'not_found', __( 'Post not found.', 'syria-oil' ), array( 'status' => 404 ) );
	}

	$count   = min( 10, max( 1, absint( $request->get_param( 'count' ) ) ) );
	$exclude = array( $post_id );
	$raw_ex  = (string) $request->get_param( 'exclude' );
	if ( $raw_ex ) {
		foreach ( explode( ',', $raw_ex ) as $maybe_id ) {
			$maybe_id = absint( trim( $maybe_id ) );
			if ( $maybe_id ) {
				$exclude[] = $maybe_id;
			}
		}
	}
	$exclude = array_values( array_unique( $exclude ) );

	$cats = wp_get_post_categories( $post_id );
	$args = array(
		'posts_per_page'      => $count,
		'post__not_in'        => $exclude,
		'ignore_sticky_posts' => true,
		'date_query'          => array(
			array(
				'before'    => $post->post_date,
				'inclusive' => false,
			),
		),
		'orderby'             => 'date',
		'order'               => 'DESC',
	);

	if ( $cats ) {
		$args['category__in'] = $cats;
	}

	$query = new WP_Query( $args );
	if ( ! $query->have_posts() ) {
		unset( $args['category__in'] );
		$query = new WP_Query( $args );
	}

	if ( ! $query->have_posts() ) {
		return rest_ensure_response(
			array(
				'html'     => '',
				'posts'    => array(),
				'has_more' => false,
			)
		);
	}

	$chunks = array();
	$meta   = array();
	while ( $query->have_posts() ) {
		$query->the_post();
		$current_id = get_the_ID();
		ob_start();
		?>
		<article class="article-root article-root--appended" data-post-id="<?php echo esc_attr( (string) $current_id ); ?>">
			<section class="article-top article-top--compact">
				<div class="container article-top-inner">
					<div class="article-meta-row">
						<?php syria_oil_category_badge(); ?>
						<?php syria_oil_posted_on(); ?>
					</div>
					<h2 class="article-appended-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				</div>
			</section>
			<section class="section article-main">
				<div class="container">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="article-featured"><?php the_post_thumbnail( 'syria-oil-hero' ); ?></div>
					<?php endif; ?>
					<div class="article-prose entry-content"><?php the_content(); ?></div>
					<?php syria_oil_the_article_share_bar( $current_id ); ?>
				</div>
			</section>
		</article>
		<?php
		$chunks[] = ob_get_clean();
		$meta[]   = array(
			'id'    => $current_id,
			'url'   => get_permalink(),
			'title' => get_the_title(),
		);
	}
	wp_reset_postdata();

	$last = end( $meta );
	$more = syria_oil_rest_has_more_posts( $last ? (int) $last['id'] : $post_id, $exclude );

	return rest_ensure_response(
		array(
			'html'     => implode( '', $chunks ),
			'posts'    => $meta,
			'has_more' => $more,
			'url'      => $last ? $last['url'] : '',
			'title'    => $last ? $last['title'] : '',
			'id'       => $last ? $last['id'] : 0,
		)
	);
}

/**
 * Whether another post exists before the anchor post.
 *
 * @param int   $post_id Anchor post ID.
 * @param int[] $exclude Post IDs to skip.
 * @return bool
 */
function syria_oil_rest_has_more_posts( $post_id, $exclude = array() ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return false;
	}

	$exclude = array_values( array_unique( array_merge( array( $post_id ), array_map( 'absint', (array) $exclude ) ) ) );
	$cats    = wp_get_post_categories( $post_id );
	$args    = array(
		'posts_per_page'      => 1,
		'post__not_in'        => $exclude,
		'ignore_sticky_posts' => true,
		'fields'              => 'ids',
		'date_query'          => array(
			array(
				'before'    => $post->post_date,
				'inclusive' => false,
			),
		),
		'orderby'             => 'date',
		'order'               => 'DESC',
	);

	if ( $cats ) {
		$args['category__in'] = $cats;
	}

	$query = new WP_Query( $args );
	if ( $query->have_posts() ) {
		return true;
	}

	unset( $args['category__in'] );
	$query = new WP_Query( $args );
	return $query->have_posts();
}

/**
 * Enqueue article tools on single posts.
 */
function syria_oil_enqueue_article_tools() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	wp_enqueue_style(
		'syria-oil-article-tools',
		SYRIA_OIL_URI . '/assets/css/article-tools.css',
		array( 'syria-oil-theme' ),
		syria_oil_asset_version( 'assets/css/article-tools.css' )
	);

	wp_enqueue_script(
		'syria-oil-article-tools',
		SYRIA_OIL_URI . '/assets/js/article-tools.js',
		array(),
		syria_oil_asset_version( 'assets/js/article-tools.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	wp_localize_script(
		'syria-oil-article-tools',
		'syriaOilArticle',
		array(
			'restUrl'           => rest_url( 'syria-oil/v1/next-post/' ),
			'continuousReading' => syria_oil_continuous_reading_enabled(),
			'fontScale'         => syria_oil_article_font_scale_enabled(),
			'copyDone'          => __( 'Link copied', 'syria-oil' ),
			'loadCount'         => 5,
			'loadMoreLabel'     => __( 'Load 5 more articles', 'syria-oil' ),
			'loadMoreDone'      => __( 'No more articles', 'syria-oil' ),
			'loadingLabel'      => __( 'Loading…', 'syria-oil' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'syria_oil_enqueue_article_tools', 35 );
