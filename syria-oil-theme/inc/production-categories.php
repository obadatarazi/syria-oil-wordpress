<?php
/**
 * Production category map for demo import and Elementor widgets.
 *
 * Source of truth: demo-content/demo-from-production.json (from syria-oil.com).
 * Widgets resolve terms by name/slug/alias so layouts work on the original site
 * where posts already exist.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Path to the production categories JSON pack.
 *
 * @return string
 */
function syria_oil_production_categories_path() {
	return trailingslashit( SYRIA_OIL_DIR ) . 'demo-content/demo-from-production.json';
}

/**
 * Load the production pack (categories + widget map).
 *
 * @return array<string,mixed>
 */
function syria_oil_production_pack() {
	static $pack = null;
	if ( null !== $pack ) {
		return $pack;
	}

	$path = syria_oil_production_categories_path();
	if ( ! is_readable( $path ) ) {
		$pack = array();
		return $pack;
	}

	$data = json_decode( (string) file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$pack = is_array( $data ) ? $data : array();
	return $pack;
}

/**
 * All production categories (name + slug).
 *
 * @return array<int,array{name:string,slug:string}>
 */
function syria_oil_production_categories() {
	$pack = syria_oil_production_pack();
	$cats = ! empty( $pack['categories'] ) && is_array( $pack['categories'] ) ? $pack['categories'] : array();
	$out  = array();
	foreach ( $cats as $row ) {
		if ( empty( $row['name'] ) ) {
			continue;
		}
		$out[] = array(
			'name' => (string) $row['name'],
			'slug' => isset( $row['slug'] ) ? (string) $row['slug'] : '',
		);
	}
	return $out;
}

/**
 * Widget → category layout map used by the design import.
 *
 * @return array<string,mixed>
 */
function syria_oil_widget_category_map() {
	$pack = syria_oil_production_pack();
	if ( ! empty( $pack['widget_map'] ) && is_array( $pack['widget_map'] ) ) {
		return $pack['widget_map'];
	}
	return syria_oil_widget_category_map_defaults();
}

/**
 * Fallback widget map when JSON has no widget_map key.
 *
 * @return array<string,mixed>
 */
function syria_oil_widget_category_map_defaults() {
	return array(
		'homepage_sections'     => array(
			array( 'title' => 'أخبار سوريا', 'cat_name' => 'أخبار سوريا', 'layout' => 'magazine' ),
			array( 'title' => 'نفط وغاز عالمي', 'cat_name' => 'أخبار النفط والغاز العالمي', 'layout' => 'list' ),
			array( 'title' => 'نفط وغاز عربي', 'cat_name' => 'أخبار النفط والغاز العربي', 'layout' => 'rail' ),
			array( 'title' => 'المحروقات', 'cat_name' => 'المحروقات', 'layout' => 'video' ),
			array( 'title' => 'قرارات', 'cat_name' => 'قرارات وأخبار وزارة النفط', 'layout' => 'list' ),
			array(
				'title'              => 'الشركات',
				'cat_name'           => 'الشركات العاملة في سوريا',
				'secondary_cat_name' => 'أخبار شركات وزارة النفط',
				'layout'             => 'gallery',
			),
			array( 'title' => 'مشاريع', 'cat_name' => 'أخبار المشاريع النفطية', 'layout' => 'magazine' ),
			array( 'title' => 'طاقة الغد', 'cat_name' => 'أخبار الكهرباء والطاقة البديلة', 'layout' => 'grid' ),
		),
		'service_tiles'         => array(
			array( 'icon' => '1', 'title' => 'أخبار بارزة', 'cat_name' => 'أخبار بارزة', 'text' => 'تحديثات عاجلة من القطاع' ),
			array( 'icon' => '2', 'title' => 'نفط وغاز عالمي', 'cat_name' => 'أخبار النفط والغاز العالمي', 'text' => 'الأسواق الدولية' ),
			array( 'icon' => '3', 'title' => 'نفط وغاز عربي', 'cat_name' => 'أخبار النفط والغاز العربي', 'text' => 'تغطية إقليمية' ),
			array( 'icon' => '4', 'title' => 'المحروقات', 'cat_name' => 'المحروقات', 'text' => 'التوزيع والأسعار' ),
			array( 'icon' => '5', 'title' => 'قرارات الوزارة', 'cat_name' => 'قرارات وأخبار وزارة النفط', 'text' => 'متابعة رسمية' ),
			array( 'icon' => '6', 'title' => 'شركات عاملة', 'cat_name' => 'الشركات العاملة في سوريا', 'text' => 'شركات وطنية ودولية' ),
		),
		'featured_slider'       => array( 'أخبار بارزة' ),
		'news_ticker'           => array( 'أخبار بارزة', 'أخبار النفط والغاز العالمي', 'أخبار النفط والغاز العربي' ),
		'latest_news'           => array( 'أخبار بارزة', 'أخبار النفط والغاز العالمي', 'أخبار النفط والغاز العربي', 'أخبار المشاريع النفطية', 'المحروقات' ),
		'opinion'               => array( 'مقالات' ),
		'posts_filter_featured' => array( 'أخبار بارزة', 'أخبار النفط والغاز العالمي', 'أخبار النفط والغاز العربي', 'المحروقات', 'أخبار المشاريع النفطية', 'قرارات وأخبار وزارة النفط', 'مقالات' ),
		'category_flow'         => array(
			'أخبار بارزة',
			'أخبار النفط والغاز العالمي',
			'أخبار النفط والغاز العربي',
			'المحروقات',
			'قرارات وأخبار وزارة النفط',
			'الشركات العاملة في سوريا',
			'أخبار المشاريع النفطية',
			'أخبار شركات وزارة النفط',
			'صناعة التكرير',
			'مقالات',
			'مناقصات الشركات',
			'الأوبك والأوابك',
			'الثروات المعدنية',
			'معارض وندوات',
			'أخبار الكهرباء والطاقة البديلة',
			'تقارير',
		),
	);
}

/**
 * Display-title / short-name aliases → production category names.
 *
 * @return array<string,array<int,string>>
 */
function syria_oil_production_category_aliases() {
	return array(
		'أخبار سوريا'              => array( 'أخبار سوريا', 'أخبار بارزة', 'اخبار بارزة', 'Top news', 'Featured' ),
		'أخبار بارزة'              => array( 'أخبار سوريا', 'أخبار بارزة', 'اخبار بارزة', 'Top news', 'Featured' ),
		'نفط وغاز عالمي'           => array( 'أخبار النفط والغاز العالمي', 'أخبار النفط العالمي' ),
		'أخبار النفط العالمي'      => array( 'أخبار النفط والغاز العالمي', 'أخبار النفط العالمي' ),
		'نفط وغاز عربي'            => array( 'أخبار النفط والغاز العربي' ),
		'المحروقات'                => array( 'المحروقات' ),
		'قرارات'                   => array( 'قرارات وأخبار وزارة النفط' ),
		'قرارات الوزارة'           => array( 'قرارات وأخبار وزارة النفط' ),
		'وزارة النفط'              => array( 'قرارات وأخبار وزارة النفط', 'نشرة وزارة النفط' ),
		'شركات عاملة'              => array( 'الشركات العاملة في سوريا' ),
		'مشاريع'                   => array( 'أخبار المشاريع النفطية' ),
		'طاقة الغد'                => array( 'أخبار الكهرباء والطاقة البديلة' ),
		'مناقصات'                  => array( 'مناقصات الشركات' ),
		'مقالات الرأي'             => array( 'مقالات' ),
		'تحليل'                    => array( 'مقالات', 'دراسات وتحقيقات', 'تقارير' ),
		'أحدث الأخبار'             => array( 'أخبار بارزة' ),
		'Global oil & gas'         => array( 'أخبار النفط والغاز العالمي', 'أخبار النفط العالمي' ),
		'Arab oil & gas'           => array( 'أخبار النفط والغاز العربي' ),
		'Fuels'                    => array( 'المحروقات' ),
	);
}

/**
 * Short blurbs for category chips / service tiles.
 *
 * @return array<string,string>
 */
function syria_oil_production_category_blurbs() {
	return array(
		'أخبار بارزة'                      => 'تحديثات عاجلة من القطاع',
		'اخبار بارزة'                      => 'تحديثات عاجلة من القطاع',
		'أخبار النفط العالمي'              => 'الأسواق الدولية',
		'أخبار النفط والغاز العالمي'       => 'الأسواق الدولية',
		'أخبار النفط والغاز العربي'        => 'تغطية إقليمية',
		'المحروقات'                        => 'التوزيع والأسعار',
		'أخبار المشاريع النفطية'           => 'مشاريع وحقول الإنتاج',
		'قرارات وأخبار وزارة النفط'        => 'قرارات رسمية',
		'مناقصات الشركات'                  => 'فرص تعاقد ومناقصات',
		'الشركات العاملة في سوريا'         => 'شركات وطنية ودولية',
		'أخبار شركات وزارة النفط'          => 'شركات الوزارة',
		'شركات وزارة النفط'                => 'شركات الوزارة',
		'مقالات'                           => 'تحليلات ودراسات',
		'معارض وندوات'                     => 'فعاليات القطاع',
		'صناعة التكرير'                    => 'مصافٍ ووحدات التكرير',
		'الثروات المعدنية'                 => 'فوسفات ومعادن',
		'الأوبك والأوابك'                  => 'أسواق أوبك',
		'الأخبار الاقتصادية ذات الصلة'     => 'اقتصاد وطاقة',
		'تصريحات'                          => 'تصريحات رسمية',
		'أخبار الشركات المشتركة'           => 'شراكات القطاع',
		'عقود وامتيازات'                   => 'عقود وامتيازات',
		'تقارير'                           => 'تقارير ميدانية',
		'دراسات وتحقيقات'                  => 'دراسات وتحقيقات',
		'أخبار الكهرباء والطاقة البديلة'   => 'كهرباء وطاقة بديلة',
		'نشرة وزارة النفط'                 => 'نشرة الوزارة',
		'تاريخ النفط والغاز'               => 'تاريخ القطاع',
		'شخصية الأسبوع'                    => 'وجوه القطاع',
		'كاميرا الموقع'                    => 'صور وتغطيات',
		'دليل الشركات'                     => 'دليل الشركات',
		'تأمين ومصارف'                     => 'تأمين ومصارف',
		'هموم وحلول'                       => 'هموم وحلول',
		'نشاطات بيئية'                     => 'نشاطات بيئية',
		'مساهمات اجتماعية'                 => 'مساهمات اجتماعية',
		'ضيف الموقع'                       => 'ضيف الموقع',
		'وجوه اقتصادية'                    => 'وجوه اقتصادية',
		'طب وصحة'                          => 'طب وصحة',
		'استبيان'                          => 'استطلاعات',
		'أعلن معنا'                        => 'أعلن معنا',
		'نشاطات الموقع'                    => 'نشاطات الموقع',
	);
}

/**
 * Find a category term by production name, slug, or alias.
 *
 * @param string $name Category name or short title.
 * @return WP_Term|null
 */
function syria_oil_resolve_category_term( $name ) {
	$name = trim( wp_strip_all_tags( (string) $name ) );
	if ( '' === $name ) {
		return null;
	}

	$candidates = array( $name );
	$aliases    = syria_oil_production_category_aliases();
	if ( isset( $aliases[ $name ] ) ) {
		$candidates = array_merge( $candidates, $aliases[ $name ] );
	}
	foreach ( $aliases as $alias_list ) {
		if ( in_array( $name, $alias_list, true ) ) {
			$candidates = array_merge( $candidates, $alias_list );
		}
	}
	$candidates = array_values( array_unique( $candidates ) );

	foreach ( $candidates as $candidate ) {
		$term = get_term_by( 'name', $candidate, 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			return $term;
		}
		$slug = sanitize_title( $candidate );
		if ( $slug ) {
			$term = get_term_by( 'slug', $slug, 'category' );
			if ( $term && ! is_wp_error( $term ) ) {
				return $term;
			}
			$term = get_term_by( 'slug', $candidate, 'category' );
			if ( $term && ! is_wp_error( $term ) ) {
				return $term;
			}
		}
	}

	return null;
}

/**
 * Resolve one or more category names to term IDs.
 *
 * @param string|array<int,string> $names Names.
 * @return array<int,int>
 */
function syria_oil_resolve_category_ids( $names ) {
	$ids = array();
	foreach ( (array) $names as $name ) {
		$term = syria_oil_resolve_category_term( (string) $name );
		if ( $term ) {
			$ids[] = (int) $term->term_id;
		}
	}
	return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * Bind production category IDs into an Elementor content tree.
 *
 * @param array $elements Elementor elements.
 * @return array
 */
function syria_oil_bind_elementor_categories( $elements ) {
	if ( ! is_array( $elements ) ) {
		return $elements;
	}

	foreach ( $elements as &$el ) {
		if ( ! is_array( $el ) ) {
			continue;
		}
		if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
			$el['elements'] = syria_oil_bind_elementor_categories( $el['elements'] );
		}
		if ( isset( $el['elType'] ) && 'widget' === $el['elType'] && ! empty( $el['widgetType'] ) ) {
			$el['settings'] = syria_oil_bind_widget_category_settings(
				(string) $el['widgetType'],
				isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array()
			);
		}
	}
	unset( $el );

	return $elements;
}

/**
 * Inject production category IDs/names into one widget's settings.
 *
 * @param string $widget_type Widget type.
 * @param array  $settings    Settings.
 * @return array
 */
function syria_oil_bind_widget_category_settings( $widget_type, $settings ) {
	$map = syria_oil_widget_category_map();

	switch ( $widget_type ) {
		case 'syria_oil_category_sections':
			$settings['sections'] = syria_oil_elementor_category_sections_rows( $map['homepage_sections'] ?? array() );
			break;

		case 'syria_oil_service_tiles':
			$settings['items'] = syria_oil_elementor_service_tile_rows( $map['service_tiles'] ?? array() );
			break;

		case 'syria_oil_featured_news_slider':
			$ids = syria_oil_resolve_category_ids( $map['featured_slider'] ?? array( 'أخبار بارزة' ) );
			if ( $ids ) {
				$settings['source']           = 'query';
				$settings['query_categories'] = $ids;
				$settings['query_posts_per_page'] = ! empty( $settings['query_posts_per_page'] ) ? $settings['query_posts_per_page'] : 5;
			}
			break;

		case 'syria_oil_news_ticker':
			$ids = syria_oil_resolve_category_ids( $map['news_ticker'] ?? array() );
			if ( $ids ) {
				$settings['source']           = 'query';
				$settings['query_categories'] = $ids;
			}
			break;

		case 'syria_oil_article_card_grid':
			$heading = isset( $settings['heading'] ) ? (string) $settings['heading'] : '';
			$eyebrow = isset( $settings['eyebrow'] ) ? (string) $settings['eyebrow'] : '';
			$key     = 'latest_news';
			if ( false !== strpos( $heading, 'مقال' ) || false !== strpos( $eyebrow, 'تحليل' ) ) {
				$key = 'opinion';
			}
			$ids = syria_oil_resolve_category_ids( $map[ $key ] ?? array() );
			if ( $ids ) {
				$settings['query_categories'] = $ids;
			}
			break;

		case 'syria_oil_category_flow':
			$settings['source'] = 'terms';
			$settings['number'] = max( 12, count( (array) ( $map['category_flow'] ?? array() ) ) );
			break;

		case 'syria_oil_posts_filter':
			$settings['chip_limit']     = 38;
			$settings['featured_count'] = 6;
			$settings['show_featured']  = 'yes';
			$settings['show_chips']     = 'yes';
			break;
	}

	return $settings;
}

/**
 * Repeater rows for Category Sections, with live term IDs.
 *
 * @param array $rows Map rows.
 * @return array<int,array<string,mixed>>
 */
function syria_oil_elementor_category_sections_rows( $rows ) {
	$out = array();
	foreach ( (array) $rows as $row ) {
		$cat_name = isset( $row['cat_name'] ) ? (string) $row['cat_name'] : '';
		$term     = syria_oil_resolve_category_term( $cat_name );
		$item     = array(
			'_id'                => substr( md5( 'sec-' . $cat_name ), 0, 7 ),
			'title'              => isset( $row['title'] ) ? (string) $row['title'] : $cat_name,
			'cat_name'           => $cat_name,
			'secondary_cat_name' => isset( $row['secondary_cat_name'] ) ? (string) $row['secondary_cat_name'] : '',
			'layout'             => isset( $row['layout'] ) ? (string) $row['layout'] : 'grid',
			'posts_count'        => isset( $row['posts_count'] ) ? $row['posts_count'] : '',
			'text'               => isset( $row['text'] ) ? (string) $row['text'] : '',
			'category_id'        => $term ? (int) $term->term_id : 0,
			'link'               => array( 'url' => '', 'is_external' => '', 'nofollow' => '' ),
		);
		if ( $term ) {
			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				$item['link'] = array(
					'url'         => $link,
					'is_external' => '',
					'nofollow'    => '',
				);
			}
		}
		$out[] = $item;
	}
	return $out;
}

/**
 * Repeater rows for Service Tiles, linked to category archives.
 *
 * @param array $rows Map rows.
 * @return array<int,array<string,mixed>>
 */
function syria_oil_elementor_service_tile_rows( $rows ) {
	$out    = array();
	$blurbs = syria_oil_production_category_blurbs();
	foreach ( (array) $rows as $i => $row ) {
		$cat_name = isset( $row['cat_name'] ) ? (string) $row['cat_name'] : ( isset( $row['title'] ) ? (string) $row['title'] : '' );
		$term     = syria_oil_resolve_category_term( $cat_name );
		$url      = '';
		if ( $term ) {
			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				$url = $link;
			}
		}
		$title = isset( $row['title'] ) ? (string) $row['title'] : $cat_name;
		$text  = isset( $row['text'] ) ? (string) $row['text'] : '';
		if ( '' === $text && isset( $blurbs[ $cat_name ] ) ) {
			$text = $blurbs[ $cat_name ];
		}
		$out[] = array(
			'_id'   => substr( md5( 'tile-' . $cat_name . $i ), 0, 7 ),
			'icon'  => isset( $row['icon'] ) ? (string) $row['icon'] : (string) ( $i + 1 ),
			'title' => $title,
			'text'  => $text,
			'link'  => array(
				'url'         => $url,
				'is_external' => '',
				'nofollow'    => '',
			),
		);
	}
	return $out;
}

/**
 * Find an existing WP category matching a production pack row.
 *
 * @param array{name?:string,slug?:string} $cat_data Pack row.
 * @return WP_Term|null
 */
function syria_oil_find_production_category_existing( $cat_data ) {
	if ( empty( $cat_data['name'] ) ) {
		return null;
	}

	$name = trim( (string) $cat_data['name'] );
	$slug = ! empty( $cat_data['slug'] ) ? (string) $cat_data['slug'] : '';

	if ( $slug ) {
		$existing = get_term_by( 'slug', $slug, 'category' );
		if ( $existing && ! is_wp_error( $existing ) ) {
			return $existing;
		}
		$sanitized = sanitize_title( $slug );
		if ( $sanitized && $sanitized !== $slug ) {
			$existing = get_term_by( 'slug', $sanitized, 'category' );
			if ( $existing && ! is_wp_error( $existing ) ) {
				return $existing;
			}
		}
	}

	$existing = get_term_by( 'name', $name, 'category' );
	if ( $existing && ! is_wp_error( $existing ) ) {
		return $existing;
	}

	if ( function_exists( 'syria_oil_resolve_category_term' ) ) {
		$resolved = syria_oil_resolve_category_term( $name );
		if ( $resolved && ! is_wp_error( $resolved ) ) {
			return $resolved;
		}
	}

	return null;
}

/**
 * Preview production category import (no writes).
 *
 * @return array{total:int,existing:int,missing:int,existing_names:array<int,string>,missing_names:array<int,string>}
 */
function syria_oil_preview_production_categories() {
	$pack = syria_oil_production_categories();
	$out  = array(
		'total'          => count( $pack ),
		'existing'       => 0,
		'missing'        => 0,
		'existing_names' => array(),
		'missing_names'  => array(),
	);

	foreach ( $pack as $cat_data ) {
		$name = isset( $cat_data['name'] ) ? (string) $cat_data['name'] : '';
		if ( '' === $name ) {
			continue;
		}
		if ( syria_oil_find_production_category_existing( $cat_data ) ) {
			++$out['existing'];
			$out['existing_names'][] = $name;
		} else {
			++$out['missing'];
			$out['missing_names'][] = $name;
		}
	}

	return $out;
}

/**
 * Import categories from production site export.
 *
 * @since 1.16.0
 * @param array $options Import options. `overwrite_categories` updates name/slug on existing terms.
 * @return array{created:int,skipped:int,updated:int,error?:string}
 */
function syria_oil_import_production_categories( $options = array() ) {
	$options   = wp_parse_args(
		$options,
		array(
			'overwrite_categories' => false,
		)
	);
	$overwrite = ! empty( $options['overwrite_categories'] );

	$pack = syria_oil_production_categories();
	if ( empty( $pack ) ) {
		return array(
			'created' => 0,
			'skipped' => 0,
			'updated' => 0,
			'error'   => __( 'Production categories JSON file not found or empty.', 'syria-oil' ),
		);
	}

	$created = 0;
	$skipped = 0;
	$updated = 0;

	foreach ( $pack as $cat_data ) {
		if ( empty( $cat_data['name'] ) ) {
			continue;
		}

		$name        = trim( (string) $cat_data['name'] );
		$target_slug = ! empty( $cat_data['slug'] ) ? sanitize_title( (string) $cat_data['slug'] ) : sanitize_title( $name );
		$existing    = syria_oil_find_production_category_existing( $cat_data );

		if ( $existing ) {
			if ( $overwrite ) {
				$args = array();

				if ( $name !== $existing->name ) {
					$args['name'] = $name;
				}

				if ( $target_slug && $target_slug !== $existing->slug ) {
					$conflict = get_term_by( 'slug', $target_slug, 'category' );
					if ( ! $conflict || (int) $conflict->term_id === (int) $existing->term_id ) {
						$args['slug'] = $target_slug;
					}
				}

				if ( ! empty( $args ) ) {
					$result = wp_update_term( (int) $existing->term_id, 'category', $args );
					if ( is_wp_error( $result ) ) {
						++$skipped;
					} else {
						++$updated;
					}
				} else {
					++$skipped;
				}
			} else {
				++$skipped;
			}
			continue;
		}

		$insert_args = array();
		if ( $target_slug ) {
			$insert_args['slug'] = $target_slug;
		}

		$result = wp_insert_term( $name, 'category', $insert_args );

		if ( is_wp_error( $result ) ) {
			if ( ! empty( $result->error_data['term_exists'] ) ) {
				++$skipped;
			}
		} else {
			++$created;
		}
	}

	return array(
		'created' => $created,
		'skipped' => $skipped,
		'updated' => $updated,
	);
}

/**
 * Demo-content role keys used by starter posts → production category names.
 *
 * @return array<string,string>
 */
function syria_oil_starter_category_role_map() {
	return array(
		'featured'  => 'أخبار بارزة',
		'upstream'  => 'أخبار المشاريع النفطية',
		'ministry'  => 'قرارات وأخبار وزارة النفط',
		'companies' => 'الشركات العاملة في سوريا',
		'tenders'   => 'مناقصات الشركات',
		'global'    => 'أخبار النفط والغاز العالمي',
		'regional'  => 'أخبار النفط والغاز العربي',
		'fuels'     => 'المحروقات',
		'refining'  => 'صناعة التكرير',
		'articles'  => 'مقالات',
	);
}
