<?php
/**
 * Plugin bootstrap.
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 */
class Plugin {

	/**
	 * Singleton.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Activation: flag + schedule price-feed cron.
	 */
	public static function activate() {
		if ( ! get_option( 'syria_oil_widgets_activated' ) ) {
			add_option( 'syria_oil_widgets_activated', time(), '', false );
		}

		// cron_schedules must be available when scheduling a custom interval.
		$feed = Price_Feed::instance();
		add_filter( 'cron_schedules', array( $feed, 'register_cron_interval' ) );
		Price_Feed::schedule_cron();

		if ( class_exists( '\\Syria_Oil_Widgets\\Newsletter\\Bootstrap' ) ) {
			Newsletter\Bootstrap::activate();
		}
	}

	/**
	 * Deactivation: clear transients and price-feed cron.
	 */
	public static function deactivate() {
		delete_transient( 'syria_oil_widgets_presets_notice' );
		Price_Feed::clear_cron();
		if ( class_exists( '\\Syria_Oil_Widgets\\Newsletter\\Bootstrap' ) ) {
			Newsletter\Bootstrap::deactivate();
		}
	}

	/**
	 * Hook Elementor integration + price feed.
	 */
	public function init() {
		load_plugin_textdomain( 'syria-oil-widgets', false, dirname( plugin_basename( SYRIA_OIL_WIDGETS_FILE ) ) . '/languages' );

		// Price feed (settings + cron) does not require Elementor.
		Price_Feed::instance()->register_hooks();
		Newsletter_Form::instance()->register_hooks();
		Newsletter\Bootstrap::init();

		add_action( 'admin_notices', array( $this, 'missing_elementor_notice' ) );

		if ( ! did_action( 'elementor/loaded' ) && ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'elementor/dynamic_tags/register', array( $this, 'register_dynamic_tags' ) );

		Assets::instance()->register_hooks();

		add_action( 'admin_menu', array( $this, 'register_tools_page' ) );
		add_action( 'admin_post_syria_oil_import_presets', array( $this, 'handle_import_presets' ) );
	}

	/**
	 * Tools submenu for importing widget preset JSON into Elementor library.
	 */
	public function register_tools_page() {
		add_management_page(
			__( 'Syria Oil Widget Presets', 'syria-oil-widgets' ),
			__( 'Syria Oil Presets', 'syria-oil-widgets' ),
			'manage_options',
			'syria-oil-widget-presets',
			array( $this, 'render_tools_page' )
		);
	}

	/**
	 * Tools page markup.
	 */
	public function render_tools_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		$imported        = get_option( 'syria_oil_widgets_presets_imported' );
		$imported_ver    = get_option( 'syria_oil_widgets_presets_version' );
		$version_changed = $imported_ver && version_compare( $imported_ver, SYRIA_OIL_WIDGETS_VERSION, '<' );

		// Success message
		if ( ! empty( $_GET['imported'] ) ) {
			$created = isset( $_GET['created'] ) ? absint( $_GET['created'] ) : 0;
			$updated = isset( $_GET['updated'] ) ? absint( $_GET['updated'] ) : 0;
			echo '<div class="notice notice-success"><p>';
			if ( $created > 0 && $updated > 0 ) {
				printf(
					/* translators: 1: created count 2: updated count */
					esc_html__( 'Widget presets imported: %1$d created, %2$d updated.', 'syria-oil-widgets' ),
					$created,
					$updated
				);
			} elseif ( $created > 0 ) {
				printf(
					/* translators: %d: created count */
					esc_html__( 'Widget presets imported: %d created.', 'syria-oil-widgets' ),
					$created
				);
			} elseif ( $updated > 0 ) {
				printf(
					/* translators: %d: updated count */
					esc_html__( 'Widget presets updated: %d templates refreshed.', 'syria-oil-widgets' ),
					$updated
				);
			} else {
				esc_html_e( 'Widget presets imported successfully.', 'syria-oil-widgets' );
			}
			echo '</p></div>';
		}

		echo '<div class="wrap"><h1>' . esc_html__( 'Syria Oil Widget Presets', 'syria-oil-widgets' ) . '</h1>';
		
		if ( $imported && ! $version_changed ) {
			echo '<div class="notice notice-info inline"><p>';
			esc_html_e( 'Presets already imported. Re-importing will update existing templates with the latest versions.', 'syria-oil-widgets' );
			echo '</p></div>';
		}

		if ( $version_changed ) {
			echo '<div class="notice notice-warning inline"><p>';
			printf(
				/* translators: 1: old version 2: new version */
				esc_html__( 'Plugin updated from %1$s to %2$s. Re-import to get updated widget templates.', 'syria-oil-widgets' ),
				esc_html( $imported_ver ),
				esc_html( SYRIA_OIL_WIDGETS_VERSION )
			);
			echo '</p></div>';
		}
		
		echo '<p>' . esc_html__( 'Import pre-built widget layouts (Featured Slider, Card Grid, Spotlight Rail, etc.) into your Elementor Library for quick drag-and-drop use.', 'syria-oil-widgets' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="syria_oil_import_presets" />';
		wp_nonce_field( 'syria_oil_import_presets' );
		submit_button( __( 'Import Widget Presets', 'syria-oil-widgets' ) );
		echo '</form></div>';
	}

	/**
	 * Import templates/*.json as Elementor library items.
	 */
	public function handle_import_presets() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Forbidden', 'syria-oil-widgets' ) );
		}
		check_admin_referer( 'syria_oil_import_presets' );

		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			wp_safe_redirect( admin_url( 'tools.php?page=syria-oil-widget-presets&error=no-elementor' ) );
			exit;
		}

		$count_created = 0;
		$count_updated = 0;
		$dir           = trailingslashit( SYRIA_OIL_WIDGETS_DIR ) . 'templates';
		$files         = glob( $dir . '/*.json' );
		
		if ( ! is_array( $files ) ) {
			$files = array();
		}

		foreach ( $files as $file ) {
			$json = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$data = json_decode( (string) $json, true );
			if ( empty( $data ) || ! is_array( $data ) ) {
				continue;
			}
			
			$title    = ! empty( $data['title'] ) ? sanitize_text_field( $data['title'] ) : basename( $file, '.json' );
			$content  = isset( $data['content'] ) ? $data['content'] : array();
			$settings = ! empty( $data['page_settings'] ) ? $data['page_settings'] : array();
			$type     = ! empty( $data['type'] ) ? sanitize_text_field( $data['type'] ) : 'section';

			// Check if template already exists by title.
			$existing = get_page_by_title( $title, OBJECT, 'elementor_library' );

			if ( $existing ) {
				// Update existing template.
				update_post_meta( $existing->ID, '_elementor_data', wp_slash( wp_json_encode( $content ) ) );
				update_post_meta( $existing->ID, '_elementor_page_settings', $settings );
				update_post_meta( $existing->ID, '_elementor_edit_mode', 'builder' );
				update_post_meta( $existing->ID, '_elementor_template_type', $type );
				wp_set_object_terms( $existing->ID, $type, 'elementor_library_type' );
				$count_updated++;
			} else {
				// Create new template.
				$post_id = wp_insert_post(
					array(
						'post_title'  => $title,
						'post_status' => 'publish',
						'post_type'   => 'elementor_library',
					),
					true
				);
				if ( is_wp_error( $post_id ) || ! $post_id ) {
					continue;
				}
				update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
				update_post_meta( $post_id, '_elementor_template_type', $type );
				update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $content ) ) );
				update_post_meta( $post_id, '_elementor_page_settings', $settings );
				wp_set_object_terms( $post_id, $type, 'elementor_library_type' );
				$count_created++;
			}
		}

		update_option( 'syria_oil_widgets_presets_imported', time(), false );
		update_option( 'syria_oil_widgets_presets_version', SYRIA_OIL_WIDGETS_VERSION, false );
		
		$redirect_args = array(
			'page'     => 'syria-oil-widget-presets',
			'imported' => 1,
			'created'  => $count_created,
			'updated'  => $count_updated,
		);
		wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'tools.php' ) ) );
		exit;
	}

	/**
	 * Admin notice when Elementor is missing.
	 */
	public function missing_elementor_notice() {
		if ( did_action( 'elementor/loaded' ) || class_exists( '\Elementor\Plugin' ) ) {
			return;
		}
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		echo '<div class="notice notice-warning is-dismissible"><p>';
		echo esc_html__( 'Syria Oil Widgets requires Elementor to be installed and active.', 'syria-oil-widgets' );
		echo '</p></div>';
	}

	/**
	 * Register widget category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Manager.
	 */
	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'syria-oil-widgets',
			array(
				'title' => __( 'Syria Oil', 'syria-oil-widgets' ),
				'icon'  => 'eicon-flash',
			)
		);
	}

	/**
	 * Register all widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Manager.
	 */
	public function register_widgets( $widgets_manager ) {
		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return;
		}

		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/trait-post-query.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/trait-animation-controls.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/trait-news-card-style-controls.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/trait-news-card-slider-controls.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-featured-news-slider.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-article-card-grid.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-news-ticker.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-spotlight-rail.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-faq-accordion.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-newsletter-cta.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-price-ticker.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-page-hero.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-overview-cards.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-stats-cards.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-service-tiles.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-category-flow.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-minister-block.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-ad-banner.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-gif-banner.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-project-cards.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-partners.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-contact-cta.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-posts-filter.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-category-sections.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/widgets/class-jobs-grid.php';

		$widgets_manager->register( new Widgets\Featured_News_Slider() );
		$widgets_manager->register( new Widgets\Article_Card_Grid() );
		$widgets_manager->register( new Widgets\News_Ticker() );
		$widgets_manager->register( new Widgets\Spotlight_Rail() );
		$widgets_manager->register( new Widgets\Faq_Accordion() );
		$widgets_manager->register( new Widgets\Newsletter_Cta() );
		$widgets_manager->register( new Widgets\Price_Ticker() );
		$widgets_manager->register( new Widgets\Page_Hero() );
		$widgets_manager->register( new Widgets\Overview_Cards() );
		$widgets_manager->register( new Widgets\Stats_Cards() );
		$widgets_manager->register( new Widgets\Service_Tiles() );
		$widgets_manager->register( new Widgets\Category_Flow() );
		$widgets_manager->register( new Widgets\Category_Sections() );
		$widgets_manager->register( new Widgets\Jobs_Grid() );
		$widgets_manager->register( new Widgets\Minister_Block() );
		$widgets_manager->register( new Widgets\Ad_Banner() );
		$widgets_manager->register( new Widgets\Gif_Banner() );
		$widgets_manager->register( new Widgets\Project_Cards() );
		$widgets_manager->register( new Widgets\Partners() );
		$widgets_manager->register( new Widgets\Contact_Cta() );
		$widgets_manager->register( new Widgets\Posts_Filter() );
	}

	/**
	 * Register Dynamic Tags group + tags.
	 *
	 * @param \Elementor\Core\DynamicTags\Manager $dynamic_tags Manager.
	 */
	public function register_dynamic_tags( $dynamic_tags ) {
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/dynamic-tags/class-tag-post-title.php';
		require_once SYRIA_OIL_WIDGETS_DIR . '/includes/dynamic-tags/class-tag-post-url.php';

		$dynamic_tags->register_group(
			'syria-oil',
			array(
				'title' => __( 'Syria Oil', 'syria-oil-widgets' ),
			)
		);

		$dynamic_tags->register( new Dynamic_Tags\Post_Title() );
		$dynamic_tags->register( new Dynamic_Tags\Post_Url() );

		if ( function_exists( 'get_field' ) ) {
			require_once SYRIA_OIL_WIDGETS_DIR . '/includes/dynamic-tags/class-tag-acf-text.php';
			$dynamic_tags->register( new Dynamic_Tags\Acf_Text() );
		}
	}
}
