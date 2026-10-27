<?php
/**
 * Commodity price feed: cron, cache, settings, admin refresh.
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Server-side fetch + cache for oil/gas benchmark prices.
 */
class Price_Feed {

	const TRANSIENT_KEY   = 'syria_oil_commodity_prices';
	const OPTION_KEY      = 'syria_oil_eia_api_key';
	const CRON_HOOK       = 'syria_oil_fetch_commodity_prices';
	const CRON_INTERVAL   = 'syria_oil_every_30_minutes';
	const TRANSIENT_TTL   = 2700; // 45 minutes — slightly longer than cron.
	const SETTINGS_PAGE   = 'syria-oil-price-feed';
	const REFRESH_ACTION  = 'syria_oil_refresh_prices';

	/**
	 * @var Price_Feed|null
	 */
	private static $instance = null;

	/**
	 * @return Price_Feed
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register runtime hooks (settings, cron callback, refresh).
	 */
	public function register_hooks() {
		add_filter( 'cron_schedules', array( $this, 'register_cron_interval' ) );
		add_action( self::CRON_HOOK, array( $this, 'fetch_and_cache' ) );
		add_action( 'admin_post_' . self::REFRESH_ACTION, array( $this, 'handle_refresh' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		// Ensure cron is scheduled even if activation hook was missed (e.g. file copy).
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + 60, self::CRON_INTERVAL, self::CRON_HOOK );
		}
	}

	/**
	 * Schedule cron on plugin activation.
	 */
	public static function schedule_cron() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + 60, self::CRON_INTERVAL, self::CRON_HOOK );
		}
	}

	/**
	 * Clear cron + transient on deactivation (keep API key option until uninstall).
	 */
	public static function clear_cron() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		while ( false !== $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
			$timestamp = wp_next_scheduled( self::CRON_HOOK );
		}
		wp_clear_scheduled_hook( self::CRON_HOOK );
		delete_transient( self::TRANSIENT_KEY );
	}

	/**
	 * Full cleanup for uninstall.php.
	 */
	public static function uninstall_cleanup() {
		self::clear_cron();
		delete_option( self::OPTION_KEY );
		delete_transient( self::TRANSIENT_KEY );
	}

	/**
	 * Custom 30-minute cron interval.
	 *
	 * @param array<string, array{interval: int, display: string}> $schedules Schedules.
	 * @return array<string, array{interval: int, display: string}>
	 */
	public function register_cron_interval( $schedules ) {
		if ( ! isset( $schedules[ self::CRON_INTERVAL ] ) ) {
			$schedules[ self::CRON_INTERVAL ] = array(
				'interval' => 1800,
				'display'  => __( 'Every 30 minutes (Syria Oil prices)', 'syria-oil-widgets' ),
			);
		}
		return $schedules;
	}

	/**
	 * Read cached prices (never hits the external API).
	 *
	 * @return array{last_updated: int, items: array<string, array>}|null
	 */
	public static function get_prices() {
		$cached = get_transient( self::TRANSIENT_KEY );
		if ( ! is_array( $cached ) || empty( $cached['items'] ) || ! is_array( $cached['items'] ) ) {
			return null;
		}
		return array(
			'last_updated' => isset( $cached['last_updated'] ) ? absint( $cached['last_updated'] ) : 0,
			'items'        => $cached['items'],
		);
	}

	/**
	 * Format a price row as a single display string (for tickers).
	 *
	 * @param array<string, mixed> $row         Price row.
	 * @param bool                 $show_change Whether to include change %.
	 * @return string
	 */
	public static function format_item_text( $row, $show_change = true ) {
		$label = isset( $row['label'] ) ? (string) $row['label'] : '';
		$price = isset( $row['price'] ) ? number_format_i18n( (float) $row['price'], 2 ) : '—';
		$unit  = isset( $row['unit'] ) ? (string) $row['unit'] : '';
		$text  = sprintf( '%1$s %2$s%3$s%4$s', $label, 'USD ', $price, $unit );

		if ( $show_change && isset( $row['change_pct'] ) && null !== $row['change_pct'] && is_numeric( $row['change_pct'] ) ) {
			$pct  = (float) $row['change_pct'];
			$sign = $pct > 0 ? '+' : '';
			$text .= sprintf( ' (%s%s%%)', $sign, number_format_i18n( $pct, 2 ) );
		}

		return $text;
	}

	/**
	 * Fetch from EIA and update transient on success only (fail-soft).
	 *
	 * @return true|\WP_Error
	 */
	public function fetch_and_cache() {
		$api_key = (string) get_option( self::OPTION_KEY, '' );
		$client  = new Price_API();
		$result  = $client->fetch_all( $api_key );

		if ( is_wp_error( $result ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- intentional fail-soft logging.
			error_log( '[syria-oil-widgets] Price fetch failed: ' . $result->get_error_message() );
			return $result;
		}

		if ( empty( $result['items'] ) || ! is_array( $result['items'] ) ) {
			$error = new \WP_Error( 'syria_oil_empty_items', __( 'Price fetch returned no items.', 'syria-oil-widgets' ) );
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( '[syria-oil-widgets] Price fetch failed: ' . $error->get_error_message() );
			return $error;
		}

		if ( ! empty( $result['error'] ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( '[syria-oil-widgets] Partial price fetch: ' . $result['error'] );
		}

		$payload = array(
			'last_updated' => time(),
			'items'        => $result['items'],
		);

		set_transient( self::TRANSIENT_KEY, $payload, self::TRANSIENT_TTL );

		/**
		 * Signal page-cache plugins after a successful price refresh.
		 * Theme listens via syria_oil_purge_cache (Phase 9).
		 */
		do_action( 'syria_oil_purge_cache', 'commodity_prices' );

		return true;
	}

	/**
	 * Settings submenu under Settings.
	 */
	public function register_settings_page() {
		add_options_page(
			__( 'Oil & Gas Prices', 'syria-oil-widgets' ),
			__( 'Oil & Gas Prices', 'syria-oil-widgets' ),
			'manage_options',
			self::SETTINGS_PAGE,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register Settings API field for EIA key.
	 */
	public function register_settings() {
		register_setting(
			'syria_oil_price_feed',
			self::OPTION_KEY,
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		add_settings_section(
			'syria_oil_price_feed_main',
			__( 'EIA Open Data', 'syria-oil-widgets' ),
			static function () {
				echo '<p>' . esc_html__(
					'Prices are fetched server-side on a schedule and cached. The API key never reaches the browser.',
					'syria-oil-widgets'
				) . '</p>';
				echo '<p><a href="https://www.eia.gov/opendata/register.php" target="_blank" rel="noopener noreferrer">'
					. esc_html__( 'Register for a free EIA API key', 'syria-oil-widgets' )
					. '</a></p>';
			},
			self::SETTINGS_PAGE
		);

		add_settings_field(
			self::OPTION_KEY,
			__( 'EIA API key', 'syria-oil-widgets' ),
			array( $this, 'render_api_key_field' ),
			self::SETTINGS_PAGE,
			'syria_oil_price_feed_main'
		);
	}

	/**
	 * API key input.
	 */
	public function render_api_key_field() {
		$value = (string) get_option( self::OPTION_KEY, '' );
		printf(
			'<input type="password" class="regular-text" name="%1$s" id="%1$s" value="%2$s" autocomplete="off" />',
			esc_attr( self::OPTION_KEY ),
			esc_attr( $value )
		);
	}

	/**
	 * Settings page markup + refresh button + cache status.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$cached = self::get_prices();
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Oil & Gas Prices', 'syria-oil-widgets' ); ?></h1>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'syria_oil_price_feed' );
				do_settings_sections( self::SETTINGS_PAGE );
				submit_button( __( 'Save settings', 'syria-oil-widgets' ) );
				?>
			</form>

			<hr />

			<h2><?php echo esc_html__( 'Cache status', 'syria-oil-widgets' ); ?></h2>
			<?php if ( $cached ) : ?>
				<p>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: localized datetime */
							__( 'Last updated: %s', 'syria-oil-widgets' ),
							wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $cached['last_updated'] )
						)
					);
					?>
				</p>
				<ul>
					<?php foreach ( $cached['items'] as $row ) : ?>
						<li>
							<?php
							echo esc_html(
								sprintf(
									'%1$s: %2$s %3$s%4$s',
									isset( $row['label'] ) ? $row['label'] : '',
									isset( $row['currency'] ) ? $row['currency'] : 'USD',
									isset( $row['price'] ) ? number_format_i18n( (float) $row['price'], 2 ) : '—',
									isset( $row['unit'] ) ? $row['unit'] : ''
								)
							);
							?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p><?php echo esc_html__( 'No cached prices yet. Save an API key and click Refresh now.', 'syria-oil-widgets' ); ?></p>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr( self::REFRESH_ACTION ); ?>" />
				<?php wp_nonce_field( self::REFRESH_ACTION ); ?>
				<?php submit_button( __( 'Refresh now', 'syria-oil-widgets' ), 'secondary', 'submit', false ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Manual refresh (capability-gated).
	 */
	public function handle_refresh() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Forbidden', 'syria-oil-widgets' ) );
		}
		check_admin_referer( self::REFRESH_ACTION );

		$result = $this->fetch_and_cache();
		$arg    = is_wp_error( $result ) ? 'price_error' : 'price_ok';

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'           => 'syria-oil-options',
					'sow_price_feed' => $arg,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Admin notices after refresh redirect (on Syria Oil options page).
	 */
	public function admin_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only notice flag.
		if ( empty( $_GET['page'] ) || 'syria-oil-options' !== $_GET['page'] || empty( $_GET['sow_price_feed'] ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status = sanitize_key( wp_unslash( $_GET['sow_price_feed'] ) );
		if ( 'price_ok' === $status ) {
			echo '<div class="notice notice-success is-dismissible"><p>'
				. esc_html__( 'Commodity prices refreshed successfully.', 'syria-oil-widgets' )
				. '</p></div>';
		} elseif ( 'price_error' === $status ) {
			echo '<div class="notice notice-error is-dismissible"><p>'
				. esc_html__( 'Price refresh failed. Last known good cache (if any) was kept. Check the error log and API key.', 'syria-oil-widgets' )
				. '</p></div>';
		}
	}
}
