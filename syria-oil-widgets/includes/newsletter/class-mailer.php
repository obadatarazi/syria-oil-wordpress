<?php
/**
 * Newsletter mailer — batched WP-Cron sending.
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets\Newsletter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sends campaign emails in batches.
 */
class Mailer {

	const CRON_HOOK = 'syria_oil_nl_process_queue';
	const BATCH     = 20;

	/**
	 * Register cron + public unsubscribe/confirm endpoints.
	 */
	public static function register() {
		add_action( self::CRON_HOOK, array( __CLASS__, 'process_queue' ) );
		add_action( 'init', array( __CLASS__, 'maybe_handle_public_actions' ) );
		add_filter( 'cron_schedules', array( __CLASS__, 'cron_interval' ) );
	}

	/**
	 * @param array $schedules Schedules.
	 * @return array
	 */
	public static function cron_interval( $schedules ) {
		if ( ! isset( $schedules['syria_oil_nl_minute'] ) ) {
			$schedules['syria_oil_nl_minute'] = array(
				'interval' => 60,
				'display'  => __( 'Every minute (newsletter)', 'syria-oil-widgets' ),
			);
		}
		return $schedules;
	}

	/**
	 * Ensure cron is scheduled.
	 */
	public static function schedule() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + 15, 'syria_oil_nl_minute', self::CRON_HOOK );
		}
	}

	/**
	 * Clear cron on deactivate.
	 */
	public static function clear_cron() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		while ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
			$timestamp = wp_next_scheduled( self::CRON_HOOK );
		}
	}

	/**
	 * Settings defaults.
	 *
	 * @return array<string,mixed>
	 */
	public static function settings() {
		$defaults = array(
			'from_name'     => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			'from_email'    => get_option( 'admin_email' ),
			'double_opt_in' => 0,
			'footer_text'   => __( 'You receive this email because you subscribed on our website.', 'syria-oil-widgets' ),
			'reply_to'      => '',
		);
		$saved = get_option( 'syria_oil_newsletter_settings', array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return array_merge( $defaults, $saved );
	}

	/**
	 * Process queued campaigns.
	 */
	public static function process_queue() {
		$q = new \WP_Query(
			array(
				'post_type'      => Campaigns::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => 3,
				'fields'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_sow_nl_send_status',
						'value'   => array( 'queued', 'sending' ),
						'compare' => 'IN',
					),
				),
			)
		);

		if ( ! $q->posts ) {
			self::clear_cron();
			return;
		}

		foreach ( $q->posts as $campaign_id ) {
			self::process_campaign( (int) $campaign_id );
		}

		$still = new \WP_Query(
			array(
				'post_type'      => Campaigns::POST_TYPE,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_sow_nl_send_status',
						'value'   => array( 'queued', 'sending' ),
						'compare' => 'IN',
					),
				),
			)
		);
		if ( $still->posts ) {
			self::schedule();
		} else {
			self::clear_cron();
		}
	}

	/**
	 * @param int $campaign_id Campaign ID.
	 */
	public static function process_campaign( $campaign_id ) {
		$queue = get_post_meta( $campaign_id, '_sow_nl_queue', true );
		if ( ! is_array( $queue ) || ! $queue ) {
			update_post_meta( $campaign_id, '_sow_nl_send_status', 'sent' );
			return;
		}

		update_post_meta( $campaign_id, '_sow_nl_send_status', 'sending' );

		$batch  = array_splice( $queue, 0, self::BATCH );
		$sent   = (int) get_post_meta( $campaign_id, '_sow_nl_sent_count', true );
		$failed = (int) get_post_meta( $campaign_id, '_sow_nl_fail_count', true );
		$subject = (string) get_post_meta( $campaign_id, '_sow_nl_subject', true );
		if ( ! $subject ) {
			$subject = get_the_title( $campaign_id );
		}
		$body = apply_filters( 'the_content', get_post_field( 'post_content', $campaign_id ) );

		foreach ( $batch as $email ) {
			$email = sanitize_email( $email );
			if ( ! is_email( $email ) ) {
				++$failed;
				continue;
			}
			$sub_id = Subscribers::find_by_email( $email );
			$html   = self::wrap_email( $body, $sub_id, $email );
			$ok     = self::send( $email, $subject, $html );
			if ( $ok ) {
				++$sent;
			} else {
				++$failed;
			}
		}

		update_post_meta( $campaign_id, '_sow_nl_queue', $queue );
		update_post_meta( $campaign_id, '_sow_nl_sent_count', $sent );
		update_post_meta( $campaign_id, '_sow_nl_fail_count', $failed );

		if ( ! $queue ) {
			update_post_meta( $campaign_id, '_sow_nl_send_status', 'sent' );
			update_post_meta( $campaign_id, '_sow_nl_sent_at', time() );
		}
	}

	/**
	 * @param string $html   Body HTML.
	 * @param int    $sub_id Subscriber ID.
	 * @param string $email  Email.
	 * @return string
	 */
	public static function wrap_email( $html, $sub_id, $email ) {
		$settings = self::settings();
		$unsub    = '';
		if ( $sub_id ) {
			$unsub = add_query_arg(
				array(
					'sow_nl_action' => 'unsubscribe',
					'sid'           => (int) $sub_id,
					'token'         => Subscribers::get_token( $sub_id ),
				),
				home_url( '/' )
			);
		}

		$footer = esc_html( (string) $settings['footer_text'] );
		$link   = $unsub
			? '<p style="margin-top:24px;font-size:12px;color:#64748b"><a href="' . esc_url( $unsub ) . '">' . esc_html__( 'Unsubscribe', 'syria-oil-widgets' ) . '</a></p>'
			: '';

		return '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;line-height:1.6;color:#0f1419;max-width:640px;margin:0 auto;padding:24px">'
			. $html
			. '<hr style="border:0;border-top:1px solid #e2e8f0;margin:28px 0" />'
			. '<p style="font-size:12px;color:#64748b">' . $footer . '</p>'
			. $link
			. '</body></html>';
	}

	/**
	 * @param string $to      Recipient.
	 * @param string $subject Subject.
	 * @param string $html    HTML body.
	 * @return bool
	 */
	public static function send( $to, $subject, $html ) {
		$settings   = self::settings();
		$headers    = array( 'Content-Type: text/html; charset=UTF-8' );
		$from_name  = (string) $settings['from_name'];
		$from_email = sanitize_email( (string) $settings['from_email'] );
		if ( $from_email ) {
			$headers[] = 'From: ' . sprintf( '%s <%s>', $from_name, $from_email );
		}
		if ( ! empty( $settings['reply_to'] ) && is_email( $settings['reply_to'] ) ) {
			$headers[] = 'Reply-To: ' . sanitize_email( $settings['reply_to'] );
		}

		return (bool) wp_mail( $to, $subject, $html, $headers );
	}

	/**
	 * Send confirmation email for double opt-in.
	 *
	 * @param int $subscriber_id Subscriber ID.
	 * @return bool
	 */
	public static function send_confirmation( $subscriber_id ) {
		$email = get_the_title( $subscriber_id );
		if ( ! is_email( $email ) ) {
			return false;
		}
		$url = add_query_arg(
			array(
				'sow_nl_action' => 'confirm',
				'sid'           => (int) $subscriber_id,
				'token'         => Subscribers::get_token( $subscriber_id ),
			),
			home_url( '/' )
		);
		$html = '<p>' . esc_html__( 'Please confirm your newsletter subscription:', 'syria-oil-widgets' ) . '</p>'
			. '<p><a href="' . esc_url( $url ) . '">' . esc_html__( 'Confirm subscription', 'syria-oil-widgets' ) . '</a></p>';
		return self::send( $email, __( 'Confirm your subscription', 'syria-oil-widgets' ), self::wrap_email( $html, 0, $email ) );
	}

	/**
	 * Public confirm / unsubscribe.
	 */
	public static function maybe_handle_public_actions() {
		if ( empty( $_GET['sow_nl_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$action = sanitize_key( wp_unslash( $_GET['sow_nl_action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$sid    = isset( $_GET['sid'] ) ? absint( $_GET['sid'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$token  = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'confirm' === $action && Subscribers::confirm( $sid, $token ) ) {
			wp_safe_redirect( add_query_arg( 'sow_nl', 'confirmed', home_url( '/' ) ) );
			exit;
		}
		if ( 'unsubscribe' === $action && Subscribers::unsubscribe( $sid, $token ) ) {
			wp_safe_redirect( add_query_arg( 'sow_nl', 'unsubscribed', home_url( '/' ) ) );
			exit;
		}
	}
}
