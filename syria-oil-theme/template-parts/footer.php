<?php
/**
 * Classic site footer chrome.
 *
 * Called from footer.php when Elementor Pro has no Theme Builder footer.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tagline       = syria_oil_mod( 'footer_tagline' );
$explore_title = syria_oil_mod( 'footer_explore_title' );
$cats_title    = syria_oil_mod( 'footer_cats_title' );
$contact_title = syria_oil_mod( 'footer_contact_title' );
$bottom_text   = syria_oil_mod( 'footer_bottom_text' );
$email         = syria_oil_mod( 'contact_email' );
$phone         = syria_oil_mod( 'contact_phone' );
$contact_url   = syria_oil_mod( 'contact_page_url' );
$about_url     = syria_oil_mod( 'about_page_url' );
$footer_cols   = (string) syria_oil_mod( 'footer_columns', '3' );
$col_count     = ( '4' === $footer_cols ) ? 4 : 3;
$sponsors_raw  = (string) syria_oil_mod( 'footer_sponsors', '' );
$sponsors_title = syria_oil_mod( 'footer_sponsors_title' );

if ( '' === $explore_title ) {
	$explore_title = __( 'Explore', 'syria-oil' );
}
if ( '' === $cats_title ) {
	$cats_title = __( 'Categories', 'syria-oil' );
}
if ( '' === $contact_title ) {
	$contact_title = __( 'Contact', 'syria-oil' );
}
if ( '' === $sponsors_title ) {
	$sponsors_title = __( 'الشركات الداعمة والراعية', 'syria-oil' );
}

$privacy_page = get_page_by_path( 'privacy-policy' );
if ( ! $privacy_page ) {
	$privacy_page = get_page_by_path( 'privacy' );
}
?>
<footer class="site-footer">
	<div class="container">
		<div class="footer-grid footer-grid--<?php echo esc_attr( (string) $col_count ); ?>">
			<div class="footer-brand is-footer-brand">
				<?php syria_oil_the_brand_logo( 'dark' ); ?>
				<?php if ( function_exists( 'syria_oil_mod_enabled' ) && syria_oil_mod_enabled( 'show_footer_oil_pump', false ) && function_exists( 'syria_oil_the_oil_pump' ) ) : ?>
					<div class="footer-oil-pump" aria-hidden="true">
						<?php syria_oil_the_oil_pump( 'footer-oil-pump__img' ); ?>
					</div>
				<?php endif; ?>
				<h3><?php bloginfo( 'name' ); ?></h3>
				<?php if ( $tagline || get_bloginfo( 'description' ) ) : ?>
					<p><?php echo esc_html( $tagline ? $tagline : get_bloginfo( 'description' ) ); ?></p>
				<?php endif; ?>
				<?php syria_oil_render_social_links(); ?>
			</div>

			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<div class="footer-col">
					<h3><?php echo esc_html( $explore_title ); ?></h3>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'menu_class'     => 'footer-links',
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					?>
				</div>
			<?php endif; ?>

			<div class="footer-col">
				<h3><?php echo esc_html( $cats_title ); ?></h3>
				<ul>
					<?php
					wp_list_categories(
						array(
							'title_li'   => '',
							'number'     => 6,
							'orderby'    => 'count',
							'order'      => 'DESC',
							'show_count' => false,
						)
					);
					?>
				</ul>
			</div>

			<?php
			$has_footer_widgets = false;
			for ( $i = 1; $i <= $col_count; $i++ ) {
				if ( is_active_sidebar( 'footer-' . $i ) ) {
					$has_footer_widgets = true;
					break;
				}
			}
			?>

			<?php if ( $has_footer_widgets ) : ?>
				<?php for ( $i = 1; $i <= $col_count; $i++ ) : ?>
					<?php if ( is_active_sidebar( 'footer-' . $i ) ) : ?>
						<div class="footer-col footer-widget-area">
							<?php dynamic_sidebar( 'footer-' . $i ); ?>
						</div>
					<?php endif; ?>
				<?php endfor; ?>
			<?php else : ?>
				<div class="footer-col footer-col--contact">
					<h3><?php echo esc_html( $contact_title ); ?></h3>
					<ul class="footer-contact-list">
						<?php if ( $email ) : ?>
							<li class="footer-contact-item">
								<span class="footer-contact-ico" aria-hidden="true">✉</span>
								<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
							</li>
						<?php endif; ?>
						<?php if ( $phone ) : ?>
							<li class="footer-contact-item">
								<span class="footer-contact-ico" aria-hidden="true">☎</span>
								<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
							</li>
						<?php endif; ?>
						<?php if ( $contact_url ) : ?>
							<li class="footer-contact-item">
								<span class="footer-contact-ico" aria-hidden="true">→</span>
								<a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Contact us', 'syria-oil' ); ?></a>
							</li>
						<?php endif; ?>
						<?php if ( $about_url ) : ?>
							<li class="footer-contact-item footer-contact-item--desktop-only">
								<a href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'من نحن', 'syria-oil' ); ?></a>
							</li>
						<?php endif; ?>
						<?php if ( $privacy_page ) : ?>
							<li class="footer-contact-item">
								<a href="<?php echo esc_url( get_permalink( $privacy_page ) ); ?>"><?php esc_html_e( 'سياسة الخصوصية', 'syria-oil' ); ?></a>
							</li>
						<?php endif; ?>
						<?php
						$careers_page = get_page_by_path( 'careers' );
						if ( $careers_page && function_exists( 'syria_oil_careers_show_footer_link' ) && syria_oil_careers_show_footer_link() ) :
							?>
							<li class="footer-contact-item">
								<a href="<?php echo esc_url( get_permalink( $careers_page ) ); ?>"><?php esc_html_e( 'فرص العمل', 'syria-oil' ); ?></a>
							</li>
						<?php elseif ( $careers_page && ! function_exists( 'syria_oil_careers_show_footer_link' ) ) : ?>
							<li class="footer-contact-item">
								<a href="<?php echo esc_url( get_permalink( $careers_page ) ); ?>"><?php esc_html_e( 'فرص العمل', 'syria-oil' ); ?></a>
							</li>
						<?php endif; ?>
						<?php
						$articles_page = get_page_by_path( 'articles' );
						if ( $articles_page ) :
							?>
							<li class="footer-contact-item">
								<a href="<?php echo esc_url( get_permalink( $articles_page ) ); ?>"><?php esc_html_e( 'مقالات الرأي', 'syria-oil' ); ?></a>
							</li>
						<?php endif; ?>
						<?php
						$sitemap_page = get_page_by_path( 'sitemap' );
						if ( $sitemap_page ) :
							?>
							<li class="footer-contact-item">
								<a href="<?php echo esc_url( get_permalink( $sitemap_page ) ); ?>"><?php esc_html_e( 'Sitemap', 'syria-oil' ); ?></a>
							</li>
						<?php endif; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>

		<?php
		$sponsor_lines = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $sponsors_raw ) ) );
		if ( ! empty( $sponsor_lines ) ) :
			?>
			<div class="footer-sponsors">
				<h3 class="footer-sponsors__title"><?php echo esc_html( $sponsors_title ); ?></h3>
				<div class="footer-sponsors__row">
					<?php foreach ( $sponsor_lines as $line ) : ?>
						<?php
						$parts = array_map( 'trim', explode( '|', $line, 2 ) );
						$name  = $parts[0];
						$href  = isset( $parts[1] ) ? esc_url( $parts[1] ) : '';
						if ( '' === $name ) {
							continue;
						}
						if ( $href ) {
							printf( '<a class="footer-sponsors__item" href="%1$s" target="_blank" rel="noopener sponsored">%2$s</a>', $href, esc_html( $name ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						} else {
							printf( '<span class="footer-sponsors__item">%s</span>', esc_html( $name ) );
						}
						?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="footer-bottom">
			<span>&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.</span>
			<?php if ( $bottom_text ) : ?>
				<span><?php echo esc_html( $bottom_text ); ?></span>
			<?php endif; ?>
			<?php if ( $privacy_page ) : ?>
				<a href="<?php echo esc_url( get_permalink( $privacy_page ) ); ?>"><?php esc_html_e( 'سياسة الخصوصية', 'syria-oil' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</footer>
