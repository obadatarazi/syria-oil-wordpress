<?php
/**
 * Careers / Jobs: CPT, applications inbox, helpers, apply form, demo seed.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the careers module is enabled in Theme Options.
 *
 * @return bool
 */
function syria_oil_careers_enabled() {
	if ( function_exists( 'syria_oil_mod_enabled' ) ) {
		return syria_oil_mod_enabled( 'careers_enabled', true );
	}
	return (bool) ( function_exists( 'syria_oil_mod' ) ? syria_oil_mod( 'careers_enabled', true ) : true );
}

/**
 * Employment type options.
 *
 * @return array<string,string>
 */
function syria_oil_job_type_options() {
	return array(
		'full_time'   => __( 'دوام كامل', 'syria-oil' ),
		'part_time'   => __( 'دوام جزئي', 'syria-oil' ),
		'contract'    => __( 'عقد', 'syria-oil' ),
		'internship'  => __( 'تدريب', 'syria-oil' ),
	);
}

/**
 * Work mode options.
 *
 * @return array<string,string>
 */
function syria_oil_job_mode_options() {
	return array(
		'onsite'  => __( 'حضوري', 'syria-oil' ),
		'hybrid'  => __( 'هجين', 'syria-oil' ),
		'remote'  => __( 'عن بُعد', 'syria-oil' ),
	);
}

/**
 * Job status options.
 *
 * @return array<string,string>
 */
function syria_oil_job_status_options() {
	return array(
		'open'   => __( 'مفتوحة', 'syria-oil' ),
		'closed' => __( 'مغلقة', 'syria-oil' ),
		'filled' => __( 'تم التوظيف', 'syria-oil' ),
	);
}

/**
 * Register Jobs CPT.
 */
function syria_oil_register_job_cpt() {
	$labels = array(
		'name'               => __( 'فرص العمل', 'syria-oil' ),
		'singular_name'      => __( 'فرصة عمل', 'syria-oil' ),
		'add_new'            => __( 'إضافة فرصة', 'syria-oil' ),
		'add_new_item'       => __( 'إضافة فرصة عمل جديدة', 'syria-oil' ),
		'edit_item'          => __( 'تعديل فرصة العمل', 'syria-oil' ),
		'new_item'           => __( 'فرصة عمل جديدة', 'syria-oil' ),
		'view_item'          => __( 'عرض فرصة العمل', 'syria-oil' ),
		'search_items'       => __( 'بحث في فرص العمل', 'syria-oil' ),
		'not_found'          => __( 'لا توجد فرص عمل', 'syria-oil' ),
		'not_found_in_trash' => __( 'لا توجد فرص في سلة المهملات', 'syria-oil' ),
		'menu_name'          => __( 'فرص العمل', 'syria-oil' ),
		'all_items'          => __( 'كل الفرص', 'syria-oil' ),
	);

	register_post_type(
		'syria_oil_job',
		array(
			'labels'              => $labels,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-businessman',
			'menu_position'       => 25,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'excerpt' ),
			'capability_type'     => 'post',
			'has_archive'         => false,
			'rewrite'             => array( 'slug' => 'job', 'with_front' => false ),
			'show_in_rest'        => true,
			'exclude_from_search' => false,
		)
	);
}
add_action( 'init', 'syria_oil_register_job_cpt' );

/**
 * Register Job Applications CPT (admin inbox under Careers).
 */
function syria_oil_register_job_application_cpt() {
	$labels = array(
		'name'               => __( 'طلبات التقديم', 'syria-oil' ),
		'singular_name'      => __( 'طلب تقديم', 'syria-oil' ),
		'edit_item'          => __( 'عرض طلب التقديم', 'syria-oil' ),
		'search_items'       => __( 'بحث في الطلبات', 'syria-oil' ),
		'not_found'          => __( 'لا توجد طلبات', 'syria-oil' ),
		'not_found_in_trash' => __( 'لا توجد طلبات في سلة المهملات', 'syria-oil' ),
		'menu_name'          => __( 'طلبات التقديم', 'syria-oil' ),
		'all_items'          => __( 'طلبات التقديم', 'syria-oil' ),
	);

	register_post_type(
		'syria_oil_job_application',
		array(
			'labels'              => $labels,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=syria_oil_job',
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
			'capabilities'        => array(
				'create_posts' => 'do_not_allow',
			),
			'map_meta_cap'        => true,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'rewrite'             => false,
		)
	);
}
add_action( 'init', 'syria_oil_register_job_application_cpt' );

/**
 * Flush rewrite rules once after jobs CPT ships.
 */
function syria_oil_careers_maybe_flush_rewrites() {
	if ( get_option( 'syria_oil_careers_rewrite_v1' ) ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'syria_oil_careers_rewrite_v1', '1', false );
}
add_action( 'init', 'syria_oil_careers_maybe_flush_rewrites', 99 );

/**
 * Job metabox.
 */
function syria_oil_job_register_metabox() {
	add_meta_box(
		'syria_oil_job_details',
		__( 'تفاصيل فرصة العمل', 'syria-oil' ),
		'syria_oil_job_render_metabox',
		'syria_oil_job',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'syria_oil_job_register_metabox' );

/**
 * @param WP_Post $post Post.
 */
function syria_oil_job_render_metabox( $post ) {
	wp_nonce_field( 'syria_oil_job_save', 'syria_oil_job_nonce' );

	$entity     = (string) get_post_meta( $post->ID, '_syria_oil_job_entity', true );
	$positions  = absint( get_post_meta( $post->ID, '_syria_oil_job_positions', true ) );
	$type       = (string) get_post_meta( $post->ID, '_syria_oil_job_type', true );
	$mode       = (string) get_post_meta( $post->ID, '_syria_oil_job_mode', true );
	$location   = (string) get_post_meta( $post->ID, '_syria_oil_job_location', true );
	$department = (string) get_post_meta( $post->ID, '_syria_oil_job_department', true );
	$experience = (string) get_post_meta( $post->ID, '_syria_oil_job_experience', true );
	$education  = (string) get_post_meta( $post->ID, '_syria_oil_job_education', true );
	$salary     = (string) get_post_meta( $post->ID, '_syria_oil_job_salary', true );
	$deadline   = (string) get_post_meta( $post->ID, '_syria_oil_job_deadline', true );
	$status     = (string) get_post_meta( $post->ID, '_syria_oil_job_status', true );
	$email      = (string) get_post_meta( $post->ID, '_syria_oil_job_email', true );
	$apply_url  = (string) get_post_meta( $post->ID, '_syria_oil_job_apply_url', true );
	$featured   = (string) get_post_meta( $post->ID, '_syria_oil_job_featured', true );
	$show_page  = get_post_meta( $post->ID, '_syria_oil_job_show_page', true );

	if ( ! isset( syria_oil_job_type_options()[ $type ] ) ) {
		$type = 'full_time';
	}
	if ( ! isset( syria_oil_job_mode_options()[ $mode ] ) ) {
		$mode = 'onsite';
	}
	if ( ! isset( syria_oil_job_status_options()[ $status ] ) ) {
		$status = 'open';
	}
	if ( '' === $show_page ) {
		$show_page = '1';
	}
	if ( $positions < 1 ) {
		$positions = 1;
	}

	echo '<table class="form-table" role="presentation"><tbody>';

	echo '<tr><th><label for="syria_oil_job_entity">' . esc_html__( 'اسم الجهة / الشركة', 'syria-oil' ) . '</label></th><td>';
	echo '<input type="text" class="widefat" id="syria_oil_job_entity" name="syria_oil_job_entity" value="' . esc_attr( $entity ) . '" /></td></tr>';

	echo '<tr><th><label for="syria_oil_job_positions">' . esc_html__( 'عدد الموظفين المطلوبين', 'syria-oil' ) . '</label></th><td>';
	echo '<input type="number" min="1" max="9999" id="syria_oil_job_positions" name="syria_oil_job_positions" value="' . esc_attr( (string) $positions ) . '" /></td></tr>';

	echo '<tr><th><label for="syria_oil_job_type">' . esc_html__( 'نوع التوظيف', 'syria-oil' ) . '</label></th><td><select id="syria_oil_job_type" name="syria_oil_job_type">';
	foreach ( syria_oil_job_type_options() as $val => $label ) {
		printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $val ), selected( $type, $val, false ), esc_html( $label ) );
	}
	echo '</select></td></tr>';

	echo '<tr><th><label for="syria_oil_job_mode">' . esc_html__( 'نمط العمل', 'syria-oil' ) . '</label></th><td><select id="syria_oil_job_mode" name="syria_oil_job_mode">';
	foreach ( syria_oil_job_mode_options() as $val => $label ) {
		printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $val ), selected( $mode, $val, false ), esc_html( $label ) );
	}
	echo '</select></td></tr>';

	echo '<tr><th><label for="syria_oil_job_location">' . esc_html__( 'الموقع / المدينة', 'syria-oil' ) . '</label></th><td>';
	echo '<input type="text" class="widefat" id="syria_oil_job_location" name="syria_oil_job_location" value="' . esc_attr( $location ) . '" /></td></tr>';

	echo '<tr><th><label for="syria_oil_job_department">' . esc_html__( 'القسم / التخصص', 'syria-oil' ) . '</label></th><td>';
	echo '<input type="text" class="widefat" id="syria_oil_job_department" name="syria_oil_job_department" value="' . esc_attr( $department ) . '" /></td></tr>';

	echo '<tr><th><label for="syria_oil_job_experience">' . esc_html__( 'الخبرة المطلوبة', 'syria-oil' ) . '</label></th><td>';
	echo '<input type="text" class="widefat" id="syria_oil_job_experience" name="syria_oil_job_experience" value="' . esc_attr( $experience ) . '" placeholder="' . esc_attr__( 'مثال: 3–5 سنوات', 'syria-oil' ) . '" /></td></tr>';

	echo '<tr><th><label for="syria_oil_job_education">' . esc_html__( 'المؤهل العلمي', 'syria-oil' ) . '</label></th><td>';
	echo '<textarea class="widefat" rows="2" id="syria_oil_job_education" name="syria_oil_job_education">' . esc_textarea( $education ) . '</textarea></td></tr>';

	echo '<tr><th><label for="syria_oil_job_salary">' . esc_html__( 'الراتب / المزايا (اختياري)', 'syria-oil' ) . '</label></th><td>';
	echo '<input type="text" class="widefat" id="syria_oil_job_salary" name="syria_oil_job_salary" value="' . esc_attr( $salary ) . '" /></td></tr>';

	echo '<tr><th><label for="syria_oil_job_deadline">' . esc_html__( 'آخر موعد للتقديم', 'syria-oil' ) . '</label></th><td>';
	echo '<input type="date" id="syria_oil_job_deadline" name="syria_oil_job_deadline" value="' . esc_attr( $deadline ) . '" /></td></tr>';

	echo '<tr><th><label for="syria_oil_job_status">' . esc_html__( 'حالة الإعلان', 'syria-oil' ) . '</label></th><td><select id="syria_oil_job_status" name="syria_oil_job_status">';
	foreach ( syria_oil_job_status_options() as $val => $label ) {
		printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $val ), selected( $status, $val, false ), esc_html( $label ) );
	}
	echo '</select></td></tr>';

	echo '<tr><th><label for="syria_oil_job_email">' . esc_html__( 'بريد الاستقبال (اختياري)', 'syria-oil' ) . '</label></th><td>';
	echo '<input type="email" class="widefat" id="syria_oil_job_email" name="syria_oil_job_email" value="' . esc_attr( $email ) . '" placeholder="' . esc_attr__( 'اتركه فارغاً لاستخدام البريد العام من Theme Options', 'syria-oil' ) . '" /></td></tr>';

	echo '<tr><th><label for="syria_oil_job_apply_url">' . esc_html__( 'رابط تقديم خارجي (اختياري)', 'syria-oil' ) . '</label></th><td>';
	echo '<input type="url" class="widefat" id="syria_oil_job_apply_url" name="syria_oil_job_apply_url" value="' . esc_attr( $apply_url ) . '" placeholder="https://" /></td></tr>';

	echo '<tr><th>' . esc_html__( 'العرض', 'syria-oil' ) . '</th><td>';
	echo '<label><input type="checkbox" name="syria_oil_job_featured" value="1" ' . checked( $featured, '1', false ) . ' /> ' . esc_html__( 'مميز على الصفحة الرئيسية', 'syria-oil' ) . '</label><br />';
	echo '<label><input type="checkbox" name="syria_oil_job_show_page" value="1" ' . checked( (string) $show_page, '1', false ) . ' /> ' . esc_html__( 'إظهار في صفحة فرص العمل', 'syria-oil' ) . '</label>';
	echo '</td></tr>';

	echo '</tbody></table>';
	echo '<p class="description">' . esc_html__( 'اكتب الوصف الكامل والمهام والمتطلبات في المحرر أعلاه. الترتيب من حقل Order في مربع النشر.', 'syria-oil' ) . '</p>';
}

/**
 * Save job metabox.
 *
 * @param int $post_id Post ID.
 */
function syria_oil_job_save_metabox( $post_id ) {
	if ( ! isset( $_POST['syria_oil_job_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['syria_oil_job_nonce'] ) ), 'syria_oil_job_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( 'syria_oil_job' !== get_post_type( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$entity = isset( $_POST['syria_oil_job_entity'] ) ? sanitize_text_field( wp_unslash( $_POST['syria_oil_job_entity'] ) ) : '';
	update_post_meta( $post_id, '_syria_oil_job_entity', $entity );

	$positions = isset( $_POST['syria_oil_job_positions'] ) ? absint( $_POST['syria_oil_job_positions'] ) : 1;
	if ( $positions < 1 ) {
		$positions = 1;
	}
	update_post_meta( $post_id, '_syria_oil_job_positions', $positions );

	$type = isset( $_POST['syria_oil_job_type'] ) ? sanitize_text_field( wp_unslash( $_POST['syria_oil_job_type'] ) ) : 'full_time';
	$type = isset( syria_oil_job_type_options()[ $type ] ) ? $type : 'full_time';
	update_post_meta( $post_id, '_syria_oil_job_type', $type );

	$mode = isset( $_POST['syria_oil_job_mode'] ) ? sanitize_text_field( wp_unslash( $_POST['syria_oil_job_mode'] ) ) : 'onsite';
	$mode = isset( syria_oil_job_mode_options()[ $mode ] ) ? $mode : 'onsite';
	update_post_meta( $post_id, '_syria_oil_job_mode', $mode );

	update_post_meta( $post_id, '_syria_oil_job_location', isset( $_POST['syria_oil_job_location'] ) ? sanitize_text_field( wp_unslash( $_POST['syria_oil_job_location'] ) ) : '' );
	update_post_meta( $post_id, '_syria_oil_job_department', isset( $_POST['syria_oil_job_department'] ) ? sanitize_text_field( wp_unslash( $_POST['syria_oil_job_department'] ) ) : '' );
	update_post_meta( $post_id, '_syria_oil_job_experience', isset( $_POST['syria_oil_job_experience'] ) ? sanitize_text_field( wp_unslash( $_POST['syria_oil_job_experience'] ) ) : '' );
	update_post_meta( $post_id, '_syria_oil_job_education', isset( $_POST['syria_oil_job_education'] ) ? sanitize_textarea_field( wp_unslash( $_POST['syria_oil_job_education'] ) ) : '' );
	update_post_meta( $post_id, '_syria_oil_job_salary', isset( $_POST['syria_oil_job_salary'] ) ? sanitize_text_field( wp_unslash( $_POST['syria_oil_job_salary'] ) ) : '' );

	$deadline = isset( $_POST['syria_oil_job_deadline'] ) ? sanitize_text_field( wp_unslash( $_POST['syria_oil_job_deadline'] ) ) : '';
	if ( $deadline && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $deadline ) ) {
		$deadline = '';
	}
	update_post_meta( $post_id, '_syria_oil_job_deadline', $deadline );

	$status = isset( $_POST['syria_oil_job_status'] ) ? sanitize_text_field( wp_unslash( $_POST['syria_oil_job_status'] ) ) : 'open';
	$status = isset( syria_oil_job_status_options()[ $status ] ) ? $status : 'open';
	update_post_meta( $post_id, '_syria_oil_job_status', $status );

	$email = isset( $_POST['syria_oil_job_email'] ) ? sanitize_email( wp_unslash( $_POST['syria_oil_job_email'] ) ) : '';
	update_post_meta( $post_id, '_syria_oil_job_email', $email );

	$apply_url = isset( $_POST['syria_oil_job_apply_url'] ) ? esc_url_raw( wp_unslash( $_POST['syria_oil_job_apply_url'] ) ) : '';
	update_post_meta( $post_id, '_syria_oil_job_apply_url', $apply_url );

	update_post_meta( $post_id, '_syria_oil_job_featured', ! empty( $_POST['syria_oil_job_featured'] ) ? '1' : '0' );
	update_post_meta( $post_id, '_syria_oil_job_show_page', ! empty( $_POST['syria_oil_job_show_page'] ) ? '1' : '0' );
}
add_action( 'save_post_syria_oil_job', 'syria_oil_job_save_metabox' );

/**
 * Admin columns for jobs.
 *
 * @param array $columns Columns.
 * @return array
 */
function syria_oil_job_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['syria_oil_job_entity']    = __( 'الجهة', 'syria-oil' );
			$new['syria_oil_job_positions'] = __( 'العدد', 'syria-oil' );
			$new['syria_oil_job_type']      = __( 'النوع', 'syria-oil' );
			$new['syria_oil_job_location']  = __( 'الموقع', 'syria-oil' );
			$new['syria_oil_job_deadline']  = __( 'الموعد', 'syria-oil' );
			$new['syria_oil_job_status']    = __( 'الحالة', 'syria-oil' );
			$new['syria_oil_job_featured']  = __( 'مميز', 'syria-oil' );
		}
	}
	return $new;
}
add_filter( 'manage_syria_oil_job_posts_columns', 'syria_oil_job_columns' );

/**
 * @param string $column Column.
 * @param int    $post_id Post ID.
 */
function syria_oil_job_column_content( $column, $post_id ) {
	$map = array(
		'syria_oil_job_entity'    => '_syria_oil_job_entity',
		'syria_oil_job_location'  => '_syria_oil_job_location',
		'syria_oil_job_deadline'  => '_syria_oil_job_deadline',
	);
	if ( isset( $map[ $column ] ) ) {
		echo esc_html( (string) get_post_meta( $post_id, $map[ $column ], true ) );
		return;
	}
	if ( 'syria_oil_job_positions' === $column ) {
		echo (int) get_post_meta( $post_id, '_syria_oil_job_positions', true );
		return;
	}
	if ( 'syria_oil_job_type' === $column ) {
		$type = (string) get_post_meta( $post_id, '_syria_oil_job_type', true );
		$opts = syria_oil_job_type_options();
		echo esc_html( isset( $opts[ $type ] ) ? $opts[ $type ] : $type );
		return;
	}
	if ( 'syria_oil_job_status' === $column ) {
		$status = (string) get_post_meta( $post_id, '_syria_oil_job_status', true );
		$opts   = syria_oil_job_status_options();
		$label  = isset( $opts[ $status ] ) ? $opts[ $status ] : $status;
		if ( ! syria_oil_job_is_open( $post_id ) && 'open' === $status ) {
			$label .= ' · ' . __( 'منتهية', 'syria-oil' );
		}
		echo esc_html( $label );
		return;
	}
	if ( 'syria_oil_job_featured' === $column ) {
		echo ( '1' === (string) get_post_meta( $post_id, '_syria_oil_job_featured', true ) ) ? '★' : '—';
	}
}
add_action( 'manage_syria_oil_job_posts_custom_column', 'syria_oil_job_column_content', 10, 2 );

/**
 * Whether a job is currently open (status + deadline).
 *
 * @param int $post_id Job ID.
 * @return bool
 */
function syria_oil_job_is_open( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id || 'publish' !== get_post_status( $post_id ) ) {
		return false;
	}
	$status = (string) get_post_meta( $post_id, '_syria_oil_job_status', true );
	if ( 'open' !== $status && '' !== $status ) {
		return false;
	}
	$deadline = (string) get_post_meta( $post_id, '_syria_oil_job_deadline', true );
	if ( $deadline && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $deadline ) ) {
		$end = strtotime( $deadline . ' 23:59:59' );
		if ( $end && time() > $end ) {
			return false;
		}
	}
	return true;
}

/**
 * Read all job meta as a structured array.
 *
 * @param int $post_id Job ID.
 * @return array<string,mixed>
 */
function syria_oil_get_job_meta( $post_id ) {
	$post_id = absint( $post_id );
	$type    = (string) get_post_meta( $post_id, '_syria_oil_job_type', true );
	$mode    = (string) get_post_meta( $post_id, '_syria_oil_job_mode', true );
	$status  = (string) get_post_meta( $post_id, '_syria_oil_job_status', true );
	$types   = syria_oil_job_type_options();
	$modes   = syria_oil_job_mode_options();
	$statuses = syria_oil_job_status_options();

	return array(
		'entity'     => (string) get_post_meta( $post_id, '_syria_oil_job_entity', true ),
		'positions'  => max( 1, absint( get_post_meta( $post_id, '_syria_oil_job_positions', true ) ) ),
		'type'       => $type,
		'type_label' => isset( $types[ $type ] ) ? $types[ $type ] : $type,
		'mode'       => $mode,
		'mode_label' => isset( $modes[ $mode ] ) ? $modes[ $mode ] : $mode,
		'location'   => (string) get_post_meta( $post_id, '_syria_oil_job_location', true ),
		'department' => (string) get_post_meta( $post_id, '_syria_oil_job_department', true ),
		'experience' => (string) get_post_meta( $post_id, '_syria_oil_job_experience', true ),
		'education'  => (string) get_post_meta( $post_id, '_syria_oil_job_education', true ),
		'salary'     => (string) get_post_meta( $post_id, '_syria_oil_job_salary', true ),
		'deadline'   => (string) get_post_meta( $post_id, '_syria_oil_job_deadline', true ),
		'status'     => $status,
		'status_label' => isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status,
		'email'      => (string) get_post_meta( $post_id, '_syria_oil_job_email', true ),
		'apply_url'  => (string) get_post_meta( $post_id, '_syria_oil_job_apply_url', true ),
		'featured'   => ( '1' === (string) get_post_meta( $post_id, '_syria_oil_job_featured', true ) ),
		'show_page'  => ( '0' !== (string) get_post_meta( $post_id, '_syria_oil_job_show_page', true ) ),
		'is_open'    => syria_oil_job_is_open( $post_id ),
	);
}

/**
 * Query jobs for front display.
 *
 * @param array $args {
 *   @type int  $limit
 *   @type bool $open_only
 *   @type bool $featured_only
 *   @type bool $for_page   Respect show_page meta.
 * }
 * @return WP_Post[]
 */
function syria_oil_get_jobs( $args = array() ) {
	if ( ! syria_oil_careers_enabled() ) {
		return array();
	}

	$args = wp_parse_args(
		$args,
		array(
			'limit'          => 12,
			'open_only'      => true,
			'featured_only'  => false,
			'for_page'       => false,
		)
	);

	$show_closed = function_exists( 'syria_oil_mod_enabled' )
		? syria_oil_mod_enabled( 'careers_show_closed', false )
		: false;
	if ( $show_closed ) {
		$args['open_only'] = false;
	}

	$limit = absint( $args['limit'] );
	if ( $limit < 1 ) {
		$limit = 12;
	}
	if ( $limit > 50 ) {
		$limit = 50;
	}

	$meta_query = array( 'relation' => 'AND' );
	if ( ! empty( $args['featured_only'] ) ) {
		$meta_query[] = array(
			'key'   => '_syria_oil_job_featured',
			'value' => '1',
		);
	}
	if ( ! empty( $args['for_page'] ) ) {
		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'     => '_syria_oil_job_show_page',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'   => '_syria_oil_job_show_page',
				'value' => '1',
			),
		);
	}

	$query_args = array(
		'post_type'              => 'syria_oil_job',
		'post_status'            => 'publish',
		'posts_per_page'         => $limit,
		'orderby'                => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
		'no_found_rows'          => true,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => false,
	);
	if ( count( $meta_query ) > 1 ) {
		$query_args['meta_query'] = $meta_query;
	}

	$q    = new WP_Query( $query_args );
	$jobs = array();
	foreach ( $q->posts as $post ) {
		if ( ! empty( $args['open_only'] ) && ! syria_oil_job_is_open( $post->ID ) ) {
			continue;
		}
		$jobs[] = $post;
	}
	return $jobs;
}

/**
 * Default apply notification email.
 *
 * @param int $job_id Optional job for override.
 * @return string
 */
function syria_oil_job_apply_email( $job_id = 0 ) {
	$job_id = absint( $job_id );
	if ( $job_id ) {
		$override = sanitize_email( (string) get_post_meta( $job_id, '_syria_oil_job_email', true ) );
		if ( $override ) {
			return $override;
		}
	}
	$email = function_exists( 'syria_oil_mod' ) ? sanitize_email( (string) syria_oil_mod( 'careers_apply_email', '' ) ) : '';
	if ( ! $email ) {
		$email = function_exists( 'syria_oil_mod' ) ? sanitize_email( (string) syria_oil_mod( 'contact_email', '' ) ) : '';
	}
	if ( ! $email ) {
		$email = sanitize_email( (string) get_option( 'admin_email' ) );
	}
	return $email;
}

/**
 * Application admin columns.
 *
 * @param array $columns Columns.
 * @return array
 */
function syria_oil_job_app_columns( $columns ) {
	return array(
		'cb'                        => $columns['cb'],
		'title'                     => __( 'مقدم الطلب', 'syria-oil' ),
		'syria_oil_app_job'         => __( 'الوظيفة', 'syria-oil' ),
		'syria_oil_app_email'       => __( 'البريد', 'syria-oil' ),
		'syria_oil_app_phone'       => __( 'الهاتف', 'syria-oil' ),
		'syria_oil_app_cv'          => __( 'السيرة', 'syria-oil' ),
		'date'                      => __( 'التاريخ', 'syria-oil' ),
	);
}
add_filter( 'manage_syria_oil_job_application_posts_columns', 'syria_oil_job_app_columns' );

/**
 * @param string $column Column.
 * @param int    $post_id Post ID.
 */
function syria_oil_job_app_column_content( $column, $post_id ) {
	if ( 'syria_oil_app_job' === $column ) {
		$job_id = absint( get_post_meta( $post_id, '_syria_oil_app_job_id', true ) );
		if ( $job_id ) {
			printf(
				'<a href="%1$s">%2$s</a>',
				esc_url( get_edit_post_link( $job_id ) ),
				esc_html( get_the_title( $job_id ) )
			);
		}
		return;
	}
	if ( 'syria_oil_app_email' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_syria_oil_app_email', true ) );
		return;
	}
	if ( 'syria_oil_app_phone' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_syria_oil_app_phone', true ) );
		return;
	}
	if ( 'syria_oil_app_cv' === $column ) {
		$cv = absint( get_post_meta( $post_id, '_syria_oil_app_cv_id', true ) );
		if ( $cv ) {
			$url = wp_get_attachment_url( $cv );
			if ( $url ) {
				printf( '<a href="%1$s" target="_blank" rel="noopener">%2$s</a>', esc_url( $url ), esc_html__( 'تحميل', 'syria-oil' ) );
			}
		} else {
			echo '—';
		}
	}
}
add_action( 'manage_syria_oil_job_application_posts_custom_column', 'syria_oil_job_app_column_content', 10, 2 );

/**
 * Application detail metabox (read-only).
 */
function syria_oil_job_app_register_metabox() {
	add_meta_box(
		'syria_oil_app_details',
		__( 'تفاصيل الطلب', 'syria-oil' ),
		'syria_oil_job_app_render_metabox',
		'syria_oil_job_application',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'syria_oil_job_app_register_metabox' );

/**
 * @param WP_Post $post Post.
 */
function syria_oil_job_app_render_metabox( $post ) {
	$job_id = absint( get_post_meta( $post->ID, '_syria_oil_app_job_id', true ) );
	$email  = (string) get_post_meta( $post->ID, '_syria_oil_app_email', true );
	$phone  = (string) get_post_meta( $post->ID, '_syria_oil_app_phone', true );
	$note   = (string) get_post_meta( $post->ID, '_syria_oil_app_note', true );
	$cv     = absint( get_post_meta( $post->ID, '_syria_oil_app_cv_id', true ) );

	echo '<p><strong>' . esc_html__( 'الوظيفة:', 'syria-oil' ) . '</strong> ';
	if ( $job_id ) {
		printf( '<a href="%1$s">%2$s</a>', esc_url( get_edit_post_link( $job_id ) ), esc_html( get_the_title( $job_id ) ) );
	} else {
		echo '—';
	}
	echo '</p>';
	echo '<p><strong>' . esc_html__( 'البريد:', 'syria-oil' ) . '</strong> ' . esc_html( $email ) . '</p>';
	echo '<p><strong>' . esc_html__( 'الهاتف:', 'syria-oil' ) . '</strong> ' . esc_html( $phone ) . '</p>';
	if ( $note ) {
		echo '<p><strong>' . esc_html__( 'رسالة:', 'syria-oil' ) . '</strong><br />' . nl2br( esc_html( $note ) ) . '</p>';
	}
	if ( $cv ) {
		$url = wp_get_attachment_url( $cv );
		if ( $url ) {
			echo '<p><strong>' . esc_html__( 'السيرة الذاتية:', 'syria-oil' ) . '</strong> <a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html__( 'فتح الملف', 'syria-oil' ) . '</a></p>';
		}
	}
}

/**
 * Handle front-end job application submit.
 */
function syria_oil_handle_job_application() {
	if ( empty( $_POST['syria_oil_job_apply'] ) ) {
		return;
	}
	if ( ! syria_oil_careers_enabled() ) {
		return;
	}

	$job_id = isset( $_POST['job_id'] ) ? absint( $_POST['job_id'] ) : 0;
	$nonce  = isset( $_POST['syria_oil_job_apply_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['syria_oil_job_apply_nonce'] ) ) : '';

	$redirect = $job_id ? get_permalink( $job_id ) : home_url( '/' );
	if ( ! $redirect ) {
		$redirect = home_url( '/' );
	}

	// Honeypot.
	if ( ! empty( $_POST['syria_oil_company_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'applied', '1', $redirect ) );
		exit;
	}

	if ( ! $job_id || 'syria_oil_job' !== get_post_type( $job_id ) || ! wp_verify_nonce( $nonce, 'syria_oil_job_apply_' . $job_id ) ) {
		wp_safe_redirect( add_query_arg( 'apply_error', 'invalid', $redirect ) );
		exit;
	}

	if ( ! syria_oil_job_is_open( $job_id ) ) {
		wp_safe_redirect( add_query_arg( 'apply_error', 'closed', $redirect ) );
		exit;
	}

	$name  = isset( $_POST['applicant_name'] ) ? sanitize_text_field( wp_unslash( $_POST['applicant_name'] ) ) : '';
	$email = isset( $_POST['applicant_email'] ) ? sanitize_email( wp_unslash( $_POST['applicant_email'] ) ) : '';
	$phone = isset( $_POST['applicant_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['applicant_phone'] ) ) : '';
	$note  = isset( $_POST['applicant_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['applicant_note'] ) ) : '';

	if ( '' === $name || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'apply_error', 'fields', $redirect ) );
		exit;
	}

	$cv_id = 0;
	if ( ! empty( $_FILES['applicant_cv']['name'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$overrides = array(
			'test_form' => false,
			'mimes'     => array(
				'pdf'  => 'application/pdf',
				'doc'  => 'application/msword',
				'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			),
		);
		$upload = wp_handle_upload( $_FILES['applicant_cv'], $overrides ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( empty( $upload['error'] ) && ! empty( $upload['file'] ) ) {
			$filetype  = wp_check_filetype( basename( $upload['file'] ), null );
			$attach_id = wp_insert_attachment(
				array(
					'post_mime_type' => $filetype['type'],
					'post_title'     => sanitize_file_name( basename( $upload['file'] ) ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				),
				$upload['file']
			);
			if ( ! is_wp_error( $attach_id ) && $attach_id ) {
				$cv_id = (int) $attach_id;
			}
		}
	}

	$app_id = wp_insert_post(
		array(
			'post_type'   => 'syria_oil_job_application',
			'post_status' => 'private',
			'post_title'  => $name . ' — ' . get_the_title( $job_id ),
		),
		true
	);

	if ( is_wp_error( $app_id ) || ! $app_id ) {
		wp_safe_redirect( add_query_arg( 'apply_error', 'save', $redirect ) );
		exit;
	}

	update_post_meta( $app_id, '_syria_oil_app_job_id', $job_id );
	update_post_meta( $app_id, '_syria_oil_app_email', $email );
	update_post_meta( $app_id, '_syria_oil_app_phone', $phone );
	update_post_meta( $app_id, '_syria_oil_app_note', $note );
	if ( $cv_id ) {
		update_post_meta( $app_id, '_syria_oil_app_cv_id', $cv_id );
	}

	$to      = syria_oil_job_apply_email( $job_id );
	$subject = sprintf(
		/* translators: 1: applicant name, 2: job title */
		__( 'طلب تقديم جديد: %1$s — %2$s', 'syria-oil' ),
		$name,
		get_the_title( $job_id )
	);
	$body  = __( 'وصل طلب تقديم جديد عبر الموقع.', 'syria-oil' ) . "\n\n";
	$body .= __( 'الوظيفة:', 'syria-oil' ) . ' ' . get_the_title( $job_id ) . "\n";
	$body .= __( 'الاسم:', 'syria-oil' ) . ' ' . $name . "\n";
	$body .= __( 'البريد:', 'syria-oil' ) . ' ' . $email . "\n";
	$body .= __( 'الهاتف:', 'syria-oil' ) . ' ' . $phone . "\n";
	if ( $note ) {
		$body .= __( 'الرسالة:', 'syria-oil' ) . "\n" . $note . "\n";
	}
	$body .= "\n" . admin_url( 'post.php?post=' . $app_id . '&action=edit' );

	$headers = array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $name . ' <' . $email . '>' );
	$attachments = array();
	if ( $cv_id ) {
		$path = get_attached_file( $cv_id );
		if ( $path && is_readable( $path ) ) {
			$attachments[] = $path;
		}
	}
	if ( $to ) {
		wp_mail( $to, $subject, $body, $headers, $attachments );
	}

	wp_safe_redirect( add_query_arg( 'applied', '1', $redirect ) );
	exit;
}
add_action( 'template_redirect', 'syria_oil_handle_job_application' );

/**
 * Render apply form for a job.
 *
 * @param int $job_id Job ID.
 */
function syria_oil_render_job_apply_form( $job_id ) {
	$job_id = absint( $job_id );
	if ( ! $job_id || ! syria_oil_job_is_open( $job_id ) ) {
		return;
	}

	$meta = syria_oil_get_job_meta( $job_id );
	if ( ! empty( $meta['apply_url'] ) ) {
		printf(
			'<p class="so-job-apply-external"><a class="btn btn-primary btn-lg" href="%1$s" target="_blank" rel="noopener">%2$s</a></p>',
			esc_url( $meta['apply_url'] ),
			esc_html__( 'التقديم عبر الرابط الخارجي', 'syria-oil' )
		);
		return;
	}

	if ( isset( $_GET['applied'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<div class="so-job-notice so-job-notice--ok" role="status">' . esc_html__( 'تم استلام طلبك بنجاح. شكراً لاهتمامك.', 'syria-oil' ) . '</div>';
		return;
	}
	if ( ! empty( $_GET['apply_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$err = sanitize_text_field( wp_unslash( $_GET['apply_error'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$msg = __( 'تعذّر إرسال الطلب. تحقق من الحقول وحاول مجدداً.', 'syria-oil' );
		if ( 'closed' === $err ) {
			$msg = __( 'هذه الفرصة لم تعد مفتوحة للتقديم.', 'syria-oil' );
		}
		echo '<div class="so-job-notice so-job-notice--err" role="alert">' . esc_html( $msg ) . '</div>';
	}
	?>
	<form class="so-job-apply" method="post" enctype="multipart/form-data" action="">
		<input type="hidden" name="syria_oil_job_apply" value="1" />
		<input type="hidden" name="job_id" value="<?php echo (int) $job_id; ?>" />
		<?php wp_nonce_field( 'syria_oil_job_apply_' . $job_id, 'syria_oil_job_apply_nonce' ); ?>
		<p class="so-job-apply__hp" aria-hidden="true" style="position:absolute;left:-9999px;opacity:0;height:0;overflow:hidden;">
			<label><?php esc_html_e( 'الموقع', 'syria-oil' ); ?>
				<input type="text" name="syria_oil_company_website" value="" tabindex="-1" autocomplete="off" />
			</label>
		</p>
		<div class="so-job-apply__grid">
			<p>
				<label for="applicant_name"><?php esc_html_e( 'الاسم الكامل', 'syria-oil' ); ?> *</label>
				<input type="text" id="applicant_name" name="applicant_name" required maxlength="120" />
			</p>
			<p>
				<label for="applicant_email"><?php esc_html_e( 'البريد الإلكتروني', 'syria-oil' ); ?> *</label>
				<input type="email" id="applicant_email" name="applicant_email" required maxlength="180" />
			</p>
			<p>
				<label for="applicant_phone"><?php esc_html_e( 'الهاتف', 'syria-oil' ); ?></label>
				<input type="tel" id="applicant_phone" name="applicant_phone" maxlength="40" />
			</p>
			<p>
				<label for="applicant_cv"><?php esc_html_e( 'السيرة الذاتية (PDF / Word)', 'syria-oil' ); ?></label>
				<input type="file" id="applicant_cv" name="applicant_cv" accept=".pdf,.doc,.docx,application/pdf" />
			</p>
			<p class="so-job-apply__full">
				<label for="applicant_note"><?php esc_html_e( 'رسالة مختصرة', 'syria-oil' ); ?></label>
				<textarea id="applicant_note" name="applicant_note" rows="4" maxlength="2000"></textarea>
			</p>
		</div>
		<button type="submit" class="btn btn-primary btn-lg"><?php esc_html_e( 'إرسال الطلب', 'syria-oil' ); ?></button>
	</form>
	<?php
}

/**
 * Shortcode: [syria_oil_jobs limit="6" featured="0" open="1"]
 *
 * @param array $atts Attributes.
 * @return string
 */
function syria_oil_jobs_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'limit'    => 6,
			'featured' => '0',
			'open'     => '1',
			'for_page' => '0',
			'heading'  => '',
		),
		$atts,
		'syria_oil_jobs'
	);

	if ( ! syria_oil_careers_enabled() ) {
		return '';
	}

	$jobs = syria_oil_get_jobs(
		array(
			'limit'         => absint( $atts['limit'] ),
			'open_only'     => ( '1' === (string) $atts['open'] || 'true' === $atts['open'] ),
			'featured_only' => ( '1' === (string) $atts['featured'] || 'true' === $atts['featured'] ),
			'for_page'      => ( '1' === (string) $atts['for_page'] || 'true' === $atts['for_page'] ),
		)
	);

	ob_start();
	echo '<div class="so-jobs">';
	if ( $atts['heading'] ) {
		echo '<div class="section-head"><div><h2>' . esc_html( $atts['heading'] ) . '</h2></div></div>';
	}
	if ( empty( $jobs ) ) {
		echo '<p class="so-jobs__empty text-muted text-center">' . esc_html__( 'لا توجد فرص عمل متاحة حالياً.', 'syria-oil' ) . '</p>';
	} else {
		echo '<div class="so-jobs__grid">';
		foreach ( $jobs as $job ) {
			syria_oil_render_job_card( $job );
		}
		echo '</div>';
	}
	echo '</div>';
	return (string) ob_get_clean();
}
add_shortcode( 'syria_oil_jobs', 'syria_oil_jobs_shortcode' );

/**
 * Render one job card.
 *
 * @param WP_Post $job Job post.
 */
function syria_oil_render_job_card( $job ) {
	if ( ! $job instanceof WP_Post ) {
		return;
	}
	$meta = syria_oil_get_job_meta( $job->ID );
	$url  = get_permalink( $job );
	?>
	<article class="so-job-card<?php echo $meta['is_open'] ? '' : ' so-job-card--closed'; ?>">
		<div class="so-job-card__body">
			<?php if ( $meta['entity'] ) : ?>
				<div class="so-job-card__entity"><?php echo esc_html( $meta['entity'] ); ?></div>
			<?php endif; ?>
			<h3 class="so-job-card__title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( get_the_title( $job ) ); ?></a></h3>
			<ul class="so-job-card__chips">
				<li><?php echo esc_html( sprintf( /* translators: %d positions */ _n( '%d شاغر', '%d شواغر', $meta['positions'], 'syria-oil' ), $meta['positions'] ) ); ?></li>
				<?php if ( $meta['type_label'] ) : ?>
					<li><?php echo esc_html( $meta['type_label'] ); ?></li>
				<?php endif; ?>
				<?php if ( $meta['location'] ) : ?>
					<li><?php echo esc_html( $meta['location'] ); ?></li>
				<?php endif; ?>
				<?php if ( $meta['mode_label'] ) : ?>
					<li><?php echo esc_html( $meta['mode_label'] ); ?></li>
				<?php endif; ?>
			</ul>
			<?php if ( $meta['deadline'] ) : ?>
				<p class="so-job-card__deadline"><?php echo esc_html__( 'آخر موعد:', 'syria-oil' ); ?> <?php echo esc_html( $meta['deadline'] ); ?></p>
			<?php endif; ?>
			<a class="btn btn-secondary btn-sm" href="<?php echo esc_url( $url ); ?>"><?php echo $meta['is_open'] ? esc_html__( 'التفاصيل والتقديم', 'syria-oil' ) : esc_html__( 'عرض التفاصيل', 'syria-oil' ); ?></a>
		</div>
	</article>
	<?php
}

/**
 * Seed demo jobs once (no media sideload).
 *
 * @param bool $force Force re-seed missing slugs only.
 * @return int Created count.
 */
function syria_oil_seed_demo_jobs( $force = false ) {
	if ( ! $force && get_option( 'syria_oil_jobs_demo_seeded' ) ) {
		return 0;
	}

	$defs = array(
		array(
			'slug'       => 'demo-petroleum-engineer',
			'title'      => 'مهندس نفط',
			'content'    => "<p>نبحث عن مهندس نفط للمشاركة في تخطيط ومراقبة عمليات الإنتاج والحفر.</p><ul><li>تحليل بيانات الآبار</li><li>التنسيق مع الفرق الميدانية</li><li>الالتزام بمعايير السلامة</li></ul>",
			'entity'     => 'شركة النفط السورية',
			'positions'  => 3,
			'type'       => 'full_time',
			'mode'       => 'onsite',
			'location'   => 'دمشق / الحقول',
			'department' => 'الإنتاج',
			'experience' => '3–7 سنوات',
			'education'  => 'بكالوريوس هندسة نفط أو ما يعادلها',
			'salary'     => 'حسب الخبرة + بدل ميداني',
			'deadline'   => gmdate( 'Y-m-d', strtotime( '+45 days' ) ),
			'featured'   => '1',
			'order'      => 1,
		),
		array(
			'slug'       => 'demo-hse-specialist',
			'title'      => 'أخصائي صحة وسلامة وبيئة',
			'content'    => '<p>الإشراف على أنظمة السلامة والتفتيش الميداني والتدريب.</p>',
			'entity'     => 'وزارة النفط والثروة المعدنية',
			'positions'  => 2,
			'type'       => 'full_time',
			'mode'       => 'hybrid',
			'location'   => 'حمص',
			'department' => 'HSE',
			'experience' => '5 سنوات فأكثر',
			'education'  => 'شهادة في السلامة الصناعية أو مجال ذي صلة',
			'salary'     => '',
			'deadline'   => gmdate( 'Y-m-d', strtotime( '+30 days' ) ),
			'featured'   => '1',
			'order'      => 2,
		),
		array(
			'slug'       => 'demo-accountant',
			'title'      => 'محاسب تكاليف',
			'content'    => '<p>إعداد تقارير التكاليف والموازنة ومتابعة العقود.</p>',
			'entity'     => 'شركة مصفاة بانياس',
			'positions'  => 1,
			'type'       => 'contract',
			'mode'       => 'onsite',
			'location'   => 'طرطوس',
			'department' => 'المالية',
			'experience' => '2–4 سنوات',
			'education'  => 'بكالوريوس محاسبة',
			'salary'     => 'قابل للتفاوض',
			'deadline'   => gmdate( 'Y-m-d', strtotime( '+60 days' ) ),
			'featured'   => '0',
			'order'      => 3,
		),
	);

	$created = 0;
	foreach ( $defs as $def ) {
		$existing = new WP_Query(
			array(
				'post_type'              => 'syria_oil_job',
				'post_status'            => 'any',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'meta_key'               => '_syria_oil_job_demo_slug',
				'meta_value'             => $def['slug'],
			)
		);
		if ( ! empty( $existing->posts ) ) {
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'syria_oil_job',
				'post_status'  => 'publish',
				'post_title'   => $def['title'],
				'post_content' => $def['content'],
				'menu_order'   => (int) $def['order'],
			),
			true
		);
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		update_post_meta( $post_id, '_syria_oil_job_demo_slug', $def['slug'] );
		update_post_meta( $post_id, '_syria_oil_job_entity', $def['entity'] );
		update_post_meta( $post_id, '_syria_oil_job_positions', (int) $def['positions'] );
		update_post_meta( $post_id, '_syria_oil_job_type', $def['type'] );
		update_post_meta( $post_id, '_syria_oil_job_mode', $def['mode'] );
		update_post_meta( $post_id, '_syria_oil_job_location', $def['location'] );
		update_post_meta( $post_id, '_syria_oil_job_department', $def['department'] );
		update_post_meta( $post_id, '_syria_oil_job_experience', $def['experience'] );
		update_post_meta( $post_id, '_syria_oil_job_education', $def['education'] );
		update_post_meta( $post_id, '_syria_oil_job_salary', $def['salary'] );
		update_post_meta( $post_id, '_syria_oil_job_deadline', $def['deadline'] );
		update_post_meta( $post_id, '_syria_oil_job_status', 'open' );
		update_post_meta( $post_id, '_syria_oil_job_featured', $def['featured'] );
		update_post_meta( $post_id, '_syria_oil_job_show_page', '1' );
		++$created;
	}

	update_option( 'syria_oil_jobs_demo_seeded', time(), false );
	return $created;
}

/**
 * Auto-seed demo jobs once in admin.
 */
function syria_oil_maybe_seed_demo_jobs() {
	if ( get_option( 'syria_oil_jobs_demo_seeded' ) ) {
		return;
	}
	if ( ! is_admin() ) {
		return;
	}
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	syria_oil_seed_demo_jobs( false );
}
add_action( 'admin_init', 'syria_oil_maybe_seed_demo_jobs', 40 );

/**
 * Hide careers footer link when module disabled.
 *
 * @param bool $show Whether to show.
 * @return bool
 */
function syria_oil_careers_show_footer_link() {
	if ( ! syria_oil_careers_enabled() ) {
		return false;
	}
	if ( function_exists( 'syria_oil_mod_enabled' ) ) {
		return syria_oil_mod_enabled( 'careers_show_footer', true );
	}
	return true;
}

/**
 * Hide homepage teaser when disabled.
 *
 * @return bool
 */
function syria_oil_careers_show_home_teaser() {
	if ( ! syria_oil_careers_enabled() ) {
		return false;
	}
	if ( function_exists( 'syria_oil_mod_enabled' ) ) {
		return syria_oil_mod_enabled( 'careers_show_home', true );
	}
	return true;
}
