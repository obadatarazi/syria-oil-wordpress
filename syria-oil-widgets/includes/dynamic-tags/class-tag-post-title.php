<?php
/**
 * Dynamic Tag: Post Title.
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets\Dynamic_Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

/**
 * Post title tag.
 */
class Post_Title extends Tag {

	public function get_name() {
		return 'syria-oil-post-title';
	}

	public function get_title() {
		return __( 'Post Title', 'syria-oil-widgets' );
	}

	public function get_group() {
		return 'syria-oil';
	}

	public function get_categories() {
		return array( Module::TEXT_CATEGORY );
	}

	public function render() {
		echo esc_html( get_the_title() );
	}
}
