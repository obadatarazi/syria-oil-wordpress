<?php
/**
 * "Nothing found" fallback markup.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="no-results">
	<div class="alert alert-info">
		<?php if ( is_search() ) : ?>
			<p><?php esc_html_e( 'No results matched your search. Try different keywords.', 'syria-oil' ); ?></p>
		<?php else : ?>
			<p><?php esc_html_e( 'No content found here yet. Please check back soon.', 'syria-oil' ); ?></p>
		<?php endif; ?>
	</div>
	<?php get_search_form(); ?>
</section>
