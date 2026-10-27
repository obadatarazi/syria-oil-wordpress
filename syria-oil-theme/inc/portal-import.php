<?php
/**
 * Import ministry-portal pages + syria-oil.com posts bundled in demos/ministry-portal/.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Absolute path to a ministry-portal demo pack file.
 *
 * @param string $file Filename.
 * @return string
 */
function syria_oil_portal_pack_path( $file ) {
	return trailingslashit( SYRIA_OIL_DIR ) . 'demos/ministry-portal/' . ltrim( $file, '/' );
}

/**
 * Decode a JSON pack file.
 *
 * @param string $file Filename.
 * @return array<string,mixed>
 */
function syria_oil_portal_load_json( $file ) {
	$path = syria_oil_portal_pack_path( $file );
	if ( ! file_exists( $path ) ) {
		return array();
	}
	$data = json_decode( (string) file_get_contents( $path ), true );
	return is_array( $data ) ? $data : array();
}

/**
 * Ensure a category exists by name.
 *
 * @param string $name Category name.
 * @return int
 */
function syria_oil_portal_ensure_category( $name ) {
	$name = trim( wp_strip_all_tags( (string) $name ) );
	if ( '' === $name ) {
		return 0;
	}
	$existing = get_term_by( 'name', $name, 'category' );
	if ( $existing ) {
		return (int) $existing->term_id;
	}
	$created = wp_insert_term( $name, 'category' );
	if ( is_wp_error( $created ) ) {
		if ( ! empty( $created->error_data['term_exists'] ) ) {
			return (int) $created->error_data['term_exists'];
		}
		return 0;
	}
	return (int) $created['term_id'];
}

/**
 * Find a post previously imported from syria-oil.com by source ID.
 *
 * @param int $source_id Remote WP post ID.
 * @return int Local post ID or 0.
 */
function syria_oil_portal_find_post( $source_id ) {
	$source_id = absint( $source_id );
	if ( ! $source_id ) {
		return 0;
	}
	$q = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => 'any',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_key'               => '_syria_oil_source_id',
			'meta_value'             => (string) $source_id,
		)
	);
	return ! empty( $q->posts[0] ) ? (int) $q->posts[0] : 0;
}

/**
 * Sideload a remote image as the featured image for a post.
 *
 * @param int    $post_id Post ID.
 * @param string $url     Image URL.
 * @param string $desc    Attachment title.
 * @return int Attachment ID or 0.
 */
function syria_oil_portal_sideload_featured( $post_id, $url, $desc = '' ) {
	$post_id = absint( $post_id );
	$url     = esc_url_raw( $url );
	if ( ! $post_id || ! $url ) {
		return 0;
	}
	if ( has_post_thumbnail( $post_id ) ) {
		return (int) get_post_thumbnail_id( $post_id );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// syria-oil.com sometimes blocks default WP user-agents.
	$ua_filter = static function ( $args, $request_url ) use ( $url ) {
		if ( false !== strpos( (string) $request_url, 'syria-oil.com' ) || false !== strpos( (string) $url, 'syria-oil.com' ) ) {
			$args['user-agent'] = 'Mozilla/5.0 (compatible; SyriaOilTheme/1.5; +https://www.syria-oil.com/)';
			$args['timeout']    = 60;
		}
		return $args;
	};
	add_filter( 'http_request_args', $ua_filter, 10, 2 );

	$tmp = download_url( $url, 60 );
	remove_filter( 'http_request_args', $ua_filter, 10 );

	if ( is_wp_error( $tmp ) ) {
		return 0;
	}

	$path = wp_parse_url( $url, PHP_URL_PATH );
	$name = $path ? wp_basename( $path ) : 'featured.jpg';
	// Avoid broken attachment names from GIF masquerading as jpg.
	$name = preg_replace( '/\.jpg\.gif$/i', '.gif', $name );
	$file = array(
		'name'     => sanitize_file_name( $name ),
		'tmp_name' => $tmp,
	);

	$id = media_handle_sideload( $file, $post_id, $desc ? $desc : '' );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return 0;
	}

	set_post_thumbnail( $post_id, $id );
	update_post_meta( $id, '_syria_oil_source_image', $url );
	return (int) $id;
}

/**
 * Load curated syria-oil.com image URLs from the demo pack.
 *
 * @return string[]
 */
function syria_oil_portal_image_pool() {
	$pack = syria_oil_portal_load_json( 'image-pool.json' );
	$urls = array();
	if ( ! empty( $pack['images'] ) && is_array( $pack['images'] ) ) {
		foreach ( $pack['images'] as $url ) {
			$url = esc_url_raw( (string) $url );
			if ( $url ) {
				$urls[] = $url;
			}
		}
	}
	if ( empty( $urls ) ) {
		$content = syria_oil_portal_load_json( 'content-pack.json' );
		foreach ( (array) ( $content['posts'] ?? array() ) as $row ) {
			if ( ! empty( $row['image'] ) ) {
				$url = esc_url_raw( (string) $row['image'] );
				if ( $url ) {
					$urls[] = $url;
				}
			}
		}
	}
	return array_values( array_unique( $urls ) );
}

/**
 * Attach featured images from syria-oil.com to posts missing thumbnails.
 *
 * Prefer matching content-pack source IDs, then cycle the demo image pool.
 *
 * @param int $limit Max posts to process (0 = all).
 * @return array{attached:int,skipped:int,failed:int}
 */
function syria_oil_attach_demo_images( $limit = 0 ) {
	$counts = array(
		'attached' => 0,
		'skipped'  => 0,
		'failed'   => 0,
	);

	$pool = syria_oil_portal_image_pool();
	if ( empty( $pool ) ) {
		return $counts;
	}

	$by_source = array();
	$content   = syria_oil_portal_load_json( 'content-pack.json' );
	foreach ( (array) ( $content['posts'] ?? array() ) as $row ) {
		$sid = absint( $row['id'] ?? 0 );
		$url = ! empty( $row['image'] ) ? esc_url_raw( (string) $row['image'] ) : '';
		if ( $sid && $url ) {
			$by_source[ $sid ] = $url;
		}
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => $limit > 0 ? absint( $limit ) : -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);

	$i = 0;
	foreach ( $query->posts as $post_id ) {
		$post_id = (int) $post_id;
		if ( has_post_thumbnail( $post_id ) ) {
			$counts['skipped']++;
			continue;
		}

		$url       = '';
		$source_id = absint( get_post_meta( $post_id, '_syria_oil_source_id', true ) );
		if ( $source_id && isset( $by_source[ $source_id ] ) ) {
			$url = $by_source[ $source_id ];
		}
		if ( ! $url ) {
			$url = $pool[ $i % count( $pool ) ];
			$i++;
		}

		$title = get_the_title( $post_id );
		$att   = syria_oil_portal_sideload_featured( $post_id, $url, $title ? $title : '' );
		if ( $att ) {
			$counts['attached']++;
		} else {
			$counts['failed']++;
		}
	}

	return $counts;
}

/**
 * Import all posts from content-pack.json (full HTML from syria-oil.com).
 *
 * @param bool  $force        Refresh existing imported posts.
 * @param array $existing_titles Optional array of existing post titles.
 * @return array{posts_created:int,posts_updated:int,images:int,renamed:int}
 */
function syria_oil_import_portal_posts( $force = false, $existing_titles = array() ) {
	$pack   = syria_oil_portal_load_json( 'content-pack.json' );
	$posts  = isset( $pack['posts'] ) && is_array( $pack['posts'] ) ? $pack['posts'] : array();
	$counts = array(
		'posts_created' => 0,
		'posts_updated' => 0,
		'images'        => 0,
		'renamed'       => 0,
	);
	$author = get_current_user_id() ? get_current_user_id() : 1;

	// Load existing titles if not provided.
	if ( empty( $existing_titles ) && function_exists( 'syria_oil_get_existing_post_titles' ) ) {
		$existing_titles = syria_oil_get_existing_post_titles();
	}

	foreach ( $posts as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$source_id = absint( $row['id'] ?? 0 );
		$title     = wp_strip_all_tags( html_entity_decode( (string) ( $row['title'] ?? '' ), ENT_QUOTES, 'UTF-8' ) );
		if ( ! $title ) {
			continue;
		}

		$content = (string) ( $row['content_html'] ?? '' );
		$excerpt = wp_strip_all_tags( (string) ( $row['excerpt'] ?? '' ) );
		$date    = (string) ( $row['date'] ?? '' );
		if ( $date && ! preg_match( '/\d{2}:\d{2}/', $date ) ) {
			$date .= ' 10:00:00';
		}
		$cat_id = syria_oil_portal_ensure_category( (string) ( $row['category'] ?? '' ) );
		$kind   = (string) ( $row['kind'] ?? 'news' );
		if ( 'article' === $kind ) {
			$articles_cat = syria_oil_portal_ensure_category( 'مقالات' );
			$terms        = array_filter( array( $cat_id, $articles_cat ) );
		} else {
			$terms = array_filter( array( $cat_id ) );
		}

		$existing = $source_id ? syria_oil_portal_find_post( $source_id ) : 0;
		$args     = array(
			'post_title'   => $title,
			'post_content' => $content,
			'post_excerpt' => $excerpt,
			'post_status'  => 'publish',
			'post_type'    => 'post',
			'post_author'  => $author,
		);
		if ( $date ) {
			$args['post_date']     = $date;
			$args['post_date_gmt'] = get_gmt_from_date( $date );
		}

		if ( $existing ) {
			$post_id = (int) $existing;
			if ( $force ) {
				$args['ID'] = $existing;
				$updated    = (int) wp_update_post( $args, true );
				if ( $updated && ! is_wp_error( $updated ) ) {
					$post_id = $updated;
					$counts['posts_updated']++;
				}
			}
		} else {
			$post_id = (int) wp_insert_post( $args, true );
			if ( $post_id && ! is_wp_error( $post_id ) ) {
				$counts['posts_created']++;
			}
		}

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			continue;
		}

		if ( $source_id ) {
			update_post_meta( $post_id, '_syria_oil_source_id', $source_id );
		}
		if ( ! empty( $row['link'] ) ) {
			update_post_meta( $post_id, '_syria_oil_source_url', esc_url_raw( (string) $row['link'] ) );
		}
		update_post_meta( $post_id, '_syria_oil_kind', $kind );

		if ( $terms ) {
			wp_set_post_terms( $post_id, $terms, 'category' );
		}

		// Always try to fill missing featured images (even when skipping content refresh).
		if ( ! empty( $row['image'] ) && ! has_post_thumbnail( $post_id ) ) {
			$att = syria_oil_portal_sideload_featured( $post_id, (string) $row['image'], $title );
			if ( $att ) {
				$counts['images']++;
			}
		}
	}

	return $counts;
}

/**
 * Page slug → template file map for listing pages.
 *
 * @return array<string,string>
 */
function syria_oil_portal_page_templates() {
	return array(
		'news'     => 'page-templates/page-news.php',
		'articles' => 'page-templates/page-articles.php',
	);
}

/**
 * Import / refresh static pages from pages-pack.json (ministry-portal layouts).
 *
 * @param bool $force Overwrite existing page content.
 * @return array{pages_created:int,pages_updated:int,pages_total:int}
 */
function syria_oil_import_portal_pages( $force = true ) {
	$pack   = syria_oil_portal_load_json( 'pages-pack.json' );
	$counts = array(
		'pages_created' => 0,
		'pages_updated' => 0,
		'pages_total'   => 0,
	);
	$author    = get_current_user_id() ? get_current_user_id() : 1;
	$templates = syria_oil_portal_page_templates();

	// Core nav pages that may not be in pages-pack (listings use templates).
	$core = array(
		'home'     => array(
			'title'    => 'الرئيسية',
			'subtitle' => 'تغطية متخصصة لحظة بلحظة لقطاع النفط والغاز السوري.',
			// Empty: front-page.php renders the real homepage (hero + grids).
			'content'  => '',
		),
		'news'     => array(
			'title'    => 'الأخبار',
			'subtitle' => 'مركز الأخبار: آخر التغطيات حول النفط والغاز.',
			'content'  => '',
		),
		'articles' => array(
			'title'    => 'المقالات',
			'subtitle' => 'مقالات وتقارير تحليلية.',
			'content'  => '',
		),
	);

	$defs = array();
	foreach ( $core as $slug => $meta ) {
		$defs[ $slug ] = $meta;
	}
	foreach ( $pack as $slug => $meta ) {
		if ( ! is_array( $meta ) ) {
			continue;
		}
		$defs[ $slug ] = array(
			'title'    => (string) ( $meta['title'] ?? $slug ),
			'subtitle' => (string) ( $meta['subtitle'] ?? '' ),
			'content'  => (string) ( $meta['content_html'] ?? '' ),
		);
	}

	// Prefer syria-oil branding for contact emails when pack still has oil.gov.sy placeholders.
	if ( ! empty( $defs['contact']['content'] ) ) {
		$defs['contact']['content'] = str_replace(
			array( 'info@oil.gov.sy', 'minister@oil.gov.sy', 'licensing@oil.gov.sy', 'media@oil.gov.sy', 'invest@oil.gov.sy' ),
			array( 'info@syria-oil.com', 'info@syria-oil.com', 'info@syria-oil.com', 'info@syria-oil.com', 'info@syria-oil.com' ),
			$defs['contact']['content']
		);
	}

	$page_ids = array();
	foreach ( $defs as $slug => $meta ) {
		$existing = get_page_by_path( $slug );
		if ( ! $existing && function_exists( 'syria_oil_get_page_by_title' ) ) {
			$existing = syria_oil_get_page_by_title( $meta['title'] );
		}

		$content = $meta['content'];
		// Listing pages: keep a short note; template renders the grid.
		if ( isset( $templates[ $slug ] ) && '' === trim( wp_strip_all_tags( $content ) ) ) {
			$content = '<p class="text-muted">' . esc_html( $meta['subtitle'] ) . '</p>';
		}

		$args = array(
			'post_title'   => $meta['title'],
			'post_name'    => $slug,
			'post_content' => $content,
			'post_excerpt' => $meta['subtitle'],
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => $author,
		);

		if ( $existing ) {
			if ( ! $force ) {
				$page_ids[ $slug ] = (int) $existing->ID;
				continue;
			}
			$args['ID']        = $existing->ID;
			$page_ids[ $slug ] = (int) wp_update_post( $args );
			$counts['pages_updated']++;
		} else {
			$page_ids[ $slug ] = (int) wp_insert_post( $args );
			if ( ! empty( $page_ids[ $slug ] ) ) {
				$counts['pages_created']++;
			}
		}

		$pid = $page_ids[ $slug ] ?? 0;
		if ( $pid ) {
			if ( isset( $templates[ $slug ] ) ) {
				update_post_meta( $pid, '_wp_page_template', $templates[ $slug ] );
			}
			update_post_meta( $pid, '_syria_oil_portal_page', 1 );
		}
	}

	$counts['pages_total'] = count( array_filter( $page_ids ) );

	if ( ! empty( $page_ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $page_ids['home'] );
	}

	if ( ! empty( $page_ids['services'] ) ) {
		if ( function_exists( 'syria_oil_set_mod' ) ) {
			syria_oil_set_mod( 'top_bar_link_url', get_permalink( $page_ids['services'] ) );
		} else {
			set_theme_mod( 'top_bar_link_url', get_permalink( $page_ids['services'] ) );
		}
	}
	if ( ! empty( $page_ids['contact'] ) ) {
		if ( function_exists( 'syria_oil_set_mod' ) ) {
			syria_oil_set_mod( 'contact_page_url', get_permalink( $page_ids['contact'] ) );
		} else {
			set_theme_mod( 'contact_page_url', get_permalink( $page_ids['contact'] ) );
		}
	}
	if ( ! empty( $page_ids['about'] ) ) {
		if ( function_exists( 'syria_oil_set_mod' ) ) {
			syria_oil_set_mod( 'about_page_url', get_permalink( $page_ids['about'] ) );
		} else {
			set_theme_mod( 'about_page_url', get_permalink( $page_ids['about'] ) );
		}
	}

	return array(
		'counts'   => $counts,
		'page_ids' => $page_ids,
	);
}

/**
 * Full portal install: pages + posts + menus.
 *
 * @param bool $force Force refresh.
 * @return array<string,int|bool>
 */
function syria_oil_install_portal_content( $force = true ) {
	$pages = syria_oil_import_portal_pages( $force );
	$posts = syria_oil_import_portal_posts( $force );
	$media = syria_oil_attach_demo_images();

	$page_ids = $pages['page_ids'];
	$counts   = array_merge( $pages['counts'], $posts );
	$counts['images_attached'] = (int) ( $media['attached'] ?? 0 );
	$counts['images']          = (int) ( $counts['images'] ?? 0 ) + (int) ( $media['attached'] ?? 0 );

	$ensure_menu = static function ( $name ) {
		$menu = wp_get_nav_menu_object( $name );
		if ( $menu ) {
			$items = wp_get_nav_menu_items( $menu->term_id );
			if ( $items ) {
				foreach ( $items as $item ) {
					wp_delete_post( $item->ID, true );
				}
			}
			return (int) $menu->term_id;
		}
		$id = wp_create_nav_menu( $name );
		return is_wp_error( $id ) ? 0 : (int) $id;
	};

	$add_page = static function ( $menu_id, $page_id ) {
		if ( ! $menu_id || ! $page_id ) {
			return;
		}
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-object-id' => $page_id,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);
	};

	$primary_id = $ensure_menu( 'القائمة الرئيسية' );
	$footer_id  = $ensure_menu( 'قائمة التذييل' );
	$mobile_id  = $ensure_menu( 'شريط الجوال السفلي' );

	foreach ( array( 'home', 'news', 'articles', 'projects', 'services', 'media', 'tenders', 'about', 'contact' ) as $slug ) {
		$add_page( $primary_id, $page_ids[ $slug ] ?? 0 );
	}
	foreach ( array( 'about', 'news', 'articles', 'careers', 'faq', 'privacy-policy', 'contact', 'sitemap' ) as $slug ) {
		$add_page( $footer_id, $page_ids[ $slug ] ?? 0 );
	}
	foreach ( array( 'home', 'news', 'projects', 'services' ) as $slug ) {
		$add_page( $mobile_id, $page_ids[ $slug ] ?? 0 );
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( ! is_array( $locations ) ) {
		$locations = array();
	}
	if ( $primary_id ) {
		$locations['primary'] = $primary_id;
	}
	if ( $footer_id ) {
		$locations['footer'] = $footer_id;
	}
	if ( $mobile_id ) {
		$locations['mobile_bottom'] = $mobile_id;
	}
	set_theme_mod( 'nav_menu_locations', $locations );
	$counts['menus'] = count( array_filter( $locations ) );

	$mods = array(
		'show_top_bar'         => true,
		'show_top_bar_date'    => true,
		'top_bar_text'         => 'أخبار النفط والغاز في سوريا والعالم',
		'top_bar_link_label'   => 'الأقسام',
		'search_placeholder'   => 'ابحث في الأخبار…',
		'footer_tagline'       => 'أخبار النفط والغاز في سوريا والعالم.',
		'footer_explore_title' => 'الموقع',
		'footer_cats_title'    => 'التصنيفات',
		'footer_contact_title' => 'اتصل بنا',
		'footer_bottom_text'   => 'جميع الحقوق محفوظة.',
		'contact_email'        => 'info@syria-oil.com',
	);
	foreach ( $mods as $key => $value ) {
		if ( function_exists( 'syria_oil_set_mod' ) ) {
			syria_oil_set_mod( $key, $value );
		} else {
			set_theme_mod( $key, $value );
		}
	}

	update_option( 'syria_oil_starter_installed', 1 );
	update_option( 'syria_oil_portal_imported', 1 );

	// Restore preserved user settings.
	if ( function_exists( 'syria_oil_restore_preserved_settings' ) && ! empty( $backup ) ) {
		syria_oil_restore_preserved_settings( $backup );
	}

	if ( function_exists( 'syria_oil_seed_elementor_pages' ) ) {
		$el = syria_oil_seed_elementor_pages( true );
		$counts['elementor_seeded']  = $el['seeded'];
		$counts['elementor_skipped'] = $el['skipped'];
		$counts['elementor_missing'] = $el['missing'];
	}

	if ( function_exists( 'syria_oil_seed_theme_builder_templates' ) ) {
		$tb = syria_oil_seed_theme_builder_templates( true );
		$counts['theme_builder_seeded'] = $tb['seeded'];
	}

	if ( function_exists( 'syria_oil_seed_demo_side_ads' ) ) {
		// Do not force-reupload creatives on every portal install (Media Library bloat).
		$ads = syria_oil_seed_demo_side_ads( false );
		$counts['side_ads_seeded'] = $ads['seeded'];
	}

	return $counts;
}
