<?php
/**
 * Seed Elementor Pro Theme Builder templates (Single Post + Archive).
 *
 * Requires Elementor Pro. Safe no-op when Pro is inactive.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Builder demo files map: slug => filename basename.
 *
 * @return array<string,string>
 */
function syria_oil_theme_builder_map() {
	return array(
		'header'      => 'header',
		'single-post' => 'single-post',
		'archive'     => 'archive',
	);
}

/**
 * Default Elementor conditions per template type.
 *
 * @param string $type Template type (header|single-post|archive).
 * @return string[]
 */
function syria_oil_theme_builder_default_conditions( $type ) {
	if ( 'header' === $type ) {
		return array( 'include/entire_site' );
	}
	if ( 'single-post' === $type ) {
		return array( 'include/singular/post' );
	}
	if ( 'archive' === $type ) {
		return array( 'include/archive', 'include/archive/post' );
	}
	return array( 'include/entire_site' );
}

/**
 * Load Theme Builder JSON payload.
 *
 * @param string $slug Basename without .json.
 * @return array|null
 */
function syria_oil_load_theme_builder_json( $slug ) {
	$path = trailingslashit( SYRIA_OIL_DIR ) . 'demos/elementor/theme-builder/' . $slug . '.json';
	if ( ! is_readable( $path ) ) {
		return null;
	}
	$data = json_decode( (string) file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	return is_array( $data ) ? $data : null;
}

/**
 * Find an existing Theme Builder library item by title + type.
 *
 * @param string $title Title.
 * @param string $type  Template type.
 * @return int Post ID or 0.
 */
function syria_oil_find_theme_builder_template( $title, $type ) {
	$query = new WP_Query(
		array(
			'post_type'              => 'elementor_library',
			'title'                  => $title,
			'post_status'            => array( 'publish', 'draft' ),
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => true,
			'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'   => '_elementor_template_type',
					'value' => $type,
				),
			),
		)
	);

	if ( empty( $query->posts ) ) {
		return 0;
	}

	return (int) $query->posts[0]->ID;
}

/**
 * Resolve the primary menu term ID for Elementor Nav Menu widget seeds.
 *
 * @return int Menu term ID or 0.
 */
function syria_oil_theme_builder_primary_menu_id() {
	$locations = get_nav_menu_locations();
	if ( ! empty( $locations['primary'] ) ) {
		return absint( $locations['primary'] );
	}
	$menus = wp_get_nav_menus( array( 'number' => 1 ) );
	if ( ! empty( $menus[0] ) && ! empty( $menus[0]->term_id ) ) {
		return absint( $menus[0]->term_id );
	}
	return 0;
}

/**
 * Replace seed placeholders in Theme Builder Elementor data (e.g. primary menu).
 *
 * @param array $content Elementor content tree.
 * @return array
 */
function syria_oil_theme_builder_prepare_content( $content ) {
	if ( ! is_array( $content ) ) {
		return array();
	}

	$menu_id = syria_oil_theme_builder_primary_menu_id();
	$json    = wp_json_encode( $content );
	if ( ! is_string( $json ) ) {
		return $content;
	}

	if ( $menu_id ) {
		$json = str_replace( '"__PRIMARY_MENU__"', (string) $menu_id, $json );
		$json = str_replace( '__PRIMARY_MENU__', (string) $menu_id, $json );
	} else {
		// Drop the placeholder so Elementor falls back to its first menu.
		$json = str_replace( '"menu":"__PRIMARY_MENU__"', '"menu":""', $json );
		$json = str_replace( '"menu": "__PRIMARY_MENU__"', '"menu": ""', $json );
	}

	$decoded = json_decode( $json, true );
	return is_array( $decoded ) ? $decoded : $content;
}

/**
 * Apply Elementor content + Pro conditions to a library template.
 *
 * @param int    $post_id Library post ID.
 * @param array  $payload JSON payload.
 * @param string $type    Template type.
 * @return bool
 */
function syria_oil_apply_theme_builder_template( $post_id, $payload, $type ) {
	$post_id = absint( $post_id );
	if ( ! $post_id || empty( $payload['content'] ) || ! is_array( $payload['content'] ) ) {
		return false;
	}

	$content    = syria_oil_theme_builder_prepare_content( $payload['content'] );
	if ( function_exists( 'syria_oil_bind_elementor_categories' ) ) {
		$content = syria_oil_bind_elementor_categories( $content );
	}
	$conditions = ! empty( $payload['conditions'] ) && is_array( $payload['conditions'] )
		? $payload['conditions']
		: syria_oil_theme_builder_default_conditions( $type );

	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', $type );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $content ) ) );
	update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
	update_post_meta( $post_id, '_elementor_pro_version', defined( 'ELEMENTOR_PRO_VERSION' ) ? ELEMENTOR_PRO_VERSION : '' );
	update_post_meta( $post_id, '_elementor_conditions', $conditions );

	// Elementor Pro location conditions option (used by Theme Builder resolver).
	if ( class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
		try {
			$module = \ElementorPro\Modules\ThemeBuilder\Module::instance();
			if ( $module && method_exists( $module, 'get_conditions_manager' ) ) {
				$cm = $module->get_conditions_manager();
				if ( $cm && method_exists( $cm, 'save_conditions' ) ) {
					$cm->save_conditions( $post_id, $conditions );
				}
			}
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Fail-soft — meta conditions above still help on many Pro versions.
		}
	}

	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		try {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Fail-soft.
		}
	}

	return true;
}

/**
 * Seed Elementor Pro Theme Builder templates from demos/elementor/theme-builder/.
 *
 * @param bool $force Overwrite existing template content.
 * @return array{seeded:int,skipped:int,missing:int,pro:bool}
 */
function syria_oil_seed_theme_builder_templates( $force = false ) {
	$stats = array(
		'seeded'  => 0,
		'skipped' => 0,
		'missing' => 0,
		'pro'     => function_exists( 'syria_oil_has_elementor_pro' ) ? syria_oil_has_elementor_pro() : false,
	);

	if ( ! $stats['pro'] || ! class_exists( '\Elementor\Plugin' ) ) {
		return $stats;
	}

	foreach ( syria_oil_theme_builder_map() as $type => $file ) {
		$payload = syria_oil_load_theme_builder_json( $file );
		if ( ! $payload ) {
			++$stats['missing'];
			continue;
		}

		$title = ! empty( $payload['title'] ) ? (string) $payload['title'] : 'Syria Oil — ' . $type;
		$type  = ! empty( $payload['type'] ) ? (string) $payload['type'] : $type;

		$existing_id = syria_oil_find_theme_builder_template( $title, $type );

		if ( $existing_id && ! $force ) {
			$existing_data = get_post_meta( $existing_id, '_elementor_data', true );
			if ( ! empty( $existing_data ) && '[]' !== $existing_data ) {
				++$stats['skipped'];
				continue;
			}
		}

		if ( ! $existing_id ) {
			$existing_id = wp_insert_post(
				array(
					'post_title'  => $title,
					'post_status' => 'publish',
					'post_type'   => 'elementor_library',
					'post_author' => get_current_user_id() ? get_current_user_id() : 1,
				),
				true
			);
			if ( is_wp_error( $existing_id ) || ! $existing_id ) {
				++$stats['missing'];
				continue;
			}
			wp_set_object_terms( $existing_id, $type, 'elementor_library_type' );
		}

		if ( syria_oil_apply_theme_builder_template( (int) $existing_id, $payload, $type ) ) {
			++$stats['seeded'];
		}
	}

	update_option( 'syria_oil_theme_builder_seeded', time(), false );
	return $stats;
}

/**
 * After OCDI, seed Pro Theme Builder templates when available.
 */
add_action(
	'ocdi/after_import',
	static function () {
		if ( function_exists( 'syria_oil_seed_theme_builder_templates' ) ) {
			syria_oil_seed_theme_builder_templates( true );
		}
	},
	40
);
