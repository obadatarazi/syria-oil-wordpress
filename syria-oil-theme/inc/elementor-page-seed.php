<?php
/**
 * Seed Elementor layouts onto portal pages so Edit with Elementor keeps the demo.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slug → Elementor page template basename (without .json).
 *
 * @return array<string,string>
 */
function syria_oil_elementor_page_map() {
	return array(
		'home'         => 'home',
		'news'         => 'news',
		'articles'     => 'articles',
		'about'        => 'about',
		'leadership'   => 'leadership',
		'services'     => 'services',
		'projects'     => 'projects',
		'contact'      => 'contact',
		'faq'          => 'faq',
		'media'        => 'media',
		'sitemap'      => 'sitemap',
		'tenders'      => 'tenders',
		'departments'  => 'departments',
		'downloads'    => 'downloads',
		'careers'      => 'careers',
		'exploration'  => 'exploration',
		'investments'  => 'investments',
		'statistics'   => 'statistics',
		'privacy-policy' => 'privacy-policy',
	);
}

/**
 * Build a unique Elementor element id.
 *
 * @return string
 */
function syria_oil_el_id() {
	return substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 7 );
}

/**
 * Wrap widget(s) in a full-width or boxed section.
 *
 * @param array  $widgets List of widget element arrays.
 * @param string $layout  full_width|boxed.
 * @return array
 */
function syria_oil_el_section( $widgets, $layout = 'boxed' ) {
	$col = array(
		'id'       => syria_oil_el_id(),
		'elType'   => 'column',
		'settings' => array( '_column_size' => 100 ),
		'elements' => array_values( $widgets ),
	);
	return array(
		'id'       => syria_oil_el_id(),
		'elType'   => 'section',
		'settings' => array(
			'layout' => ( 'full_width' === $layout ) ? 'full_width' : 'boxed',
			'gap'    => 'default',
		),
		'elements' => array( $col ),
	);
}

/**
 * Widget element helper.
 *
 * @param string $type     Widget type.
 * @param array  $settings Settings.
 * @return array
 */
function syria_oil_el_widget( $type, $settings = array() ) {
	return array(
		'id'         => syria_oil_el_id(),
		'elType'     => 'widget',
		'widgetType' => $type,
		'settings'   => $settings,
	);
}

/**
 * Text-editor widget with HTML body.
 *
 * @param string $html HTML.
 * @return array
 */
function syria_oil_el_html( $html ) {
	return syria_oil_el_widget(
		'text-editor',
		array(
			'editor' => $html,
		)
	);
}

/**
 * Load pack page meta (title/subtitle/html).
 *
 * @param string $slug Page slug.
 * @return array{title?:string,subtitle?:string,content_html?:string}
 */
function syria_oil_elementor_pack_page( $slug ) {
	$path = function_exists( 'syria_oil_portal_pack_path' )
		? syria_oil_portal_pack_path( 'pages-pack.json' )
		: trailingslashit( SYRIA_OIL_DIR ) . 'demos/ministry-portal/pages-pack.json';
	if ( ! is_readable( $path ) ) {
		return array();
	}
	$data = json_decode( (string) file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( empty( $data[ $slug ] ) || ! is_array( $data[ $slug ] ) ) {
		return array();
	}
	return $data[ $slug ];
}

/**
 * Build Elementor content array for a page slug.
 *
 * @param string $slug Page slug.
 * @return array
 */
function syria_oil_build_elementor_content( $slug ) {
	if ( 'home' === $slug ) {
		return array(
			syria_oil_el_section(
				array(
					syria_oil_el_widget( 'syria_oil_news_ticker', array( 'source' => 'query', 'label' => 'آخر الأخبار', 'query_posts_per_page' => 6 ) ),
				),
				'full_width'
			),
			syria_oil_el_section(
				array(
					syria_oil_el_widget(
						'syria_oil_featured_news_slider',
						array(
							'source'         => 'manual',
							'autoplay'       => 'yes',
							'autoplay_delay' => 6000,
							'show_arrows'    => 'yes',
							'show_dots'      => 'yes',
							'slides'         => array(
								array(
									'image'    => array(
										'url' => 'https://www.syria-oil.com/wp-content/uploads/image/IMG-20260730-WA0089-scaled.jpg',
									),
									'kicker'   => 'أخبار بارزة',
									'title'    => 'بخبرة تتجاوز 30 عاماً واكتشافات عالمية.. “آسبيكت إنرجي” الأمريكية تدرس دخول السوق السورية وتطوير حقول النفط والغاز',
									'excerpt'  => 'في خطوة تعكس عودة اهتمام شركات الطاقة الدولية بالسوق السورية، أبدت شركة “آسبيكت إنرجي” الأمريكية اهتماماً جدياً بدراسة الفرص الاستثمارية والدخول إلى قطاع النفط والغاز في سوريا.',
									'cta_text' => 'اقرأ التفاصيل',
									'link'     => array( 'url' => 'https://www.syria-oil.com/20577/', 'is_external' => 'on', 'nofollow' => '' ),
								),
								array(
									'image'    => array(
										'url' => 'https://www.syria-oil.com/wp-content/uploads/image/FB_IMG_1785151142379.jpg',
									),
									'kicker'   => 'المحروقات',
									'title'    => 'سوريا تستورد شحنتي مازوت وغاز منزلي بإجمالي يقترب من مليون برميل.. وبدء التفريغ خلال ساعات',
									'excerpt'  => 'استقبل مصب بانياس البحري ناقلة مازوت وناقلة غاز مسال في إطار استمرار تأمين احتياجات السوق المحلية من المشتقات النفطية.',
									'cta_text' => 'اقرأ التفاصيل',
									'link'     => array( 'url' => 'https://www.syria-oil.com/20573/', 'is_external' => 'on', 'nofollow' => '' ),
								),
								array(
									'image'    => array(
										'url' => 'https://www.syria-oil.com/wp-content/uploads/image/IMG_20260718_093125_314-scaled.jpg',
									),
									'kicker'   => 'تصريحات',
									'title'    => 'قبلاوي: مذكرتا التفاهم خطوة جديدة نحو تعزيز التكامل الإقليمي في قطاع الطاقة',
									'excerpt'  => 'إحياء خط الحديثة – بانياس ليس مجرد نقل نفط، بل مشروع استراتيجي يعيد رسم خريطة التعاون الإقليمي في الطاقة.',
									'cta_text' => 'اقرأ التفاصيل',
									'link'     => array( 'url' => 'https://www.syria-oil.com/20569/', 'is_external' => 'on', 'nofollow' => '' ),
								),
								array(
									'image'    => array(
										'url' => 'https://www.syria-oil.com/wp-content/uploads/image/IMG_20260718_000529_522.jpg',
									),
									'kicker'   => 'أخبار بارزة',
									'title'    => 'سوريا توقّع مذكرتي تفاهم لإحياء مشروع خط الحديثة – بانياس بالشراكة مع العراق وائتلاف شركات دولي',
									'excerpt'  => 'توقيع مذكرة تفاهم لتأهيل وإعادة إحياء خط نقل النفط الحديثة – بانياس بالشراكة مع العراق وائتلاف شركات دولي.',
									'cta_text' => 'اقرأ التفاصيل',
									'link'     => array( 'url' => 'https://www.syria-oil.com/20565/', 'is_external' => 'on', 'nofollow' => '' ),
								),
							),
						)
					),
				),
				'full_width'
			),
			syria_oil_el_section(
				array(
					syria_oil_el_widget(
						'shortcode',
						array(
							'shortcode' => '[syria_oil_mid_ads]',
						)
					),
				)
			),
			syria_oil_el_section(
				array(
					syria_oil_el_widget(
						'syria_oil_gif_banner',
						array(
							'source'            => 'url',
							'gif_url'           => array(
								'url' => 'https://www.syria-oil.com/wp-content/uploads/image/free-limousine-service-from-and-to-UAE-airports-by-fly-cham-1.gif',
							),
							'alt'               => 'خدمة الليموزين المجانية — Fly Cham',
							'align'             => 'center',
							'full_bleed_mobile' => '',
							'object_fit'        => 'contain',
							'width'             => array( 'unit' => '%', 'size' => 25 ),
							'sor_animate'       => 'yes',
							'sor_animate_style' => 'fade-in',
						)
					),
				)
			),
			syria_oil_el_section(
				array(
					syria_oil_el_widget(
						'syria_oil_category_sections',
						array(
							'posts_per_section' => 2,
							'sor_animate'       => 'yes',
							'sor_animate_style' => 'fade-up',
							'sor_stagger'       => 'yes',
							'sor_stagger_ms'    => 80,
							'sections'          => function_exists( 'syria_oil_elementor_category_sections_rows' )
								? syria_oil_elementor_category_sections_rows( ( syria_oil_widget_category_map()['homepage_sections'] ?? array() ) )
								: array(),
						)
					),
				)
			),
			syria_oil_el_section(
				array(
					syria_oil_el_widget(
						'syria_oil_article_card_grid',
						array(
							'show_header'          => 'yes',
							'eyebrow'              => 'الأخبار',
							'heading'              => 'أحدث الأخبار',
							'query_posts_per_page' => 6,
							'query_offset'         => 4,
						)
					),
				)
			),
			syria_oil_el_section(
				array(
					syria_oil_el_widget(
						'syria_oil_jobs_grid',
						array(
							'show_header'    => 'yes',
							'heading'        => 'فرص العمل',
							'subtitle'       => 'إعلانات فرص العمل المتاحة في شركات قطاع النفط والغاز.',
							'show_benefits'  => '',
							'posts_count'    => 3,
							'open_only'      => 'yes',
							'featured_only'  => 'yes',
							'for_page'       => '',
							'columns'        => '3',
							'view_all_text'  => 'عرض الفرص',
							'view_all_link'  => array( 'url' => '/careers/' ),
							'sor_animate'    => 'yes',
							'sor_animate_style' => 'fade-up',
						)
					),
				)
			),
			syria_oil_el_section(
				array(
					syria_oil_el_widget(
						'syria_oil_article_card_grid',
						array(
							'show_header'          => 'yes',
							'eyebrow'              => 'تحليل',
							'heading'              => 'مقالات الرأي',
							'query_posts_per_page' => 3,
							'view_all_text'        => 'جميع المقالات',
							'view_all_link'        => array( 'url' => '/articles/' ),
						)
					),
				)
			),
			syria_oil_el_section(
				array(
					syria_oil_el_widget( 'syria_oil_category_flow', array( 'source' => 'terms', 'number' => 16 ) ),
				)
			),
			syria_oil_el_section(
				array(
					syria_oil_el_widget( 'syria_oil_partners', array() ),
				)
			),
			syria_oil_el_section(
				array(
					syria_oil_el_widget( 'syria_oil_price_ticker', array() ),
				),
				'full_width'
			),
			syria_oil_el_section(
				array(
					syria_oil_el_widget(
						'syria_oil_newsletter_cta',
						array(
							'mode'  => 'form',
							'title' => 'ابقَ على اطلاع',
							'text'  => 'آخر أخبار النفط والغاز في سوريا والعالم.',
						)
					),
				)
			),
			syria_oil_el_section(
				array(
					syria_oil_el_widget( 'syria_oil_contact_cta', array() ),
				)
			),
		);
	}

	if ( 'news' === $slug || 'articles' === $slug ) {
		$heading = ( 'articles' === $slug ) ? 'المقالات' : 'الأخبار';
		$filter  = array(
			'heading'        => ( 'articles' === $slug ) ? 'المقالات حسب التصنيف' : 'الأخبار حسب التصنيف',
			'show_featured'  => 'yes',
			'featured_count' => 6,
			'show_chips'     => 'yes',
			'chip_limit'     => 38,
			'posts_per_page' => 9,
		);
		return array(
			syria_oil_el_section(
				array(
					syria_oil_el_widget(
						'syria_oil_page_hero',
						array(
							'title'           => $heading,
							'show_breadcrumb' => 'yes',
						)
					),
				),
				'full_width'
			),
			syria_oil_el_section(
				array(
					syria_oil_el_widget( 'syria_oil_posts_filter', $filter ),
				)
			),
		);
	}

	if ( 'faq' === $slug ) {
		$pack = syria_oil_elementor_pack_page( 'faq' );
		return array(
			syria_oil_el_section(
				array(
					syria_oil_el_widget(
						'syria_oil_page_hero',
						array(
							'title'           => ! empty( $pack['title'] ) ? $pack['title'] : 'الأسئلة الشائعة',
							'subtitle'        => ! empty( $pack['subtitle'] ) ? $pack['subtitle'] : '',
							'show_breadcrumb' => 'yes',
						)
					),
				),
				'full_width'
			),
			syria_oil_el_section(
				array(
					syria_oil_el_widget( 'syria_oil_faq_accordion', array() ),
				)
			),
		);
	}

	if ( 'contact' === $slug ) {
		$pack = syria_oil_elementor_pack_page( 'contact' );
		$widgets = array(
			syria_oil_el_widget(
				'syria_oil_page_hero',
				array(
					'title'           => ! empty( $pack['title'] ) ? $pack['title'] : 'اتصل بنا',
					'subtitle'        => ! empty( $pack['subtitle'] ) ? $pack['subtitle'] : '',
					'show_breadcrumb' => 'yes',
				)
			),
		);
		$content = array(
			syria_oil_el_section( $widgets, 'full_width' ),
			syria_oil_el_section( array( syria_oil_el_widget( 'syria_oil_contact_cta', array() ) ) ),
		);
		if ( ! empty( $pack['content_html'] ) ) {
			$content[] = syria_oil_el_section( array( syria_oil_el_html( $pack['content_html'] ) ) );
		}
		$content[] = syria_oil_el_section(
			array(
				syria_oil_el_widget( 'syria_oil_newsletter_cta', array( 'mode' => 'form', 'title' => 'تواصل معنا' ) ),
			)
		);
		return $content;
	}

	if ( 'projects' === $slug ) {
		$pack = syria_oil_elementor_pack_page( 'projects' );
		return array(
			syria_oil_el_section(
				array(
					syria_oil_el_widget(
						'syria_oil_page_hero',
						array(
							'title'           => ! empty( $pack['title'] ) ? $pack['title'] : 'المشاريع',
							'subtitle'        => ! empty( $pack['subtitle'] ) ? $pack['subtitle'] : '',
							'show_breadcrumb' => 'yes',
						)
					),
				),
				'full_width'
			),
			syria_oil_el_section( array( syria_oil_el_widget( 'syria_oil_project_cards', array() ) ) ),
			syria_oil_el_section( array( syria_oil_el_widget( 'syria_oil_stats_cards', array() ) ) ),
		);
	}

	if ( 'services' === $slug ) {
		$pack = syria_oil_elementor_pack_page( 'services' );
		$out  = array(
			syria_oil_el_section(
				array(
					syria_oil_el_widget(
						'syria_oil_page_hero',
						array(
							'title'           => ! empty( $pack['title'] ) ? $pack['title'] : 'الخدمات',
							'subtitle'        => ! empty( $pack['subtitle'] ) ? $pack['subtitle'] : '',
							'show_breadcrumb' => 'yes',
						)
					),
				),
				'full_width'
			),
			syria_oil_el_section( array( syria_oil_el_widget( 'syria_oil_service_tiles', array() ) ) ),
		);
		if ( ! empty( $pack['content_html'] ) ) {
			$out[] = syria_oil_el_section( array( syria_oil_el_html( $pack['content_html'] ) ) );
		}
		return $out;
	}

	if ( 'statistics' === $slug ) {
		$pack = syria_oil_elementor_pack_page( 'statistics' );
		return array(
			syria_oil_el_section(
				array(
					syria_oil_el_widget(
						'syria_oil_page_hero',
						array(
							'title'           => ! empty( $pack['title'] ) ? $pack['title'] : 'الإحصاءات',
							'subtitle'        => ! empty( $pack['subtitle'] ) ? $pack['subtitle'] : '',
							'show_breadcrumb' => 'yes',
						)
					),
				),
				'full_width'
			),
			syria_oil_el_section( array( syria_oil_el_widget( 'syria_oil_stats_cards', array() ) ) ),
			syria_oil_el_section( array( syria_oil_el_widget( 'syria_oil_price_ticker', array() ) ), 'full_width' ),
		);
	}

	if ( 'careers' === $slug ) {
		$intro_title = function_exists( 'syria_oil_mod' ) ? (string) syria_oil_mod( 'careers_intro_title', 'الوظائف والتوظيف' ) : 'الوظائف والتوظيف';
		$intro_text  = function_exists( 'syria_oil_mod' ) ? (string) syria_oil_mod( 'careers_intro_text', 'الوظائف الشاغرة والمزايا ونموذج التقديم.' ) : 'الوظائف الشاغرة والمزايا ونموذج التقديم.';
		return array(
			syria_oil_el_section(
				array(
					syria_oil_el_widget(
						'syria_oil_page_hero',
						array(
							'title'           => $intro_title ? $intro_title : 'الوظائف والتوظيف',
							'subtitle'        => $intro_text,
							'show_breadcrumb' => 'yes',
						)
					),
				),
				'full_width'
			),
			syria_oil_el_section(
				array(
					syria_oil_el_widget(
						'syria_oil_jobs_grid',
						array(
							'show_header'   => 'yes',
							'heading'       => 'الوظائف المفتوحة',
							'subtitle'      => 'قدّم مباشرة من صفحة كل فرصة.',
							'show_benefits' => 'yes',
							'posts_count'   => 12,
							'open_only'     => 'yes',
							'for_page'      => 'yes',
							'columns'       => '3',
						)
					),
				)
			),
			syria_oil_el_section( array( syria_oil_el_widget( 'syria_oil_contact_cta', array() ) ) ),
		);
	}

	// Generic inner page: hero + pack HTML + overview + contact.
	$pack  = syria_oil_elementor_pack_page( $slug );
	$title = ! empty( $pack['title'] ) ? $pack['title'] : ucfirst( str_replace( '-', ' ', $slug ) );
	$out   = array(
		syria_oil_el_section(
			array(
				syria_oil_el_widget(
					'syria_oil_page_hero',
					array(
						'title'           => $title,
						'subtitle'        => ! empty( $pack['subtitle'] ) ? $pack['subtitle'] : '',
						'show_breadcrumb' => 'yes',
					)
				),
			),
			'full_width'
		),
	);
	if ( ! empty( $pack['content_html'] ) ) {
		$out[] = syria_oil_el_section( array( syria_oil_el_html( $pack['content_html'] ) ) );
	} else {
		$out[] = syria_oil_el_section( array( syria_oil_el_widget( 'syria_oil_overview_cards', array() ) ) );
	}
	if ( in_array( $slug, array( 'leadership', 'departments' ), true ) ) {
		$out[] = syria_oil_el_section( array( syria_oil_el_widget( 'syria_oil_minister_block', array() ) ) );
	}
	$out[] = syria_oil_el_section( array( syria_oil_el_widget( 'syria_oil_contact_cta', array() ) ) );
	return $out;
}

/**
 * Persist built content to demos/elementor/pages/{slug}.json (for shipping).
 *
 * @param string $slug    Slug.
 * @param array  $content Elementor content.
 */
function syria_oil_write_elementor_page_json( $slug, $content ) {
	$dir = trailingslashit( SYRIA_OIL_DIR ) . 'demos/elementor/pages';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	$path = trailingslashit( $dir ) . $slug . '.json';
	$payload = array(
		'version'       => '0.4',
		'title'         => 'Syria Oil — ' . $slug,
		'type'          => 'page',
		'content'       => $content,
		'page_settings' => array(),
	);
	file_put_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		$path,
		wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT )
	);
}

/**
 * Load content from JSON file if present, else build.
 *
 * @param string $slug Slug.
 * @return array
 */
function syria_oil_load_elementor_page_content( $slug ) {
	$path = trailingslashit( SYRIA_OIL_DIR ) . 'demos/elementor/pages/' . $slug . '.json';
	if ( is_readable( $path ) ) {
		$data = json_decode( (string) file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( ! empty( $data['content'] ) && is_array( $data['content'] ) ) {
			return $data['content'];
		}
	}
	return syria_oil_build_elementor_content( $slug );
}

/**
 * Apply Elementor meta to a page.
 *
 * @param int   $page_id Page ID.
 * @param array $content Elementor content tree.
 * @return bool
 */
function syria_oil_apply_elementor_to_page( $page_id, $content ) {
	$page_id = absint( $page_id );
	if ( ! $page_id || empty( $content ) ) {
		return false;
	}

	if ( function_exists( 'syria_oil_bind_elementor_categories' ) ) {
		$content = syria_oil_bind_elementor_categories( $content );
	}

	update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $content ) ) );
	update_post_meta( $page_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );

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
 * One-time: insert Fly Cham GIF Banner on the Elementor homepage after the hero slider.
 *
 * @return void
 */
function syria_oil_maybe_inject_home_gif_banner() {
	if ( get_option( 'syria_oil_home_gif_banner_v1' ) ) {
		return;
	}

	$page_id = (int) get_option( 'page_on_front' );
	if ( ! $page_id ) {
		$home = get_page_by_path( 'home' );
		$page_id = $home ? (int) $home->ID : 0;
	}
	if ( ! $page_id ) {
		update_option( 'syria_oil_home_gif_banner_v1', 'skip-no-home', false );
		return;
	}

	$raw = get_post_meta( $page_id, '_elementor_data', true );
	if ( empty( $raw ) || '[]' === $raw ) {
		update_option( 'syria_oil_home_gif_banner_v1', 'skip-no-data', false );
		return;
	}

	$data = is_string( $raw ) ? json_decode( $raw, true ) : $raw;
	if ( ! is_array( $data ) ) {
		update_option( 'syria_oil_home_gif_banner_v1', 'skip-bad-json', false );
		return;
	}

	$json = wp_json_encode( $data );
	if ( false !== strpos( (string) $json, 'syria_oil_gif_banner' ) ) {
		update_option( 'syria_oil_home_gif_banner_v1', 'already', false );
		return;
	}

	$gif_section = array(
		'id'       => substr( md5( 'gif-banner-home-v1' ), 0, 7 ),
		'elType'   => 'section',
		'settings' => array(
			'layout' => 'full_width',
			'gap'    => 'default',
		),
		'elements' => array(
			array(
				'id'       => substr( md5( 'gif-banner-home-col-v1' ), 0, 7 ),
				'elType'   => 'column',
				'settings' => array( '_column_size' => 100 ),
				'elements' => array(
					array(
						'id'         => substr( md5( 'gif-banner-home-wid-v1' ), 0, 7 ),
						'elType'     => 'widget',
						'widgetType' => 'syria_oil_gif_banner',
						'settings'   => array(
							'source'            => 'url',
							'gif_url'           => array(
								'url' => 'https://www.syria-oil.com/wp-content/uploads/image/free-limousine-service-from-and-to-UAE-airports-by-fly-cham-1.gif',
							),
							'alt'               => 'خدمة الليموزين المجانية — Fly Cham',
							'align'             => 'center',
							'full_bleed_mobile' => 'yes',
							'object_fit'        => 'contain',
							'width'             => array(
								'unit' => '%',
								'size' => 100,
							),
						),
					),
				),
			),
		),
	);

	$inserted = false;
	$out      = array();
	foreach ( $data as $section ) {
		$out[] = $section;
		if ( $inserted || empty( $section['elements'] ) || ! is_array( $section['elements'] ) ) {
			continue;
		}
		foreach ( $section['elements'] as $col ) {
			if ( empty( $col['elements'] ) || ! is_array( $col['elements'] ) ) {
				continue;
			}
			foreach ( $col['elements'] as $el ) {
				if ( ! empty( $el['widgetType'] ) && 'syria_oil_featured_news_slider' === $el['widgetType'] ) {
					$out[]     = $gif_section;
					$inserted  = true;
					break 2;
				}
			}
		}
	}

	if ( ! $inserted ) {
		// Fallback: place after first section.
		array_splice( $out, 1, 0, array( $gif_section ) );
	}

	update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( array_values( $out ) ) ) );
	update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );

	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		try {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		}
	}

	update_option( 'syria_oil_home_gif_banner_v1', '1', false );
}
add_action( 'init', 'syria_oil_maybe_inject_home_gif_banner', 30 );

/**
 * Seed all mapped pages with Elementor demos.
 *
 * @param bool $force Overwrite existing Elementor data.
 * @return array{seeded:int,skipped:int,missing:int}
 */
function syria_oil_seed_elementor_pages( $force = false ) {
	$stats = array(
		'seeded'  => 0,
		'skipped' => 0,
		'missing' => 0,
	);

	// Meta can be written without Elementor active; widgets render once Elementor + syria-oil-widgets are on.
	foreach ( syria_oil_elementor_page_map() as $slug => $file ) {
		$page = get_page_by_path( $slug );
		if ( ! $page ) {
			// Arabic titles sometimes use different paths — try get by title from pack.
			$pack = syria_oil_elementor_pack_page( $slug );
			if ( ! empty( $pack['title'] ) && function_exists( 'syria_oil_get_page_by_title' ) ) {
				$page = syria_oil_get_page_by_title( $pack['title'] );
			}
		}
		if ( ! $page && 'home' === $slug ) {
			$front = (int) get_option( 'page_on_front' );
			if ( $front ) {
				$page = get_post( $front );
			}
		}
		if ( ! $page ) {
			++$stats['missing'];
			continue;
		}

		$existing = get_post_meta( $page->ID, '_elementor_data', true );
		if ( ! $force && ! empty( $existing ) && '[]' !== $existing ) {
			++$stats['skipped'];
			continue;
		}

		// Prefer PHP builders as source of truth; refresh shipped JSON on every forced seed.
		$content = syria_oil_build_elementor_content( $slug );
		if ( empty( $content ) ) {
			$content = syria_oil_load_elementor_page_content( $file );
		}

		// Ensure JSON on disk for shipping.
		syria_oil_write_elementor_page_json( $file, $content );

		if ( syria_oil_apply_elementor_to_page( $page->ID, $content ) ) {
			++$stats['seeded'];
		}
	}

	// Sync homepage.json alias.
	$home_path = trailingslashit( SYRIA_OIL_DIR ) . 'demos/elementor/pages/home.json';
	$alias     = trailingslashit( SYRIA_OIL_DIR ) . 'demos/elementor/homepage.json';
	if ( is_readable( $home_path ) ) {
		copy( $home_path, $alias ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_copy
	}

	update_option( 'syria_oil_elementor_pages_seeded', time(), false );
	return $stats;
}

/**
 * After portal content install, seed Elementor layouts.
 *
 * @param array $counts Portal install counts.
 * @return array
 */
function syria_oil_seed_elementor_after_portal( $counts ) {
	if ( is_array( $counts ) ) {
		$el = syria_oil_seed_elementor_pages( true );
		$counts['elementor_seeded']  = $el['seeded'];
		$counts['elementor_skipped'] = $el['skipped'];
		$counts['elementor_missing'] = $el['missing'];
	}
	return $counts;
}

/**
 * Hook portal install completion via filter if available; also after OCDI.
 */
add_action(
	'ocdi/after_import',
	static function () {
		syria_oil_seed_elementor_pages( true );
	},
	30
);
