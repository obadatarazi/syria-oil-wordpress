<?php
/**
 * Standalone WXR to JSON Converter
 *
 * Run this script from command line or browser to convert
 * the WordPress export file to demo JSON format.
 *
 * Usage:
 *   php convert-wxr-to-json.php
 *
 * @package Syria_Oil
 */

// Paths configuration
$wxr_file  = '/Users/obadaal-tarazi/Downloads/WordPress.2026-08-17.xml';
$json_file = __DIR__ . '/../demo-content/demo-from-production.json';

// How many sample posts to extract (keep it low, 5-10 posts max)
$post_limit = 8;

// ============================================================================
// Parser Functions
// ============================================================================

/**
 * Parse WXR file and extract categories
 *
 * @param string $file_path Path to the WXR file.
 * @return array Array of categories.
 */
function parse_categories( $file_path ) {
	if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
		return array();
	}

	$categories = array();
	$xml_reader = new XMLReader();
	
	if ( ! $xml_reader->open( $file_path ) ) {
		return array();
	}

	echo "Reading categories from WXR file...\n";

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
					echo "  Found category: {$name} ({$slug})\n";
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
 * @return array Array of posts.
 */
function parse_sample_posts( $file_path, $limit = 5 ) {
	if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
		return array();
	}

	$posts      = array();
	$count      = 0;
	$xml_reader = new XMLReader();
	
	if ( ! $xml_reader->open( $file_path ) ) {
		return array();
	}

	echo "\nExtracting {$limit} sample posts...\n";

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
			} else {
				$post['content'] = '';
			}

			// Excerpt
			if ( preg_match( '/<excerpt:encoded><!\[CDATA\[(.*?)\]\]><\/excerpt:encoded>/s', $item_xml, $excerpt_match ) ) {
				$post['excerpt'] = $excerpt_match[1];
			} else {
				$post['excerpt'] = '';
			}

			// Date
			if ( preg_match( '/<wp:post_date><!\[CDATA\[([^\]]+)\]\]><\/wp:post_date>/', $item_xml, $date_match ) ) {
				$post['date'] = $date_match[1];
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
				$post['categories'][] = urldecode( $match[1] );
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
				$post['tags'][] = $match[2];
			}

			$posts[] = $post;
			$count++;
			
			echo "  [{$count}] {$post['title']}\n";
		}
	}

	$xml_reader->close();
	
	return $posts;
}

// ============================================================================
// Main Execution
// ============================================================================

echo "==============================================\n";
echo "WXR to Demo JSON Converter\n";
echo "==============================================\n\n";

// Check if WXR file exists
if ( ! file_exists( $wxr_file ) ) {
	die( "ERROR: WXR file not found at: {$wxr_file}\n" );
}

echo "Source: {$wxr_file}\n";
echo "Target: {$json_file}\n\n";

// Extract categories
$categories = parse_categories( $wxr_file );
echo "\nTotal unique categories: " . count( $categories ) . "\n";

// Extract sample posts
$posts = parse_sample_posts( $wxr_file, $post_limit );
echo "\nTotal posts extracted: " . count( $posts ) . "\n";

// Prepare the demo data structure
$demo_data = array(
	'categories' => $categories,
	'posts'      => $posts,
	'meta'       => array(
		'source'       => 'syria-oil.com Production WXR Export',
		'created_at'   => gmdate( 'Y-m-d H:i:s' ),
		'post_count'   => count( $posts ),
		'cat_count'    => count( $categories ),
		'original_url' => 'https://www.syria-oil.com',
	),
);

// Save to JSON file
echo "\nConverting to JSON...\n";
$json_content = json_encode( $demo_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

if ( false === $json_content ) {
	die( "ERROR: Failed to encode data to JSON.\n" );
}

// Create directory if it doesn't exist
$json_dir = dirname( $json_file );
if ( ! is_dir( $json_dir ) ) {
	mkdir( $json_dir, 0755, true );
	echo "Created directory: {$json_dir}\n";
}

// Write to file
$result = file_put_contents( $json_file, $json_content );

if ( false === $result ) {
	die( "ERROR: Failed to write JSON file.\n" );
}

echo "\n==============================================\n";
echo "SUCCESS!\n";
echo "==============================================\n";
echo "Demo JSON file created at:\n{$json_file}\n\n";
echo "File size: " . number_format( filesize( $json_file ) ) . " bytes\n";
echo "Categories: " . count( $categories ) . "\n";
echo "Sample posts: " . count( $posts ) . "\n";
echo "\n";
