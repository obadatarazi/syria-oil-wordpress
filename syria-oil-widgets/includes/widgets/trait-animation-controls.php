<?php
/**
 * Shared Elementor entrance-animation + counter controls.
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

/**
 * Reusable Advanced-tab animation controls for Syria Oil widgets.
 */
trait Animation_Controls_Trait {

	/**
	 * Register entrance animation controls on the Advanced tab.
	 *
	 * @param array<string, mixed> $args {
	 *     @type bool $stagger Whether to expose stagger option (grids).
	 * }
	 */
	protected function register_animation_controls( $args = array() ) {
		$stagger = ! empty( $args['stagger'] );

		$this->start_controls_section(
			'section_sor_animation',
			array(
				'label' => __( 'Entrance Animation', 'syria-oil-widgets' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			)
		);

		$this->add_control(
			'sor_animate',
			array(
				'label'        => __( 'Enable entrance animation', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'sor_animate_style',
			array(
				'label'     => __( 'Animation style', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fade-up',
				'options'   => array(
					'fade-up'    => __( 'Fade up', 'syria-oil-widgets' ),
					'fade-in'    => __( 'Fade in', 'syria-oil-widgets' ),
					'fade-scale' => __( 'Fade scale', 'syria-oil-widgets' ),
					'fade-start' => __( 'Fade from start', 'syria-oil-widgets' ),
				),
				'condition' => array( 'sor_animate' => 'yes' ),
			)
		);

		if ( $stagger ) {
			$this->add_control(
				'sor_stagger',
				array(
					'label'        => __( 'Stagger items', 'syria-oil-widgets' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => 'yes',
					'return_value' => 'yes',
					'condition'    => array( 'sor_animate' => 'yes' ),
				)
			);

			$this->add_control(
				'sor_stagger_ms',
				array(
					'label'     => __( 'Stagger delay (ms)', 'syria-oil-widgets' ),
					'type'      => Controls_Manager::NUMBER,
					'default'   => 80,
					'min'       => 40,
					'max'       => 120,
					'step'      => 10,
					'condition' => array(
						'sor_animate' => 'yes',
						'sor_stagger' => 'yes',
					),
				)
			);
		}

		$this->end_controls_section();
	}

	/**
	 * Register number-counter controls (Stats / Price Ticker).
	 */
	protected function register_counter_controls() {
		$this->start_controls_section(
			'section_sor_counter',
			array(
				'label' => __( 'Number Counter', 'syria-oil-widgets' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			)
		);

		$this->add_control(
			'sor_counter',
			array(
				'label'        => __( 'Enable counter animation', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => __( 'Respects prefers-reduced-motion regardless of this setting.', 'syria-oil-widgets' ),
			)
		);

		$this->add_control(
			'sor_counter_duration',
			array(
				'label'     => __( 'Duration (ms)', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 1100,
				'min'       => 800,
				'max'       => 1600,
				'step'      => 50,
				'condition' => array( 'sor_counter' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * HTML attribute string for entrance animation on an element.
	 *
	 * @param array<string, mixed> $settings Widget settings.
	 * @param int                  $index    Item index for stagger.
	 * @return string
	 */
	protected function sor_animate_attrs( $settings, $index = 0 ) {
		if ( empty( $settings['sor_animate'] ) || 'yes' !== $settings['sor_animate'] ) {
			return '';
		}

		$style = ! empty( $settings['sor_animate_style'] ) ? $settings['sor_animate_style'] : 'fade-up';
		$attrs = ' data-sor-animate="' . esc_attr( $style ) . '"';

		if ( ! empty( $settings['sor_stagger'] ) && 'yes' === $settings['sor_stagger'] ) {
			$step = ! empty( $settings['sor_stagger_ms'] ) ? absint( $settings['sor_stagger_ms'] ) : 70;
			$step = max( 40, min( 120, $step ) );
			$attrs .= ' data-sor-delay="' . esc_attr( (string) ( (int) $index * $step ) ) . '"';
		}

		return $attrs;
	}

	/**
	 * Script handles needed when entrance animation is on (always declare; cheap if unused).
	 *
	 * @return string[]
	 */
	protected function get_animation_script_depends() {
		return array( 'syria-oil-scroll-reveal' );
	}

	/**
	 * Style handles for entrance animations.
	 *
	 * @return string[]
	 */
	protected function get_animation_style_depends() {
		return array( 'syria-oil-animations' );
	}

	/**
	 * Script handles for number counters.
	 *
	 * @return string[]
	 */
	protected function get_counter_script_depends() {
		return array( 'syria-oil-number-counter' );
	}
}
