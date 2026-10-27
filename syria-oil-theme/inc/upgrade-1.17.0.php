<?php
/**
 * One-time upgrade to 1.17.0 — safe refresh (no content loss).
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SYRIA_OIL_UPGRADE_TARGET', '1.17.0' );

/**
 * Stored DB upgrade version (separate from SYRIA_OIL_VERSION constant).
 *
 * @return string
 */
function syria_oil_db_version() {
	return (string) get_option( 'syria_oil_db_version', '0' );
}

/**
 * Run pending upgrades after theme load.
 */
function syria_oil_maybe_run_upgrade() {
	if ( version_compare( syria_oil_db_version(), SYRIA_OIL_UPGRADE_TARGET, '>=' ) ) {
		return;
	}
	syria_oil_upgrade_to_1_17_0();
	update_option( 'syria_oil_db_version', SYRIA_OIL_UPGRADE_TARGET, false );
}
add_action( 'after_setup_theme', 'syria_oil_maybe_run_upgrade', 20 );

/**
 * Main 1.17.0 upgrade routine.
 */
function syria_oil_upgrade_to_1_17_0() {
	if ( ! function_exists( 'syria_oil_get_options_raw' ) || ! function_exists( 'syria_oil_set_mod' ) ) {
		return;
	}

	$opts = syria_oil_get_options_raw();

	// Disable official banner decoration (independent media look).
	syria_oil_set_mod( 'banner_flag_overlay', 0 );
	syria_oil_set_mod( 'show_syrian_emblem', 0 );
	syria_oil_set_mod( 'banner_edge_mode', 'none' );

	// Top bar tagline.
	syria_oil_set_mod( 'top_bar_text', 'أخبار النفط والغاز في سوريا والعالم' );

	// Logo — bump only if still on previous defaults.
	if ( 120 === absint( $opts['logo_desktop_height'] ?? 0 ) ) {
		syria_oil_set_mod( 'logo_desktop_height', 176 );
	}
	if ( 140 === absint( $opts['logo_desktop_max_width'] ?? 0 ) ) {
		syria_oil_set_mod( 'logo_desktop_max_width', 200 );
	}
	if ( 96 === absint( $opts['logo_mobile_height'] ?? 0 ) ) {
		syria_oil_set_mod( 'logo_mobile_height', 112 );
	}
	if ( 108 === absint( $opts['logo_mobile_max_width'] ?? 0 ) ) {
		syria_oil_set_mod( 'logo_mobile_max_width', 128 );
	}

	// Fix emblem URL pointing at staging server.
	$emblem = isset( $opts['banner_emblem_image'] ) ? $opts['banner_emblem_image'] : array();
	if ( is_array( $emblem ) && ! empty( $emblem['url'] ) && false !== strpos( (string) $emblem['url'], 'dashmarketing.us' ) ) {
		$bundled = trailingslashit( get_template_directory_uri() ) . 'assets/images/syrian-emblem-2025.png';
		syria_oil_set_mod(
			'banner_emblem_image',
			array(
				'url'   => $bundled,
				'id'    => 0,
				'width' => '',
				'height' => '',
			)
		);
	}

	syria_oil_upgrade_rename_featured_category();
	syria_oil_upgrade_patch_homepage_elementor();
	syria_oil_upgrade_sync_header_menu_categories();

	if ( function_exists( 'syria_oil_clear_theme_cache' ) ) {
		syria_oil_clear_theme_cache();
	}
	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) && \Elementor\Plugin::$instance->files_manager ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
}

/**
 * Rename «أخبار بارزة» display name to «أخبار سوريا» (slug unchanged).
 */
function syria_oil_upgrade_rename_featured_category() {
	$term = get_term_by( 'name', 'أخبار بارزة', 'category' );
	if ( ! $term || is_wp_error( $term ) ) {
		$term = get_term_by( 'slug', '%d8%a3%d8%ae%d8%a8%d8%a7%d8%b1-%d8%a8%d8%a7%d8%b1%d8%b2%d8%a9', 'category' );
	}
	if ( $term && ! is_wp_error( $term ) && 'أخبار سوريا' !== $term->name ) {
		wp_update_term(
			(int) $term->term_id,
			'category',
			array(
				'name' => 'أخبار سوريا',
				'slug' => $term->slug,
			)
		);
	}
}

/**
 * Backup and patch homepage Elementor data (page 9875).
 */
function syria_oil_upgrade_patch_homepage_elementor() {
	$page_id = absint( get_option( 'page_on_front', 0 ) );
	if ( ! $page_id ) {
		$page = get_page_by_path( 'home' );
		$page_id = $page ? (int) $page->ID : 9875;
	}

	$raw = get_post_meta( $page_id, '_elementor_data', true );
	if ( ! $raw ) {
		return;
	}

	if ( ! get_post_meta( $page_id, '_syria_oil_elementor_backup_1170', true ) ) {
		update_post_meta( $page_id, '_syria_oil_elementor_backup_1170', $raw );
	}

	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) {
		return;
	}

	$data = syria_oil_upgrade_walk_elementor_nodes( $data );
	update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
}

/**
 * Recursively patch Elementor nodes.
 *
 * @param array $nodes Nodes.
 * @return array
 */
function syria_oil_upgrade_walk_elementor_nodes( $nodes ) {
	$out = array();
	foreach ( (array) $nodes as $node ) {
		if ( ! empty( $node['elements'] ) ) {
			$node['elements'] = syria_oil_upgrade_walk_elementor_nodes( $node['elements'] );
		}

		$type = isset( $node['widgetType'] ) ? (string) $node['widgetType'] : '';
		$settings = isset( $node['settings'] ) && is_array( $node['settings'] ) ? $node['settings'] : array();

		if ( 'syria_oil_category_sections' === $type ) {
			$node['settings'] = syria_oil_upgrade_homepage_category_sections( $settings );
		}

		if ( 'syria_oil_article_card_grid' === $type ) {
			$heading = isset( $settings['heading'] ) ? (string) $settings['heading'] : '';
			if ( false !== strpos( $heading, 'أحدث الأخبار' ) ) {
				continue; // Drop latest-news block on homepage.
			}
		}

		$out[] = $node;
	}
	return $out;
}

/**
 * Rebuild Category Sections rows for homepage.
 *
 * @param array $settings Widget settings.
 * @return array
 */
function syria_oil_upgrade_homepage_category_sections( $settings ) {
	$sections = array(
		array(
			'title'              => 'أخبار سوريا',
			'cat_name'           => 'أخبار سوريا',
			'secondary_cat_name' => '',
			'layout'             => 'magazine',
			'posts_count'        => '',
			'text'               => '',
		),
		array(
			'title'              => 'نفط وغاز عالمي',
			'cat_name'           => 'أخبار النفط والغاز العالمي',
			'secondary_cat_name' => '',
			'layout'             => 'list',
			'posts_count'        => '',
			'text'               => '',
		),
		array(
			'title'              => 'نفط وغاز عربي',
			'cat_name'           => 'أخبار النفط والغاز العربي',
			'secondary_cat_name' => '',
			'layout'             => 'rail',
			'posts_count'        => '',
			'text'               => '',
		),
		array(
			'title'              => 'المحروقات',
			'cat_name'           => 'المحروقات',
			'secondary_cat_name' => '',
			'layout'             => 'video',
			'posts_count'        => '',
			'text'               => '',
		),
		array(
			'title'              => 'قرارات',
			'cat_name'           => 'قرارات وأخبار وزارة النفط',
			'secondary_cat_name' => '',
			'layout'             => 'list',
			'posts_count'        => '',
			'text'               => '',
		),
		array(
			'title'              => 'الشركات',
			'cat_name'           => 'الشركات العاملة في سوريا',
			'secondary_cat_name' => 'أخبار شركات وزارة النفط',
			'layout'             => 'gallery',
			'posts_count'        => '',
			'text'               => '',
		),
		array(
			'title'              => 'مشاريع',
			'cat_name'           => 'أخبار المشاريع النفطية',
			'secondary_cat_name' => '',
			'layout'             => 'magazine',
			'posts_count'        => '',
			'text'               => '',
		),
		array(
			'title'              => 'طاقة الغد',
			'cat_name'           => 'أخبار الكهرباء والطاقة البديلة',
			'secondary_cat_name' => '',
			'layout'             => 'grid',
			'posts_count'        => '',
			'text'               => '',
		),
	);

	$rows = array();
	foreach ( $sections as $row ) {
		$cat_name = $row['cat_name'];
		$term     = function_exists( 'syria_oil_resolve_category_term' )
			? syria_oil_resolve_category_term( $cat_name )
			: get_term_by( 'name', $cat_name, 'category' );

		$item = array(
			'_id'                => substr( md5( 'sec117-' . $cat_name ), 0, 7 ),
			'title'              => $row['title'],
			'cat_name'           => $cat_name,
			'secondary_cat_name' => $row['secondary_cat_name'],
			'layout'             => $row['layout'],
			'posts_count'        => $row['posts_count'],
			'text'               => $row['text'],
			'category_id'        => ( $term && ! is_wp_error( $term ) ) ? (int) $term->term_id : 0,
			'link'               => array( 'url' => '', 'is_external' => '', 'nofollow' => '' ),
		);
		if ( $term && ! is_wp_error( $term ) ) {
			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				$item['link']['url'] = $link;
			}
		}
		$rows[] = $item;
	}

	$settings['sections']          = $rows;
	$settings['posts_per_section'] = 2;
	return $settings;
}

/**
 * Add category children under «الأقسام» in the primary menu.
 */
function syria_oil_upgrade_sync_header_menu_categories() {
	$locations = get_nav_menu_locations();
	if ( empty( $locations['primary'] ) ) {
		return;
	}

	$menu_id = absint( $locations['primary'] );
	$items   = wp_get_nav_menu_items( $menu_id );
	if ( ! $items ) {
		return;
	}

	$parent_id = 0;
	foreach ( $items as $item ) {
		if ( 'الأقسام' === $item->title || false !== strpos( (string) $item->url, '/services' ) ) {
			$parent_id = (int) $item->ID;
			break;
		}
	}
	if ( ! $parent_id ) {
		return;
	}

	$existing_titles = array();
	foreach ( $items as $item ) {
		if ( (int) $item->menu_item_parent === $parent_id ) {
			$existing_titles[] = $item->title;
		}
	}

	$categories = array(
		'أخبار سوريا'                  => array( 'أخبار سوريا', 'أخبار بارزة' ),
		'نفط وغاز عالمي'               => array( 'أخبار النفط والغاز العالمي' ),
		'نفط وغاز عربي'                => array( 'أخبار النفط والغاز العربي' ),
		'المحروقات'                    => array( 'المحروقات' ),
		'الشركات'                      => array( 'الشركات العاملة في سوريا' ),
		'مشاريع'                       => array( 'أخبار المشاريع النفطية' ),
		'قرارات'                       => array( 'قرارات وأخبار وزارة النفط' ),
		'طاقة الغد'                    => array( 'أخبار الكهرباء والطاقة البديلة' ),
	);

	$position = 1;
	foreach ( $categories as $label => $aliases ) {
		if ( in_array( $label, $existing_titles, true ) ) {
			continue;
		}
		$term = null;
		foreach ( $aliases as $alias ) {
			if ( function_exists( 'syria_oil_resolve_category_term' ) ) {
				$term = syria_oil_resolve_category_term( $alias );
			} else {
				$term = get_term_by( 'name', $alias, 'category' );
			}
			if ( $term && ! is_wp_error( $term ) ) {
				break;
			}
		}
		if ( ! $term || is_wp_error( $term ) ) {
			continue;
		}
		$link = get_term_link( $term );
		if ( is_wp_error( $link ) ) {
			continue;
		}

		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $label,
				'menu-item-url'       => $link,
				'menu-item-status'    => 'publish',
				'menu-item-type'      => 'custom',
				'menu-item-parent-id' => $parent_id,
				'menu-item-position'  => $position,
			)
		);
		++$position;
	}
}
