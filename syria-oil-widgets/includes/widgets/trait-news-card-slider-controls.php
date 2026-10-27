<?php
/**
 * Elementor controls + helpers for responsive news-card sliders.
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

/**
 * Slider breakpoint / min-items controls shared by card widgets.
 */
trait News_Card_Slider_Controls_Trait {

	/**
	 * Register layout controls for mobile slider behaviour.
	 */
	protected function register_news_card_slider_controls() {
		$this->start_controls_section(
			'section_card_slider',
			array(
				'label' => __( 'Mobile slider', 'syria-oil-widgets' ),
			)
		);

		$this->add_control(
			'slider_enable',
			array(
				'label'        => __( 'Use slider on small screens', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'slider_breakpoint',
			array(
				'label'       => __( 'Slider below (px)', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '767',
				'options'     => array(
					'480'  => '480 — large phones',
					'576'  => '576 — phones landscape',
					'767'  => '767 — tablet portrait',
					'991'  => '991 — tablet landscape',
					'1024' => '1024 — small laptop',
					'1280' => '1280 — laptop',
				),
				'condition'   => array( 'slider_enable' => 'yes' ),
				'description' => __( 'Below this width, cards become a horizontal slider (when enough items).', 'syria-oil-widgets' ),
			)
		);

		$this->add_control(
			'slider_min_items',
			array(
				'label'       => __( 'Minimum items for slider', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 3,
				'min'         => 2,
				'max'         => 12,
				'condition'   => array( 'slider_enable' => 'yes' ),
				'description' => __( 'Example: 3 = slider from the 3rd card up. With 2 items, see next option.', 'syria-oil-widgets' ),
			)
		);

		$this->add_control(
			'slider_pair_two',
			array(
				'label'        => __( 'Two items: side-by-side grid', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'slider_enable' => 'yes' ),
				'description'  => __( 'When exactly 2 posts, show them in two columns instead of a slider.', 'syria-oil-widgets' ),
			)
		);

		$this->add_control(
			'slider_autoplay',
			array(
				'label'        => __( 'Auto-slide on mobile', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'slider_enable' => 'yes' ),
			)
		);

		$this->add_control(
			'slider_autoplay_delay',
			array(
				'label'       => __( 'Auto-slide interval (ms)', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 3000,
				'min'         => 1000,
				'max'         => 15000,
				'step'        => 500,
				'condition'   => array(
					'slider_enable'  => 'yes',
					'slider_autoplay' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Data attributes for slider JS (breakpoint + autoplay).
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 * @return string
	 */
	protected function news_card_slider_data_attrs( $settings ) {
		if ( empty( $settings['slider_enable'] ) || 'yes' !== $settings['slider_enable'] ) {
			return '';
		}

		$attrs = ' data-so-slider-bp="' . esc_attr( (string) $this->get_news_card_slider_breakpoint( $settings ) ) . '"';

		if ( ! isset( $settings['slider_autoplay'] ) || 'yes' === $settings['slider_autoplay'] ) {
			$delay = isset( $settings['slider_autoplay_delay'] ) ? absint( $settings['slider_autoplay_delay'] ) : 3000;
			$attrs  .= ' data-so-autoplay="' . esc_attr( (string) max( 1000, $delay ) ) . '"';
		}

		return $attrs;
	}

	/**
	 * Resolve layout mode from widget settings and post count.
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 * @param int                 $count    Number of cards.
	 * @return string none|pair|slider
	 */
	protected function resolve_news_card_slider_mode( $settings, $count ) {
		$count = max( 0, absint( $count ) );
		if ( $count <= 1 ) {
			return 'none';
		}

		if ( empty( $settings['slider_enable'] ) || 'yes' !== $settings['slider_enable'] ) {
			return 'none';
		}

		$min_items = isset( $settings['slider_min_items'] ) ? absint( $settings['slider_min_items'] ) : 3;
		$min_items = max( 2, $min_items );

		if ( 2 === $count ) {
			$pair = ! isset( $settings['slider_pair_two'] ) || 'yes' === $settings['slider_pair_two'];
			return $pair ? 'pair' : ( $min_items <= 2 ? 'slider' : 'none' );
		}

		return $count >= $min_items ? 'slider' : 'none';
	}

	/**
	 * Breakpoint px from settings.
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 * @return int
	 */
	protected function get_news_card_slider_breakpoint( $settings ) {
		$bp = isset( $settings['slider_breakpoint'] ) ? absint( $settings['slider_breakpoint'] ) : 767;
		return max( 360, min( 1600, $bp ) );
	}

	/**
	 * Print scoped CSS for slider / pair layouts.
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 * @param string              $scope    CSS scope selector (e.g. .elementor-element-abc).
	 */
	protected function print_news_card_slider_breakpoint_css( $settings, $scope ) {
		if ( empty( $settings['slider_enable'] ) || 'yes' !== $settings['slider_enable'] ) {
			return;
		}

		$bp  = $this->get_news_card_slider_breakpoint( $settings );
		$min = $bp + 1;
		$scope = trim( $scope );
		if ( ! $scope ) {
			return;
		}

		$tracks = implode(
			",\n",
			array(
				$scope . ' .so-news-card-slider--responsive .so-card-grid__track',
				$scope . ' .so-news-card-slider--responsive .so-news-card-slider__track.sow-cat-sec__grid',
				$scope . ' .so-news-card-slider--responsive .so-news-card-slider__track.sow-cat-sec__list',
			)
		);

		$cards = implode(
			",\n",
			array(
				$scope . ' .so-news-card-slider--responsive .so-card-grid__track .so-news-card--stack',
				$scope . ' .so-news-card-slider--responsive .so-news-card-slider__track .so-news-card--stack',
				$scope . ' .so-news-card-slider--responsive .so-news-card-slider__track .so-news-card--row',
				$scope . ' .so-news-card-slider--responsive .so-news-card-slider__track .so-news-card--row-compact',
			)
		);

		$shell = esc_html( $scope . ' .so-news-card-slider--responsive' );
		$arrows_hide = esc_html(
			$scope . ' .so-news-card-slider--responsive .so-news-card-slider__arrow,'
			. $scope . ' .so-news-card-slider--responsive .so-news-card-slider__dots'
		);
		$track_reset = esc_html(
			$scope . ' .so-news-card-slider--responsive .so-card-grid__track,'
			. $scope . ' .so-news-card-slider--responsive .so-news-card-slider__track'
		);

		$track_rules = 'display:flex;flex-direction:row;overflow-x:auto;overscroll-behavior-x:contain;scroll-snap-type:x mandatory;scroll-behavior:smooth;-webkit-overflow-scrolling:touch;scrollbar-width:none;grid-template-columns:unset;gap:.85rem;padding-block:.25rem';

		$css  = '@media (max-width:' . (int) $bp . 'px){';
		$css .= $shell . '{padding-inline:2.5rem}';
		$css .= esc_html( $tracks ) . '{' . $track_rules . '}';
		foreach ( array(
			$scope . ' .so-news-card-slider--responsive .so-card-grid__track',
			$scope . ' .so-news-card-slider--responsive .so-news-card-slider__track.sow-cat-sec__grid',
			$scope . ' .so-news-card-slider--responsive .so-news-card-slider__track.sow-cat-sec__list',
		) as $track_sel ) {
			$css .= esc_html( $track_sel ) . '::-webkit-scrollbar{display:none}';
		}
		$css .= esc_html( $scope . ' .so-news-card-slider--responsive .so-news-card-slider__arrow' ) . '{display:inline-flex}';
		$css .= esc_html( $scope . ' .so-news-card-slider--responsive .so-news-card-slider__dots' ) . '{display:flex}';
		$css .= esc_html( $cards ) . '{flex:0 0 min(88vw,340px);scroll-snap-align:center;height:auto}';
		$css .= '}';
		$css .= '@media (min-width:' . (int) $min . 'px){';
		$css .= $shell . '{padding-inline:0}';
		$css .= $arrows_hide . '{display:none!important}';
		$css .= $track_reset . '{overflow:visible;scroll-snap-type:none}';
		$css .= '}';

		printf(
			'<style id="so-slider-bp-%1$s">%2$s</style>',
			esc_attr( sanitize_key( $this->get_id() ) ),
			$css // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts.
		);

		$pair_css  = '@media (max-width:' . (int) $bp . 'px){';
		$pair_css .= esc_html( $scope ) . ' .so-card-grid--pair-mobile .so-grid,';
		$pair_css .= esc_html( $scope ) . ' .so-card-grid--pair-mobile .so-grid--news-cards{grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem}';
		$pair_css .= esc_html( $scope ) . ' .so-card-grid--pair-mobile .so-news-card__title{font-size:.88rem}';
		$pair_css .= esc_html( $scope ) . ' .so-card-grid--pair-mobile .so-news-card__excerpt{-webkit-line-clamp:2;font-size:.78rem}';
		$pair_css .= esc_html( $scope ) . ' .so-card-grid--pair-mobile .so-news-card__body{padding:.85rem .75rem 1rem;gap:.55rem}';
		$pair_css .= '}';

		printf(
			'<style id="so-slider-pair-%1$s">%2$s</style>',
			esc_attr( sanitize_key( $this->get_id() ) ),
			$pair_css // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}
}
