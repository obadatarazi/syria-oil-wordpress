<?php
/**
 * Import live content from https://www.syria-oil.com/ via RSS.
 *
 * Usage:
 *   wp eval-file syria-oil-theme/inc/import-live-syria-oil.php --path=local-wp
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$log = static function ( $msg ) {
	if ( class_exists( 'WP_CLI' ) ) {
		WP_CLI::log( $msg );
	} else {
		echo $msg . PHP_EOL;
	}
};

$log( 'Importing live content from https://www.syria-oil.com/ …' );

update_option( 'WPLANG', 'ar' );
update_option( 'blogname', 'أخبار النفط والغاز السوري' );
update_option( 'blogdescription', 'أخبار النفط والغاز السوري لحظة بلحظة' );
update_option( 'timezone_string', 'Asia/Damascus' );
update_option( 'date_format', 'j F Y' );

$set = static function ( $key, $value ) {
	if ( function_exists( 'syria_oil_set_mod' ) ) {
		syria_oil_set_mod( $key, $value );
	} else {
		set_theme_mod( $key, $value );
	}
};
$set( 'show_top_bar', true );
$set( 'top_bar_text', 'أخبار النفط والغاز السوري لحظة بلحظة' );
$set( 'search_placeholder', 'ابحث في الأخبار…' );
$set( 'footer_tagline', 'أخبار النفط والغاز السوري لحظة بلحظة' );
$set( 'footer_bottom_text', '© ' . gmdate( 'Y' ) . ' - أخبار النفط والغاز السوري. جميع الحقوق محفوظة.' );

/**
 * Fetch remote URL body.
 *
 * @param string $url URL.
 * @return string
 */
$fetch = static function ( $url ) {
	$response = wp_remote_get(
		$url,
		array(
			'timeout'    => 45,
			'redirection' => 5,
			'user-agent' => 'SyriaOilLocalImporter/1.0 (+local-dev)',
			'headers'    => array( 'Accept' => 'application/rss+xml, application/xml, text/xml, */*' ),
		)
	);
	if ( is_wp_error( $response ) ) {
		return '';
	}
	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		return '';
	}
	return (string) wp_remote_retrieve_body( $response );
};

/**
 * Ensure category exists; return term ID.
 *
 * @param string $name Category name.
 * @return int
 */
$ensure_cat = static function ( $name ) {
	$name = trim( wp_strip_all_tags( $name ) );
	if ( '' === $name ) {
		return 0;
	}
	$existing = get_term_by( 'name', $name, 'category' );
	if ( $existing ) {
		return (int) $existing->term_id;
	}
	$created = wp_insert_term( $name, 'category' );
	if ( is_wp_error( $created ) ) {
		if ( isset( $created->error_data['term_exists'] ) ) {
			return (int) $created->error_data['term_exists'];
		}
		return 0;
	}
	return (int) $created['term_id'];
};

/**
 * Find existing post by source URL meta.
 *
 * @param string $source Source permalink.
 * @return int
 */
$find_by_source = static function ( $source ) {
	$q = new WP_Query(
		array(
			'post_type'      => 'post',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_syria_oil_source_url',
			'meta_value'     => $source,
			'no_found_rows'  => true,
		)
	);
	return ! empty( $q->posts[0] ) ? (int) $q->posts[0] : 0;
};

/**
 * Sideload first <img> from HTML as featured image.
 *
 * @param string $html    HTML.
 * @param int    $post_id Post ID.
 * @param string $title   Title for alt.
 * @return int Attachment ID.
 */
$sideload_featured = static function ( $html, $post_id, $title ) {
	if ( ! preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m ) ) {
		return 0;
	}
	$url = esc_url_raw( html_entity_decode( $m[1], ENT_QUOTES, 'UTF-8' ) );
	if ( ! $url || false === strpos( $url, 'syria-oil.com' ) && false === strpos( $url, 'http' ) ) {
		return 0;
	}
	if ( ! function_exists( 'media_sideload_image' ) ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}
	$att_id = media_sideload_image( $url, $post_id, $title, 'id' );
	if ( is_wp_error( $att_id ) || ! $att_id ) {
		return 0;
	}
	set_post_thumbnail( $post_id, (int) $att_id );
	return (int) $att_id;
};

$feeds = array(
	'https://www.syria-oil.com/feed/',
	'https://www.syria-oil.com/feed/?paged=2',
	'https://www.syria-oil.com/feed/?paged=3',
);

$imported = 0;
$updated  = 0;
$seen     = array();

foreach ( $feeds as $feed_url ) {
	$log( 'Fetching ' . $feed_url );
	$body = $fetch( $feed_url );
	if ( '' === $body ) {
		$log( '  skipped (empty/failed)' );
		continue;
	}

	$xml = @simplexml_load_string( $body );
	if ( ! $xml || empty( $xml->channel->item ) ) {
		$log( '  skipped (invalid XML)' );
		continue;
	}

	foreach ( $xml->channel->item as $item ) {
		$title   = trim( html_entity_decode( (string) $item->title, ENT_QUOTES, 'UTF-8' ) );
		$link    = esc_url_raw( (string) $item->link );
		$guid    = (string) $item->guid;
		$pub     = (string) $item->pubDate;
		$desc    = (string) $item->description;
		$content = '';
		$ns      = $item->children( 'http://purl.org/rss/1.0/modules/content/' );
		if ( isset( $ns->encoded ) ) {
			$content = (string) $ns->encoded;
		}
		if ( '' === $content ) {
			$content = $desc;
		}

		$key = $link ? $link : $guid;
		if ( ! $title || ! $key || isset( $seen[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = true;

		$cat_ids = array();
		foreach ( $item->category as $cat ) {
			$cid = $ensure_cat( (string) $cat );
			if ( $cid ) {
				$cat_ids[] = $cid;
			}
		}

		$post_date = $pub ? gmdate( 'Y-m-d H:i:s', strtotime( $pub ) ) : current_time( 'mysql', true );
		$excerpt   = wp_strip_all_tags( $desc );
		$excerpt   = wp_trim_words( $excerpt, 40, '…' );

		// Keep a readable credit without copying site chrome.
		$body_html = wp_kses_post( $content );
		$body_html .= "\n<p><em>المصدر: <a href=\"" . esc_url( $link ) . "\" rel=\"noopener noreferrer\" target=\"_blank\">syria-oil.com</a></em></p>";

		$existing_id = $find_by_source( $key );
		$postarr     = array(
			'post_title'    => $title,
			'post_content'  => $body_html,
			'post_excerpt'  => $excerpt,
			'post_status'   => 'publish',
			'post_type'     => 'post',
			'post_author'   => 1,
			'post_date_gmt' => $post_date,
			'post_date'     => get_date_from_gmt( $post_date ),
		);

		if ( $existing_id ) {
			$postarr['ID'] = $existing_id;
			$post_id       = wp_update_post( $postarr, true );
			$updated++;
		} else {
			$post_id = wp_insert_post( $postarr, true );
			$imported++;
		}

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			$log( '  FAIL: ' . $title );
			continue;
		}

		update_post_meta( (int) $post_id, '_syria_oil_source_url', $key );
		if ( $cat_ids ) {
			wp_set_post_terms( (int) $post_id, array_unique( $cat_ids ), 'category' );
		}
		if ( ! has_post_thumbnail( (int) $post_id ) ) {
			$sideload_featured( $body_html, (int) $post_id, $title );
		}

		$log( '  OK: ' . mb_substr( $title, 0, 70 ) );
	}
}

// Prefer live “أخبار بارزة” feel on the homepage grid.
update_option( 'show_on_front', 'posts' );
update_option( 'page_on_front', 0 );

$log( sprintf( 'Done. Imported %d new, updated %d. Total unique feed items: %d', $imported, $updated, count( $seen ) ) );
$log( 'Open http://localhost:8000' );
