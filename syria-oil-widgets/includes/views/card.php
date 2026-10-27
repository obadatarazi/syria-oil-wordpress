<?php
/**
 * Plugin-owned article card view (theme-independent).
 *
 * @package Syria_Oil_Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = isset( $syria_oil_card_args ) && is_array( $syria_oil_card_args ) ? $syria_oil_card_args : array();
$defaults = array(
	'show_image'     => true,
	'show_badge'     => true,
	'show_date'      => true,
	'show_excerpt'   => true,
	'show_more'      => true,
	'show_caption'   => false,
	'excerpt_words'  => 24,
	'image_size'     => 'medium_large',
	'read_more_text' => __( 'Read more', 'syria-oil-widgets' ),
);
$a = wp_parse_args( $args, $defaults );

if ( function_exists( 'syria_oil_widgets_localize_button' ) ) {
	$a['read_more_text'] = syria_oil_widgets_localize_button( $a['read_more_text'] );
}

$permalink = get_permalink();
$title     = get_the_title();
$display   = $title ? $title : __( '(Untitled)', 'syria-oil-widgets' );
$url       = $permalink ? $permalink : '#';
$has_thumb = ! empty( $a['show_image'] ) && has_post_thumbnail();
$anim      = ! empty( $a['animate_attrs'] ) ? $a['animate_attrs'] : '';
$classes   = array( 'card' );
if ( ! $has_thumb ) {
	$classes[] = 'card--no-media';
}
if ( ! empty( $a['extra_class'] ) ) {
	$extra = is_array( $a['extra_class'] ) ? $a['extra_class'] : array( $a['extra_class'] );
	$classes = array_merge( $classes, $extra );
}
?>
<article <?php post_class( $classes ); ?><?php echo $anim; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
			<?php
			$categories = get_the_category();
			if ( ! empty( $categories ) ) {
				printf(
					'<span class="badge">%s</span>',
					esc_html( $categories[0]->name )
				);
			}
			if ( ! empty( $a['show_date'] ) ) {
				if ( function_exists( 'syria_oil_posted_on' ) ) {
					syria_oil_posted_on();
				} else {
					$ts = function_exists( 'syria_oil_get_post_timestamp' ) ? syria_oil_get_post_timestamp() : strtotime( get_post()->post_date );
					printf(
						'<time class="posted-on" datetime="%1$s">%2$s</time>',
						esc_attr( wp_date( 'c', $ts, wp_timezone() ) ),
						esc_html( function_exists( 'syria_oil_format_post_date' ) ? syria_oil_format_post_date() : wp_date( 'j F Y', $ts, wp_timezone() ) )
					);
				}
			}
			?>
		</div>

		<?php if ( empty( $a['show_caption'] ) ) : ?>
			<h3><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $display ); ?></a></h3>
		<?php endif; ?>

		<?php if ( ! empty( $a['show_excerpt'] ) ) : ?>
			<?php
			$excerpt = wp_trim_words( get_the_excerpt(), absint( $a['excerpt_words'] ), '…' );
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
