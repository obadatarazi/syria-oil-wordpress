<?php
/**
 * Default loop content template part.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'content-entry' ); ?>>
	<header class="entry-header">
		<?php
		if ( is_singular() ) {
			the_title( '<h1 class="entry-title">', '</h1>' );
		} else {
			the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
		}
		?>
		<div class="entry-meta">
			<?php syria_oil_posted_on(); ?>
			<?php syria_oil_category_badge(); ?>
		</div>
	</header>

	<?php if ( has_post_thumbnail() && ! is_singular() ) : ?>
		<div class="entry-media">
			<a href="<?php the_permalink(); ?>">
				<?php
				the_post_thumbnail(
					'syria-oil-card',
					array(
						'loading'  => 'lazy',
						'decoding' => 'async',
					)
				);
				?>
			</a>
		</div>
	<?php endif; ?>

	<div class="entry-content">
		<?php
		if ( is_singular() ) {
			the_content();
			wp_link_pages(
				array(
					'before' => '<nav class="page-links">' . esc_html__( 'Pages:', 'syria-oil' ),
					'after'  => '</nav>',
				)
			);
		} else {
			echo '<p>' . esc_html( syria_oil_get_excerpt( 40 ) ) . '</p>';
			echo '<a class="btn btn-secondary btn-sm" href="' . esc_url( get_permalink() ) . '">' . esc_html__( 'Read more', 'syria-oil' ) . '</a>';
		}
		?>
	</div>
</article>
