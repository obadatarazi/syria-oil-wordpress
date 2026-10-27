<?php
/**
 * Category Flow Elementor widget.
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

class Category_Flow extends Widget_Base {

	public function get_name() {
		return 'syria_oil_category_flow';
	}

	public function get_title() {
		return __( 'Category Flow', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-tags';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_style_depends() {
		return array( 'syria-oil-portal-sections' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'syria-oil-widgets' ) ) );
		$this->add_control( 'source', array(
			'label' => __( 'Source', 'syria-oil-widgets' ),
			'type' => Controls_Manager::SELECT,
			'default' => 'terms',
			'options' => array(
				'terms' => __( 'WordPress categories', 'syria-oil-widgets' ),
				'manual' => __( 'Manual list', 'syria-oil-widgets' ),
			),
		) );
		$this->add_control( 'number', array(
			'label' => __( 'Max categories', 'syria-oil-widgets' ),
			'type' => Controls_Manager::NUMBER,
			'default' => 12,
			'condition' => array( 'source' => 'terms' ),
		) );
		$this->add_control( 'show_count', array(
			'label' => __( 'Show counts', 'syria-oil-widgets' ),
			'type' => Controls_Manager::SWITCHER,
			'default' => 'yes',
			'return_value' => 'yes',
			'condition' => array( 'source' => 'terms' ),
		) );
		$rep = new Repeater();
		$rep->add_control( 'label', array( 'label' => __( 'Label', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXT, 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'count', array( 'label' => __( 'Count label', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXT, 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'link', array( 'label' => __( 'Link', 'syria-oil-widgets' ), 'type' => Controls_Manager::URL, 'dynamic' => array( 'active' => true ) ) );
		$this->add_control( 'items', array(
			'type' => Controls_Manager::REPEATER,
			'fields' => $rep->get_controls(),
			'condition' => array( 'source' => 'manual' ),
			'title_field' => '{{{ label }}}',
		) );
		$this->end_controls_section();
		$this->start_controls_section( 'section_style', array( 'label' => __( 'Style', 'syria-oil-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'chip_bg', array( 'label' => __( 'Chip background', 'syria-oil-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .sow-cat-flow a' => 'background-color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'chip_typo', 'selector' => '{{WRAPPER}} .sow-cat-flow a' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$items = array();
		if ( isset( $settings['source'] ) && 'manual' === $settings['source'] ) {
			foreach ( (array) $settings['items'] as $row ) {
				$items[] = array(
					'label' => isset( $row['label'] ) ? $row['label'] : '',
					'count' => isset( $row['count'] ) ? $row['count'] : '',
					'url' => ! empty( $row['link']['url'] ) ? $row['link']['url'] : '',
				);
			}
		} else {
			$preferred = array();
			if ( function_exists( 'syria_oil_widget_category_map' ) ) {
				$map = syria_oil_widget_category_map();
				$preferred = ! empty( $map['category_flow'] ) && is_array( $map['category_flow'] ) ? $map['category_flow'] : array();
			}
			$limit = ! empty( $settings['number'] ) ? absint( $settings['number'] ) : 16;
			$terms = get_terms( array(
				'taxonomy'   => 'category',
				'hide_empty' => true,
				'number'     => 0,
				'orderby'    => 'count',
				'order'      => 'DESC',
			) );
			if ( ! is_wp_error( $terms ) && $terms ) {
				$ordered = array();
				$by_name = array();
				foreach ( $terms as $term ) {
					$by_name[ $term->name ] = $term;
				}
				foreach ( $preferred as $name ) {
					$resolved = function_exists( 'syria_oil_widgets_resolve_category' )
						? syria_oil_widgets_resolve_category( $name )
						: ( isset( $by_name[ $name ] ) ? $by_name[ $name ] : null );
					if ( $resolved && ! isset( $ordered[ (int) $resolved->term_id ] ) ) {
						$ordered[ (int) $resolved->term_id ] = $resolved;
					}
					if ( count( $ordered ) >= $limit ) {
						break;
					}
				}
				foreach ( $terms as $term ) {
					if ( count( $ordered ) >= $limit ) {
						break;
					}
					if ( ! isset( $ordered[ (int) $term->term_id ] ) ) {
						$ordered[ (int) $term->term_id ] = $term;
					}
				}
				$show = ( isset( $settings['show_count'] ) && 'yes' === $settings['show_count'] );
				foreach ( $ordered as $term ) {
					$label = $term->name;
					if ( 'Blog' === $term->name || 'blog' === $term->name ) {
						$label = __( 'Blog', 'syria-oil-widgets' );
					}
					$items[] = array(
						'label' => $label,
						'count' => $show ? (string) $term->count : '',
						'url'   => get_term_link( $term ),
					);
				}
			}
		}
		if ( empty( $items ) ) {
			return;
		}
		echo '<div class="sow-cat-flow">';
		foreach ( $items as $item ) {
			$url = ! empty( $item['url'] ) && ! is_wp_error( $item['url'] ) ? $item['url'] : '#';
			echo '<a href="' . esc_url( $url ) . '">' . esc_html( $item['label'] );
			if ( ! empty( $item['count'] ) ) {
				echo ' <small>(' . esc_html( $item['count'] ) . ')</small>';
			}
			echo '</a>';
		}
		echo '</div>';
	}
}
