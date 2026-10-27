<?php
/**
 * Service Tiles Elementor widget.
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

class Service_Tiles extends Widget_Base {

	public function get_name() {
		return 'syria_oil_service_tiles';
	}

	public function get_title() {
		return __( 'Service Tiles', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-info-box';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_style_depends() {
		return array( 'syria-oil-portal-sections' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'syria-oil-widgets' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'Icon mark', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => '◆', 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'title', array( 'label' => __( 'Title', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Service', 'syria-oil-widgets' ), 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'text', array( 'label' => __( 'Text', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXTAREA, 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'link', array( 'label' => __( 'Link', 'syria-oil-widgets' ), 'type' => Controls_Manager::URL, 'dynamic' => array( 'active' => true ) ) );
		$this->add_control( 'items', array(
			'type' => Controls_Manager::REPEATER,
			'fields' => $rep->get_controls(),
			'default' => array(
				array( 'icon' => '1', 'title' => __( 'أخبار بارزة', 'syria-oil-widgets' ), 'text' => __( 'تحديثات عاجلة من القطاع', 'syria-oil-widgets' ) ),
				array( 'icon' => '2', 'title' => __( 'نفط وغاز عالمي', 'syria-oil-widgets' ), 'text' => __( 'الأسواق الدولية', 'syria-oil-widgets' ) ),
				array( 'icon' => '3', 'title' => __( 'نفط وغاز عربي', 'syria-oil-widgets' ), 'text' => __( 'تغطية إقليمية', 'syria-oil-widgets' ) ),
				array( 'icon' => '4', 'title' => __( 'المحروقات', 'syria-oil-widgets' ), 'text' => __( 'التوزيع والأسعار', 'syria-oil-widgets' ) ),
				array( 'icon' => '5', 'title' => __( 'قرارات الوزارة', 'syria-oil-widgets' ), 'text' => __( 'متابعة رسمية', 'syria-oil-widgets' ) ),
				array( 'icon' => '6', 'title' => __( 'شركات عاملة', 'syria-oil-widgets' ), 'text' => __( 'شركات وطنية ودولية', 'syria-oil-widgets' ) ),
			),
			'title_field' => '{{{ title }}}',
		) );
		$this->add_responsive_control( 'columns', array(
			'label' => __( 'Columns', 'syria-oil-widgets' ),
			'type' => Controls_Manager::NUMBER,
			'default' => 3, 'tablet_default' => 2, 'mobile_default' => 1,
			'selectors' => array( '{{WRAPPER}} .sow-service-tiles' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));' ),
		) );
		$this->end_controls_section();
		$this->start_controls_section( 'section_style', array( 'label' => __( 'Style', 'syria-oil-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'icon_bg', array( 'label' => __( 'Icon background', 'syria-oil-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .sow-service-tile .icon' => 'background-color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typo', 'selector' => '{{WRAPPER}} .sow-service-tile h3' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$items = ! empty( $settings['items'] ) ? $settings['items'] : array();
		if ( empty( $items ) ) {
			return;
		}

		// Map Arabic titles to production category archives.
		echo '<div class="sow-service-tiles">';
		foreach ( $items as $item ) {
			$url   = ! empty( $item['link']['url'] ) ? $item['link']['url'] : '';
			$title = isset( $item['title'] ) ? $item['title'] : '';
			$count = '';

			if ( ! $url && $title && function_exists( 'syria_oil_widgets_resolve_category' ) ) {
				$term = syria_oil_widgets_resolve_category( $title );
				if ( $term ) {
					$link = get_term_link( $term );
					if ( ! is_wp_error( $link ) ) {
						$url   = $link;
						$count = (string) $term->count;
					}
				}
			}

			$tag   = $url ? 'a' : 'div';
			$attrs = $url ? ' href="' . esc_url( $url ) . '"' : '';
			echo '<' . $tag . ' class="sow-service-tile"' . $attrs . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="icon">' . esc_html( $count ? $count : ( isset( $item['icon'] ) ? $item['icon'] : '' ) ) . '</div><div>';
			echo '<h3>' . esc_html( $title ) . '</h3>';
			if ( ! empty( $item['text'] ) ) {
				echo '<p>' . esc_html( $item['text'] ) . '</p>';
			}
			echo '</div></' . $tag . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';
	}
}
