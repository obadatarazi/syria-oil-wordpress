<?php
/**
 * Stats Cards Elementor widget.
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

class Stats_Cards extends Widget_Base {

	use Animation_Controls_Trait;

	public function get_name() {
		return 'syria_oil_stats_cards';
	}

	public function get_title() {
		return __( 'Stats Cards', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-counter';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_style_depends() {
		return array_merge( array( 'syria-oil-portal-sections' ), $this->get_animation_style_depends() );
	}

	public function get_script_depends() {
		return array_merge( $this->get_animation_script_depends(), $this->get_counter_script_depends() );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'syria-oil-widgets' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'value', array( 'label' => __( 'Value', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => '100+', 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'label', array( 'label' => __( 'Label', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Metric', 'syria-oil-widgets' ), 'dynamic' => array( 'active' => true ) ) );
		$this->add_control( 'items', array(
			'type' => Controls_Manager::REPEATER,
			'fields' => $rep->get_controls(),
			'default' => array(
				array( 'value' => '1,482+', 'label' => __( 'أخبار بارزة', 'syria-oil-widgets' ) ),
				array( 'value' => '954+', 'label' => __( 'نفط وغاز عالمي', 'syria-oil-widgets' ) ),
				array( 'value' => '610+', 'label' => __( 'المحروقات', 'syria-oil-widgets' ) ),
				array( 'value' => '72+', 'label' => __( 'مناقصات', 'syria-oil-widgets' ) ),
			),
			'title_field' => '{{{ label }}}',
		) );
		$this->add_responsive_control( 'columns', array(
			'label' => __( 'Columns', 'syria-oil-widgets' ),
			'type' => Controls_Manager::NUMBER,
			'default' => 4, 'tablet_default' => 2, 'mobile_default' => 2,
			'selectors' => array( '{{WRAPPER}} .sow-stats' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr)); --sow-stats-cols: {{VALUE}};' ),
		) );
		$this->end_controls_section();
		$this->start_controls_section( 'section_style', array( 'label' => __( 'Style', 'syria-oil-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'value_color', array( 'label' => __( 'Value color', 'syria-oil-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .sow-stat-card .value' => 'color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'value_typo', 'selector' => '{{WRAPPER}} .sow-stat-card .value' ) );
		$this->add_responsive_control( 'gap', array( 'label' => __( 'Gap', 'syria-oil-widgets' ), 'type' => Controls_Manager::SLIDER, 'selectors' => array( '{{WRAPPER}} .sow-stats' => 'gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();

		$this->register_animation_controls( array( 'stagger' => true ) );
		$this->register_counter_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$items    = ! empty( $settings['items'] ) ? $settings['items'] : array();
		if ( empty( $items ) ) {
			return;
		}

		$counter_on = ! empty( $settings['sor_counter'] ) && 'yes' === $settings['sor_counter'];
		$duration   = ! empty( $settings['sor_counter_duration'] ) ? absint( $settings['sor_counter_duration'] ) : 1100;
		$duration   = max( 800, min( 1600, $duration ) );

		echo '<div class="sow-stats">';
		foreach ( $items as $i => $item ) {
			$raw   = isset( $item['value'] ) ? (string) $item['value'] : '';
			$label = isset( $item['label'] ) ? (string) $item['label'] : '';
			$parsed = function_exists( 'syria_oil_widgets_parse_counter_value' )
				? syria_oil_widgets_parse_counter_value( $raw )
				: null;

			echo '<div class="sow-stat-card"' . $this->sor_animate_attrs( $settings, (int) $i ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			if ( $counter_on && $parsed ) {
				$attrs = array(
					'data-so-count="' . esc_attr( (string) $parsed['target'] ) . '"',
					'data-so-prefix="' . esc_attr( $parsed['prefix'] ) . '"',
					'data-so-suffix="' . esc_attr( $parsed['suffix'] ) . '"',
					'data-so-decimals="' . esc_attr( (string) $parsed['decimals'] ) . '"',
					'data-so-duration="' . esc_attr( (string) $duration ) . '"',
					'data-so-locale="en-US"',
					'data-so-trigger="scroll"',
				);
				if ( $parsed['grouping'] ) {
					$attrs[] = 'data-so-grouping="1"';
				}
				$initial = $parsed['prefix'] . '0' . ( $parsed['decimals'] > 0 ? '.' . str_repeat( '0', $parsed['decimals'] ) : '' ) . $parsed['suffix'];
				echo '<div class="value" ' . implode( ' ', $attrs ) . '>' . esc_html( $initial ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo '<div class="value">' . esc_html( $raw ) . '</div>';
			}

			echo '<div class="label">' . esc_html( $label ) . '</div>';
			echo '</div>';
		}
		echo '</div>';
	}
}
