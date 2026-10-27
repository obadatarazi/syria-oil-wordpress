<?php
/**
 * Unified news card — thumbnail, badge, title (line-clamp), date.
 *
 * @package Syria_Oil_Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = isset( $syria_oil_news_card_args ) && is_array( $syria_oil_news_card_args ) ? $syria_oil_news_card_args : array();
$defaults = array(
	'variant'       => 'stack',
	'show_badge'    => true,
	'show_date'     => true,
	'show_excerpt'  => false,
	'excerpt_words' => 18,
	'image_size'    => 'syria-oil-card',
	'image_loading' => 'lazy',
	'title_lines'   => 2,
	'animate_attrs' => '',
	'extra_class'   => array(),
);
$a = wp_parse_args( $args, $defaults );

$permalink = get_permalink();
$title     = get_the_title();
$display   = $title ? $title : __( '(Untitled)', 'syria-oil-widgets' );
$url       = $permalink ? $permalink : '#';
$has_thumb = has_post_thumbnail();
$anim      = ! empty( $a['animate_attrs'] ) ? $a['animate_attrs'] : '';
$variant   = sanitize_key( (string) $a['variant'] );
$allowed   = array( 'stack', 'row', 'row-compact', 'featured' );
if ( ! in_array( $variant, $allowed, true ) ) {
	$variant = 'stack';
}

$classes = array( 'so-news-card', 'so-news-card--' . $variant );
if ( ! $has_thumb ) {
	$classes[] = 'so-news-card--no-media';
}
if ( ! empty( $a['extra_class'] ) ) {
	$extra   = is_array( $a['extra_class'] ) ? $a['extra_class'] : array( $a['extra_class'] );
	$classes = array_merge( $classes, $extra );
}

$title_lines = max( 1, min( 4, absint( $a['title_lines'] ) ) );
$alt_text    = $title ? $title : __( 'News image', 'syria-oil-widgets' );
$loading     = in_array( $a['image_loading'], array( 'lazy', 'eager' ), true ) ? $a['image_loading'] : 'lazy';
?>
<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo $anim; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<a class="so-news-card__media<?php echo $has_thumb ? '' : ' so-news-card__media--fallback'; ?>" href="<?php echo esc_url( $url ); ?>">
		<?php
		if ( $has_thumb ) {
			the_post_thumbnail(
				$a['image_size'],
				array(
					'loading'       => $loading,
					'decoding'      => 'async',
					'alt'           => $alt_text,
					'fetchpriority' => 'eager' === $loading ? 'high' : 'auto',
				)
			);
		}
		?>
	</a>

	<div class="so-news-card__body">
		<?php if ( ! empty( $a['show_badge'] ) || ! empty( $a['show_date'] ) ) : ?>
			<div class="so-news-card__meta">
				<?php
				if ( ! empty( $a['show_badge'] ) ) {
					$categories = get_the_category();
					if ( ! empty( $categories ) ) {
						printf(
							'<span class="so-news-card__badge">%s</span>',
							esc_html( $categories[0]->name )
						);
					}
				}
				if ( ! empty( $a['show_date'] ) ) {
					$ts = function_exists( 'syria_oil_get_post_timestamp' ) ? syria_oil_get_post_timestamp() : strtotime( get_post()->post_date );
					printf(
						'<time class="so-news-card__date" datetime="%1$s">%2$s</time>',
						esc_attr( wp_date( 'c', $ts, wp_timezone() ) ),
						esc_html( function_exists( 'syria_oil_format_post_date' ) ? syria_oil_format_post_date() : wp_date( 'j F Y', $ts, wp_timezone() ) )
					);
				}
				?>
			</div>
		<?php endif; ?>

		<h3 class="so-news-card__title so-news-card__title--lines-<?php echo esc_attr( (string) $title_lines ); ?>">
			<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $display ); ?></a>
		</h3>

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
				<p class="so-news-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</article>
