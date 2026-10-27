<?php
/**
 * One-click starter content: Arabic pages, sample posts, menus, Customizer.
 *
 * Runs once after theme activation (skips if already installed).
 * Also callable from OCDI after-import and Tools → Syria Oil Starter.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether starter content has already been installed.
 *
 * @return bool
 */
function syria_oil_starter_is_installed() {
	return (bool) get_option( 'syria_oil_starter_installed' );
}

/**
 * Preserve existing site settings before importing demo content.
 *
 * @return array<string,mixed>
 */
function syria_oil_backup_existing_settings() {
	return array(
		'custom_logo'         => get_theme_mod( 'custom_logo' ),
		'site_icon'           => get_option( 'site_icon' ),
		'blogname'            => get_option( 'blogname' ),
		'blogdescription'     => get_option( 'blogdescription' ),
		'permalink_structure' => get_option( 'permalink_structure' ),
		'show_on_front'       => get_option( 'show_on_front' ),
		'page_on_front'       => get_option( 'page_on_front' ),
		'page_for_posts'      => get_option( 'page_for_posts' ),
		'menus'               => get_theme_mod( 'nav_menu_locations' ),
		'existing_pages'      => syria_oil_get_existing_page_titles(),
		'existing_posts'      => syria_oil_get_existing_post_titles(),
		'existing_categories' => syria_oil_get_existing_category_slugs(),
	);
}

/**
 * Get all existing page titles (to avoid collisions).
 *
 * @return array
 */
function syria_oil_get_existing_page_titles() {
	global $wpdb;
	return $wpdb->get_col(
		"SELECT post_title FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status IN ('publish','draft','pending','private')"
	);
}

/**
 * Get all existing post titles (to avoid collisions).
 *
 * @return array
 */
function syria_oil_get_existing_post_titles() {
	global $wpdb;
	return $wpdb->get_col(
		"SELECT post_title FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status IN ('publish','draft','pending','private')"
	);
}

/**
 * Get all existing category slugs.
 *
 * @return array
 */
function syria_oil_get_existing_category_slugs() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => false,
			'fields'     => 'id=>slug',
		)
	);
	return is_array( $terms ) ? array_values( $terms ) : array();
}

/**
 * Generate unique title to avoid collision with existing content.
 *
 * @param string $title         Proposed title.
 * @param array  $existing      Array of existing titles.
 * @param string $suffix_format Suffix format (default: ' (Demo %d)').
 * @return string
 */
function syria_oil_unique_title( $title, $existing, $suffix_format = ' (Demo %d)' ) {
	if ( ! in_array( $title, $existing, true ) ) {
		return $title;
	}
	
	// Try adding suffix with incrementing number.
	$counter = 1;
	do {
		$new_title = $title . sprintf( $suffix_format, $counter );
		$counter++;
	} while ( in_array( $new_title, $existing, true ) && $counter < 100 );
	
	return $new_title;
}

/**
 * Generate unique slug to avoid collision with existing terms.
 *
 * @param string $slug     Proposed slug.
 * @param array  $existing Array of existing slugs.
 * @return string
 */
function syria_oil_unique_slug( $slug, $existing ) {
	if ( ! in_array( $slug, $existing, true ) ) {
		return $slug;
	}
	
	$counter = 1;
	do {
		$new_slug = $slug . '-demo-' . $counter;
		$counter++;
	} while ( in_array( $new_slug, $existing, true ) && $counter < 100 );
	
	return $new_slug;
}

/**
 * Restore preserved settings that shouldn't be overwritten.
 *
 * @param array $backup Backed-up settings.
 */
function syria_oil_restore_preserved_settings( $backup ) {
	if ( ! is_array( $backup ) || empty( $backup ) ) {
		return;
	}
	
	// Restore custom logo if exists (don't overwrite with demo logo).
	if ( ! empty( $backup['custom_logo'] ) ) {
		set_theme_mod( 'custom_logo', $backup['custom_logo'] );
	}
	if ( ! empty( $backup['site_icon'] ) ) {
		update_option( 'site_icon', $backup['site_icon'] );
	}
	
	// Keep existing site title/tagline if customized.
	if ( ! empty( $backup['blogname'] ) && 'Just another WordPress site' !== $backup['blogname'] ) {
		update_option( 'blogname', $backup['blogname'] );
	}
	if ( ! empty( $backup['blogdescription'] ) && 'Just another WordPress site' !== $backup['blogdescription'] ) {
		update_option( 'blogdescription', $backup['blogdescription'] );
	}
	
	// Preserve permalink structure if already customized.
	if ( ! empty( $backup['permalink_structure'] ) && '/%postname%/' !== $backup['permalink_structure'] ) {
		update_option( 'permalink_structure', $backup['permalink_structure'] );
	}
	
	// Preserve front page settings if already configured.
	if ( ! empty( $backup['page_on_front'] ) ) {
		update_option( 'show_on_front', $backup['show_on_front'] );
		update_option( 'page_on_front', $backup['page_on_front'] );
		if ( ! empty( $backup['page_for_posts'] ) ) {
			update_option( 'page_for_posts', $backup['page_for_posts'] );
		}
	}
}

/**
 * Find a published post by exact title.
 *
 * @param string $title Title.
 * @return WP_Post|null
 */
function syria_oil_get_post_by_title( $title ) {
	$query = new WP_Query(
		array(
			'post_type'              => 'post',
			'title'                  => $title,
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	if ( ! empty( $query->posts[0] ) && $query->posts[0]->post_title === $title ) {
		return $query->posts[0];
	}
	return null;
}

/**
 * Install starter pages, posts, menus, and Customizer defaults.
 *
 * Prefers the ministry-portal content pack (demos/ministry-portal/) when present.
 *
 * @param bool  $force   Re-run even if previously installed.
 * @param array $options Import options (overwrite flags).
 * @return array<string,int|bool>
 */
function syria_oil_install_starter_content( $force = false, $options = array() ) {
	if ( ! $force && syria_oil_starter_is_installed() ) {
		return array(
			'pages_created' => 0,
			'pages_updated' => 0,
			'posts_created' => 0,
			'posts_updated' => 0,
			'menus'         => 0,
			'pages_total'   => 0,
			'skipped'       => true,
		);
	}
	
	// Backup existing content for collision detection.
	$backup = syria_oil_backup_existing_settings();

	$pack = syria_oil_portal_pack_path( 'content-pack.json' );
	if ( file_exists( $pack ) && function_exists( 'syria_oil_install_portal_content' ) ) {
		$counts = syria_oil_install_portal_content( true, $options );
		flush_rewrite_rules( false );
		return $counts;
	}

	// Legacy minimal fallback if packs are missing.
	$counts = syria_oil_install_starter_content_legacy( $force, $backup, $options );
	if ( function_exists( 'syria_oil_seed_demo_side_ads' ) ) {
		// false = refresh ad meta without re-uploading creatives into Media Library.
		$ads = syria_oil_seed_demo_side_ads( false );
		$counts['side_ads_seeded'] = $ads['seeded'];
	}
	if ( function_exists( 'syria_oil_seed_emblem_identity' ) ) {
		$counts['emblem_identity'] = (int) syria_oil_seed_emblem_identity( false );
	}
	if ( function_exists( 'syria_oil_seed_brand_logo' ) ) {
		$counts['brand_logo'] = (int) syria_oil_seed_brand_logo( false );
	}
	return $counts;
}

/**
 * Legacy starter install (minimal Arabic stubs) when portal packs are absent.
 *
 * @param bool  $force   Force refresh.
 * @param array $backup  Optional backup of existing content.
 * @param array $options Import options (overwrite flags).
 * @return array<string,int|bool>
 */
function syria_oil_install_starter_content_legacy( $force = false, $backup = array(), $options = array() ) {
	$counts = array(
		'pages_created' => 0,
		'pages_updated' => 0,
		'posts_created' => 0,
		'menus'         => 0,
		'pages_total'   => 0,
		'skipped'       => false,
		'renamed'       => 0,
		'overwritten'   => 0,
	);

	// Extract existing titles for collision detection.
	$existing_pages = ! empty( $backup['existing_pages'] ) ? $backup['existing_pages'] : array();
	$existing_posts = ! empty( $backup['existing_posts'] ) ? $backup['existing_posts'] : array();
	$existing_cats  = ! empty( $backup['existing_categories'] ) ? $backup['existing_categories'] : array();
	
	// Parse options.
	$overwrite_pages = ! empty( $options['overwrite_pages'] );
	$overwrite_posts = ! empty( $options['overwrite_posts'] );
	$overwrite_cats  = ! empty( $options['overwrite_categories'] );

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
		'contact_email'        => get_option( 'admin_email' ),
	);
	foreach ( $mods as $key => $value ) {
		$opts    = function_exists( 'syria_oil_get_options_raw' ) ? syria_oil_get_options_raw() : array();
		$current = array_key_exists( $key, $opts ) ? $opts[ $key ] : null;
		if ( $force || null === $current || '' === $current ) {
			if ( function_exists( 'syria_oil_set_mod' ) ) {
				syria_oil_set_mod( $key, $value );
			} else {
				set_theme_mod( $key, $value );
			}
		}
	}

	if ( $force || ! get_option( 'blogdescription' ) ) {
		update_option( 'blogdescription', 'أخبار النفط والغاز في سوريا والعالم' );
	}

	update_option( 'permalink_structure', '/%postname%/' );

	$pages_def = array(
		'home'     => array( 'الرئيسية', 'الصفحة الرئيسية. ابنِها بـ Elementor: شريط الأخبار المميزة + شبكة المقالات.' ),
		'news'     => array( 'الأخبار', 'مركز الأخبار: آخر التغطيات حول النفط والغاز.' ),
		'articles' => array( 'المقالات', 'مقالات وتقارير تحليلية.' ),
		'projects' => array( 'المشاريع', 'متابعة المشاريع الجارية والشراكات.' ),
		'services' => array( 'الأقسام', 'تصفح أقسام وتصنيفات الموقع.' ),
		'media'    => array( 'الأخبار', 'أرشيف الأخبار والمواد الإعلامية.' ),
		'tenders'  => array( 'المناقصات', 'المناقصات والمشتريات.' ),
		'about'    => array( 'من نحن', 'نبذة عن منصة سيريا أويل الإخبارية.' ),
		'contact'  => array( 'اتصل بنا', 'للتواصل مع فريق التحرير.' ),
		'careers'  => array( 'فرص العمل', 'إعلانات التوظيف في شركات القطاع.' ),
		'privacy-policy' => array(
			'سياسة الخصوصية',
			'<h2>سياسة الخصوصية</h2><p>تحترم سيريا أويل خصوصية زوارها. نوضح هنا البيانات التي قد تُجمع وكيفية استخدامها وحمايتها.</p><h2>التواصل</h2><p>للاستفسارات المتعلقة بالخصوصية استخدم صفحة اتصل بنا.</p>',
		),
		'sitemap'  => array( 'خريطة الموقع', 'دليل سريع لأهم صفحات البوابة.' ),
		'faq'      => array(
			'الأسئلة الشائعة',
			'<h2>ما هو هذا الموقع؟</h2><p>منصة إخبارية متخصصة بقطاع النفط والغاز.</p><h2>كيف أتابع المناقصات؟</h2><p>من قائمة المناقصات في الأعلى.</p>',
		),
	);

	$page_ids = array();
	$author   = get_current_user_id() ? get_current_user_id() : 1;

	foreach ( $pages_def as $slug => $meta ) {
		$title    = $meta[0];
		$existing = get_page_by_path( $slug );
		if ( ! $existing && function_exists( 'syria_oil_get_page_by_title' ) ) {
			$existing = syria_oil_get_page_by_title( $title );
		}
		
		// Check for title collision and decide: overwrite or rename.
		if ( ! $existing && in_array( $title, $existing_pages, true ) ) {
			if ( $overwrite_pages ) {
				// User chose to overwrite — find and update existing page.
				$existing = syria_oil_get_page_by_title( $title );
				$counts['overwritten']++;
			} else {
				// User chose to keep both — rename demo page.
				$original = $title;
				$title    = syria_oil_unique_title( $title, $existing_pages, ' (Demo)' );
				$existing_pages[] = $title;
				$counts['renamed']++;
			}
		}
		
		$content = ( false !== strpos( $meta[1], '<h2>' ) ) ? $meta[1] : '<p>' . esc_html( $meta[1] ) . '</p>';
		$args    = array(
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => $author,
		);
		if ( $existing ) {
			$args['ID']        = $existing->ID;
			$page_ids[ $slug ] = (int) wp_update_post( $args );
			$counts['pages_updated']++;
		} else {
			$page_ids[ $slug ] = (int) wp_insert_post( $args );
			if ( ! empty( $page_ids[ $slug ] ) && ! is_wp_error( $page_ids[ $slug ] ) ) {
				$counts['pages_created']++;
			}
		}
	}
	$counts['pages_total'] = count( array_filter( $page_ids ) );

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

	$cats    = function_exists( 'syria_oil_starter_category_role_map' )
		? syria_oil_starter_category_role_map()
		: array(
			'featured'  => 'أخبار بارزة',
			'upstream'  => 'أخبار المشاريع النفطية',
			'ministry'  => 'قرارات وأخبار وزارة النفط',
			'companies' => 'الشركات العاملة في سوريا',
			'tenders'   => 'مناقصات الشركات',
		);
	$cat_ids = array();
	foreach ( $cats as $role => $name ) {
		$term = function_exists( 'syria_oil_resolve_category_term' )
			? syria_oil_resolve_category_term( $name )
			: get_term_by( 'name', $name, 'category' );

		if ( $term && ! is_wp_error( $term ) ) {
			$cat_ids[ $role ] = (int) $term->term_id;
			continue;
		}

		$slug = sanitize_title( $name );
		if ( in_array( $slug, $existing_cats, true ) ) {
			if ( $overwrite_cats ) {
				$term = get_term_by( 'slug', $slug, 'category' );
				if ( $term ) {
					$cat_ids[ $role ] = (int) $term->term_id;
					$counts['overwritten']++;
				}
				continue;
			}
			$slug              = syria_oil_unique_slug( $slug, $existing_cats );
			$existing_cats[]   = $slug;
			$counts['renamed']++;
		}

		$term = term_exists( $slug, 'category' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
		}
		if ( ! is_wp_error( $term ) ) {
			$cat_ids[ $role ] = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
		}
	}

	$post_count = (int) wp_count_posts( 'post' )->publish;
	// Never seed sample news on a site that already has articles (production).
	if ( $post_count < 3 ) {
		$samples = array(
			array(
				'قبلاوي: مذكرتا التفاهم خطوة جديدة نحو تعزيز التكامل الإقليمي في قطاع الطاقة',
				'إحياء مشروع خط الحديثة – بانياس يمثل خطوة عملية لتعزيز التكامل الإقليمي في قطاع الطاقة.',
				array( 'featured', 'ministry' ),
			),
			array(
				'سوريا توقّع مذكرتي تفاهم لإحياء مشروع خط الحديثة – بانياس',
				'تم التوقيع على مذكرتي تفاهم لإعادة تأهيل خط نقل النفط الحديثة – بانياس.',
				array( 'featured', 'upstream' ),
			),
			array(
				'النفط السوري يستعيد زخم الاستثمار في الحقول الرئيسية',
				'مؤشرات على عودة النشاط الاستثماري وخطط تطوير الإنتاج في عدد من الحقول.',
				array( 'companies', 'upstream' ),
			),
			array(
				'إعلان مناقصة توريد معدات سلامة لحقول الإنتاج',
				'أُعلن عن مناقصة لتوريد معدات سلامة ميدانية لصالح مواقع الإنتاج.',
				array( 'tenders', 'ministry' ),
			),
		);

		foreach ( $samples as $i => $row ) {
			list( $title, $content, $slugs ) = $row;
			
			// Check for collision and decide: overwrite or rename.
			if ( in_array( $title, $existing_posts, true ) ) {
				if ( $overwrite_posts ) {
					// Overwrite: find existing post and update it.
					$existing_post = syria_oil_get_post_by_title( $title );
					if ( $existing_post ) {
						$id = wp_update_post(
							array(
								'ID'           => $existing_post->ID,
								'post_content' => '<p>' . esc_html( $content ) . '</p>',
								'post_excerpt' => $content,
							)
						);
						if ( $id && ! is_wp_error( $id ) ) {
							$counts['posts_updated']++;
							$counts['overwritten']++;
						}
						continue;
					}
				} else {
					// Keep both: rename demo post.
					$title = syria_oil_unique_title( $title, $existing_posts, ' (Demo)' );
					$existing_posts[] = $title;
					$counts['renamed']++;
				}
			}
			
			if ( ! $force && syria_oil_get_post_by_title( $title ) ) {
				continue;
			}

			$terms = array();
			foreach ( $slugs as $s ) {
				if ( ! empty( $cat_ids[ $s ] ) ) {
					$terms[] = $cat_ids[ $s ];
				}
			}

			$id = wp_insert_post(
				array(
					'post_title'   => $title,
					'post_content' => '<p>' . esc_html( $content ) . '</p>',
					'post_excerpt' => $content,
					'post_status'  => 'publish',
					'post_type'    => 'post',
					'post_author'  => $author,
					'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( $i * DAY_IN_SECONDS ) ),
				)
			);
			if ( $id && ! is_wp_error( $id ) ) {
				if ( $terms ) {
					wp_set_post_terms( $id, $terms, 'category' );
				}
				$counts['posts_created']++;
			}
		}
	}

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
		if ( ! empty( $page_ids[ $slug ] ) ) {
			$add_page( $primary_id, $page_ids[ $slug ] );
		}
	}
	foreach ( array( 'about', 'news', 'articles', 'contact', 'sitemap' ) as $slug ) {
		if ( ! empty( $page_ids[ $slug ] ) ) {
			$add_page( $footer_id, $page_ids[ $slug ] );
		}
	}
	foreach ( array( 'home', 'news', 'projects', 'services' ) as $slug ) {
		if ( ! empty( $page_ids[ $slug ] ) ) {
			$add_page( $mobile_id, $page_ids[ $slug ] );
		}
	}

	$locations = array_filter(
		array(
			'primary'       => $primary_id,
			'footer'        => $footer_id,
			'mobile_bottom' => $mobile_id,
		)
	);
	set_theme_mod( 'nav_menu_locations', $locations );
	$counts['menus'] = count( $locations );

	update_option( 'show_on_front', 'posts' );
	update_option( 'page_on_front', 0 );
	if ( ! empty( $page_ids['news'] ) ) {
		update_option( 'page_for_posts', (int) $page_ids['news'] );
	}

	flush_rewrite_rules( false );
	update_option( 'syria_oil_starter_installed', SYRIA_OIL_VERSION, false );

	if ( function_exists( 'syria_oil_seed_elementor_pages' ) ) {
		$el = syria_oil_seed_elementor_pages( true );
		$counts['elementor_seeded']  = $el['seeded'];
		$counts['elementor_skipped'] = $el['skipped'];
		$counts['elementor_missing'] = $el['missing'];
	}

	return $counts;
}

/**
 * Detect previous theme and store for migration reference.
 *
 * @param string $old_name  Previous theme name.
 * @param WP_Theme $old_theme Previous theme object.
 */
function syria_oil_store_previous_theme( $old_name, $old_theme ) {
	update_option( 'syria_oil_previous_theme', $old_name, false );
	update_option( 'syria_oil_previous_theme_mods', get_theme_mods(), false );
	
	// Store previous menu locations for potential recovery.
	$locations = get_theme_mod( 'nav_menu_locations' );
	if ( $locations ) {
		update_option( 'syria_oil_previous_menu_locations', $locations, false );
	}
}
add_action( 'switch_theme', 'syria_oil_store_previous_theme', 10, 2 );

/**
 * Check if site has existing content (switching from another theme).
 *
 * @return bool
 */
function syria_oil_has_existing_content() {
	$page_count = wp_count_posts( 'page' );
	$post_count = wp_count_posts( 'post' );
	
	return ( $page_count->publish > 3 || $post_count->publish > 5 );
}

/**
 * Auto-install starter content once after theme activation.
 */
function syria_oil_after_switch_theme() {
	$has_content = syria_oil_has_existing_content();
	$prev_theme  = get_option( 'syria_oil_previous_theme' );
	
	// If switching from another theme with existing content, don't auto-import.
	if ( $has_content && $prev_theme ) {
		set_transient( 'syria_oil_migration_notice', 1, DAY_IN_SECONDS );
		return;
	}
	
	// Fresh install or minimal content — auto-install starter.
	if ( syria_oil_starter_is_installed() ) {
		return;
	}
	syria_oil_install_starter_content( false );
	set_transient( 'syria_oil_starter_notice', 1, HOUR_IN_SECONDS );
}
add_action( 'after_switch_theme', 'syria_oil_after_switch_theme' );

/**
 * Whether the full one-click demo has been imported.
 *
 * @return bool
 */
function syria_oil_demo_is_imported() {
	return (bool) get_option( 'syria_oil_demo_imported' );
}

/**
 * Count published posts of a type.
 *
 * @param string $post_type Post type.
 * @return int
 */
function syria_oil_count_published( $post_type ) {
	$counts = wp_count_posts( $post_type );
	return $counts ? (int) $counts->publish : 0;
}

/**
 * Snapshot of demo pieces for the Setup checklist.
 *
 * @return array<string,bool|int>
 */
function syria_oil_demo_status() {
	$ads = function_exists( 'syria_oil_published_side_ad_count' )
		? syria_oil_published_side_ad_count()
		: syria_oil_count_published( 'syria_oil_ad' );

	// Count categories (excluding default 'Uncategorized').
	$cat_terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => false,
			'fields'     => 'count',
		)
	);
	$cat_count = is_array( $cat_terms ) ? count( $cat_terms ) : 0;
	// Exclude default "Uncategorized" category.
	if ( $cat_count > 0 ) {
		$cat_count = max( 0, $cat_count - 1 );
	}

	return array(
		'full_demo'     => syria_oil_demo_is_imported(),
		'pages'         => syria_oil_count_published( 'page' ),
		'posts'         => syria_oil_count_published( 'post' ),
		'categories'    => $cat_count,
		'side_ads'      => $ads,
		'jobs'          => post_type_exists( 'syria_oil_job' ) ? syria_oil_count_published( 'syria_oil_job' ) : 0,
		'elementor'     => (bool) get_option( 'syria_oil_elementor_pages_seeded' ),
		'theme_builder' => (bool) get_option( 'syria_oil_theme_builder_seeded' ),
		'has_elementor' => function_exists( 'syria_oil_has_elementor' ) && syria_oil_has_elementor(),
		'has_widgets'   => defined( 'SYRIA_OIL_WIDGETS_VERSION' ),
		'has_pro'       => function_exists( 'syria_oil_has_elementor_pro' ) && syria_oil_has_elementor_pro(),
		'kit_sync_ok'   => function_exists( 'syria_oil_has_elementor' ) && syria_oil_has_elementor() && ! get_transient( 'syria_oil_kit_sync_failed' ),
	);
}

/**
 * One-click first-time demo: pages, news, images, Side Ads, jobs, Elementor.
 *
 * @param array $options Import options (overwrite flags).
 * @return array<string,mixed>
 */
function syria_oil_run_full_demo_import( $options = array() ) {
	// Default options: keep both (rename demo content).
	$options = wp_parse_args(
		$options,
		array(
			'overwrite_pages'      => false,
			'overwrite_posts'      => false,
			'overwrite_menus'      => false,
			'overwrite_categories' => false,
		)
	);
	
	// Prevent concurrent imports.
	if ( get_transient( 'syria_oil_import_running' ) ) {
		return array(
			'errors' => array( __( 'Import already in progress. Please wait.', 'syria-oil' ) ),
		);
	}
	set_transient( 'syria_oil_import_running', 1, 5 * MINUTE_IN_SECONDS );

	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}
	if ( function_exists( 'wp_raise_memory_limit' ) ) {
		wp_raise_memory_limit( 'admin' );
	}

	// Hook: before import starts.
	do_action( 'syria_oil_before_full_import', $options );

	$report = array(
		'errors'   => array(),
		'warnings' => array(),
		'options'  => $options,
	);

	// Step 1: Production categories first so widgets can bind to live term IDs.
	if ( function_exists( 'syria_oil_import_production_categories' ) ) {
		try {
			$cats = syria_oil_import_production_categories( $options );
			$report['categories_created'] = (int) ( $cats['created'] ?? 0 );
			$report['categories_skipped'] = (int) ( $cats['skipped'] ?? 0 );
			$report['categories_updated'] = (int) ( $cats['updated'] ?? 0 );
			if ( ! empty( $cats['error'] ) ) {
				$report['warnings'][] = (string) $cats['error'];
			}
		} catch ( Exception $e ) {
			$report['errors'][] = sprintf( 'Categories: %s', $e->getMessage() );
		}
	}

	// Step 2: Core content (pages, menus). Sample posts only if the site is empty.
	try {
		$counts = syria_oil_install_starter_content( true, $options );
		if ( is_array( $counts ) ) {
			$report = array_merge( $report, $counts );
		}
	} catch ( Exception $e ) {
		$report['errors'][] = sprintf( 'Starter content: %s', $e->getMessage() );
	}

	// Step 3: Featured images.
	if ( function_exists( 'syria_oil_attach_demo_images' ) ) {
		try {
			$img = syria_oil_attach_demo_images();
			$report['images_attached'] = (int) ( $report['images_attached'] ?? 0 ) + (int) ( $img['attached'] ?? 0 );
			$report['images_failed']   = (int) ( $img['failed'] ?? 0 );
			if ( $img['failed'] > 0 ) {
				$report['warnings'][] = sprintf( '%d images failed to download.', $img['failed'] );
			}
		} catch ( Exception $e ) {
			$report['errors'][] = sprintf( 'Images: %s', $e->getMessage() );
		}
	}

	// Step 4: Side Ads.
	if ( function_exists( 'syria_oil_seed_demo_side_ads' ) ) {
		try {
			$ads = syria_oil_seed_demo_side_ads( true );
			$report['side_ads_seeded'] = (int) ( $ads['seeded'] ?? 0 );
			if ( ! empty( $ads['images_repaired'] ) ) {
				$report['side_ads_images_repaired'] = (int) $ads['images_repaired'];
			}
		} catch ( Exception $e ) {
			$report['errors'][] = sprintf( 'Side Ads: %s', $e->getMessage() );
		}
	}
	if ( function_exists( 'syria_oil_repair_side_ad_images' ) ) {
		$repaired = syria_oil_repair_side_ad_images();
		if ( $repaired > 0 ) {
			$report['side_ads_images_repaired'] = (int) ( ( $report['side_ads_images_repaired'] ?? 0 ) + $repaired );
		}
	}
	$report['side_ads_total'] = function_exists( 'syria_oil_published_side_ad_count' )
		? syria_oil_published_side_ad_count()
		: 0;

	// Step 5: Elementor page layouts.
	if ( function_exists( 'syria_oil_seed_elementor_pages' ) ) {
		if ( ! did_action( 'elementor/loaded' ) ) {
			$report['warnings'][] = __( 'Elementor plugin not active. Page layouts skipped.', 'syria-oil' );
		} else {
			try {
				$el = syria_oil_seed_elementor_pages( true );
				$report['elementor_seeded']  = (int) ( $el['seeded'] ?? 0 );
				$report['elementor_skipped'] = (int) ( $el['skipped'] ?? 0 );
				$report['elementor_missing'] = (int) ( $el['missing'] ?? 0 );
			} catch ( Exception $e ) {
				$report['errors'][] = sprintf( 'Elementor layouts: %s', $e->getMessage() );
			}
		}
	}

	// Step 6: Theme Builder templates (Pro).
	if ( function_exists( 'syria_oil_seed_theme_builder_templates' ) ) {
		try {
			$tb = syria_oil_seed_theme_builder_templates( true );
			$report['theme_builder_seeded'] = (int) ( $tb['seeded'] ?? 0 );
			$report['theme_builder_pro']    = ! empty( $tb['pro'] );
			if ( empty( $tb['pro'] ) ) {
				$report['warnings'][] = __( 'Elementor Pro not active. Theme Builder templates skipped.', 'syria-oil' );
			}
		} catch ( Exception $e ) {
			$report['errors'][] = sprintf( 'Theme Builder: %s', $e->getMessage() );
		}
	}

	// Step 7: Demo jobs.
	if ( function_exists( 'syria_oil_seed_demo_jobs' ) ) {
		try {
			$report['jobs_seeded'] = (int) syria_oil_seed_demo_jobs( false );
		} catch ( Exception $e ) {
			$report['errors'][] = sprintf( 'Jobs: %s', $e->getMessage() );
		}
	}

	// Step 8: Logos & identity.
	if ( function_exists( 'syria_oil_seed_emblem_identity' ) ) {
		try {
			$report['emblem_identity'] = (int) syria_oil_seed_emblem_identity( false );
		} catch ( Exception $e ) {
			$report['errors'][] = sprintf( 'Emblem: %s', $e->getMessage() );
		}
	}
	if ( function_exists( 'syria_oil_seed_brand_logo' ) ) {
		try {
			$report['brand_logo'] = (int) syria_oil_seed_brand_logo( false );
		} catch ( Exception $e ) {
			$report['errors'][] = sprintf( 'Logo: %s', $e->getMessage() );
		}
	}

	// Step 9: Elementor Global Kit (editor globals; front end uses theme CSS vars).
	$kit_file = trailingslashit( SYRIA_OIL_DIR ) . 'demos/elementor/kit.json';
	$kit_ok   = false;
	if ( file_exists( $kit_file ) && function_exists( 'syria_oil_import_elementor_kit_handler' ) ) {
		try {
			$kit_ok = (bool) syria_oil_import_elementor_kit_handler( $kit_file );
		} catch ( Exception $e ) {
			$report['errors'][] = sprintf( 'Elementor Kit: %s', $e->getMessage() );
		}
	}
	if ( function_exists( 'syria_oil_sync_kit_from_options' ) ) {
		$kit_ok = syria_oil_sync_kit_from_options() || $kit_ok;
	}
	$report['elementor_kit'] = $kit_ok;
	if ( ! $kit_ok && function_exists( 'syria_oil_has_elementor' ) && syria_oil_has_elementor() ) {
		$report['warnings'][] = __( 'Elementor Site Kit sync failed. Front-end brand colors still apply via theme CSS — retry from Syria Oil → Setup.', 'syria-oil' );
	}

	flush_rewrite_rules( false );
	update_option( 'syria_oil_demo_imported', SYRIA_OIL_VERSION, false );
	update_option( 'syria_oil_demo_imported_at', time(), false );

	// Clear import lock.
	delete_transient( 'syria_oil_import_running' );

	// Hook: after import completes.
	do_action( 'syria_oil_after_full_import', $report );

	return $report;
}

/**
 * Admin notice after automatic starter install.
 */
function syria_oil_starter_admin_notice() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	
	$setup = admin_url( 'admin.php?page=syria-oil-setup' );
	
	// Migration notice (switching from another theme with content).
	if ( get_transient( 'syria_oil_migration_notice' ) ) {
		delete_transient( 'syria_oil_migration_notice' );
		$prev_theme = get_option( 'syria_oil_previous_theme' );
		?>
		<div class="notice notice-info is-dismissible">
			<h3 style="margin-top:0.5rem;"><?php esc_html_e( 'Welcome to Syria Oil Theme!', 'syria-oil' ); ?></h3>
			<p>
				<?php
				if ( $prev_theme ) {
					printf(
						/* translators: %s: previous theme name */
						esc_html__( 'We detected you switched from %s. Your existing content (pages, posts, menus) has been preserved.', 'syria-oil' ),
						'<strong>' . esc_html( $prev_theme ) . '</strong>'
					);
				} else {
					esc_html_e( 'We detected existing content on your site. Your pages, posts, and menus have been preserved.', 'syria-oil' );
				}
				?>
			</p>
			<p>
				<strong><?php esc_html_e( 'Next steps:', 'syria-oil' ); ?></strong>
			</p>
			<ol style="margin-left:1.5rem;line-height:1.8;">
				<li><?php esc_html_e( 'Check your menus under Appearance → Menus and assign them to Syria Oil locations (Primary, Footer, Mobile Bottom)', 'syria-oil' ); ?></li>
				<li><?php esc_html_e( 'Set your logo and colors under Syria Oil → Theme Options', 'syria-oil' ); ?></li>
				<li><?php esc_html_e( 'If you want demo Side Ads and Elementor layouts, use the Setup page', 'syria-oil' ); ?></li>
			</ol>
			<p style="margin-bottom:0.5rem;">
				<a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Configure Menus', 'syria-oil' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=syria-oil-options' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Theme Options', 'syria-oil' ); ?></a>
				<a href="<?php echo esc_url( $setup ); ?>" class="button button-secondary"><?php esc_html_e( 'Setup Page', 'syria-oil' ); ?></a>
			</p>
		</div>
		<?php
		return;
	}
	
	// Fresh install notice.
	if ( get_transient( 'syria_oil_starter_notice' ) ) {
		delete_transient( 'syria_oil_starter_notice' );
		echo '<div class="notice notice-success is-dismissible"><p>';
		printf(
			/* translators: %s: Setup page URL */
			wp_kses_post( __( 'Syria Oil is active. For a complete first-time demo (pages, news, images, Side Ads, and Elementor layouts), open <a href="%s"><strong>Syria Oil → Setup</strong></a> and click <strong>Import complete demo</strong>.', 'syria-oil' ) ),
			esc_url( $setup )
		);
		echo '</p></div>';
	}
}
add_action( 'admin_notices', 'syria_oil_starter_admin_notice' );

/**
 * Appearance → Syria Oil Setup (starter + demo importer help).
 */
function syria_oil_register_starter_tool_page() {
	add_submenu_page(
		'syria-oil-options',
		__( 'Syria Oil Setup', 'syria-oil' ),
		__( 'Setup', 'syria-oil' ),
		'edit_theme_options',
		'syria-oil-setup',
		'syria_oil_render_starter_tool_page'
	);
}
add_action( 'admin_menu', 'syria_oil_register_starter_tool_page', 20 );

/**
 * Whether One Click Demo Import is available.
 *
 * @return bool
 */
function syria_oil_has_ocdi() {
	return class_exists( 'OCDI_Plugin' ) || defined( 'PT_OCDI_PATH' ) || function_exists( 'ocdi_import_files' );
}

/**
 * Plugin install URL for One Click Demo Import.
 *
 * @return string
 */
function syria_oil_ocdi_install_url() {
	return self_admin_url( 'plugin-install.php?s=One%20Click%20Demo%20Import&tab=search&type=term' );
}

/**
 * OCDI admin page URL when the plugin is active.
 *
 * @return string
 */
function syria_oil_ocdi_page_url() {
	if ( menu_page_url( 'one-click-demo-import', false ) ) {
		return menu_page_url( 'one-click-demo-import', false );
	}
	return self_admin_url( 'themes.php?page=one-click-demo-import' );
}

/**
 * Render setup / starter tool page.
 */
function syria_oil_render_starter_tool_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$done   = false;
	$counts = null;
	$el_done = false;
	$el_stats = null;
	$tb_done = false;
	$tb_stats = null;
	$img_done = false;
	$img_stats = null;
	$full_done = false;
	$full_report = null;

	// One-click full demo import.
	if ( isset( $_POST['syria_oil_full_demo_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['syria_oil_full_demo_nonce'] ) ), 'syria_oil_full_demo' ) ) {
		// Double-check capability (belt & suspenders).
		if ( ! current_user_can( 'import' ) && ! current_user_can( 'edit_theme_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to import content.', 'syria-oil' ) );
		}
		
		// Collect import options from user.
		$import_options = array(
			'overwrite_pages'      => ! empty( $_POST['syria_oil_overwrite_pages'] ),
			'overwrite_posts'      => ! empty( $_POST['syria_oil_overwrite_posts'] ),
			'overwrite_menus'      => ! empty( $_POST['syria_oil_overwrite_menus'] ),
			'overwrite_categories' => ! empty( $_POST['syria_oil_overwrite_categories'] ),
		);
		
		$full_report = syria_oil_run_full_demo_import( $import_options );
		$full_done   = true;
	}

	if ( isset( $_POST['syria_oil_starter_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['syria_oil_starter_nonce'] ) ), 'syria_oil_starter_run' ) ) {
		$counts = syria_oil_install_starter_content( true );
		$done   = true;
	}

	if ( isset( $_POST['syria_oil_images_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['syria_oil_images_nonce'] ) ), 'syria_oil_attach_images' ) ) {
		if ( function_exists( 'syria_oil_attach_demo_images' ) ) {
			$img_stats = syria_oil_attach_demo_images();
			// Also refresh pack posts' missing thumbs via import path.
			if ( function_exists( 'syria_oil_import_portal_posts' ) ) {
				$pack_imgs = syria_oil_import_portal_posts( false );
				$img_stats['pack_images'] = (int) ( $pack_imgs['images'] ?? 0 );
			}
			$img_done = true;
		}
	}

	if ( isset( $_POST['syria_oil_elementor_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['syria_oil_elementor_nonce'] ) ), 'syria_oil_elementor_seed' ) ) {
		if ( function_exists( 'syria_oil_seed_elementor_pages' ) ) {
			$el_stats = syria_oil_seed_elementor_pages( true );
			$el_done  = true;
		}
		if ( function_exists( 'syria_oil_seed_theme_builder_templates' ) ) {
			$tb_stats = syria_oil_seed_theme_builder_templates( true );
			$tb_done  = true;
		}
	}

	if ( isset( $_POST['syria_oil_theme_builder_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['syria_oil_theme_builder_nonce'] ) ), 'syria_oil_theme_builder_seed' ) ) {
		if ( function_exists( 'syria_oil_seed_theme_builder_templates' ) ) {
			$tb_stats = syria_oil_seed_theme_builder_templates( true );
			$tb_done  = true;
		}
	}

	$has_ocdi = syria_oil_has_ocdi();
	$has_pro  = function_exists( 'syria_oil_has_elementor_pro' ) && syria_oil_has_elementor_pro();
	$status   = syria_oil_demo_status();
	$prev_theme = get_option( 'syria_oil_previous_theme' );
	$has_content = syria_oil_has_existing_content();
	$cat_preview = function_exists( 'syria_oil_preview_production_categories' )
		? syria_oil_preview_production_categories()
		: null;
	?>
	<div class="wrap syria-oil-setup-wrap">
		<h1><?php esc_html_e( 'Syria Oil Setup', 'syria-oil' ); ?></h1>

		<?php
		$kit_sync_result = isset( $_GET['syria_oil_kit_sync'] ) ? sanitize_text_field( wp_unslash( $_GET['syria_oil_kit_sync'] ) ) : '';
		if ( '1' === $kit_sync_result ) :
			?>
			<div class="notice notice-success is-dismissible" style="padding:1rem;margin:1rem 0;">
				<p><?php esc_html_e( 'Elementor Site Kit synced with Theme Options brand colors and fonts.', 'syria-oil' ); ?></p>
			</div>
		<?php elseif ( '0' === $kit_sync_result ) : ?>
			<div class="notice notice-error is-dismissible" style="padding:1rem;margin:1rem 0;">
				<p><?php esc_html_e( 'Elementor Site Kit sync failed. Front-end colors still apply via theme CSS — ensure Elementor is active and try again.', 'syria-oil' ); ?></p>
			</div>
		<?php endif; ?>

		<?php
		$ads_repaired = isset( $_GET['syria_oil_ads_repaired'] ) ? absint( $_GET['syria_oil_ads_repaired'] ) : 0;
		if ( $ads_repaired > 0 ) :
			?>
			<div class="notice notice-success is-dismissible" style="padding:1rem;margin:1rem 0;">
				<p>
					<?php
					printf(
						/* translators: %d: number of side ads repaired */
						esc_html__( 'Repaired featured images for %d side ad(s).', 'syria-oil' ),
						$ads_repaired
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<?php if ( function_exists( 'syria_oil_side_ads_need_image_repair' ) && syria_oil_side_ads_need_image_repair() ) : ?>
			<div class="notice notice-warning" style="padding:1rem;margin:1rem 0;">
				<p>
					<?php esc_html_e( 'Some Side Ads are missing their featured images (upload may have failed during import). Images can still load from the theme bundle, or you can re-import creatives into the Media Library.', 'syria-oil' ); ?>
					<a href="<?php echo esc_url( syria_oil_seed_demo_ads_url( 'setup' ) ); ?>" class="button button-secondary" style="margin-left:0.5rem;">
						<?php esc_html_e( 'Repair Side Ad Images', 'syria-oil' ); ?>
					</a>
				</p>
			</div>
		<?php endif; ?>
		
		<?php
		// Theme migration info box.
		if ( $prev_theme && $has_content ) :
			?>
			<div class="notice notice-info" style="padding:1.25rem;margin:1rem 0;border-left:4px solid #2271b1;">
				<h3 style="margin-top:0;color:#2271b1;">
					<?php
					printf(
						/* translators: %s: previous theme name */
						esc_html__( '📦 Migrating from %s', 'syria-oil' ),
						esc_html( $prev_theme )
					);
					?>
				</h3>
				<p>
					<?php
					printf(
						/* translators: 1: page count 2: post count */
						esc_html__( 'Your existing content has been preserved: %1$d pages, %2$d posts. The demo import below is optional — only use it if you want to add Syria Oil demo content alongside your existing content.', 'syria-oil' ),
						(int) $status['pages'],
						(int) $status['posts']
					);
					?>
				</p>
				<p style="margin:0;">
					<a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>" class="button button-secondary">
						<?php esc_html_e( 'Assign Menus to New Theme', 'syria-oil' ); ?>
					</a>
				</p>
			</div>
			<?php
		endif;
		?>

		<?php
		// Dependency warnings.
		$missing = array();
		if ( ! $status['has_elementor'] ) {
			$missing[] = '<strong>Elementor</strong>';
		}
		if ( ! $status['has_widgets'] ) {
			$missing[] = '<strong>Syria Oil Widgets</strong>';
		}
		if ( ! empty( $missing ) ) :
			?>
			<div class="notice notice-warning" style="padding:1rem;margin:1rem 0;">
				<p>
					<?php
					printf(
						/* translators: %s: list of missing plugins */
						wp_kses_post( __( '<strong>Recommended:</strong> Install %s before importing to get Elementor page layouts and custom widgets. The import will work without them, but pages will be empty.', 'syria-oil' ) ),
						implode( ' ' . __( 'and', 'syria-oil' ) . ' ', $missing ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					);
					?>
				</p>
				<p style="margin:0.5rem 0 0;">
					<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=elementor&tab=search' ) ); ?>" class="button button-secondary">
						<?php esc_html_e( 'Install Elementor', 'syria-oil' ); ?>
					</a>
				</p>
			</div>
		<?php endif; ?>

		<?php if ( $full_done && is_array( $full_report ) ) : ?>
			<?php
			// Show errors if any.
			if ( ! empty( $full_report['errors'] ) ) :
				?>
				<div class="notice notice-error" style="padding:1rem;margin:1rem 0;">
					<h3 style="margin-top:0;color:#d63638;"><?php esc_html_e( 'Import Errors', 'syria-oil' ); ?></h3>
					<ul style="margin:0;">
						<?php foreach ( $full_report['errors'] as $error ) : ?>
							<li><?php echo esc_html( $error ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php
			// Show warnings if any.
			if ( ! empty( $full_report['warnings'] ) ) :
				?>
				<div class="notice notice-warning" style="padding:1rem;margin:1rem 0;">
					<h3 style="margin-top:0;color:#dba617;"><?php esc_html_e( 'Import Warnings', 'syria-oil' ); ?></h3>
					<ul style="margin:0;">
						<?php foreach ( $full_report['warnings'] as $warning ) : ?>
							<li><?php echo esc_html( $warning ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			<div class="notice notice-success" style="padding:1.5rem;margin:1rem 0;border-left:4px solid #00a32a;">
				<h2 style="margin-top:0;color:#00a32a;"><?php esc_html_e( '✓ Complete Demo Imported Successfully', 'syria-oil' ); ?></h2>
				<p style="font-size:1.05em;margin-bottom:1rem;">
					<?php esc_html_e( 'Your Syria Oil site is ready with pages, news articles, images, Side Ads, and Elementor layouts.', 'syria-oil' ); ?>
				</p>
				<ul style="margin:0;padding-left:1.5rem;line-height:1.8;">
					<?php if ( ! empty( $full_report['pages_created'] ) || ! empty( $full_report['pages_updated'] ) ) : ?>
						<li>
							<?php
							printf(
								/* translators: 1: created 2: updated 3: total */
								esc_html__( 'Pages: %1$d created, %2$d updated (total %3$d)', 'syria-oil' ),
								(int) ( $full_report['pages_created'] ?? 0 ),
								(int) ( $full_report['pages_updated'] ?? 0 ),
								(int) ( $full_report['pages_total'] ?? 0 )
							);
							?>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $full_report['posts_created'] ) || ! empty( $full_report['posts_updated'] ) ) : ?>
						<li>
							<?php
							printf(
								/* translators: 1: created 2: updated */
								esc_html__( 'Posts: %1$d created, %2$d updated', 'syria-oil' ),
								(int) ( $full_report['posts_created'] ?? 0 ),
								(int) ( $full_report['posts_updated'] ?? 0 )
							);
							?>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $full_report['categories_created'] ) || ! empty( $full_report['categories_skipped'] ) || ! empty( $full_report['categories_updated'] ) ) : ?>
						<li>
							<?php
							printf(
								/* translators: 1: created 2: kept 3: updated */
								esc_html__( 'Categories: %1$d added, %2$d kept as-is, %3$d updated', 'syria-oil' ),
								(int) ( $full_report['categories_created'] ?? 0 ),
								(int) ( $full_report['categories_skipped'] ?? 0 ),
								(int) ( $full_report['categories_updated'] ?? 0 )
							);
							?>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $full_report['images_attached'] ) ) : ?>
						<li>
							<?php
							printf(
								/* translators: %d: images attached */
								esc_html__( 'Featured images: %d attached', 'syria-oil' ),
								(int) $full_report['images_attached']
							);
							?>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $full_report['side_ads_total'] ) ) : ?>
						<li>
							<?php
							printf(
								/* translators: %d: Side Ads */
								esc_html__( 'Side Ads: %d ready', 'syria-oil' ),
								(int) $full_report['side_ads_total']
							);
							?>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $full_report['elementor_seeded'] ) ) : ?>
						<li>
							<?php
							printf(
								/* translators: %d: Elementor pages */
								esc_html__( 'Elementor layouts: %d pages ready', 'syria-oil' ),
								(int) $full_report['elementor_seeded']
							);
							?>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $full_report['theme_builder_seeded'] ) && ! empty( $full_report['theme_builder_pro'] ) ) : ?>
						<li>
							<?php
							printf(
								/* translators: %d: Theme Builder templates */
								esc_html__( 'Theme Builder: %d templates (Header, Single, Archive)', 'syria-oil' ),
								(int) $full_report['theme_builder_seeded']
							);
							?>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $full_report['elementor_kit'] ) ) : ?>
						<li><?php esc_html_e( 'Elementor Site Kit: synced with brand colors', 'syria-oil' ); ?></li>
					<?php elseif ( ! empty( $status['has_elementor'] ) && array_key_exists( 'elementor_kit', $full_report ) ) : ?>
						<li><?php esc_html_e( 'Elementor Site Kit: sync failed (front-end colors still apply via theme CSS)', 'syria-oil' ); ?></li>
					<?php endif; ?>
				</ul>
				<p style="margin-top:1.5rem;margin-bottom:0;">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button button-primary button-large"><?php esc_html_e( 'View Your Site', 'syria-oil' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Manage Pages', 'syria-oil' ); ?></a>
				</p>
			</div>
		<?php endif; ?>

		<!-- One-Click Full Demo Import -->
		<div class="card" style="max-width:900px;padding:2rem;margin:1rem 0;background:linear-gradient(135deg,#f8f9fa 0%,#ffffff 100%);border:2px solid #007a3d;box-shadow:0 4px 12px rgba(0,122,61,0.1);">
			<div style="display:flex;gap:2rem;align-items:start;">
				<div style="flex:1;">
					<h2 style="margin:0 0 0.75rem 0;color:#007a3d;font-size:1.5rem;">
						<?php esc_html_e( '🚀 One-Click Complete Demo Import', 'syria-oil' ); ?>
					</h2>
					<p style="font-size:1.05em;line-height:1.6;margin-bottom:1.25rem;color:#334155;">
						<?php
						if ( $has_content ) {
							esc_html_e( 'Import demo content (pages, news, Side Ads, Elementor layouts) alongside your existing content. Your current content will not be removed.', 'syria-oil' );
						} else {
							esc_html_e( 'Import everything in one click: pages, news articles, images, Side Ads, Elementor layouts, and Theme Builder templates. Perfect for first-time setup.', 'syria-oil' );
						}
						?>
					</p>

					<!-- Status Checklist -->
					<div style="background:#fff;padding:1rem;border-radius:8px;margin-bottom:1.5rem;border:1px solid #e2e8f0;">
						<h3 style="margin:0 0 0.75rem 0;font-size:0.95rem;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">
							<?php esc_html_e( 'Current Status', 'syria-oil' ); ?>
						</h3>
						<ul style="margin:0;padding:0;list-style:none;font-size:0.95em;line-height:1.8;">
							<li style="display:flex;align-items:center;gap:0.5rem;">
								<span style="color:<?php echo $status['pages'] > 5 ? '#00a32a' : '#94a3b8'; ?>;">
									<?php echo $status['pages'] > 5 ? '✓' : '○'; ?>
								</span>
								<?php
								printf(
									/* translators: %d: page count */
									esc_html__( 'Pages: %d', 'syria-oil' ),
									$status['pages']
								);
								?>
							</li>
							<li style="display:flex;align-items:center;gap:0.5rem;">
								<span style="color:<?php echo $status['posts'] > 10 ? '#00a32a' : '#94a3b8'; ?>;">
									<?php echo $status['posts'] > 10 ? '✓' : '○'; ?>
								</span>
								<?php
								printf(
									/* translators: %d: post count */
									esc_html__( 'News articles: %d', 'syria-oil' ),
									$status['posts']
								);
								?>
							</li>
							<li style="display:flex;align-items:center;gap:0.5rem;">
								<span style="color:<?php echo $status['categories'] > 10 ? '#00a32a' : '#94a3b8'; ?>;">
									<?php echo $status['categories'] > 10 ? '✓' : '○'; ?>
								</span>
								<?php
								printf(
									/* translators: %d: category count */
									esc_html__( 'Categories: %d', 'syria-oil' ),
									$status['categories']
								);
								?>
							</li>
							<li style="display:flex;align-items:center;gap:0.5rem;">
								<span style="color:<?php echo $status['side_ads'] > 0 ? '#00a32a' : '#94a3b8'; ?>;">
									<?php echo $status['side_ads'] > 0 ? '✓' : '○'; ?>
								</span>
								<?php
								printf(
									/* translators: %d: Side Ads count */
									esc_html__( 'Side Ads: %d', 'syria-oil' ),
									$status['side_ads']
								);
								?>
							</li>
							<?php if ( $status['has_elementor'] ) : ?>
								<li style="display:flex;align-items:center;gap:0.5rem;">
									<span style="color:<?php echo $status['elementor'] ? '#00a32a' : '#94a3b8'; ?>;">
										<?php echo $status['elementor'] ? '✓' : '○'; ?>
									</span>
									<?php esc_html_e( 'Elementor layouts', 'syria-oil' ); ?>
								</li>
								<li style="display:flex;align-items:center;gap:0.5rem;">
									<span style="color:<?php echo ! empty( $status['kit_sync_ok'] ) ? '#00a32a' : '#dba617'; ?>;">
										<?php echo ! empty( $status['kit_sync_ok'] ) ? '✓' : '!'; ?>
									</span>
									<?php esc_html_e( 'Elementor kit (editor globals)', 'syria-oil' ); ?>
								</li>
							<?php endif; ?>
							<?php if ( $status['has_pro'] ) : ?>
								<li style="display:flex;align-items:center;gap:0.5rem;">
									<span style="color:<?php echo $status['theme_builder'] ? '#00a32a' : '#94a3b8'; ?>;">
										<?php echo $status['theme_builder'] ? '✓' : '○'; ?>
									</span>
									<?php esc_html_e( 'Theme Builder templates', 'syria-oil' ); ?>
								</li>
							<?php endif; ?>
						</ul>
						<?php if ( $status['has_elementor'] && empty( $status['kit_sync_ok'] ) && function_exists( 'syria_oil_sync_kit_from_options' ) ) : ?>
							<p style="margin:0.75rem 0 0;font-size:0.9em;">
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=syria_oil_sync_elementor_kit' ), 'syria_oil_sync_elementor_kit' ) ); ?>" class="button button-secondary">
									<?php esc_html_e( 'Retry Elementor Kit Sync', 'syria-oil' ); ?>
								</a>
								<span style="color:#64748b;margin-left:0.5rem;">
									<?php esc_html_e( 'Pushes Theme Options colors into Elementor for editor consistency.', 'syria-oil' ); ?>
								</span>
							</p>
						<?php endif; ?>
					</div>

					<form method="post" id="syria-oil-full-import-form">
						<?php wp_nonce_field( 'syria_oil_full_demo', 'syria_oil_full_demo_nonce' ); ?>

						<?php if ( is_array( $cat_preview ) && $cat_preview['total'] > 0 ) : ?>
							<div style="background:#fff;padding:1.25rem;border-radius:8px;margin-bottom:1.5rem;border:1px solid #e2e8f0;">
								<h4 style="margin:0 0 0.75rem 0;font-size:0.95rem;color:#334155;">
									<?php esc_html_e( 'Categories (from syria-oil.com)', 'syria-oil' ); ?>
								</h4>
								<p style="margin:0 0 1rem 0;font-size:0.9em;color:#64748b;line-height:1.6;">
									<?php
									printf(
										/* translators: 1: total 2: existing 3: missing */
										esc_html__( '%1$d production categories · %2$d already on your site · %3$d will be added if missing', 'syria-oil' ),
										(int) $cat_preview['total'],
										(int) $cat_preview['existing'],
										(int) $cat_preview['missing']
									);
									?>
								</p>
								<?php if ( $cat_preview['existing'] > 0 ) : ?>
									<label style="display:flex;align-items:flex-start;gap:0.5rem;cursor:pointer;margin-bottom:0.5rem;">
										<input type="checkbox" name="syria_oil_overwrite_categories" value="1" id="syria-oil-overwrite-categories" style="margin:0.2rem 0 0 0;">
										<span style="font-size:0.95em;line-height:1.5;">
											<strong><?php esc_html_e( 'Update existing categories', 'syria-oil' ); ?></strong>
											<br>
											<span style="color:#64748b;">
												<?php
												printf(
													/* translators: %d: existing category count */
													esc_html__( 'Sync name and slug for %d categories that already exist. Posts stay linked — nothing is deleted.', 'syria-oil' ),
													(int) $cat_preview['existing']
												);
												?>
											</span>
										</span>
									</label>
									<p class="description" style="margin:0;font-size:0.85em;color:#94a3b8;">
										<?php esc_html_e( 'Leave unchecked to keep your current categories unchanged and only add missing ones. Widgets will use existing categories automatically.', 'syria-oil' ); ?>
									</p>
								<?php else : ?>
									<p class="description" style="margin:0;font-size:0.85em;color:#94a3b8;">
										<?php esc_html_e( 'No matching categories found yet — all production categories will be created.', 'syria-oil' ); ?>
									</p>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						
						<?php if ( $has_content ) : ?>
							<!-- Import Options (only show if existing content detected) -->
							<div style="background:#fff;padding:1.25rem;border-radius:8px;margin-bottom:1.5rem;border:1px solid #e2e8f0;">
								<h4 style="margin:0 0 0.75rem 0;font-size:0.95rem;color:#334155;">
									<?php esc_html_e( '⚙️ Import Options', 'syria-oil' ); ?>
								</h4>
								<p style="margin:0 0 1rem 0;font-size:0.9em;color:#64748b;">
									<?php esc_html_e( 'You have existing content. Choose how to handle conflicts:', 'syria-oil' ); ?>
								</p>
								
								<div style="display:grid;gap:0.75rem;">
									<label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
										<input type="checkbox" name="syria_oil_overwrite_pages" value="1" style="margin:0;">
										<span style="font-size:0.95em;">
											<?php
											printf(
												/* translators: %d: existing page count */
												esc_html__( 'Overwrite existing pages (you have %d)', 'syria-oil' ),
												(int) $status['pages']
											);
											?>
										</span>
									</label>
									
									<label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
										<input type="checkbox" name="syria_oil_overwrite_posts" value="1" style="margin:0;">
										<span style="font-size:0.95em;">
											<?php
											printf(
												/* translators: %d: existing post count */
												esc_html__( 'Overwrite existing posts (you have %d)', 'syria-oil' ),
												(int) $status['posts']
											);
											?>
										</span>
									</label>
									
									<label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
										<input type="checkbox" name="syria_oil_overwrite_menus" value="1" style="margin:0;">
										<span style="font-size:0.95em;">
											<?php esc_html_e( 'Replace existing menus with demo menus', 'syria-oil' ); ?>
										</span>
									</label>
								</div>
								
								<p class="description" style="margin:0.75rem 0 0 0;font-size:0.85em;color:#94a3b8;">
									<?php esc_html_e( '💡 Tip: Unchecked options will keep both (demo content renamed with "(Demo)" suffix)', 'syria-oil' ); ?>
								</p>
							</div>
						<?php endif; ?>
						
						<p style="margin:0;">
							<button type="submit" id="syria-oil-import-btn" class="button button-primary button-hero" style="background:#007a3d;border-color:#005c2e;box-shadow:0 2px 8px rgba(0,122,61,0.2);font-size:1.1rem;padding:0.75rem 2rem;">
								<?php esc_html_e( 'Import Complete Demo', 'syria-oil' ); ?>
							</button>
							<span id="syria-oil-import-spinner" class="spinner" style="display:none;float:none;margin:0 0 0 1rem;"></span>
						</p>
					</form>
					<script>
					document.getElementById('syria-oil-full-import-form').addEventListener('submit', function(e) {
						var btn = document.getElementById('syria-oil-import-btn');
						var spinner = document.getElementById('syria-oil-import-spinner');
						var hasContent = <?php echo $has_content ? 'true' : 'false'; ?>;
						var anyChecked = false;
						
						if (hasContent) {
							var checkboxes = this.querySelectorAll('input[type="checkbox"]');
							checkboxes.forEach(function(cb) {
								if (cb.checked) anyChecked = true;
							});
						}
						
						var catOverwrite = document.getElementById('syria-oil-overwrite-categories');
						if (catOverwrite && catOverwrite.checked) {
							anyChecked = true;
						}
						
						var msg = hasContent && anyChecked
							? '<?php echo esc_js( __( 'This will overwrite or update your selected content. Continue?', 'syria-oil' ) ); ?>'
							: '<?php echo esc_js( __( 'This will import demo content. Existing categories are kept unless you checked update. The process takes 30-60 seconds. Continue?', 'syria-oil' ) ); ?>';
						
						if (!confirm(msg)) {
							e.preventDefault();
							return false;
						}
						
						if (btn) {
							btn.disabled = true;
							btn.textContent = '<?php echo esc_js( __( 'Importing...', 'syria-oil' ) ); ?>';
						}
						if (spinner) {
							spinner.style.display = 'inline-block';
							spinner.classList.add('is-active');
						}
					});
					</script>
					<p class="description" style="margin-top:0.75rem;">
						<?php esc_html_e( 'Safe to run multiple times. Existing content will be updated, not duplicated. Takes 30–60 seconds.', 'syria-oil' ); ?>
					</p>
				</div>
			</div>
		</div>

		<!-- Advanced / Individual Options (collapsed by default) -->
		<details style="margin:2rem 0;">
			<summary style="cursor:pointer;font-size:1.1rem;font-weight:600;padding:1rem;background:#f8f9fa;border-radius:6px;user-select:none;">
				<?php esc_html_e( '⚙️ Advanced Options (Individual Import Steps)', 'syria-oil' ); ?>
			</summary>

		<div class="card" style="max-width:720px;padding:1rem 1.25rem;margin:1rem 0">
			<h2 style="margin-top:0"><?php esc_html_e( '1. Built-in starter (recommended)', 'syria-oil' ); ?></h2>
			<p><?php esc_html_e( 'Imports ministry-portal inner pages (about, contact, FAQ, tenders, projects, services…) plus all bundled news and articles from syria-oil.com, with menus assigned automatically.', 'syria-oil' ); ?></p>
			<?php if ( $done && is_array( $counts ) ) : ?>
				<div class="notice notice-success inline"><p>
					<?php if ( ! empty( $counts['skipped'] ) ) : ?>
						<?php esc_html_e( 'Starter content was already installed earlier. Click the button again to refresh pages and menus.', 'syria-oil' ); ?>
					<?php else : ?>
						<?php
						printf(
							/* translators: 1: pages created 2: pages updated 3: pages total 4: posts created 5: posts updated 6: menus */
							esc_html__( 'Done. Pages: %1$d new / %2$d updated (total %3$d) · Posts: %4$d new / %5$d updated · Menus: %6$d', 'syria-oil' ),
							(int) ( $counts['pages_created'] ?? 0 ),
							(int) ( $counts['pages_updated'] ?? 0 ),
							(int) ( $counts['pages_total'] ?? 0 ),
							(int) ( $counts['posts_created'] ?? 0 ),
							(int) ( $counts['posts_updated'] ?? 0 ),
							(int) ( $counts['menus'] ?? 0 )
						);
						?>
						<?php if ( isset( $counts['elementor_seeded'] ) ) : ?>
							<br />
							<?php
							printf(
								/* translators: %d: number of Elementor pages seeded */
								esc_html__( 'Elementor demos applied to %d pages.', 'syria-oil' ),
								(int) $counts['elementor_seeded']
							);
							?>
						<?php endif; ?>
					<?php endif; ?>
				</p>
				<?php if ( empty( $counts['skipped'] ) && empty( $counts['pages_created'] ) && ! empty( $counts['pages_updated'] ) ) : ?>
					<p>
						<?php esc_html_e( '“0 created” is normal — those pages were already on the site from the first activation, so they were refreshed instead of duplicated. Check Pages → All Pages.', 'syria-oil' ); ?>
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>"><?php esc_html_e( 'View pages', 'syria-oil' ); ?></a>
						|
						<a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php esc_html_e( 'View menus', 'syria-oil' ); ?></a>
					</p>
				<?php elseif ( empty( $counts['skipped'] ) && ! empty( $counts['pages_total'] ) ) : ?>
					<p>
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>"><?php esc_html_e( 'View pages', 'syria-oil' ); ?></a>
						|
						<a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php esc_html_e( 'View menus', 'syria-oil' ); ?></a>
					</p>
				<?php endif; ?>
				</div>
			<?php endif; ?>
			<form method="post">
				<?php wp_nonce_field( 'syria_oil_starter_run', 'syria_oil_starter_nonce' ); ?>
				<p>
					<button type="submit" class="button button-primary button-hero"><?php esc_html_e( 'Install portal pages + all news/articles', 'syria-oil' ); ?></button>
				</p>
			</form>
			<p class="description">
				<?php esc_html_e( 'Safe to run again: pages are updated, menus are rebuilt, and sample posts are added when missing. Elementor layouts are applied when Elementor is active.', 'syria-oil' ); ?>
			</p>
		</div>

		<div class="card" style="max-width:720px;padding:1rem 1.25rem;margin:1rem 0">
			<h2 style="margin-top:0"><?php esc_html_e( '1b. Attach images from syria-oil.com', 'syria-oil' ); ?></h2>
			<p><?php esc_html_e( 'Downloads real featured photos from syria-oil.com and assigns them to news posts that still have empty thumbnails (cards, slider, and single pages).', 'syria-oil' ); ?></p>
			<?php if ( $img_done && is_array( $img_stats ) ) : ?>
				<div class="notice notice-success inline"><p>
					<?php
					printf(
						/* translators: 1: attached 2: failed 3: pack images */
						esc_html__( 'Images: %1$d attached · %2$d failed · %3$d from content pack.', 'syria-oil' ),
						(int) ( $img_stats['attached'] ?? 0 ),
						(int) ( $img_stats['failed'] ?? 0 ),
						(int) ( $img_stats['pack_images'] ?? 0 )
					);
					?>
				</p></div>
			<?php endif; ?>
			<form method="post">
				<?php wp_nonce_field( 'syria_oil_attach_images', 'syria_oil_images_nonce' ); ?>
				<p>
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Attach syria-oil.com images to posts', 'syria-oil' ); ?></button>
				</p>
			</form>
			<p class="description">
				<?php
				printf(
					/* translators: %s: syria-oil.com URL */
					esc_html__( 'Source: %s — only fills posts without a featured image.', 'syria-oil' ),
					'https://www.syria-oil.com/'
				);
				?>
			</p>
		</div>

		<div class="card" style="max-width:720px;padding:1rem 1.25rem;margin:1rem 0">
			<h2 style="margin-top:0"><?php esc_html_e( '1c. Seed demo side ads', 'syria-oil' ); ?></h2>
			<p><?php esc_html_e( 'Creates Side Ad posts with the bundled left/right banner images (homepage targeting). These fill the desktop side rails and the mobile ad strip.', 'syria-oil' ); ?></p>
			<p>
				<?php if ( function_exists( 'syria_oil_seed_demo_ads_url' ) ) : ?>
					<a class="button button-primary" href="<?php echo esc_url( syria_oil_seed_demo_ads_url( 'setup' ) ); ?>">
						<?php esc_html_e( 'Seed demo side ads', 'syria-oil' ); ?>
					</a>
				<?php endif; ?>
				<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=syria_oil_ad' ) ); ?>">
					<?php esc_html_e( 'Open Side Ads', 'syria-oil' ); ?>
				</a>
			</p>
			<p class="description">
				<?php esc_html_e( 'Also available under: Side Ads (admin menu) and Syria Oil → Import / Export.', 'syria-oil' ); ?>
			</p>
		</div>

		<div class="card" style="max-width:720px;padding:1rem 1.25rem;margin:1rem 0">
			<h2 style="margin-top:0"><?php esc_html_e( '2. Apply Elementor demos to all pages', 'syria-oil' ); ?></h2>
			<p><?php esc_html_e( 'Writes Elementor widget layouts onto Home and every portal page so “Edit with Elementor” opens the full demo instead of a blank canvas. Requires Elementor + Syria Oil Widgets.', 'syria-oil' ); ?></p>
			<?php if ( $el_done && is_array( $el_stats ) ) : ?>
				<div class="notice notice-success inline"><p>
					<?php
					printf(
						/* translators: 1: seeded 2: skipped 3: missing */
						esc_html__( 'Elementor demos: %1$d applied · %2$d skipped · %3$d pages not found.', 'syria-oil' ),
						(int) $el_stats['seeded'],
						(int) $el_stats['skipped'],
						(int) $el_stats['missing']
					);
					?>
				</p></div>
			<?php endif; ?>
			<form method="post">
				<?php wp_nonce_field( 'syria_oil_elementor_seed', 'syria_oil_elementor_nonce' ); ?>
				<p>
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Re-apply Elementor demos to all pages', 'syria-oil' ); ?></button>
				</p>
			</form>
			<?php if ( $tb_done && is_array( $tb_stats ) && ! empty( $tb_stats['pro'] ) ) : ?>
				<div class="notice notice-success inline"><p>
					<?php
					printf(
						/* translators: %d: Theme Builder templates seeded */
						esc_html__( 'Elementor Pro Theme Builder: %d template(s) applied (Header + Single Post + Archive).', 'syria-oil' ),
						(int) $tb_stats['seeded']
					);
					?>
				</p></div>
			<?php endif; ?>
		</div>

		<div class="card" style="max-width:720px;padding:1rem 1.25rem;margin:1rem 0">
			<h2 style="margin-top:0"><?php esc_html_e( '3. Elementor Pro Theme Builder (Header + Single + Archive)', 'syria-oil' ); ?></h2>
			<?php if ( $has_pro ) : ?>
				<p><?php esc_html_e( 'Installs editable Header, Single Post, and Archive templates. After applying, edit the header anytime under Templates → Theme Builder → Header.', 'syria-oil' ); ?></p>
				<?php if ( $tb_done && is_array( $tb_stats ) ) : ?>
					<div class="notice notice-success inline"><p>
						<?php
						printf(
							/* translators: 1: seeded 2: skipped */
							esc_html__( 'Theme Builder: %1$d applied · %2$d skipped.', 'syria-oil' ),
							(int) $tb_stats['seeded'],
							(int) $tb_stats['skipped']
						);
						?>
					</p></div>
				<?php endif; ?>
				<form method="post">
					<?php wp_nonce_field( 'syria_oil_theme_builder_seed', 'syria_oil_theme_builder_nonce' ); ?>
					<p>
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply Header + Single Post + Archive templates', 'syria-oil' ); ?></button>
					</p>
				</form>
				<p class="description">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=elementor_library&tabs_group=theme' ) ); ?>">
						<?php esc_html_e( 'Open Theme Builder', 'syria-oil' ); ?>
					</a>
				</p>
			<?php else : ?>
				<p><?php esc_html_e( 'Elementor Pro is not active. Activate Pro to use Theme Builder Header / Single Post / Archive templates. The classic PHP header remains the fallback.', 'syria-oil' ); ?></p>
			<?php endif; ?>
		</div>

		<div class="card" style="max-width:720px;padding:1rem 1.25rem;margin:1rem 0">
			<h2 style="margin-top:0"><?php esc_html_e( '4. One Click Demo Import (optional plugin)', 'syria-oil' ); ?></h2>
			<p><?php esc_html_e( 'The theme supports the free “One Click Demo Import” plugin. That menu only appears after you install and activate the plugin — it is not bundled inside the theme zip.', 'syria-oil' ); ?></p>
			<?php if ( $has_ocdi ) : ?>
				<p>
					<a class="button button-secondary" href="<?php echo esc_url( syria_oil_ocdi_page_url() ); ?>">
						<?php esc_html_e( 'Open Import Demo Data', 'syria-oil' ); ?>
					</a>
				</p>
				<p class="description"><?php esc_html_e( 'Look under Appearance → Import Demo Data.', 'syria-oil' ); ?></p>
			<?php else : ?>
				<p>
					<a class="button button-secondary" href="<?php echo esc_url( syria_oil_ocdi_install_url() ); ?>">
						<?php esc_html_e( 'Install One Click Demo Import', 'syria-oil' ); ?>
					</a>
				</p>
				<ol>
					<li><?php esc_html_e( 'Plugins → Add New → search “One Click Demo Import” → Install → Activate', 'syria-oil' ); ?></li>
					<li><?php esc_html_e( 'Then open Appearance → Import Demo Data', 'syria-oil' ); ?></li>
				</ol>
			<?php endif; ?>
		</div>

		</details><!-- End Advanced Options -->

	</div><!-- .wrap -->
	<?php
}

/**
 * Reminder on the Themes screen when OCDI is missing.
 */
function syria_oil_themes_screen_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'themes' !== $screen->id || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	if ( get_stylesheet() !== get_template() && get_template() !== 'syria-oil-theme' ) {
		// Child of another theme — skip.
	}
	$template = get_template();
	if ( 'syria-oil-theme' !== $template && 'syria-oil' !== $template ) {
		// Allow folder name syria-oil-theme.
		if ( false === strpos( $template, 'syria-oil' ) ) {
			return;
		}
	}

	$setup_url = admin_url( 'admin.php?page=syria-oil-setup' );
	echo '<div class="notice notice-info is-dismissible"><p>';
	printf(
		/* translators: %s: URL to Syria Oil Setup admin page */
		wp_kses_post( __( 'Need menus and sample content? Open <a href="%s"><strong>Appearance → Syria Oil Setup</strong></a>. The “Import Demo Data” page appears only after installing the free One Click Demo Import plugin.', 'syria-oil' ) ),
		esc_url( $setup_url )
	);
	echo '</p></div>';
}
add_action( 'admin_notices', 'syria_oil_themes_screen_notice' );
