<?php
/**
 * Newsletter campaigns — compose + queue.
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets\Newsletter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Campaign CPT.
 */
class Campaigns {

	const POST_TYPE = 'sow_nl_campaign';

	/**
	 * Register CPT.
	 */
	public static function register() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'           => array(
					'name'          => __( 'Campaigns', 'syria-oil-widgets' ),
					'singular_name' => __( 'Campaign', 'syria-oil-widgets' ),
					'add_new'       => __( 'New campaign', 'syria-oil-widgets' ),
					'add_new_item'  => __( 'New campaign', 'syria-oil-widgets' ),
					'edit_item'     => __( 'Edit campaign', 'syria-oil-widgets' ),
					'menu_name'     => __( 'Campaigns', 'syria-oil-widgets' ),
				),
				'public'           => false,
				'show_ui'          => true,
				'show_in_menu'     => 'syria-oil-newsletter',
				'capability_type'  => 'post',
				'map_meta_cap'     => true,
				'supports'         => array( 'title', 'editor' ),
				'has_archive'      => false,
				'rewrite'          => false,
				'query_var'        => false,
			)
		);

		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save_meta' ) );
		add_action( 'admin_post_sow_nl_send_campaign', array( __CLASS__, 'handle_send' ) );
	}

	/**
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['nl_send_status'] = __( 'Send status', 'syria-oil-widgets' );
				$new['nl_sent']        = __( 'Sent', 'syria-oil-widgets' );
			}
		}
		return $new;
	}

	/**
	 * @param string $column Column.
	 * @param int    $post_id ID.
	 */
	public static function column_content( $column, $post_id ) {
		if ( 'nl_send_status' === $column ) {
			$status = self::get_send_status( $post_id );
			$labels = array(
				'draft'     => __( 'Draft', 'syria-oil-widgets' ),
				'queued'    => __( 'Queued', 'syria-oil-widgets' ),
				'sending'   => __( 'Sending', 'syria-oil-widgets' ),
				'sent'      => __( 'Sent', 'syria-oil-widgets' ),
				'failed'    => __( 'Failed', 'syria-oil-widgets' ),
			);
			echo esc_html( isset( $labels[ $status ] ) ? $labels[ $status ] : $status );
			return;
		}
		if ( 'nl_sent' === $column ) {
			$sent  = (int) get_post_meta( $post_id, '_sow_nl_sent_count', true );
			$total = (int) get_post_meta( $post_id, '_sow_nl_total_count', true );
			if ( $total ) {
				echo esc_html( $sent . ' / ' . $total );
			} else {
				echo esc_html( (string) $sent );
			}
		}
	}

	/**
	 * @param int $post_id Campaign ID.
	 * @return string
	 */
	public static function get_send_status( $post_id ) {
		$status = (string) get_post_meta( $post_id, '_sow_nl_send_status', true );
		return $status ? $status : 'draft';
	}

	/**
	 * Meta boxes.
	 */
	public static function meta_boxes() {
		add_meta_box(
			'sow_nl_campaign_send',
			__( 'Send campaign', 'syria-oil-widgets' ),
			array( __CLASS__, 'render_send_box' ),
			self::POST_TYPE,
			'side',
			'high'
		);
	}

	/**
	 * @param \WP_Post $post Post.
	 */
	public static function render_send_box( $post ) {
		wp_nonce_field( 'sow_nl_campaign_meta', 'sow_nl_campaign_nonce' );
		$status = self::get_send_status( $post->ID );
		$active = count( Subscribers::active_emails() );
		$subject = (string) get_post_meta( $post->ID, '_sow_nl_subject', true );
		if ( ! $subject ) {
			$subject = $post->post_title;
		}
		?>
		<p>
			<label for="sow_nl_subject"><strong><?php esc_html_e( 'Email subject', 'syria-oil-widgets' ); ?></strong></label>
			<input type="text" class="widefat" id="sow_nl_subject" name="sow_nl_subject" value="<?php echo esc_attr( $subject ); ?>" />
		</p>
		<p class="description">
			<?php
			printf(
				/* translators: %d: active subscriber count */
				esc_html__( 'Active subscribers: %d', 'syria-oil-widgets' ),
				(int) $active
			);
			?>
		</p>
		<p><strong><?php esc_html_e( 'Status:', 'syria-oil-widgets' ); ?></strong> <?php echo esc_html( $status ); ?></p>
		<?php if ( in_array( $status, array( 'draft', 'sent', 'failed' ), true ) && $active > 0 ) : ?>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=sow_nl_send_campaign&campaign_id=' . (int) $post->ID ), 'sow_nl_send_' . (int) $post->ID ) ); ?>">
					<?php esc_html_e( 'Queue send to all active subscribers', 'syria-oil-widgets' ); ?>
				</a>
			</p>
			<p class="description"><?php esc_html_e( 'Emails are sent in small batches via WP-Cron so your host is not overloaded.', 'syria-oil-widgets' ); ?></p>
		<?php elseif ( $active < 1 ) : ?>
			<p class="description"><?php esc_html_e( 'No active subscribers yet.', 'syria-oil-widgets' ); ?></p>
		<?php else : ?>
			<p class="description"><?php esc_html_e( 'This campaign is already queued or sending.', 'syria-oil-widgets' ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * @param int $post_id Post ID.
	 */
	public static function save_meta( $post_id ) {
		if ( ! isset( $_POST['sow_nl_campaign_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sow_nl_campaign_nonce'] ) ), 'sow_nl_campaign_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( isset( $_POST['sow_nl_subject'] ) ) {
			update_post_meta( $post_id, '_sow_nl_subject', sanitize_text_field( wp_unslash( $_POST['sow_nl_subject'] ) ) );
		}
	}

	/**
	 * Queue a campaign for sending.
	 */
	public static function handle_send() {
		$campaign_id = isset( $_GET['campaign_id'] ) ? absint( $_GET['campaign_id'] ) : 0;
		if ( ! $campaign_id || ! current_user_can( 'edit_post', $campaign_id ) ) {
			wp_die( esc_html__( 'You cannot send this campaign.', 'syria-oil-widgets' ) );
		}
		check_admin_referer( 'sow_nl_send_' . $campaign_id );

		$emails = Subscribers::active_emails();
		if ( ! $emails ) {
			wp_safe_redirect( get_edit_post_link( $campaign_id, 'raw' ) );
			exit;
		}

		update_post_meta( $campaign_id, '_sow_nl_send_status', 'queued' );
		update_post_meta( $campaign_id, '_sow_nl_queue', $emails );
		update_post_meta( $campaign_id, '_sow_nl_total_count', count( $emails ) );
		update_post_meta( $campaign_id, '_sow_nl_sent_count', 0 );
		update_post_meta( $campaign_id, '_sow_nl_fail_count', 0 );
		update_post_meta( $campaign_id, '_sow_nl_queued_at', time() );

		Mailer::schedule();

		wp_safe_redirect(
			add_query_arg(
				array(
					'post'    => $campaign_id,
					'action'  => 'edit',
					'sow_nl'  => 'queued',
				),
				admin_url( 'post.php' )
			)
		);
		exit;
	}
}
