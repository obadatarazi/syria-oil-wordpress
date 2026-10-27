<?php
/**
 * Register-only widget assets (filemtime cache-bust).
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets registrar.
 */
class Assets {

	/**
	 * @var Assets|null
	 */
	private static $instance = null;

	/**
	 * @return Assets
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook register actions.
	 */
	public function register_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register' ) );
		add_action( 'elementor/frontend/after_register_styles', array( $this, 'register' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register' ) );
	}

	/**
	 * Filemtime version helper.
	 *
	 * @param string $relative Path under plugin root.
	 * @return string
	 */
	public static function version( $relative ) {
		$path = trailingslashit( SYRIA_OIL_WIDGETS_DIR ) . ltrim( $relative, '/' );
		return file_exists( $path ) ? (string) filemtime( $path ) : SYRIA_OIL_WIDGETS_VERSION;
	}

	/**
	 * Register styles/scripts (enqueue only when widgets declare depends).
	 */
	public function register() {
		$css = array(
			'syria-oil-animations'       => 'assets/css/animations.css',
			'syria-oil-featured-slider'  => 'assets/css/widgets/featured-news-slider.css',
			'syria-oil-article-grid'     => 'assets/css/widgets/article-card-grid.css',
			'syria-oil-news-ticker'      => 'assets/css/widgets/news-ticker.css',
			'syria-oil-spotlight-rail'   => 'assets/css/widgets/spotlight-rail.css',
			'syria-oil-faq-accordion'    => 'assets/css/widgets/faq-accordion.css',
			'syria-oil-newsletter-cta'   => 'assets/css/widgets/newsletter-cta.css',
			'syria-oil-price-ticker'     => 'assets/css/widgets/price-ticker.css',
			'syria-oil-portal-sections'  => 'assets/css/widgets/portal-sections.css',
			'syria-oil-posts-filter'     => 'assets/css/widgets/posts-filter.css',
			'syria-oil-gif-banner'       => 'assets/css/widgets/gif-banner.css',
			'syria-oil-category-sections'=> 'assets/css/widgets/category-sections.css',
			'syria-oil-news-card'        => 'assets/css/widgets/news-card.css',
			'syria-oil-jobs-grid'        => 'assets/css/widgets/jobs-grid.css',
		);

		foreach ( $css as $handle => $rel ) {
			wp_register_style(
				$handle,
				SYRIA_OIL_WIDGETS_URL . '/' . $rel,
				array(),
				self::version( $rel )
			);
		}

		// Theme careers card styles (shared with single job pages).
		if ( defined( 'SYRIA_OIL_URI' ) && defined( 'SYRIA_OIL_DIR' ) ) {
			$careers_rel = 'assets/css/careers.css';
			$careers_path = trailingslashit( SYRIA_OIL_DIR ) . $careers_rel;
			if ( file_exists( $careers_path ) ) {
				wp_register_style(
					'syria-oil-careers',
					SYRIA_OIL_URI . '/' . $careers_rel,
					array(),
					(string) filemtime( $careers_path )
				);
			}
		}

		// Prefer theme scroll-reveal when Syria Oil theme is active (single shared file).
		$reveal_rel  = 'assets/js/scroll-reveal.js';
		$reveal_url  = SYRIA_OIL_WIDGETS_URL . '/' . $reveal_rel;
		$reveal_ver  = self::version( $reveal_rel );
		if ( defined( 'SYRIA_OIL_URI' ) && defined( 'SYRIA_OIL_DIR' ) ) {
			$theme_reveal = trailingslashit( SYRIA_OIL_DIR ) . 'assets/js/scroll-reveal.js';
			if ( file_exists( $theme_reveal ) ) {
				$reveal_url = SYRIA_OIL_URI . '/assets/js/scroll-reveal.js';
				$reveal_ver = (string) filemtime( $theme_reveal );
			}
		}

		$js = array(
			'syria-oil-scroll-reveal'   => array( 'src' => $reveal_url, 'ver' => $reveal_ver, 'deps' => array() ),
			'syria-oil-number-counter'  => array( 'rel' => 'assets/js/number-counter.js', 'deps' => array() ),
			'syria-oil-featured-slider' => array( 'rel' => 'assets/js/featured-slider.js', 'deps' => array() ),
			'syria-oil-category-rail-slider' => array( 'rel' => 'assets/js/category-rail-slider.js', 'deps' => array() ),
			'syria-oil-news-ticker'     => array( 'rel' => 'assets/js/news-ticker.js', 'deps' => array() ),
			'syria-oil-faq-accordion'   => array( 'rel' => 'assets/js/faq-accordion.js', 'deps' => array() ),
			'syria-oil-price-ticker'    => array( 'rel' => 'assets/js/price-ticker.js', 'deps' => array( 'syria-oil-number-counter' ) ),
			'syria-oil-posts-filter'    => array( 'rel' => 'assets/js/posts-filter.js', 'deps' => array() ),
		);

		$defer = true;
		if ( function_exists( 'syria_oil_mod_enabled' ) ) {
			$defer = syria_oil_mod_enabled( 'perf_defer_js', true );
		}

		foreach ( $js as $handle => $meta ) {
			$args = array( 'in_footer' => true );
			if ( $defer ) {
				$args['strategy'] = 'defer';
			}
			$deps = isset( $meta['deps'] ) && is_array( $meta['deps'] ) ? $meta['deps'] : array();
			if ( ! empty( $meta['src'] ) ) {
				$src = $meta['src'];
				$ver = isset( $meta['ver'] ) ? $meta['ver'] : SYRIA_OIL_WIDGETS_VERSION;
			} else {
				$rel = $meta['rel'];
				$src = SYRIA_OIL_WIDGETS_URL . '/' . $rel;
				$ver = self::version( $rel );
			}
			wp_register_script( $handle, $src, $deps, $ver, $args );
		}
	}
}
