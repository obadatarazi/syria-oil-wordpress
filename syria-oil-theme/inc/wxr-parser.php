<?php
/**
 * WordPress WXR (eXtended RSS) Parser
 * Extracts categories, tags, and sample posts from a WordPress export file
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parse WXR file and extract categories
 *
 * @param string $file_path Path to the WXR file.
 * @return array Array of categories with slug, name, and description.
 */
function syria_oil_parse_wxr_categories( $file_path ) {
	if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
		return array();
	}

	$categories = array();
	$xml_reader = new XMLReader();
	
	if ( ! $xml_reader->open( $file_path ) ) {
		return array();
	}

	// Read through the XML
	while ( $xml_reader->read() ) {
		// Look for item elements
		if ( $xml_reader->nodeType === XMLReader::ELEMENT && $xml_reader->name === 'item' ) {
			// Parse the item
			$item_xml = $xml_reader->readOuterXml();
			
			// Extract categories from this item
			preg_match_all(
				'/<category domain="category" nicename="([^"]+)"><!\[CDATA\[([^\]]+)\]\]><\/category>/',
				$item_xml,
				$cat_matches,
				PREG_SET_ORDER
			);

			foreach ( $cat_matches as $match ) {
				$slug = urldecode( $match[1] );
				$name = $match[2];
				
				// Store unique categories
				if ( ! isset( $categories[ $slug ] ) ) {
					$categories[ $slug ] = array(
						'slug' => $slug,
						'name' => $name,
					);
				}
			}
		}
	}

	$xml_reader->close();
	
	return array_values( $categories );
}

/**
 * Parse WXR file and extract sample posts
 *
 * @param string $file_path Path to the WXR file.
 * @param int    $limit     Maximum number of posts to extract.
 * @return array Array of posts with title, content, excerpt, date, categories.
 */
function syria_oil_parse_wxr_sample_posts( $file_path, $limit = 5 ) {
	if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
		return array();
	}

	$posts      = array();
	$count      = 0;
	$xml_reader = new XMLReader();
	
	if ( ! $xml_reader->open( $file_path ) ) {
		return array();
	}

	// Read through the XML
	while ( $xml_reader->read() && $count < $limit ) {
		// Look for item elements
		if ( $xml_reader->nodeType === XMLReader::ELEMENT && $xml_reader->name === 'item' ) {
			// Parse the item
			$item_xml = $xml_reader->readOuterXml();
			
			// Check if it's a published post
			if ( ! preg_match( '/<wp:status><!\[CDATA\[publish\]\]><\/wp:status>/', $item_xml ) ) {
				continue;
			}

			if ( ! preg_match( '/<wp:post_type><!\[CDATA\[post\]\]><\/wp:post_type>/', $item_xml ) ) {
				continue;
			}

			// Extract post data
			$post = array();

			// Title
			if ( preg_match( '/<title><!\[CDATA\[([^\]]*)\]\]><\/title>/', $item_xml, $title_match ) ) {
				$post['title'] = $title_match[1];
			}

			// Skip if no title or empty title
			if ( empty( $post['title'] ) ) {
				continue;
			}

			// Content
			if ( preg_match( '/<content:encoded><!\[CDATA\[(.*?)\]\]><\/content:encoded>/s', $item_xml, $content_match ) ) {
				$post['content'] = $content_match[1];
			}

			// Excerpt
			if ( preg_match( '/<excerpt:encoded><!\[CDATA\[(.*?)\]\]><\/excerpt:encoded>/s', $item_xml, $excerpt_match ) ) {
				$post['excerpt'] = $excerpt_match[1];
			}

			// Date
			if ( preg_match( '/<wp:post_date><!\[CDATA\[([^\]]+)\]\]><\/wp:post_date>/', $item_xml, $date_match ) ) {
				$post['date'] = $date_match[1];
			}

			// Featured image (post thumbnail)
			if ( preg_match( '/<wp:postmeta>.*?<wp:meta_key><!\[CDATA\[_thumbnail_id\]\]><\/wp:meta_key>.*?<wp:meta_value><!\[CDATA\[(\d+)\]\]><\/wp:meta_value>.*?<\/wp:postmeta>/s', $item_xml, $thumb_match ) ) {
				$post['featured_image_id'] = $thumb_match[1];
			}

			// Categories
			$post['categories'] = array();
			preg_match_all(
				'/<category domain="category" nicename="([^"]+)"><!\[CDATA\[([^\]]+)\]\]><\/category>/',
				$item_xml,
				$cat_matches,
				PREG_SET_ORDER
			);

			foreach ( $cat_matches as $match ) {
				$post['categories'][] = array(
					'slug' => urldecode( $match[1] ),
					'name' => $match[2],
				);
			}

			// Tags
			$post['tags'] = array();
			preg_match_all(
				'/<category domain="post_tag" nicename="([^"]+)"><!\[CDATA\[([^\]]+)\]\]><\/category>/',
				$item_xml,
				$tag_matches,
				PREG_SET_ORDER
			);

			foreach ( $tag_matches as $match ) {
				$post['tags'][] = array(
					'slug' => urldecode( $match[1] ),
					'name' => $match[2],
				);
			}

			$posts[] = $post;
			$count++;
		}
	}

	$xml_reader->close();
	
	return $posts;
}

/**
 * Export WXR data to JSON format for demo import
 *
 * @param string $wxr_file_path  Path to the WXR file.
 * @param string $json_file_path Path where to save the JSON file.
 * @param int    $post_limit     Maximum number of posts to extract.
 * @return bool|WP_Error True on success, WP_Error on failure.
 */
function syria_oil_convert_wxr_to_demo_json( $wxr_file_path, $json_file_path, $post_limit = 5 ) {
	// Extract categories
	$categories = syria_oil_parse_wxr_categories( $wxr_file_path );
	
	if ( empty( $categories ) ) {
		return new WP_Error( 'no_categories', __( 'No categories found in WXR file.', 'syria-oil' ) );
	}

	// Extract sample posts
	$posts = syria_oil_parse_wxr_sample_posts( $wxr_file_path, $post_limit );

	// Prepare the demo data structure
	$demo_data = array(
		'categories' => $categories,
		'posts'      => $posts,
		'meta'       => array(
			'source'     => 'WXR Export',
			'created_at' => gmdate( 'Y-m-d H:i:s' ),
			'post_count' => count( $posts ),
			'cat_count'  => count( $categories ),
		),
	);

	// Save to JSON file
	$json_content = wp_json_encode( $demo_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
	
	if ( false === $json_content ) {
		return new WP_Error( 'json_encode_failed', __( 'Failed to encode data to JSON.', 'syria-oil' ) );
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	$result = file_put_contents( $json_file_path, $json_content );

	if ( false === $result ) {
		return new WP_Error( 'file_write_failed', __( 'Failed to write JSON file.', 'syria-oil' ) );
	}

	return true;
}
