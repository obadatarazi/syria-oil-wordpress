<?php
/**
 * Newsletter subscribers — CPT storage + helpers.
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets\Newsletter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Subscriber registry.
 */
class Subscribers {

	const POST_TYPE = 'sow_nl_subscriber';

	/**
	 * Register CPT + columns.
	 */
	public static function register() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'               => __( 'Newsletter subscribers', 'syria-oil-widgets' ),
					'singular_name'      => __( 'Subscriber', 'syria-oil-widgets' ),
					'add_new'            => __( 'Add subscriber', 'syria-oil-widgets' ),
					'add_new_item'       => __( 'Add subscriber', 'syria-oil-widgets' ),
					'edit_item'          => __( 'Edit subscriber', 'syria-oil-widgets' ),
					'new_item'           => __( 'New subscriber', 'syria-oil-widgets' ),
					'view_item'          => __( 'View subscriber', 'syria-oil-widgets' ),
					'search_items'       => __( 'Search subscribers', 'syria-oil-widgets' ),
					'not_found'          => __( 'No subscribers found.', 'syria-oil-widgets' ),
					'not_found_in_trash' => __( 'No subscribers in trash.', 'syria-oil-widgets' ),
					'menu_name'          => __( 'Subscribers', 'syria-oil-widgets' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => 'syria-oil-newsletter',
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'hierarchical'        => false,
				'supports'            => array( 'title' ),
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'delete_with_user'    => false,
			)
		);

		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_filter( 'enter_title_here', array( __CLASS__, 'title_placeholder' ), 10, 2 );
	}

	/**
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function columns( $columns ) {
		return array(
			'cb'         => $columns['cb'],
			'title'      => __( 'Email', 'syria-oil-widgets' ),
			'nl_status'  => __( 'Status', 'syria-oil-widgets' ),
			'nl_source'  => __( 'Source', 'syria-oil-widgets' ),
			'nl_date'    => __( 'Subscribed', 'syria-oil-widgets' ),
		);
	}

	/**
	 * @param string $column Column key.
	 * @param int    $post_id Post ID.
	 */
	public static function column_content( $column, $post_id ) {
		if ( 'nl_status' === $column ) {
			$status = self::get_status( $post_id );
			$labels = self::status_labels();
			echo esc_html( isset( $labels[ $status ] ) ? $labels[ $status ] : $status );
			return;
		}
		if ( 'nl_source' === $column ) {
			echo esc_html( (string) get_post_meta( $post_id, '_sow_nl_source', true ) );
			return;
		}
		if ( 'nl_date' === $column ) {
			echo esc_html( get_the_date( '', $post_id ) . ' ' . get_the_time( '', $post_id ) );
		}
	}

	/**
	 * @param string   $text Placeholder.
	 * @param \WP_Post $post Post.
	 * @return string
	 */
	public static function title_placeholder( $text, $post ) {
		if ( $post && self::POST_TYPE === $post->post_type ) {
			return __( 'subscriber@example.com', 'syria-oil-widgets' );
		}
		return $text;
	}

	/**
	 * @return array<string,string>
	 */
	public static function status_labels() {
		return array(
			'pending'      => __( 'Pending confirmation', 'syria-oil-widgets' ),
			'active'       => __( 'Active', 'syria-oil-widgets' ),
			'unsubscribed' => __( 'Unsubscribed', 'syria-oil-widgets' ),
		);
	}

	/**
	 * @param int $post_id Subscriber ID.
	 * @return string
	 */
	public static function get_status( $post_id ) {
		$status = (string) get_post_meta( $post_id, '_sow_nl_status', true );
		return $status ? $status : 'active';
	}

	/**
	 * Find subscriber by email.
	 *
	 * @param string $email Email.
	 * @return int Post ID or 0.
	 */
	public static function find_by_email( $email ) {
		$email = strtolower( sanitize_email( $email ) );
		if ( ! $email ) {
			return 0;
		}
		$q = new \WP_Query(
			array(
				'post_type'              => self::POST_TYPE,
				'post_status'            => 'any',
				'title'                  => $email,
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
		if ( $q->have_posts() ) {
			return (int) $q->posts[0];
		}

		// Fallback: exact title match across statuses (WP title query can be fuzzy).
		global $wpdb;
		$id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_title = %s LIMIT 1",
				self::POST_TYPE,
				$email
			)
		);
		return $id;
	}

	/**
	 * Create or reactivate a subscriber.
	 *
	 * @param string $email  Email.
	 * @param array  $args   Optional: source, status, double_opt_in.
	 * @return array{id:int,created:bool,status:string}|null
	 */
	public static function subscribe( $email, $args = array() ) {
		$email = strtolower( sanitize_email( $email ) );
		if ( ! is_email( $email ) ) {
			return null;
		}

		$source   = isset( $args['source'] ) ? sanitize_text_field( $args['source'] ) : 'website';
		$double   = ! empty( $args['double_opt_in'] );
		$status   = $double ? 'pending' : 'active';
		$existing = self::find_by_email( $email );

		if ( $existing ) {
			$current = self::get_status( $existing );
			if ( 'unsubscribed' === $current || 'pending' === $current ) {
				update_post_meta( $existing, '_sow_nl_status', $status );
				update_post_meta( $existing, '_sow_nl_token', wp_generate_password( 32, false, false ) );
				update_post_meta( $existing, '_sow_nl_source', $source );
				wp_update_post(
					array(
						'ID'          => $existing,
						'post_status' => 'publish',
					)
				);
			}
			return array(
				'id'      => $existing,
				'created' => false,
				'status'  => self::get_status( $existing ),
			);
		}

		$id = wp_insert_post(
			array(
				'post_type'   => self::POST_TYPE,
				'post_title'  => $email,
				'post_status' => 'publish',
				'post_author' => 1,
			),
			true
		);
		if ( is_wp_error( $id ) || ! $id ) {
			return null;
		}

		update_post_meta( $id, '_sow_nl_status', $status );
		update_post_meta( $id, '_sow_nl_source', $source );
		update_post_meta( $id, '_sow_nl_token', wp_generate_password( 32, false, false ) );
		update_post_meta( $id, '_sow_nl_ip', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );

		return array(
			'id'      => (int) $id,
			'created' => true,
			'status'  => $status,
		);
	}

	/**
	 * @param int $post_id Subscriber ID.
	 * @return string
	 */
	public static function get_token( $post_id ) {
		$token = (string) get_post_meta( $post_id, '_sow_nl_token', true );
		if ( ! $token ) {
			$token = wp_generate_password( 32, false, false );
			update_post_meta( $post_id, '_sow_nl_token', $token );
		}
		return $token;
	}

	/**
	 * Active subscriber emails.
	 *
	 * @return string[]
	 */
	public static function active_emails() {
		$q = new \WP_Query(
			array(
				'post_type'              => self::POST_TYPE,
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => '_sow_nl_status',
						'value' => 'active',
					),
				),
			)
		);
		$emails = array();
		foreach ( $q->posts as $id ) {
			$title = get_the_title( $id );
			if ( is_email( $title ) ) {
				$emails[] = $title;
			}
		}
		return $emails;
	}

	/**
	 * Confirm pending subscription.
	 *
	 * @param int    $id    Subscriber ID.
	 * @param string $token Token.
	 * @return bool
	 */
	public static function confirm( $id, $token ) {
		$id = absint( $id );
		if ( ! $id || self::get_token( $id ) !== (string) $token ) {
			return false;
		}
		update_post_meta( $id, '_sow_nl_status', 'active' );
		return true;
	}

	/**
	 * Unsubscribe by id + token.
	 *
	 * @param int    $id    Subscriber ID.
	 * @param string $token Token.
	 * @return bool
	 */
	public static function unsubscribe( $id, $token ) {
		$id = absint( $id );
		if ( ! $id || self::get_token( $id ) !== (string) $token ) {
			return false;
		}
		update_post_meta( $id, '_sow_nl_status', 'unsubscribed' );
		return true;
	}
}
