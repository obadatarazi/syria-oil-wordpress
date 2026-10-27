<?php
/**
 * Cache purge triggers, theme-owned cache clear, and performance helpers.
 *
 * Does not implement full-page HTML caching. Clears only theme/plugin
 * internals and signals external cache plugins / optional Cloudflare.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Option: last theme/external purge timestamp. */
const SYRIA_OIL_CACHE_LAST_PURGE = 'syria_oil_cache_last_purge';

/** Standalone Cloudflare credentials (not autoloaded; mirrored like EIA key). */
const SYRIA_OIL_CF_API_TOKEN = 'syria_oil_cloudflare_api_token';
const SYRIA_OIL_CF_ZONE_ID   = 'syria_oil_cloudflare_zone_id';

/** Combined/minified theme CSS basename under uploads. */
const SYRIA_OIL_COMBINED_CSS = 'syria-oil-combined.css';

/**
 * Known caching plugins for status readout + direct purge.
 *
 * @return array<string, array{label:string, active:bool}>
 */
function syria_oil_detect_cache_plugins() {
	$plugins = array(
		'wp_rocket'         => array(
			'label'  => 'WP Rocket',
			'active' => function_exists( 'rocket_clean_domain' ),
		),
		'w3tc'              => array(
			'label'  => 'W3 Total Cache',
			'active' => function_exists( 'w3tc_flush_all' ),
		),
		'wp_super_cache'    => array(
			'label'  => 'WP Super Cache',
			'active' => function_exists( 'wp_cache_clear_cache' ),
		),
		'litespeed'         => array(
			'label'  => 'LiteSpeed Cache',
			'active' => has_action( 'litespeed_purge_all' ),
		),
		'siteground'        => array(
			'label'  => 'SiteGround Optimizer',
			'active' => function_exists( 'sg_cachepress_purge_cache' ),
		),
		'cloudflare_plugin' => array(
			'label'  => 'Cloudflare (plugin)',
			'active' => class_exists( '\CF\WordPress\Hooks' ) || function_exists( 'cloudflare_purge_everything' ),
		),
	);

	/**
	 * Filter detected cache plugins for status UI.
	 *
	 * @param array $plugins Map of slug => { label, active }.
	 */
	return apply_filters( 'syria_oil_detected_cache_plugins', $plugins );
}

/**
 * Active cache plugin labels (for admin readout).
 *
 * @return string[]
 */
function syria_oil_active_cache_plugin_labels() {
	$labels = array();
	foreach ( syria_oil_detect_cache_plugins() as $row ) {
		if ( ! empty( $row['active'] ) && ! empty( $row['label'] ) ) {
			$labels[] = $row['label'];
		}
	}
	return $labels;
}

/**
 * Record last purge time.
 *
 * @param string $context Purge context slug.
 */
function syria_oil_mark_cache_purged( $context = '' ) {
	update_option(
		SYRIA_OIL_CACHE_LAST_PURGE,
		array(
			'time'    => time(),
			'context' => sanitize_key( (string) $context ),
		),
		false
	);
}

/**
 * Last purge metadata.
 *
 * @return array{time:int,context:string}|null
 */
function syria_oil_get_last_purge() {
	$raw = get_option( SYRIA_OIL_CACHE_LAST_PURGE, null );
	if ( ! is_array( $raw ) || empty( $raw['time'] ) ) {
		return null;
	}
	return array(
		'time'    => absint( $raw['time'] ),
		'context' => isset( $raw['context'] ) ? (string) $raw['context'] : '',
	);
}

/**
 * Commodity price cache age in seconds, or null if empty.
 *
 * @return int|null
 */
function syria_oil_commodity_cache_age() {
	if ( ! class_exists( '\Syria_Oil_Widgets\Price_Feed' ) ) {
		return null;
	}
	$cached = \Syria_Oil_Widgets\Price_Feed::get_prices();
	if ( ! $cached || empty( $cached['last_updated'] ) ) {
		return null;
	}
	return max( 0, time() - absint( $cached['last_updated'] ) );
}

/**
 * Clear theme/plugin-owned caches only (not page HTML caches).
 *
 * @param string $context Context slug for logging.
 * @return array{ok:bool,cleared:string[]}
 */
function syria_oil_clear_theme_cache( $context = 'manual' ) {
	$cleared = array();

	delete_transient( 'syria_oil_commodity_prices' );
	$cleared[] = 'commodity_prices';

	delete_transient( 'syria_oil_kit_sync_failed' );
	$cleared[] = 'kit_sync';

	delete_transient( 'syria_oil_starter_notice' );
	delete_transient( 'syria_oil_widgets_presets_notice' );

	$combined = syria_oil_combined_css_path();
	if ( $combined && file_exists( $combined ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink -- theme-owned generated file.
		@unlink( $combined );
		$cleared[] = 'combined_css';
	}

	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		try {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
			$cleared[] = 'elementor_css';
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Fail-soft: Elementor API may be unavailable mid-request.
		}
	}

	syria_oil_mark_cache_purged( 'theme_' . $context );

	/**
	 * After theme-owned caches are cleared.
	 *
	 * @param string   $context Context.
	 * @param string[] $cleared Keys cleared.
	 */
	do_action( 'syria_oil_theme_cache_cleared', $context, $cleared );

	return array(
		'ok'      => true,
		'cleared' => $cleared,
	);
}

/**
 * Call known page-cache plugins directly (fail-soft, each independent).
 */
function syria_oil_purge_known_caches() {
	if ( function_exists( 'rocket_clean_domain' ) ) {
		try {
			rocket_clean_domain();
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		}
	}

	if ( function_exists( 'w3tc_flush_all' ) ) {
		try {
			w3tc_flush_all();
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		}
	}

	if ( function_exists( 'wp_cache_clear_cache' ) ) {
		try {
			wp_cache_clear_cache();
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		}
	}

	if ( has_action( 'litespeed_purge_all' ) ) {
		try {
			do_action( 'litespeed_purge_all' );
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		}
	}

	if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
		try {
			sg_cachepress_purge_cache();
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		}
	}

	if ( function_exists( 'cloudflare_purge_everything' ) ) {
		try {
			cloudflare_purge_everything();
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		}
	}
}
add_action( 'syria_oil_purge_cache', 'syria_oil_purge_known_caches', 10 );

/**
 * Optional Cloudflare API purge (zone-wide). Fail-soft; never blocks caller.
 *
 * @param string $context Context slug.
 */
function syria_oil_purge_cloudflare( $context = '' ) {
	$token  = (string) get_option( SYRIA_OIL_CF_API_TOKEN, '' );
	$zone   = (string) get_option( SYRIA_OIL_CF_ZONE_ID, '' );
	$token  = trim( $token );
	$zone   = trim( $zone );

	if ( '' === $token || '' === $zone ) {
		return;
	}

	$response = wp_remote_post(
		'https://api.cloudflare.com/client/v4/zones/' . rawurlencode( $zone ) . '/purge_cache',
		array(
			'timeout' => 8,
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( array( 'purge_everything' => true ) ),
		)
	);

	if ( is_wp_error( $response ) ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[syria-oil] Cloudflare purge failed: ' . $response->get_error_message() );
		return;
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[syria-oil] Cloudflare purge HTTP ' . $code . ' context=' . sanitize_key( $context ) );
	}
}
add_action( 'syria_oil_purge_cache', 'syria_oil_purge_cloudflare', 20 );

/**
 * Always record last purge time when the unified hook fires
 * (manual wrapper, commodity refresh, identity, etc.).
 *
 * @param string $context Context.
 */
function syria_oil_record_purge_on_hook( $context = '' ) {
	syria_oil_mark_cache_purged( is_string( $context ) ? $context : '' );
}
add_action( 'syria_oil_purge_cache', 'syria_oil_record_purge_on_hook', 999 );

/**
 * Fire unified external purge signal + mark timestamp.
 *
 * @param string $context Context (save_post, identity, prices, manual, …).
 */
function syria_oil_purge_cache( $context = 'generic' ) {
	$context = sanitize_key( (string) $context );
	if ( '' === $context ) {
		$context = 'generic';
	}

	/**
	 * Notify cache plugins / CDN to purge. Theme does not store page HTML.
	 *
	 * @param string $context Why the purge ran.
	 */
	do_action( 'syria_oil_purge_cache', $context );
}

/**
 * Identity options saved → notify external caches.
 *
 * @param array $changed Changed keys.
 */
function syria_oil_purge_on_identity_updated( $changed = array() ) {
	syria_oil_purge_cache( 'identity' );

	// Rebuild combined CSS if that optimization is on.
	if ( syria_oil_mod_enabled( 'perf_combine_css', false ) ) {
		syria_oil_rebuild_combined_css();
	}
}
add_action( 'syria_oil_identity_updated', 'syria_oil_purge_on_identity_updated', 20 );

/**
 * Truthy check for CSF switchers (1/true/'1').
 *
 * @param string $key     Option key.
 * @param bool   $default Default when unset.
 * @return bool
 */
function syria_oil_mod_enabled( $key, $default = false ) {
	$value = function_exists( 'syria_oil_mod' ) ? syria_oil_mod( $key, $default ) : $default;
	return (bool) $value && '0' !== (string) $value && 'false' !== strtolower( (string) $value );
}

/**
 * Auto-purge on content / chrome changes (once per request).
 *
 * @param string $context Context.
 */
function syria_oil_maybe_auto_purge( $context ) {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	syria_oil_purge_cache( $context );
}

/**
 * Post publish/update — skip autosave/revisions.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 * @param bool    $update  Whether update.
 */
function syria_oil_purge_on_save_post( $post_id, $post = null, $update = false ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	if ( ! $post instanceof WP_Post ) {
		$post = get_post( $post_id );
	}
	if ( ! $post || 'auto-draft' === $post->post_status ) {
		return;
	}
	syria_oil_maybe_auto_purge( 'save_post' );
}
add_action( 'save_post', 'syria_oil_purge_on_save_post', 20, 3 );

/**
 * @param int $post_id Post ID.
 */
function syria_oil_purge_on_trash_post( $post_id ) {
	syria_oil_maybe_auto_purge( 'trash_post' );
}
add_action( 'wp_trash_post', 'syria_oil_purge_on_trash_post', 20 );

/**
 * @param int $menu_id Menu ID.
 */
function syria_oil_purge_on_nav_menu( $menu_id ) {
	syria_oil_maybe_auto_purge( 'nav_menu' );
}
add_action( 'wp_update_nav_menu', 'syria_oil_purge_on_nav_menu', 20 );

/**
 * Widget / sidebar changes.
 */
function syria_oil_purge_on_widgets() {
	syria_oil_maybe_auto_purge( 'widgets' );
}
add_action( 'update_option_sidebars_widgets', 'syria_oil_purge_on_widgets', 20 );

/**
 * Elementor document save.
 *
 * @param int   $post_id Post ID.
 * @param array $editor_data Editor data.
 */
function syria_oil_purge_on_elementor_save( $post_id, $editor_data = array() ) {
	syria_oil_maybe_auto_purge( 'elementor' );
}
add_action( 'elementor/editor/after_save', 'syria_oil_purge_on_elementor_save', 20, 2 );

/**
 * Admin-post: clear theme cache.
 */
function syria_oil_handle_clear_theme_cache() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Forbidden', 'syria-oil' ) );
	}
	check_admin_referer( 'syria_oil_clear_theme_cache' );

	syria_oil_clear_theme_cache( 'manual' );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'              => 'syria-oil-options',
				'syria_oil_cache'   => 'theme_cleared',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_post_syria_oil_clear_theme_cache', 'syria_oil_handle_clear_theme_cache' );

/**
 * Admin-post: notify external caches.
 */
function syria_oil_handle_purge_external_cache() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Forbidden', 'syria-oil' ) );
	}
	check_admin_referer( 'syria_oil_purge_external_cache' );

	syria_oil_purge_cache( 'manual' );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'            => 'syria-oil-options',
				'syria_oil_cache' => 'external_purged',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_post_syria_oil_purge_external_cache', 'syria_oil_handle_purge_external_cache' );

/**
 * Admin notices after cache actions.
 */
function syria_oil_cache_admin_notices() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only.
	if ( empty( $_GET['page'] ) || 'syria-oil-options' !== $_GET['page'] || empty( $_GET['syria_oil_cache'] ) ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$status = sanitize_key( wp_unslash( $_GET['syria_oil_cache'] ) );

	if ( 'theme_cleared' === $status ) {
		echo '<div class="notice notice-success is-dismissible"><p>'
			. esc_html__( 'Theme cache cleared (commodity prices, kit sync flags, generated CSS, Elementor CSS cache).', 'syria-oil' )
			. '</p></div>';
	} elseif ( 'external_purged' === $status ) {
		$labels = syria_oil_active_cache_plugin_labels();
		$msg    = __( 'External purge signal sent (syria_oil_purge_cache).', 'syria-oil' );
		if ( $labels ) {
			$msg .= ' ' . sprintf(
				/* translators: %s: comma-separated plugin names */
				__( 'Detected plugins notified: %s.', 'syria-oil' ),
				implode( ', ', $labels )
			);
		} else {
			$msg .= ' ' . __( 'No known caching plugin was detected — install WP Rocket, LiteSpeed, etc., or configure Cloudflare below if you use a CDN.', 'syria-oil' );
		}
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
	}
}
add_action( 'admin_notices', 'syria_oil_cache_admin_notices' );

/**
 * CSF callback: status + action buttons.
 */
function syria_oil_render_cache_actions_callback() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$last     = syria_oil_get_last_purge();
	$age      = syria_oil_commodity_cache_age();
	$plugins  = syria_oil_active_cache_plugin_labels();
	$cf_token = (string) get_option( SYRIA_OIL_CF_API_TOKEN, '' );
	$cf_zone  = (string) get_option( SYRIA_OIL_CF_ZONE_ID, '' );

	echo '<div class="syria-oil-cache-panel" style="max-width:640px">';
	echo '<h4>' . esc_html__( 'Cache status', 'syria-oil' ) . '</h4>';
	echo '<ul style="margin:0 0 1rem;list-style:disc;padding-inline-start:1.25rem">';

	echo '<li>';
	if ( $last ) {
		echo esc_html(
			sprintf(
				/* translators: 1: datetime, 2: context */
				__( 'Last purge: %1$s (%2$s)', 'syria-oil' ),
				wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last['time'] ),
				$last['context'] ? $last['context'] : '—'
			)
		);
	} else {
		echo esc_html__( 'Last purge: never', 'syria-oil' );
	}
	echo '</li>';

	echo '<li>';
	if ( null === $age ) {
		echo esc_html__( 'Commodity price cache: empty', 'syria-oil' );
	} else {
		echo esc_html(
			sprintf(
				/* translators: %s: human time diff */
				__( 'Commodity price cache age: %s', 'syria-oil' ),
				human_time_diff( time() - $age, time() )
			)
		);
	}
	echo '</li>';

	echo '<li>';
	if ( $plugins ) {
		echo esc_html(
			sprintf(
				/* translators: %s: plugin names */
				__( 'Detected caching plugins: %s', 'syria-oil' ),
				implode( ', ', $plugins )
			)
		);
	} else {
		echo esc_html__( 'Detected caching plugins: none', 'syria-oil' );
	}
	echo '</li>';

	echo '<li>';
	if ( $cf_token && $cf_zone ) {
		echo esc_html__( 'Cloudflare API: configured', 'syria-oil' );
	} else {
		echo esc_html__( 'Cloudflare API: not configured', 'syria-oil' );
	}
	echo '</li>';
	echo '</ul>';

	echo '<p class="description" style="margin-bottom:12px">'
		. esc_html__( 'Clear Theme Cache only removes data this theme/plugin stored. Notify cache plugin to purge tells installed page-cache plugins / Cloudflare to flush — the theme does not run its own full-page HTML cache.', 'syria-oil' )
		. '</p>';

	echo '<p style="display:flex;flex-wrap:wrap;gap:8px">';

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline">';
	echo '<input type="hidden" name="action" value="syria_oil_clear_theme_cache" />';
	wp_nonce_field( 'syria_oil_clear_theme_cache' );
	echo '<button type="submit" class="button button-primary">' . esc_html__( 'Clear Theme Cache', 'syria-oil' ) . '</button>';
	echo '</form>';

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline">';
	echo '<input type="hidden" name="action" value="syria_oil_purge_external_cache" />';
	wp_nonce_field( 'syria_oil_purge_external_cache' );
	echo '<button type="submit" class="button button-secondary">' . esc_html__( 'Notify cache plugin to purge', 'syria-oil' ) . '</button>';
	echo '</form>';

	echo '</p></div>';
}

/**
 * Absolute path for combined CSS in uploads, or empty string.
 *
 * @return string
 */
function syria_oil_combined_css_path() {
	$uploads = wp_upload_dir();
	if ( ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) ) {
		return '';
	}
	$dir = trailingslashit( $uploads['basedir'] ) . 'syria-oil';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	return trailingslashit( $dir ) . SYRIA_OIL_COMBINED_CSS;
}

/**
 * Public URL for combined CSS, or empty.
 *
 * @return string
 */
function syria_oil_combined_css_url() {
	$path = syria_oil_combined_css_path();
	if ( ! $path || ! file_exists( $path ) ) {
		return '';
	}
	$uploads = wp_upload_dir();
	if ( empty( $uploads['baseurl'] ) ) {
		return '';
	}
	return trailingslashit( $uploads['baseurl'] ) . 'syria-oil/' . SYRIA_OIL_COMBINED_CSS;
}

/**
 * Very small CSS minify (comments + excess whitespace). Theme-owned files only.
 *
 * @param string $css Raw CSS.
 * @return string
 */
function syria_oil_minify_css( $css ) {
	$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );
	$css = preg_replace( '/\s+/', ' ', $css );
	$css = str_replace( array( ' {', '{ ', ' }', '} ', ': ', ' :', '; ', ' ;', ', ', ' ,' ), array( '{', '{', '}', '}', ':', ':', ';', ';', ',', ',' ), $css );
	return trim( (string) $css );
}

/**
 * Rebuild combined/minified theme CSS on disk.
 *
 * @return bool Success.
 */
function syria_oil_rebuild_combined_css() {
	$files = array(
		'assets/css/tokens.css',
		'assets/css/theme.css',
		'assets/css/mobile.css',
		'style.css',
	);

	$bundle = "/* Syria Oil combined CSS — generated " . gmdate( 'c' ) . " */\n";
	foreach ( $files as $rel ) {
		$path = trailingslashit( SYRIA_OIL_DIR ) . $rel;
		if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
			continue;
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$raw = file_get_contents( $path );
		if ( false === $raw ) {
			continue;
		}
		$bundle .= "\n/* --- {$rel} --- */\n" . syria_oil_minify_css( $raw );
	}

	$out = syria_oil_combined_css_path();
	if ( ! $out ) {
		return false;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	$written = file_put_contents( $out, $bundle );
	return false !== $written;
}

/**
 * When combine CSS is enabled after options save, rebuild file.
 *
 * @param array $data Saved options.
 */
function syria_oil_maybe_rebuild_combined_on_save( $data ) {
	if ( ! is_array( $data ) ) {
		return;
	}
	if ( empty( $data['perf_combine_css'] ) ) {
		return;
	}
	syria_oil_rebuild_combined_css();
}
add_action( 'csf_' . SYRIA_OIL_OPTIONS . '_save_after', 'syria_oil_maybe_rebuild_combined_on_save', 30 );
