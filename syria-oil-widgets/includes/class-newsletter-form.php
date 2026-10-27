<?php
/**
 * Newsletter form handler (nonce + honeypot) — stores subscribers.
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets;

use Syria_Oil_Widgets\Newsletter\Mailer;
use Syria_Oil_Widgets\Newsletter\Subscribers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles admin-post newsletter submissions.
 */
class Newsletter_Form {

	/**
	 * @var Newsletter_Form|null
	 */
	private static $instance = null;

	/**
	 * @return Newsletter_Form
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register hooks.
	 */
	public function register_hooks() {
		add_action( 'admin_post_nopriv_syria_oil_newsletter', array( $this, 'handle' ) );
		add_action( 'admin_post_syria_oil_newsletter', array( $this, 'handle' ) );
	}

	/**
	 * Process form submission.
	 */
	public function handle() {
		$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );

		if ( ! empty( $_POST['syria_oil_hp'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- honeypot.
			wp_safe_redirect( $redirect );
			exit;
		}

		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'syria_oil_newsletter' ) ) {
			wp_safe_redirect( add_query_arg( 'sow_nl', 'invalid', $redirect ) );
			exit;
		}

		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( ! is_email( $email ) ) {
			wp_safe_redirect( add_query_arg( 'sow_nl', 'invalid', $redirect ) );
			exit;
		}

		$settings = class_exists( Mailer::class ) ? Mailer::settings() : array( 'double_opt_in' => 0 );
		$result   = Subscribers::subscribe(
			$email,
			array(
				'source'        => 'website',
				'double_opt_in' => ! empty( $settings['double_opt_in'] ),
			)
		);

		/**
		 * Fires when a newsletter email is collected.
		 *
		 * @param string     $email  Email address.
		 * @param array|null $result Subscribe result.
		 */
		do_action( 'syria_oil_widgets_newsletter_subscribe', $email, $result );

		$code = 'ok';
		if ( ! $result ) {
			$code = 'invalid';
		} elseif ( 'pending' === $result['status'] ) {
			if ( ! empty( $result['created'] ) || 'pending' === $result['status'] ) {
				Mailer::send_confirmation( $result['id'] );
			}
			$code = 'pending';
		} elseif ( empty( $result['created'] ) && 'active' === $result['status'] ) {
			$code = 'exists';
		}

		wp_safe_redirect( add_query_arg( 'sow_nl', $code, $redirect ) );
		exit;
	}
}
