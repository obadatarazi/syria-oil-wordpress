<?php
/**
 * Site footer.
 *
 * All user-facing strings, contact details, and social URLs come from
 * theme_mods (Customizer). Elementor Pro Theme Builder can override this
 * location entirely.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main><!-- #main -->
	<?php
	if ( function_exists( 'syria_oil_render_basic_ads' ) ) {
		syria_oil_render_basic_ads();
	}
	?>
	</div><!-- .site-shell__main -->
	<?php
	if ( function_exists( 'syria_oil_render_ad_rail' ) ) {
		syria_oil_render_ad_rail( 'right' );
	}
	?>
</div><!-- .site-shell -->

<?php
$syria_oil_el_footer = ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'footer' ) );
if ( ! $syria_oil_el_footer ) {
	get_template_part( 'template-parts/footer' );
}
?>

<?php
$syria_oil_classic_header = ! empty( $GLOBALS['syria_oil_classic_header'] );
$syria_oil_has_bottom     = has_nav_menu( 'mobile_bottom' );
$syria_oil_has_primary    = has_nav_menu( 'primary' );

/*
 * When Elementor Theme Builder owns the header, classic #primary-nav is missing
 * and the bottom-bar “More” button would do nothing. Render a lightweight
 * off-canvas primary menu so mobile navigation still works.
 */
if ( $syria_oil_has_bottom && ! $syria_oil_classic_header && $syria_oil_has_primary ) :
	?>
	<button class="nav-backdrop" id="nav-backdrop" type="button" aria-label="<?php esc_attr_e( 'Close menu', 'syria-oil' ); ?>" hidden></button>
	<nav id="primary-nav" class="nav-wrap syria-oil-nav-fallback" aria-label="<?php esc_attr_e( 'Primary', 'syria-oil' ); ?>">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'menu_class'     => 'main-nav menu',
				'container'      => 'div',
				'container_class'=> 'container main-nav',
				'fallback_cb'    => false,
				'depth'          => 2,
			)
		);
		?>
	</nav>
	<?php
endif;
?>

<?php if ( $syria_oil_has_bottom ) : ?>
	<nav class="bottom-nav" aria-label="<?php esc_attr_e( 'Mobile navigation', 'syria-oil' ); ?>">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'mobile_bottom',
				'container'      => false,
				'items_wrap'     => '%3$s',
				'depth'          => 1,
				'fallback_cb'    => false,
				'walker'         => class_exists( 'Syria_Oil_Bottom_Nav_Walker' ) ? new Syria_Oil_Bottom_Nav_Walker() : null,
			)
		);
		?>
		<?php if ( $syria_oil_classic_header || $syria_oil_has_primary ) : ?>
			<button type="button" id="nav-toggle-bottom" aria-controls="primary-nav" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open menu', 'syria-oil' ); ?>">
				<span class="bn-ico" aria-hidden="true">≡</span>
				<span class="bn-label"><?php esc_html_e( 'More', 'syria-oil' ); ?></span>
			</button>
		<?php endif; ?>
	</nav>
<?php endif; ?>

<?php if ( syria_oil_mod( 'show_back_to_top', true ) ) : ?>
	<button type="button" class="syria-oil-back-to-top" id="syria-oil-back-to-top" aria-label="<?php esc_attr_e( 'Back to top', 'syria-oil' ); ?>">
		<span aria-hidden="true">↑</span>
	</button>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
