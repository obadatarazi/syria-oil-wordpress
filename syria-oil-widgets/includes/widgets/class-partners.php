<?php
/**
 * Partners Logos widget.
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Utils;

class Partners extends Widget_Base {

	public function get_name() {
		return 'syria_oil_partners';
	}

	public function get_title() {
		return __( 'Partners Logos', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-carousel';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_style_depends() {
		return array( 'syria-oil-portal-sections' );
	}

	public function get_script_depends() {
		return array( 'syria-oil-category-rail-slider' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'syria-oil-widgets' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'logo', array( 'label' => __( 'Logo', 'syria-oil-widgets' ), 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => Utils::get_placeholder_image_src() ), 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'name', array( 'label' => __( 'Name', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXT, 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'link', array( 'label' => __( 'Link', 'syria-oil-widgets' ), 'type' => Controls_Manager::URL, 'dynamic' => array( 'active' => true ) ) );
		$this->add_control( 'items', array(
			'type' => Controls_Manager::REPEATER,
			'fields' => $rep->get_controls(),
			'default' => array(
				array( 'name' => 'Partner 1' ),
				array( 'name' => 'Partner 2' ),
				array( 'name' => 'Partner 3' ),
				array( 'name' => 'Partner 4' ),
			),
			'title_field' => '{{{ name }}}',
		) );
		$this->end_controls_section();
		$this->start_controls_section( 'section_style', array( 'label' => __( 'Style', 'syria-oil-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'logo_height', array( 'label' => __( 'Logo height', 'syria-oil-widgets' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 20, 'max' => 80 ) ), 'selectors' => array( '{{WRAPPER}} .sow-partners img' => 'max-height: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'chip_bg', array( 'label' => __( 'Chip background', 'syria-oil-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .sow-partners a, {{WRAPPER}} .sow-partners span' => 'background-color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$items = ! empty( $s['items'] ) ? $s['items'] : array();
		if ( empty( $items ) ) {
			return;
		}
		echo '<div class="sow-partners so-scrollbar-brand" data-so-autoplay="3000" data-so-slider-bp="767">';
		foreach ( $items as $item ) {
			$url = ! empty( $item['link']['url'] ) ? $item['link']['url'] : '';
			$img = ! empty( $item['logo']['url'] ) ? $item['logo']['url'] : '';
			$name = isset( $item['name'] ) ? $item['name'] : '';
			$inner = $img
				? '<img src="' . esc_url( $img ) . '" alt="' . esc_attr( $name ) . '" loading="lazy" decoding="async" />'
				: esc_html( $name );
			if ( $url ) {
				echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . $inner . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo '<span>' . $inner . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
		echo '</div>';
	}
}
