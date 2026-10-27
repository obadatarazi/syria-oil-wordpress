<?php
/**
 * Newsletter admin menu + settings + CSV export.
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets\Newsletter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Top-level Newsletter dashboard.
 */
class Admin {

	/**
	 * Hooks.
	 */
	public static function register() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 9 );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_post_sow_nl_export_csv', array( __CLASS__, 'export_csv' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		add_action( 'wp_footer', array( __CLASS__, 'front_notices' ) );
	}

	/**
	 * Top-level menu (CPTs attach as children via show_in_menu).
	 */
	public static function menu() {
		add_menu_page(
			__( 'Newsletter', 'syria-oil-widgets' ),
			__( 'Newsletter', 'syria-oil-widgets' ),
			'edit_posts',
			'syria-oil-newsletter',
			array( __CLASS__, 'render_dashboard' ),
			'dashicons-email-alt',
			26
		);

		add_submenu_page(
			'syria-oil-newsletter',
			__( 'Newsletter overview', 'syria-oil-widgets' ),
			__( 'Overview', 'syria-oil-widgets' ),
			'edit_posts',
			'syria-oil-newsletter',
			array( __CLASS__, 'render_dashboard' )
		);

		add_submenu_page(
			'syria-oil-newsletter',
			__( 'Newsletter settings', 'syria-oil-widgets' ),
			__( 'Settings', 'syria-oil-widgets' ),
			'manage_options',
			'syria-oil-newsletter-settings',
			array( __CLASS__, 'render_settings' )
		);
	}

	/**
	 * Settings API.
	 */
	public static function register_settings() {
		register_setting(
			'syria_oil_newsletter',
			'syria_oil_newsletter_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * @param mixed $input Raw.
	 * @return array
	 */
	public static function sanitize_settings( $input ) {
		$input = is_array( $input ) ? $input : array();
		return array(
			'from_name'     => isset( $input['from_name'] ) ? sanitize_text_field( $input['from_name'] ) : '',
			'from_email'    => isset( $input['from_email'] ) ? sanitize_email( $input['from_email'] ) : '',
			'reply_to'      => isset( $input['reply_to'] ) ? sanitize_email( $input['reply_to'] ) : '',
			'double_opt_in' => ! empty( $input['double_opt_in'] ) ? 1 : 0,
			'footer_text'   => isset( $input['footer_text'] ) ? sanitize_textarea_field( $input['footer_text'] ) : '',
		);
	}

	/**
	 * Dashboard counts.
	 */
	public static function render_dashboard() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		$active = count( Subscribers::active_emails() );
		$all    = (int) wp_count_posts( Subscribers::POST_TYPE )->publish;
		$sent   = new \WP_Query(
			array(
				'post_type'      => Campaigns::POST_TYPE,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_sow_nl_send_status', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => 'sent', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Newsletter', 'syria-oil-widgets' ); ?></h1>
			<p><?php esc_html_e( 'Collect subscribers from the site form, manage the list, and send campaigns from the WordPress admin.', 'syria-oil-widgets' ); ?></p>
			<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;max-width:900px;margin:20px 0">
				<div class="card" style="padding:16px;margin:0">
					<h2 style="margin-top:0"><?php echo esc_html( (string) $active ); ?></h2>
					<p><?php esc_html_e( 'Active subscribers', 'syria-oil-widgets' ); ?></p>
				</div>
				<div class="card" style="padding:16px;margin:0">
					<h2 style="margin-top:0"><?php echo esc_html( (string) $all ); ?></h2>
					<p><?php esc_html_e( 'Total subscriber records', 'syria-oil-widgets' ); ?></p>
				</div>
				<div class="card" style="padding:16px;margin:0">
					<h2 style="margin-top:0"><?php echo esc_html( (string) $sent->found_posts ); ?></h2>
					<p><?php esc_html_e( 'Campaigns sent', 'syria-oil-widgets' ); ?></p>
				</div>
			</div>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . Subscribers::POST_TYPE ) ); ?>"><?php esc_html_e( 'Manage subscribers', 'syria-oil-widgets' ); ?></a>
				<a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . Campaigns::POST_TYPE ) ); ?>"><?php esc_html_e( 'New campaign', 'syria-oil-widgets' ); ?></a>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=sow_nl_export_csv' ), 'sow_nl_export' ) ); ?>"><?php esc_html_e( 'Export CSV', 'syria-oil-widgets' ); ?></a>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=syria-oil-newsletter-settings' ) ); ?>"><?php esc_html_e( 'Settings', 'syria-oil-widgets' ); ?></a>
			</p>
			<hr />
			<h2><?php esc_html_e( 'How it works', 'syria-oil-widgets' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'Visitors subscribe via the Newsletter form widget on the site.', 'syria-oil-widgets' ); ?></li>
				<li><?php esc_html_e( 'Emails are stored under Newsletter → Subscribers (active / pending / unsubscribed).', 'syria-oil-widgets' ); ?></li>
				<li><?php esc_html_e( 'Create a Campaign with a subject + HTML body, then click “Queue send”.', 'syria-oil-widgets' ); ?></li>
				<li><?php esc_html_e( 'WordPress sends in batches of 20 via WP-Cron using wp_mail (configure SMTP on the host for best delivery).', 'syria-oil-widgets' ); ?></li>
				<li><?php esc_html_e( 'Every email includes an unsubscribe link tied to that subscriber.', 'syria-oil-widgets' ); ?></li>
			</ol>
		</div>
		<?php
	}

	/**
	 * Settings screen.
	 */
	public static function render_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s = Mailer::settings();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Newsletter settings', 'syria-oil-widgets' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'syria_oil_newsletter' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="sow_nl_from_name"><?php esc_html_e( 'From name', 'syria-oil-widgets' ); ?></label></th>
						<td><input name="syria_oil_newsletter_settings[from_name]" id="sow_nl_from_name" type="text" class="regular-text" value="<?php echo esc_attr( $s['from_name'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="sow_nl_from_email"><?php esc_html_e( 'From email', 'syria-oil-widgets' ); ?></label></th>
						<td><input name="syria_oil_newsletter_settings[from_email]" id="sow_nl_from_email" type="email" class="regular-text" value="<?php echo esc_attr( $s['from_email'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="sow_nl_reply"><?php esc_html_e( 'Reply-To', 'syria-oil-widgets' ); ?></label></th>
						<td><input name="syria_oil_newsletter_settings[reply_to]" id="sow_nl_reply" type="email" class="regular-text" value="<?php echo esc_attr( $s['reply_to'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Double opt-in', 'syria-oil-widgets' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="syria_oil_newsletter_settings[double_opt_in]" value="1" <?php checked( ! empty( $s['double_opt_in'] ) ); ?> />
								<?php esc_html_e( 'Require email confirmation before activating a subscriber', 'syria-oil-widgets' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sow_nl_footer"><?php esc_html_e( 'Email footer text', 'syria-oil-widgets' ); ?></label></th>
						<td><textarea name="syria_oil_newsletter_settings[footer_text]" id="sow_nl_footer" class="large-text" rows="3"><?php echo esc_textarea( $s['footer_text'] ); ?></textarea></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<p class="description">
				<?php esc_html_e( 'Tip: install an SMTP plugin (e.g. WP Mail SMTP) so campaign emails are not blocked by the server.', 'syria-oil-widgets' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Export subscribers CSV.
	 */
	public static function export_csv() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'Forbidden', 'syria-oil-widgets' ) );
		}
		check_admin_referer( 'sow_nl_export' );

		$q = new \WP_Query(
			array(
				'post_type'      => Subscribers::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=newsletter-subscribers-' . gmdate( 'Y-m-d' ) . '.csv' );
		$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		fputcsv( $out, array( 'email', 'status', 'source', 'subscribed_at' ) );
		foreach ( $q->posts as $post ) {
			fputcsv(
				$out,
				array(
					$post->post_title,
					Subscribers::get_status( $post->ID ),
					(string) get_post_meta( $post->ID, '_sow_nl_source', true ),
					$post->post_date_gmt,
				)
			);
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}

	/**
	 * Campaign queued notice.
	 */
	public static function admin_notices() {
		if ( empty( $_GET['sow_nl'] ) || 'queued' !== $_GET['sow_nl'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Campaign queued. Emails will send in batches shortly.', 'syria-oil-widgets' ) . '</p></div>';
	}

	/**
	 * Front-end subscribe notices.
	 */
	public static function front_notices() {
		if ( empty( $_GET['sow_nl'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$code = sanitize_key( wp_unslash( $_GET['sow_nl'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$map  = array(
			'ok'           => __( 'Thanks — you are subscribed to the newsletter.', 'syria-oil-widgets' ),
			'pending'      => __( 'Check your inbox to confirm your subscription.', 'syria-oil-widgets' ),
			'exists'       => __( 'This email is already subscribed.', 'syria-oil-widgets' ),
			'invalid'      => __( 'Please enter a valid email address.', 'syria-oil-widgets' ),
			'confirmed'    => __( 'Subscription confirmed. Welcome!', 'syria-oil-widgets' ),
			'unsubscribed' => __( 'You have been unsubscribed.', 'syria-oil-widgets' ),
		);
		if ( ! isset( $map[ $code ] ) ) {
			return;
		}
		$bg = in_array( $code, array( 'invalid' ), true ) ? '#b01020' : '#007a3d';
		printf(
			'<div class="sow-nl-toast" role="status" style="position:fixed;bottom:1.25rem;inset-inline-start:1.25rem;z-index:9999;background:%1$s;color:#fff;padding:.85rem 1.1rem;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.2);max-width:22rem;font-size:.92rem">%2$s</div><script>setTimeout(function(){var t=document.querySelector(".sow-nl-toast");if(t)t.remove();},6000);</script>',
			esc_attr( $bg ),
			esc_html( $map[ $code ] )
		);
	}
}
