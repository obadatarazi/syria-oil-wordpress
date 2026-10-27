<?php
/**
 * Shared article card markup.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$syria_oil_args = get_query_var( 'syria_oil_card_args' );
$defaults       = array(
	'show_image'     => true,
	'show_badge'     => true,
	'show_date'      => true,
	'show_excerpt'   => true,
	'show_more'      => true,
	'show_caption'   => false,
	'excerpt_words'  => 24,
	'image_size'     => 'syria-oil-card',
	'read_more_text' => '',
);
$a = wp_parse_args( is_array( $syria_oil_args ) ? $syria_oil_args : array(), $defaults );
if ( '' === $a['read_more_text'] ) {
	$a['read_more_text'] = __( 'Read more', 'syria-oil' );
}

$permalink = get_permalink();
$title     = get_the_title();
$display   = $title ? $title : __( '(Untitled)', 'syria-oil' );
$url       = $permalink ? $permalink : '#';
$has_thumb = ! empty( $a['show_image'] ) && has_post_thumbnail();
$anim      = ! empty( $a['animate_attrs'] ) ? $a['animate_attrs'] : ' data-sor-animate="fade-up"';
?>
<article <?php post_class( array( 'card', $has_thumb ? '' : 'card--no-media' ) ); ?><?php echo $anim; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( ! empty( $a['show_image'] ) ) : ?>
		<a class="card-media<?php echo $has_thumb ? '' : ' card-media--fallback'; ?>" href="<?php echo esc_url( $url ); ?>">
			<?php
			if ( $has_thumb ) {
				the_post_thumbnail(
					$a['image_size'],
					array(
						'loading'  => 'lazy',
						'decoding' => 'async',
						'alt'      => $title ? $title : '',
					)
				);
			}
			?>
			<?php if ( ! empty( $a['show_caption'] ) ) : ?>
				<span class="card-media-caption"><?php echo esc_html( $display ); ?></span>
			<?php endif; ?>
		</a>
	<?php endif; ?>

	<div class="card-body">
		<div class="card-meta">
			<?php syria_oil_category_badge( null, false ); ?>
			<?php if ( ! empty( $a['show_date'] ) ) : ?>
				<?php syria_oil_posted_on(); ?>
			<?php endif; ?>
		</div>

		<?php if ( empty( $a['show_caption'] ) ) : ?>
			<h3><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $display ); ?></a></h3>
		<?php endif; ?>

		<?php if ( ! empty( $a['show_excerpt'] ) ) : ?>
			<?php
			$excerpt = syria_oil_get_excerpt( absint( $a['excerpt_words'] ) );
			if ( $excerpt && $title ) {
				$title_start   = function_exists( 'mb_substr' ) ? mb_substr( wp_strip_all_tags( $title ), 0, 48 ) : substr( wp_strip_all_tags( $title ), 0, 48 );
				$excerpt_plain = wp_strip_all_tags( $excerpt );
				if ( 0 === stripos( $excerpt_plain, $title_start ) ) {
					$excerpt = '';
				}
			}
			if ( $excerpt ) :
				?>
				<p class="text-muted card-excerpt"><?php echo esc_html( $excerpt ); ?></p>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( ! empty( $a['show_more'] ) ) : ?>
			<a class="btn btn-secondary btn-sm card-more" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $a['read_more_text'] ); ?></a>
		<?php endif; ?>
	</div>
</article>
