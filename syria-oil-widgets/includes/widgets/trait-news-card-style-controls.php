<?php
/**
 * Shared Elementor style controls for so-news-card.
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

/**
 * Reusable Style-tab controls targeting .so-news-card markup.
 */
trait News_Card_Style_Controls_Trait {

	/**
	 * Register card styling controls.
	 *
	 * @param array<string, mixed> $args {
	 *     @type string $wrapper        Optional CSS scope inside {{WRAPPER}} (e.g. ".so-card-grid").
	 *     @type string $grid_selectors Comma-separated selectors for grid/list gap control.
	 * }
	 */
	protected function register_news_card_style_controls( $args = array() ) {
		$scope = '';
		if ( ! empty( $args['wrapper'] ) ) {
			$scope = trim( (string) $args['wrapper'] ) . ' ';
		}

		$root     = ! empty( $args['wrapper'] ) ? '{{WRAPPER}} ' . trim( (string) $args['wrapper'] ) : '{{WRAPPER}}';
		$card     = '{{WRAPPER}} ' . $scope . '.so-news-card';
		$card_h   = $card . ':hover';
		$media    = '{{WRAPPER}} ' . $scope . '.so-news-card__media';
		$body     = '{{WRAPPER}} ' . $scope . '.so-news-card__body';
		$meta     = '{{WRAPPER}} ' . $scope . '.so-news-card__meta';
		$badge    = '{{WRAPPER}} ' . $scope . '.so-news-card__badge';
		$date     = '{{WRAPPER}} ' . $scope . '.so-news-card__date';
		$title    = '{{WRAPPER}} ' . $scope . '.so-news-card__title';
		$title_a  = '{{WRAPPER}} ' . $scope . '.so-news-card__title a';
		$excerpt  = '{{WRAPPER}} ' . $scope . '.so-news-card__excerpt';
		$grid     = ! empty( $args['grid_selectors'] )
			? (string) $args['grid_selectors']
			: '{{WRAPPER}} ' . $scope . '.so-grid, {{WRAPPER}} ' . $scope . '.so-grid--news-cards';

		$this->start_controls_section(
			'section_style_news_card',
			array(
				'label' => __( 'News cards — layout', 'syria-oil-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'news_card_grid_gap',
			array(
				'label'      => __( 'Gap between cards', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem' ),
				'range'      => array(
					'px'  => array( 'min' => 0, 'max' => 80 ),
					'rem' => array( 'min' => 0, 'max' => 5, 'step' => 0.05 ),
				),
				'selectors'  => array(
					$grid => 'gap: {{SIZE}}{{UNIT}};',
					$root => '--so-card-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'news_card_margin',
			array(
				'label'      => __( 'Card margin', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( $card => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'news_card_body_padding',
			array(
				'label'      => __( 'Body padding', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					$body => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$root => '--so-card-pad: {{TOP}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'news_card_body_gap',
			array(
				'label'      => __( 'Space inside body', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem' ),
				'range'      => array(
					'px'  => array( 'min' => 0, 'max' => 48 ),
					'rem' => array( 'min' => 0, 'max' => 3, 'step' => 0.05 ),
				),
				'selectors'  => array( $body => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'news_card_body_align',
			array(
				'label'     => __( 'Body text align', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'start'  => array(
						'title' => __( 'Start', 'syria-oil-widgets' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'syria-oil-widgets' ),
						'icon'  => 'eicon-text-align-center',
					),
					'end'    => array(
						'title' => __( 'End', 'syria-oil-widgets' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'start',
				'selectors' => array( $body => 'text-align: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_news_card_box',
			array(
				'label' => __( 'News cards — box', 'syria-oil-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( 'news_card_surface_tabs' );

		$this->start_controls_tab(
			'news_card_surface_normal',
			array( 'label' => __( 'Normal', 'syria-oil-widgets' ) )
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'news_card_bg_group',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => $card,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'news_card_body_bg_group',
				'label'    => __( 'Body background', 'syria-oil-widgets' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => $body,
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'news_card_surface_hover',
			array( 'label' => __( 'Hover', 'syria-oil-widgets' ) )
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'news_card_bg_hover_group',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => $card_h,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'news_card_body_bg_hover_group',
				'label'    => __( 'Body background', 'syria-oil-widgets' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => $card_h . ' .so-news-card__body',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'news_card_radius',
			array(
				'label'      => __( 'Border radius', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 40 ),
					'%'  => array( 'min' => 0, 'max' => 50 ),
				),
				'separator'  => 'before',
				'selectors'  => array(
					$card  => 'border-radius: {{SIZE}}{{UNIT}};',
					$root  => '--so-card-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'news_card_border',
				'selector' => $card,
			)
		);

		$this->start_controls_tabs( 'news_card_shadow_tabs' );

		$this->start_controls_tab(
			'news_card_shadow_normal',
			array( 'label' => __( 'Shadow', 'syria-oil-widgets' ) )
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'news_card_shadow',
				'selector' => $card,
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab(
			'news_card_shadow_hover',
			array( 'label' => __( 'Hover shadow', 'syria-oil-widgets' ) )
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'news_card_shadow_hover',
				'selector' => $card_h,
			)
		);
		$this->add_control(
			'news_card_border_hover',
			array(
				'label'     => __( 'Border color (hover)', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $card_h => 'border-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'news_card_lift_hover',
			array(
				'label'      => __( 'Lift on hover', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 16 ) ),
				'selectors'  => array( $card_h => 'transform: translateY(calc(-1 * {{SIZE}}{{UNIT}}));' ),
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'news_card_tokens_heading',
			array(
				'label'     => __( 'Theme tokens', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'news_card_var_depth',
			array(
				'label'       => __( 'Accent / depth color', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::COLOR,
				'description' => __( 'Affects badge default, borders, and featured accent.', 'syria-oil-widgets' ),
				'selectors'   => array( $root => '--color-depth: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'news_card_var_ink',
			array(
				'label'     => __( 'Title text token', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $root => '--so-card-ink: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'news_card_var_muted',
			array(
				'label'     => __( 'Muted text token', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $root => '--so-card-muted: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'news_card_var_border',
			array(
				'label'     => __( 'Border token', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $root => '--so-card-border: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_news_card_media',
			array(
				'label' => __( 'News cards — image', 'syria-oil-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'news_card_media_ratio',
			array(
				'label'       => __( 'Aspect ratio (W÷H)', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array( 'px' => array( 'min' => 0.8, 'max' => 2.4, 'step' => 0.05 ) ),
				'default'     => array( 'size' => 1.6, 'unit' => 'px' ),
				'description' => __( '16:10 ≈ 1.6. Applies to stack cards.', 'syria-oil-widgets' ),
				'selectors'   => array(
					'{{WRAPPER}} ' . $scope . '.so-news-card--stack .so-news-card__media' => 'aspect-ratio: {{SIZE}} / 1;',
					$root => '--so-thumb-ratio: {{SIZE}} / 1;',
				),
			)
		);

		$this->add_responsive_control(
			'news_card_media_radius',
			array(
				'label'      => __( 'Image corner radius', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 40 ),
					'%'  => array( 'min' => 0, 'max' => 50 ),
				),
				'selectors'  => array(
					$media => 'border-radius: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ' . $scope . '.so-news-card--row .so-news-card__media, {{WRAPPER}} ' . $scope . '.so-news-card--row-compact .so-news-card__media' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'news_card_media_border_color',
			array(
				'label'     => __( 'Image divider color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $scope . '.so-news-card--stack .so-news-card__media' => 'border-bottom-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'news_card_media_zoom',
			array(
				'label'      => __( 'Zoom on hover', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 1, 'max' => 1.15, 'step' => 0.01 ) ),
				'default'    => array( 'size' => 1.03, 'unit' => 'px' ),
				'selectors'  => array(
					$card_h . ' .so-news-card__media img' => 'transform: scale({{SIZE}});',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_news_card_meta',
			array(
				'label' => __( 'News cards — meta row', 'syria-oil-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'news_card_meta_gap',
			array(
				'label'      => __( 'Badge / date gap', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem' ),
				'range'      => array(
					'px'  => array( 'min' => 0, 'max' => 32 ),
					'rem' => array( 'min' => 0, 'max' => 2, 'step' => 0.05 ),
				),
				'selectors'  => array( $meta => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'news_card_meta_padding',
			array(
				'label'      => __( 'Bottom padding', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem' ),
				'range'      => array(
					'px'  => array( 'min' => 0, 'max' => 32 ),
					'rem' => array( 'min' => 0, 'max' => 2, 'step' => 0.05 ),
				),
				'selectors'  => array( $meta => 'padding-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'news_card_meta_border_color',
			array(
				'label'     => __( 'Divider color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $meta => 'border-bottom-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_news_card_badge',
			array(
				'label' => __( 'News cards — badge', 'syria-oil-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'news_card_badge_color',
			array(
				'label'     => __( 'Text color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $badge => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'news_card_badge_bg',
			array(
				'label'     => __( 'Background', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $badge => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'news_card_badge_typography',
				'selector' => $badge,
			)
		);

		$this->add_responsive_control(
			'news_card_badge_size',
			array(
				'label'      => __( 'Font size', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem', 'em' ),
				'range'      => array(
					'px'  => array( 'min' => 8, 'max' => 24 ),
					'rem' => array( 'min' => 0.5, 'max' => 1.5, 'step' => 0.05 ),
				),
				'selectors'  => array( $badge => 'font-size: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'news_card_badge_padding',
			array(
				'label'      => __( 'Padding', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( $badge => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'news_card_badge_radius',
			array(
				'label'      => __( 'Border radius', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 24 ),
					'%'  => array( 'min' => 0, 'max' => 50 ),
				),
				'selectors'  => array( $badge => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'news_card_badge_border',
				'selector' => $badge,
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_news_card_title',
			array(
				'label' => __( 'News cards — title', 'syria-oil-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs( 'news_card_title_tabs' );

		$this->start_controls_tab(
			'news_card_title_normal',
			array( 'label' => __( 'Normal', 'syria-oil-widgets' ) )
		);
		$this->add_control(
			'news_card_title_color',
			array(
				'label'     => __( 'Color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $title_a => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab(
			'news_card_title_hover_tab',
			array( 'label' => __( 'Hover', 'syria-oil-widgets' ) )
		);
		$this->add_control(
			'news_card_title_hover',
			array(
				'label'     => __( 'Color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $title_a . ':hover' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'news_card_title_typography',
				'selector'  => $title,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'news_card_title_size',
			array(
				'label'      => __( 'Font size', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem', 'em' ),
				'range'      => array(
					'px'  => array( 'min' => 12, 'max' => 40 ),
					'rem' => array( 'min' => 0.75, 'max' => 2.5, 'step' => 0.05 ),
				),
				'selectors'  => array( $title => 'font-size: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'news_card_title_line_height',
			array(
				'label'      => __( 'Line height', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'custom', 'em' ),
				'range'      => array(
					'custom' => array( 'min' => 1, 'max' => 2.2, 'step' => 0.05 ),
					'em'     => array( 'min' => 1, 'max' => 2.2, 'step' => 0.05 ),
				),
				'selectors'  => array( $title => 'line-height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'news_card_title_margin',
			array(
				'label'      => __( 'Margin', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( $title => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_news_card_date',
			array(
				'label' => __( 'News cards — date', 'syria-oil-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'news_card_date_color',
			array(
				'label'     => __( 'Color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $date => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'news_card_date_typography',
				'selector' => $date,
			)
		);

		$this->add_responsive_control(
			'news_card_date_size',
			array(
				'label'      => __( 'Font size', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem', 'em' ),
				'range'      => array(
					'px'  => array( 'min' => 10, 'max' => 24 ),
					'rem' => array( 'min' => 0.6, 'max' => 1.5, 'step' => 0.05 ),
				),
				'selectors'  => array( $date => 'font-size: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_news_card_excerpt',
			array(
				'label' => __( 'News cards — excerpt', 'syria-oil-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'news_card_excerpt_color',
			array(
				'label'     => __( 'Color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $excerpt => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'news_card_excerpt_typography',
				'selector' => $excerpt,
			)
		);

		$this->add_responsive_control(
			'news_card_excerpt_size',
			array(
				'label'      => __( 'Font size', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem', 'em' ),
				'range'      => array(
					'px'  => array( 'min' => 11, 'max' => 24 ),
					'rem' => array( 'min' => 0.65, 'max' => 1.5, 'step' => 0.05 ),
				),
				'selectors'  => array( $excerpt => 'font-size: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'news_card_excerpt_lines',
			array(
				'label'      => __( 'Max lines', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 1, 'max' => 6, 'step' => 1 ) ),
				'selectors'  => array( $excerpt => '-webkit-line-clamp: {{SIZE}};' ),
			)
		);

		$this->add_responsive_control(
			'news_card_excerpt_spacing',
			array(
				'label'      => __( 'Top spacing', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem' ),
				'range'      => array(
					'px'  => array( 'min' => 0, 'max' => 32 ),
					'rem' => array( 'min' => 0, 'max' => 2, 'step' => 0.05 ),
				),
				'selectors'  => array(
					$excerpt => 'margin-top: {{SIZE}}{{UNIT}}; padding-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'news_card_excerpt_border_color',
			array(
				'label'     => __( 'Top divider color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $excerpt => 'border-top-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}
}
