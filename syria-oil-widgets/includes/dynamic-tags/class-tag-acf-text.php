<?php
/**
 * Dynamic Tag: ACF text field (when ACF is active).
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets\Dynamic_Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

/**
 * ACF text tag.
 */
class Acf_Text extends Tag {

	public function get_name() {
		return 'syria-oil-acf-text';
	}

	public function get_title() {
		return __( 'ACF Text', 'syria-oil-widgets' );
	}

	public function get_group() {
		return 'syria-oil';
	}

	public function get_categories() {
		return array( Module::TEXT_CATEGORY );
	}

	protected function register_controls() {
		$this->add_control(
			'field',
			array(
				'label' => __( 'Field name', 'syria-oil-widgets' ),
				'type'  => Controls_Manager::TEXT,
			)
		);
	}

	public function render() {
		$field = $this->get_settings( 'field' );
		if ( ! $field || ! function_exists( 'get_field' ) ) {
			return;
		}
		$value = get_field( $field );
		if ( is_string( $value ) || is_numeric( $value ) ) {
			echo esc_html( (string) $value );
		}
	}
}
