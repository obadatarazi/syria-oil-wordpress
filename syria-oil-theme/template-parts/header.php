<?php
/**
 * Classic site header chrome (top bar + single-row site header + nav).
 *
 * Called from header.php when Elementor Pro has no Theme Builder header.
 *
 * Mobile hierarchy: menu toggle | logo (center) | search (+ lang).
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show_top_bar   = (bool) syria_oil_mod( 'show_top_bar', true );
$top_bar_text   = syria_oil_mod( 'top_bar_text' );
$show_date      = (bool) syria_oil_mod( 'show_top_bar_date', true );
$search_ph      = syria_oil_mod( 'search_placeholder' );
if ( '' === $search_ph ) {
	$search_ph = __( 'Search the news…', 'syria-oil' );
}

$header_style = syria_oil_mod( 'header_style', 'standard' );
$use_dark     = ( 'transparent_over_hero' === $header_style );
?>

<?php if ( $show_top_bar && ( $top_bar_text || $show_date ) ) : ?>
<div class="top-bar">
	<div class="container top-bar-inner">
		<div class="top-meta">
			<?php if ( $top_bar_text ) : ?>
				<span><?php echo esc_html( $top_bar_text ); ?></span>
			<?php endif; ?>
			<?php if ( $show_date ) : ?>
				<span><?php echo esc_html( function_exists( 'syria_oil_format_timestamp' ) ? syria_oil_format_timestamp( time() ) : date_i18n( get_option( 'date_format' ) ) ); ?></span>
			<?php endif; ?>
		</div>
		<?php if ( function_exists( 'syria_oil_render_header_social_links' ) ) : ?>
			<?php syria_oil_render_header_social_links(); ?>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>

<div class="site-chrome">
<header class="site-header<?php echo $use_dark ? ' has-dark-header' : ''; ?>">
	<div class="container header-main">
		<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" id="nav-toggle" aria-label="<?php esc_attr_e( 'Open menu', 'syria-oil' ); ?>">
			<span></span><span></span><span></span>
		</button>

		<div class="brand-group">
			<?php
			/* Always render default (with optional mobile sibling). Do not use
			 * wp_is_mobile() — Elementor/desktop narrow viewports would hide the logo. */
			syria_oil_the_brand_logo( $use_dark ? 'dark' : 'default' );
			?>
		</div>

		<?php /* Desktop: centered in the row. Mobile: off-canvas drawer (reparented in navigation.js). */ ?>
		<div class="nav-wrap" id="primary-nav">
			<div class="nav-drawer-brand">
				<?php syria_oil_the_drawer_logo(); ?>
				<button class="nav-drawer-close" type="button" data-nav-close aria-label="<?php esc_attr_e( 'Close menu', 'syria-oil' ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location'       => 'primary',
						'container'            => 'nav',
						'container_class'      => 'main-nav',
						'container_aria_label' => __( 'Primary menu', 'syria-oil' ),
						'menu_class'           => 'menu',
						'depth'                => 3,
						'fallback_cb'          => false,
					)
				);
			} else {
				echo '<nav class="main-nav" aria-label="' . esc_attr__( 'Primary menu', 'syria-oil' ) . '"><ul class="menu">';
				wp_list_pages( array( 'title_li' => '', 'depth' => 1 ) );
				echo '</ul></nav>';
			}
			?>
		</div>

		<div class="header-tools">
			<?php if ( function_exists( 'syria_oil_render_header_social_links' ) ) : ?>
				<?php syria_oil_render_header_social_links(); ?>
			<?php endif; ?>
			<?php
			if ( function_exists( 'syria_oil_lang_toggle_markup' ) ) {
				echo syria_oil_lang_toggle_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
			}
			?>

			<form class="header-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="sr-only" for="site-search"><?php esc_html_e( 'Search the news', 'syria-oil' ); ?></label>
				<input id="site-search" name="s" type="search" placeholder="<?php echo esc_attr( $search_ph ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" />
				<button class="btn btn-primary btn-sm header-search__submit" type="submit"><?php esc_html_e( 'Search', 'syria-oil' ); ?></button>
				<button class="header-search__icon" type="button" aria-label="<?php esc_attr_e( 'Search', 'syria-oil' ); ?>" data-search-toggle>
					<span aria-hidden="true">⌕</span>
				</button>
			</form>
		</div>
	</div>
</header>
</div><!-- .site-chrome -->

<button class="nav-backdrop" id="nav-backdrop" type="button" aria-label="<?php esc_attr_e( 'Close menu', 'syria-oil' ); ?>" hidden></button>
