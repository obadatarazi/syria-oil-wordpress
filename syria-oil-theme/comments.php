<?php
/**
 * Comments template.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>

<section id="comments" class="comments-area section-sm">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			$syria_oil_comment_count = get_comments_number();
			printf(
				/* translators: 1: comment count number, 2: post title. */
				esc_html( _n( '%1$s comment on &ldquo;%2$s&rdquo;', '%1$s comments on &ldquo;%2$s&rdquo;', $syria_oil_comment_count, 'syria-oil' ) ),
				esc_html( number_format_i18n( $syria_oil_comment_count ) ),
				esc_html( get_the_title() )
			);
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
					'avatar_size'=> 48,
				)
			);
			?>
		</ol>

		<?php
		the_comments_navigation(
			array(
				'prev_text' => __( '&laquo; Older comments', 'syria-oil' ),
				'next_text' => __( 'Newer comments &raquo;', 'syria-oil' ),
			)
		);
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'syria-oil' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply' => __( 'Leave a comment', 'syria-oil' ),
		)
	);
	?>
</section>
