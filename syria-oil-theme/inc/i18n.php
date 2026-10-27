<?php
/**
 * Arabic-first UI: RTL forcing + chrome string localization.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the front-end should be Arabic + RTL.
 *
 * Arabic-first theme: true when locale/dir is Arabic, OR when the theme
 * option force_arabic_rtl is on (default), so a mis-set WP locale cannot
 * leave the whole portal in English LTR.
 *
 * @return bool
 */
function syria_oil_is_arabic_ui() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}

	if ( function_exists( 'is_rtl' ) && is_rtl() ) {
		return true;
	}

	$locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
	if ( is_string( $locale ) && 0 === strpos( $locale, 'ar' ) ) {
		return true;
	}

	if ( ! empty( $GLOBALS['TRP_LANGUAGE'] ) && is_string( $GLOBALS['TRP_LANGUAGE'] ) && 0 === strpos( $GLOBALS['TRP_LANGUAGE'], 'ar' ) ) {
		return true;
	}

	// Theme option — default ON (Arabic-first portal).
	if ( function_exists( 'syria_oil_mod_enabled' ) ) {
		return syria_oil_mod_enabled( 'force_arabic_rtl', true );
	}
	if ( function_exists( 'syria_oil_mod' ) ) {
		$v = syria_oil_mod( 'force_arabic_rtl', true );
		return (bool) $v && '0' !== (string) $v;
	}

	return true;
}

/**
 * Hardcoded Arabic map for chrome / widget / core comment strings.
 *
 * @return array<string, string>
 */
function syria_oil_ui_ar_map() {
	static $map = null;
	if ( null !== $map ) {
		return $map;
	}

	$map = array(
		'Search'            => 'بحث',
		'Search the news…'  => 'ابحث في الأخبار…',
		'Search the news'   => 'ابحث في الأخبار',
		'Search for:'       => 'بحث عن:',
		'Read the story'    => 'اقرأ التفاصيل',
		'Latest news'       => 'آخر الأخبار',
		'Latest articles'   => 'أحدث المقالات',
		'Newsroom'          => 'الأخبار',
		'All news'          => 'كل الأخبار',
		'Read more'         => 'اقرأ المزيد',
		'Menu'              => 'المزيد',
		'More'              => 'المزيد',
		'Contact us'        => 'تواصل معنا',
		'About the portal'  => 'عن الموقع',
		'Explore'           => 'استكشف',
		'Categories'        => 'التصنيفات',
		'Contact'           => 'اتصل بنا',
		'Home'              => 'الرئيسية',
		'Open menu'         => 'فتح القائمة',
		'Close menu'        => 'إغلاق القائمة',
		'Mobile navigation' => 'تنقل سريع',
		'Analysis'          => 'تحليلات',
		'Articles'          => 'المقالات',
		'All articles'      => 'كل المقالات',
		'Featured'          => 'مميز',
		'Breaking'          => 'عاجل',
		'Latest News'       => 'آخر الأخبار',
		'Language'          => 'اللغة',
		'Side Ads'          => 'إعلانات جانبية',
		'Side Ad'           => 'إعلان جانبي',
		'Advertisements'    => 'إعلانات',
		'Subscribe'         => 'اشترك',
		'Prices'            => 'الأسعار',
		'Prices updating…'  => 'جاري تحديث الأسعار…',
		'Prices updating...' => 'جاري تحديث الأسعار…',
		'Blog'              => 'مدونة',
		'Back to top'       => 'العودة للأعلى',
		'(Untitled)'        => '(بدون عنوان)',
		'Leave a comment'   => 'اترك تعليقاً',
		'Leave a Reply'     => 'اترك رداً',
		'Post Comment'      => 'أرسل التعليق',
		'Comment'           => 'التعليق',
		'Name'              => 'الاسم',
		'Email'             => 'البريد الإلكتروني',
		'Website'           => 'الموقع',
		'Your email address will not be published. Required fields are marked *' => 'لن يتم نشر بريدك الإلكتروني. الحقول المطلوبة محددة بـ *',
		'Comments are closed.' => 'التعليقات مغلقة.',
		'Recent news'       => 'آخر الأخبار',
		'Previous'          => 'السابق',
		'Next'              => 'التالي',
		'&laquo; Previous'  => '&laquo; السابق',
		'Next &raquo;'      => 'التالي &raquo;',
		'Print'             => 'طباعة',
		'Source'            => 'المصدر',
		'Related'           => 'ذات صلة',
		'Back to news'      => 'العودة للأخبار',
		'View on syria-oil.com' => 'عرض على syria-oil.com',
		'Sidebar'           => 'الشريط الجانبي',
		'Article sidebar'   => 'شريط المقال',
		'Breadcrumb'        => 'مسار التصفح',
		'Skip to content'   => 'تخطي إلى المحتوى',
		'Primary menu'      => 'القائمة الرئيسية',
		'No posts found.'   => 'لا توجد مقالات.',
		'Nothing Found'     => 'لا يوجد محتوى',
		'Pages:'            => 'الصفحات:',
	);

	return apply_filters( 'syria_oil_ui_ar_map', $map );
}

/**
 * Resolve a UI string for the current language (gettext, then Arabic map).
 *
 * @param string $text   English source string.
 * @param string $domain Text domain.
 * @return string
 */
function syria_oil_ui( $text, $domain = 'syria-oil' ) {
	$translated = translate( $text, $domain ); // phpcs:ignore WordPress.WP.I18n.LowLevelFunctions,WordPress.WP.I18n.NonSingularStringLiteralText,WordPress.WP.I18n.NonSingularStringLiteralDomain
	if ( $translated !== $text ) {
		return $translated;
	}
	if ( syria_oil_is_arabic_ui() ) {
		$map = syria_oil_ui_ar_map();
		if ( isset( $map[ $text ] ) ) {
			return $map[ $text ];
		}
	}
	return $text;
}

/**
 * gettext fallback for theme, widgets, and core chrome strings.
 *
 * @param string $translation Translated text.
 * @param string $text        Original.
 * @param string $domain      Domain.
 * @return string
 */
function syria_oil_gettext_arabic_fallback( $translation, $text, $domain ) {
	if ( ! syria_oil_is_arabic_ui() ) {
		return $translation;
	}
	if ( $translation !== $text ) {
		return $translation;
	}
	$map = syria_oil_ui_ar_map();
	if ( isset( $map[ $text ] ) ) {
		return $map[ $text ];
	}
	return $translation;
}
add_filter( 'gettext', 'syria_oil_gettext_arabic_fallback', 5, 3 );
add_filter( 'gettext_default', 'syria_oil_gettext_arabic_fallback', 5, 3 );

/**
 * Force dir="rtl" lang="ar" on the front end when Arabic UI is active.
 *
 * @param string $output language_attributes() output.
 * @return string
 */
function syria_oil_force_rtl_language_attributes( $output ) {
	if ( ! syria_oil_is_arabic_ui() ) {
		return $output;
	}

	if ( false !== strpos( $output, 'dir=' ) ) {
		$output = preg_replace( '/dir=(["\'])ltr\1/i', 'dir=$1rtl$1', $output );
	} else {
		$output .= ' dir="rtl"';
	}

	if ( false !== strpos( $output, 'lang=' ) ) {
		$output = preg_replace( '/lang=(["\'])[^"\']+\1/i', 'lang=$1ar$1', $output );
	} else {
		$output .= ' lang="ar"';
	}

	return $output;
}
add_filter( 'language_attributes', 'syria_oil_force_rtl_language_attributes', 20 );

/**
 * Ensure body gets rtl class for CSS hooks.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function syria_oil_force_rtl_body_class( $classes ) {
	if ( syria_oil_is_arabic_ui() ) {
		$classes[] = 'rtl';
		$classes[] = 'syria-oil-ar';
	}
	return $classes;
}
add_filter( 'body_class', 'syria_oil_force_rtl_body_class' );

/**
 * Soft-localize the default English "Blog" category label in Arabic UI.
 *
 * @param WP_Term|mixed $term Term.
 * @return WP_Term|mixed
 */
function syria_oil_localize_blog_term_name( $term ) {
	if ( ! syria_oil_is_arabic_ui() || ! $term || is_wp_error( $term ) || ! isset( $term->taxonomy, $term->name ) ) {
		return $term;
	}
	if ( 'category' === $term->taxonomy && 'Blog' === $term->name ) {
		$term->name = 'مدونة';
	}
	return $term;
}
add_filter( 'get_term', 'syria_oil_localize_blog_term_name' );
