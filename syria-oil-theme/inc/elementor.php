<?php
/**
 * Elementor integration (theme): Theme Builder locations, first-run kit seed,
 * admin notices. Widgets live in the syria-oil-widgets plugin.
 *
 * Front-end --e-global-* CSS is owned solely by syria_oil_print_identity_css().
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether Elementor is active and loaded.
 *
 * @return bool
 */
function syria_oil_has_elementor() {
	return did_action( 'elementor/loaded' ) || class_exists( '\Elementor\Plugin' );
}

/**
 * Whether Elementor Pro is active.
 *
 * @return bool
 */
function syria_oil_has_elementor_pro() {
	return defined( 'ELEMENTOR_PRO_VERSION' );
}

/**
 * Register Elementor Pro Theme Builder locations.
 *
 * @param object $manager Locations manager.
 */
function syria_oil_register_elementor_locations( $manager ) {
	$manager->register_all_core_location();
}
add_action( 'elementor/theme/register_locations', 'syria_oil_register_elementor_locations' );

/**
 * Seed Elementor Kit System Colors on first run only (from Customizer defaults).
 */
function syria_oil_seed_elementor_kit_defaults() {
	if ( ! syria_oil_has_elementor() || ! class_exists( '\Elementor\Plugin' ) ) {
		return;
	}

	if ( get_option( 'syria_oil_kit_seeded' ) ) {
		return;
	}

	$settings = syria_oil_kit_settings_from_options();
	if ( empty( $settings ) ) {
		return;
	}

	if ( syria_oil_apply_kit_settings( $settings ) ) {
		update_option( 'syria_oil_kit_seeded', SYRIA_OIL_VERSION, false );
	}
}
add_action( 'elementor/init', 'syria_oil_seed_elementor_kit_defaults', 20 );

/**
 * Best-effort kit.json apply after OCDI (timeboxed / fail-soft).
 *
 * @param string $kit_path Absolute path to kit.json.
 */
function syria_oil_import_elementor_kit_handler( $kit_path ) {
	if ( ! syria_oil_has_elementor() || ! is_readable( $kit_path ) ) {
		return false;
	}

	try {
		$json = file_get_contents( $kit_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$data = json_decode( (string) $json, true );
		if ( empty( $data ) || ! is_array( $data ) ) {
			return false;
		}

		$settings = array();
		if ( ! empty( $data['settings']['system_colors'] ) ) {
			$settings['system_colors'] = $data['settings']['system_colors'];
		}
		if ( ! empty( $data['settings']['system_typography'] ) ) {
			$settings['system_typography'] = $data['settings']['system_typography'];
		}

		if ( empty( $settings ) ) {
			return false;
		}

		$applied = syria_oil_apply_kit_settings( $settings );
		if ( ! $applied ) {
			set_transient( 'syria_oil_kit_sync_failed', 1, WEEK_IN_SECONDS );
		}
		return $applied;
	} catch ( Throwable $e ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'Syria Oil kit.json import failed: ' . $e->getMessage() );
		set_transient( 'syria_oil_kit_sync_failed', 1, WEEK_IN_SECONDS );
		return false;
	}
}
add_action( 'syria_oil_import_elementor_kit', 'syria_oil_import_elementor_kit_handler' );

/**
 * Ensure Elementor has an active Site Kit document (create one if missing).
 *
 * @return \Elementor\Core\Kits\Documents\Kit|\Elementor\Core\Base\Document|null
 */
function syria_oil_ensure_elementor_active_kit() {
	if ( ! syria_oil_has_elementor() || ! class_exists( '\Elementor\Plugin' ) ) {
		return null;
	}

	$km = \Elementor\Plugin::$instance->kits_manager;
	if ( ! $km ) {
		return null;
	}

	$kit    = $km->get_active_kit();
	$kit_id = 0;
	if ( $kit && method_exists( $kit, 'get_main_id' ) ) {
		$kit_id = (int) $kit->get_main_id();
	}
	if ( ! $kit_id && method_exists( $km, 'get_active_id' ) ) {
		$kit_id = (int) $km->get_active_id();
	}

	if ( $kit_id && 'trash' !== get_post_status( $kit_id ) ) {
		return $kit;
	}

	if ( method_exists( $km, 'create_default_kit' ) ) {
		$km->create_default_kit();
	} elseif ( method_exists( $km, 'create_default' ) ) {
		$km->create_default();
	} else {
		$new_id = wp_insert_post(
			array(
				'post_title'  => __( 'Syria Oil Kit', 'syria-oil' ),
				'post_status' => 'publish',
				'post_type'   => 'elementor_library',
			),
			true
		);
		if ( ! is_wp_error( $new_id ) && $new_id ) {
			update_post_meta( $new_id, '_elementor_edit_mode', 'builder' );
			update_post_meta( $new_id, '_elementor_template_type', 'kit' );
			update_option( 'elementor_active_kit', (int) $new_id, false );
		}
	}

	return $km->get_active_kit();
}

/**
 * Clear Elementor generated CSS cache after kit changes.
 */
function syria_oil_clear_elementor_css_cache() {
	if ( ! class_exists( '\Elementor\Plugin' ) ) {
		return;
	}
	try {
		if ( isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
	} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		// Fail-soft.
	}
}

/**
 * Apply settings to the active Elementor kit (API + post-meta fallback).
 *
 * @param array<string,mixed> $settings Kit settings fragment.
 * @return bool
 */
function syria_oil_apply_kit_settings( array $settings ) {
	if ( empty( $settings ) ) {
		return false;
	}

	$kit = syria_oil_ensure_elementor_active_kit();
	if ( ! $kit ) {
		return false;
	}

	$applied = false;

	if ( method_exists( $kit, 'update_settings' ) ) {
		try {
			$kit->update_settings( $settings );
			$applied = true;
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Try post-meta fallback below.
		}
	}

	$kit_id = 0;
	if ( method_exists( $kit, 'get_main_id' ) ) {
		$kit_id = (int) $kit->get_main_id();
	}
	if ( ! $kit_id && method_exists( $kit, 'get_id' ) ) {
		$kit_id = (int) $kit->get_id();
	}

	if ( $kit_id ) {
		$page_settings = get_post_meta( $kit_id, '_elementor_page_settings', true );
		if ( ! is_array( $page_settings ) ) {
			$page_settings = array();
		}
		$page_settings = array_merge( $page_settings, $settings );
		update_post_meta( $kit_id, '_elementor_page_settings', $page_settings );
		$applied = true;
	}

	if ( $applied ) {
		syria_oil_clear_elementor_css_cache();
		delete_transient( 'syria_oil_kit_sync_failed' );
	}

	return $applied;
}

/**
 * Build kit color/typography settings from Theme Options.
 *
 * @return array<string,mixed>
 */
function syria_oil_kit_settings_from_options() {
	if ( ! function_exists( 'syria_oil_get_identity_css_vars' ) ) {
		return array();
	}

	$vars          = syria_oil_get_identity_css_vars();
	$system_colors = array(
		array( '_id' => 'primary', 'title' => 'Primary', 'color' => $vars['--e-global-color-primary'] ),
		array( '_id' => 'secondary', 'title' => 'Secondary', 'color' => $vars['--e-global-color-secondary'] ),
		array( '_id' => 'text', 'title' => 'Text', 'color' => $vars['--e-global-color-text'] ),
		array( '_id' => 'accent', 'title' => 'Accent', 'color' => $vars['--e-global-color-accent'] ),
	);

	$heading = $vars['--e-global-typography-primary-font-family'];
	$body    = $vars['--e-global-typography-secondary-font-family'];

	$system_typography = array(
		array(
			'_id'                    => 'primary',
			'title'                  => 'Primary (Display)',
			'typography_typography'  => 'custom',
			'typography_font_family' => $heading,
			'typography_font_weight' => '700',
		),
		array(
			'_id'                    => 'secondary',
			'title'                  => 'Secondary (Body)',
			'typography_typography'  => 'custom',
			'typography_font_family' => $body,
			'typography_font_weight' => '400',
		),
	);

	return array(
		'system_colors'     => $system_colors,
		'system_typography' => $system_typography,
	);
}

/**
 * Sync Theme Options brand tokens into Elementor Site Kit.
 *
 * @return bool True when kit settings were written.
 */
function syria_oil_sync_kit_from_options() {
	if ( ! syria_oil_has_elementor() ) {
		return false;
	}

	$settings = syria_oil_kit_settings_from_options();
	if ( empty( $settings ) ) {
		return false;
	}

	$ok = syria_oil_apply_kit_settings( $settings );
	if ( ! $ok ) {
		set_transient( 'syria_oil_kit_sync_failed', 1, WEEK_IN_SECONDS );
	} else {
		delete_transient( 'syria_oil_kit_sync_failed' );
	}

	return $ok;
}

/**
 * Admin: manual kit sync retry (Theme Options save path uses the same helper).
 */
function syria_oil_handle_sync_elementor_kit() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to sync the Elementor kit.', 'syria-oil' ) );
	}
	check_admin_referer( 'syria_oil_sync_elementor_kit' );

	$ok = syria_oil_sync_kit_from_options();

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'               => 'syria-oil-setup',
				'syria_oil_kit_sync' => $ok ? '1' : '0',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_post_syria_oil_sync_elementor_kit', 'syria_oil_handle_sync_elementor_kit' );

/**
 * Retry kit sync once Elementor has fully booted (covers demo import + early saves).
 */
function syria_oil_maybe_retry_kit_sync() {
	if ( get_transient( 'syria_oil_kit_sync_failed' ) ) {
		syria_oil_sync_kit_from_options();
	}
}
add_action( 'elementor/init', 'syria_oil_maybe_retry_kit_sync', 99 );

/**
 * Admin notice when Elementor is missing.
 */
function syria_oil_elementor_missing_notice() {
	if ( syria_oil_has_elementor() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && 'themes' !== $screen->id && 'dashboard' !== $screen->id ) {
		return;
	}
	echo '<div class="notice notice-info is-dismissible"><p>';
	echo esc_html__( 'Syria Oil theme: install and activate Elementor plus the Syria Oil Widgets plugin for drag-and-drop building. The theme works without them as a classic theme.', 'syria-oil' );
	echo '</p></div>';
}
add_action( 'admin_notices', 'syria_oil_elementor_missing_notice' );
