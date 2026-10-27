<?php
/**
 * Dynamic Tag: Post URL.
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets\Dynamic_Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;

/**
 * Post URL tag.
 */
class Post_Url extends Data_Tag {

	public function get_name() {
		return 'syria-oil-post-url';
	}

	public function get_title() {
		return __( 'Post URL', 'syria-oil-widgets' );
	}

	public function get_group() {
		return 'syria-oil';
	}

	public function get_categories() {
		return array( Module::URL_CATEGORY );
	}

	public function get_value( array $options = array() ) {
		$url = get_permalink();
		return $url ? $url : '';
	}
}
