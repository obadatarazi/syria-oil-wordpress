<?php
/**
 * Ad / Promo Banner widget.
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Utils;

class Ad_Banner extends Widget_Base {

	public function get_name() {
		return 'syria_oil_ad_banner';
	}

	public function get_title() {
		return __( 'Ad Banner', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-banner';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_style_depends() {
		return array( 'syria-oil-portal-sections' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'syria-oil-widgets' ) ) );
		$this->add_control( 'layout', array(
			'label' => __( 'Layout', 'syria-oil-widgets' ),
			'type' => Controls_Manager::SELECT,
			'default' => 'single',
			'options' => array( 'single' => __( 'Single', 'syria-oil-widgets' ), 'grid' => __( 'Grid', 'syria-oil-widgets' ) ),
		) );
		$rep = new Repeater();
		$rep->add_control( 'image', array( 'label' => __( 'Image', 'syria-oil-widgets' ), 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => Utils::get_placeholder_image_src() ), 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'title', array( 'label' => __( 'Title', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXT, 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'text', array( 'label' => __( 'Text', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXTAREA, 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'link', array( 'label' => __( 'Link', 'syria-oil-widgets' ), 'type' => Controls_Manager::URL, 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'tone', array(
			'label' => __( 'Tone', 'syria-oil-widgets' ),
			'type' => Controls_Manager::SELECT,
			'default' => 'navy',
			'options' => array( 'navy' => 'Navy', 'green' => 'Green', 'slate' => 'Slate' ),
		) );
		$this->add_control( 'items', array(
			'type' => Controls_Manager::REPEATER,
			'fields' => $rep->get_controls(),
			'default' => array(
				array( 'title' => __( 'Business portal', 'syria-oil-widgets' ), 'text' => __( 'Services for partners and investors', 'syria-oil-widgets' ), 'tone' => 'navy' ),
			),
			'title_field' => '{{{ title }}}',
		) );
		$this->end_controls_section();
		$this->start_controls_section( 'section_style', array( 'label' => __( 'Style', 'syria-oil-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'min_height', array( 'label' => __( 'Min height', 'syria-oil-widgets' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 80, 'max' => 400 ) ), 'selectors' => array( '{{WRAPPER}} .sow-ad-banner' => 'min-height: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typo', 'selector' => '{{WRAPPER}} .sow-ad-banner h3' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$items = ! empty( $s['items'] ) ? $s['items'] : array();
		if ( empty( $items ) ) {
			return;
		}
		$wrap = ( isset( $s['layout'] ) && 'grid' === $s['layout'] ) ? 'sow-ad-grid' : 'sow-ad-single';
		echo '<div class="' . esc_attr( $wrap ) . '">';
		foreach ( $items as $item ) {
			$url = ! empty( $item['link']['url'] ) ? $item['link']['url'] : '#';
			$img = ! empty( $item['image']['url'] ) ? $item['image']['url'] : '';
			$tone = ! empty( $item['tone'] ) ? $item['tone'] : 'navy';
			$bg = array( 'navy' => '#0f2744', 'green' => '#007a3d', 'slate' => '#334155' );
			$color = isset( $bg[ $tone ] ) ? $bg[ $tone ] : $bg['navy'];
			echo '<a class="sow-ad-banner" href="' . esc_url( $url ) . '" style="background:' . esc_attr( $color ) . '">';
			if ( $img ) {
				echo '<img src="' . esc_url( $img ) . '" alt="" loading="lazy" decoding="async" />';
			}
			echo '<div class="sow-ad-banner__overlay">';
			if ( ! empty( $item['title'] ) ) {
				echo '<h3>' . esc_html( $item['title'] ) . '</h3>';
			}
			if ( ! empty( $item['text'] ) ) {
				echo '<p>' . esc_html( $item['text'] ) . '</p>';
			}
			echo '</div></a>';
		}
		echo '</div>';
	}
}
