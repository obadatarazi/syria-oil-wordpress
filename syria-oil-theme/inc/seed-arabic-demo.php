<?php
/**
 * Seed a full Arabic portal demo (pages, posts, menus, customizer).
 *
 * Run: wp eval-file syria-oil-theme/inc/seed-arabic-demo.php --path=local-wp
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_CLI' ) && php_sapi_name() !== 'cli' ) {
	return;
}

$log = static function ( $msg ) {
	if ( class_exists( 'WP_CLI' ) ) {
		WP_CLI::log( $msg );
	} else {
		echo $msg . PHP_EOL;
	}
};

$log( 'Seeding Arabic Syria Oil demo…' );

// Locale + identity.
update_option( 'WPLANG', 'ar' );
update_option( 'blogname', 'أخبار النفط والغاز السوري' );
update_option( 'blogdescription', 'أخبار النفط والغاز في سوريا والعالم' );
update_option( 'timezone_string', 'Asia/Damascus' );
update_option( 'date_format', 'j F Y' );
update_option( 'start_of_week', 6 );
update_option( 'permalink_structure', '/%postname%/' );

// Theme options chrome (Arabic).
$set_mod = static function ( $key, $value ) {
	if ( function_exists( 'syria_oil_set_mod' ) ) {
		syria_oil_set_mod( $key, $value );
	} else {
		set_theme_mod( $key, $value );
	}
};
$set_mod( 'show_top_bar', true );
$set_mod( 'show_top_bar_date', true );
$set_mod( 'top_bar_text', 'أخبار النفط والغاز في سوريا والعالم' );
$set_mod( 'top_bar_link_label', 'الأقسام' );
$set_mod( 'search_placeholder', 'ابحث في الأخبار…' );
$set_mod( 'footer_tagline', 'أخبار النفط والغاز في سوريا والعالم.' );
$set_mod( 'footer_explore_title', 'الموقع' );
$set_mod( 'footer_cats_title', 'التصنيفات' );
$set_mod( 'footer_contact_title', 'اتصل بنا' );
$set_mod( 'footer_bottom_text', 'جميع الحقوق محفوظة.' );
$set_mod( 'contact_email', 'info@syria-oil.com' );

/**
 * Create or update a page by slug.
 *
 * @param string $slug    Slug.
 * @param string $title   Title.
 * @param string $content HTML content.
 * @return int
 */
$upsert_page = static function ( $slug, $title, $content ) {
	$existing = get_page_by_path( $slug );
	$args     = array(
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => $content,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_author'  => 1,
	);
	if ( $existing ) {
		$args['ID'] = $existing->ID;
		return (int) wp_update_post( $args );
	}
	return (int) wp_insert_post( $args );
};

$page_body = static function ( $lead, $extra = '' ) {
	return '<p>' . esc_html( $lead ) . '</p>' . $extra;
};

$pages = array(
	'home'         => array( 'الرئيسية', 'الصفحة الرئيسية لبوابة أخبار النفط والغاز السوري. استخدم Elementor لإضافة شريط الأخبار المميزة وشبكة المقالات.' ),
	'news'         => array( 'الأخبار', 'مركز الأخبار: آخر التغطيات الرسمية والقطاعية حول النفط والغاز في سوريا.' ),
	'articles'     => array( 'المقالات', 'مدونة وتقارير تحليلية حول الاستكشاف والإنتاج والتكرير والأسواق.' ),
	'projects'     => array( 'المشاريع', 'متابعة المشاريع الجارية: الحقول، الأنابيب، الشراكات، وفرص الاستثمار.' ),
	'services'     => array( 'الأقسام', 'بوابة الأقسام والخدمات الإلكترونية ومسارات المعلومات المتخصصة.' ),
	'media'        => array( 'الإعلام', 'المركز الإعلامي: بيانات، صور، وتغطيات صحفية من القطاع.' ),
	'tenders'      => array( 'المناقصات', 'المناقصات والمشتريات المعلنة ذات الصلة بقطاع النفط والغاز.' ),
	'about'        => array( 'عن الموقع', 'أخبار النفط والغاز السوري منصة متخصصة تقدّم تغطية مهنية لحظة بلحظة.' ),
	'contact'      => array( 'اتصل بنا', 'للتواصل مع فريق التحرير أو للاستفسارات العامة، راسلنا عبر البريد الإلكتروني.' ),
	'faq'          => array( 'الأسئلة الشائعة', '<h2>ما هو موقع أخبار النفط والغاز السوري؟</h2><p>منصة إخبارية متخصصة بتغطيات القطاع.</p><h2>هل توجد نسخة إنجليزية؟</h2><p>يمكن إضافة لغة ثانية لاحقاً عبر إعدادات ووردبريس.</p><h2>كيف أتابع المناقصات؟</h2><p>راجع صفحة المناقصات من القائمة الرئيسية.</p>' ),
	'careers'      => array( 'الوظائف', 'الوظائف والتوظيف في القطاع — صفحة تجريبية للمحتوى المحلي.' ),
	'leadership'   => array( 'القيادة', 'تعريف بقيادات القطاع والمسؤولين المعنيين بالتغطيات الإخبارية.' ),
	'departments'  => array( 'الإدارات', 'هيكل الإدارات والمسارات التنظيمية ذات الصلة بالمحتوى المنشور.' ),
	'downloads'    => array( 'مركز التحميل', 'ملفات وتقارير قابلة للتحميل — محتوى تجريبي.' ),
	'statistics'   => array( 'الإحصاءات', 'الإحصاءات والبيانات المفتوحة المتعلقة بالقطاع.' ),
	'exploration'  => array( 'الاستكشاف', 'الاستكشاف والحقول: تغطيات فنية وميدانية.' ),
	'investments'  => array( 'الاستثمار', 'الاستثمار وفرص الشراكة في قطاع النفط والغاز السوري.' ),
);

$page_ids = array();
foreach ( $pages as $slug => $meta ) {
	$title   = $meta[0];
	$content = ( false !== strpos( $meta[1], '<h2>' ) ) ? $meta[1] : $page_body( $meta[1] );
	$page_ids[ $slug ] = $upsert_page( $slug, $title, $content );
	$log( "Page: {$title} (#{$page_ids[ $slug ]})" );
}

$set_mod( 'top_bar_link_url', get_permalink( $page_ids['services'] ) );
$set_mod( 'contact_page_url', get_permalink( $page_ids['contact'] ) );
$set_mod( 'about_page_url', get_permalink( $page_ids['about'] ) );

// Reading settings: posts on front so front-page.php hero+grid works;
// keep "الأخبار" as posts index when switching later.
update_option( 'show_on_front', 'posts' );
update_option( 'page_on_front', 0 );
update_option( 'page_for_posts', (int) $page_ids['news'] );

// Categories.
$cats = array(
	'featured'   => 'أخبار بارزة',
	'upstream'   => 'نفط وغاز',
	'ministry'   => 'وزارة النفط',
	'fuels'      => 'المحروقات',
	'companies'  => 'شركات عاملة',
	'refining'   => 'صناعة التكرير',
	'tenders'    => 'مناقصات',
	'regional'   => 'نفط وغاز عربي',
	'global'     => 'نفط وغاز عالمي',
);
$cat_ids = array();
foreach ( $cats as $slug => $name ) {
	$term = term_exists( $slug, 'category' );
	if ( ! $term ) {
		$term = wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
	}
	$cat_ids[ $slug ] = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
}

// Remove thin English starter posts (keep if already Arabic titles).
$old = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => 'any',
		'posts_per_page' => 50,
		'fields'         => 'ids',
	)
);
foreach ( $old as $pid ) {
	$title = get_the_title( $pid );
	if ( preg_match( '/[A-Za-z]{4,}/', $title ) && ! preg_match( '/[\x{0600}-\x{06FF}]/u', $title ) ) {
		wp_delete_post( $pid, true );
	}
}

$articles = array(
	array(
		'قبلاوي: مذكرتا التفاهم خطوة جديدة نحو تعزيز التكامل الإقليمي في قطاع الطاقة',
		'قال الرئيس التنفيذي للشركة السورية للبترول المهندس يوسف قبلاوي إن إحياء مشروع خط الحديثة – بانياس يمثل خطوة عملية لتعزيز التكامل الإقليمي في قطاع الطاقة.',
		array( 'featured', 'ministry', 'upstream' ),
	),
	array(
		'سوريا توقّع مذكرتي تفاهم لإحياء مشروع خط الحديثة – بانياس',
		'تم التوقيع على مذكرتي تفاهم تهدفان إلى إحياء مشروع خط الحديثة – بانياس ضمن إطار التعاون الإقليمي في نقل الطاقة.',
		array( 'featured', 'upstream' ),
	),
	array(
		'قبلاوي خلال منتدى الأعمال السوري-الأمريكي: قطاع النفط والغاز يزخر بفرص استثمارية واعدة',
		'أكد قبلاوي أن قطاع النفط والغاز في سوريا يزخر بفرص استثمارية واعدة، داعياً إلى شراكات طويلة الأمد تقوم على الموثوقية والابتكار.',
		array( 'featured', 'companies' ),
	),
	array(
		'النفط السوري يستعيد زخم الاستثمار.. غولف ساندز تعود بقوة إلى حقل دجلة',
		'عادت شركة غولف ساندز إلى الواجهة مع خطط لإعادة تفعيل العمليات في حقل دجلة، في مؤشر على استعادة زخم الاستثمار.',
		array( 'companies', 'upstream' ),
	),
	array(
		'جناح شركة التقانات الألمانية المتقدمة (AGT) في اليوم الثاني لمعرض سيربترو 2026',
		'استعرض جناح AGT حلول التقانات المتقدمة خلال اليوم الثاني من معرض سيربترو 2026 بحضور مهني واسع.',
		array( 'companies', 'global' ),
	),
	array(
		'بشعار «موثوقية وابتكار وتميز» .. «بلو ستون» الأمريكية تشارك في سيربترو',
		'شاركت شركة بلو ستون الأمريكية في المعرض بشعار يركز على الموثوقية والابتكار والتميز التشغيلي.',
		array( 'companies', 'global' ),
	),
	array(
		'أسعار المحروقات واستقرار الأسواق المحلية هذا الأسبوع',
		'رصد موجز لحركة أسعار المحروقات ومؤشرات الاستقرار في الأسواق المحلية خلال الأسبوع الجاري.',
		array( 'fuels', 'regional' ),
	),
	array(
		'إعلان مناقصة توريد معدات سلامة لحقول الإنتاج',
		'أُعلن عن مناقصة لتوريد معدات سلامة ميدانية لصالح مواقع الإنتاج، مع تحديد مواعيد الاستلام الفنية.',
		array( 'tenders', 'ministry' ),
	),
);

foreach ( $articles as $i => $row ) {
	list( $title, $content, $slugs ) = $row;
	$terms = array();
	foreach ( $slugs as $s ) {
		if ( isset( $cat_ids[ $s ] ) ) {
			$terms[] = $cat_ids[ $s ];
		}
	}
	$id = wp_insert_post(
		array(
			'post_title'   => $title,
			'post_content' => '<p>' . esc_html( $content ) . '</p><p>هذه مادة تجريبية للبيئة المحلية وتعكس أسلوب التغطية في بوابة أخبار النفط والغاز السوري.</p>',
			'post_excerpt' => wp_trim_words( $content, 24, '…' ),
			'post_status'  => 'publish',
			'post_type'    => 'post',
			'post_author'  => 1,
			'post_date'    => gmdate( 'Y-m-d H:i:s', time() - ( $i * DAY_IN_SECONDS ) ),
		)
	);
	if ( $id && ! is_wp_error( $id ) && $terms ) {
		wp_set_post_terms( $id, $terms, 'category' );
	}
	$log( "Post: {$title}" );
}

/**
 * Ensure a nav menu exists by name and return term_id.
 *
 * @param string $name Menu name.
 * @return int
 */
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
	return (int) wp_create_nav_menu( $name );
};

$add_page_item = static function ( $menu_id, $page_id, $title = '' ) {
	$args = array(
		'menu-item-object-id' => $page_id,
		'menu-item-object'    => 'page',
		'menu-item-type'      => 'post_type',
		'menu-item-status'    => 'publish',
	);
	if ( $title ) {
		$args['menu-item-title'] = $title;
	}
	wp_update_nav_menu_item( $menu_id, 0, $args );
};

$primary_id = $ensure_menu( 'القائمة الرئيسية' );
$footer_id  = $ensure_menu( 'قائمة التذييل' );
$mobile_id  = $ensure_menu( 'شريط الجوال السفلي' );

// Primary matches portal nav.
$primary_map = array( 'home', 'news', 'articles', 'projects', 'services', 'media', 'tenders', 'about', 'contact' );
foreach ( $primary_map as $slug ) {
	$add_page_item( $primary_id, $page_ids[ $slug ] );
}

$footer_map = array( 'about', 'news', 'articles', 'contact' );
foreach ( $footer_map as $slug ) {
	$add_page_item( $footer_id, $page_ids[ $slug ] );
}

$mobile_map = array( 'home', 'news', 'projects', 'services' );
foreach ( $mobile_map as $slug ) {
	$add_page_item( $mobile_id, $page_ids[ $slug ] );
}

set_theme_mod(
	'nav_menu_locations',
	array(
		'primary'       => $primary_id,
		'footer'        => $footer_id,
		'mobile_bottom' => $mobile_id,
	)
);

flush_rewrite_rules( false );

$log( 'Arabic demo seed complete.' );
$log( 'Open http://localhost:8000 — locale=ar, RTL, full portal pages.' );
