<?php
/**
 * GIF Banner Elementor widget — animated GIF with full layout/style controls.
 *
 * Uses the original file URL (never intermediate sizes) so animation is preserved.
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Typography;
use Elementor\Utils;

/**
 * GIF Banner.
 */
class Gif_Banner extends Widget_Base {

	use Animation_Controls_Trait;

	public function get_name() {
		return 'syria_oil_gif_banner';
	}

	public function get_title() {
		return __( 'GIF Banner', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-image';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_keywords() {
		return array( 'gif', 'banner', 'ad', 'promo', 'animation', 'image', 'syria' );
	}

	public function get_style_depends() {
		return array_merge( array( 'syria-oil-gif-banner' ), $this->get_animation_style_depends() );
	}

	public function get_script_depends() {
		return $this->get_animation_script_depends();
	}

	protected function register_controls() {
		$this->register_content_controls();
		$this->register_layout_controls();
		$this->register_style_image();
		$this->register_style_caption();
		$this->register_style_box();
		$this->register_animation_controls();
	}

	/**
	 * Content tab.
	 */
	protected function register_content_controls() {
		$this->start_controls_section(
			'section_content',
			array( 'label' => __( 'GIF', 'syria-oil-widgets' ) )
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'GIF source', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'media',
				'options' => array(
					'media' => __( 'Media library', 'syria-oil-widgets' ),
					'url'   => __( 'External URL', 'syria-oil-widgets' ),
				),
			)
		);

		$this->add_control(
			'gif_media',
			array(
				'label'     => __( 'GIF file', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::MEDIA,
				'media_types' => array( 'image' ),
				'default'   => array(
					'url' => 'https://www.syria-oil.com/wp-content/uploads/image/free-limousine-service-from-and-to-UAE-airports-by-fly-cham-1.gif',
				),
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'source' => 'media' ),
				'description' => __( 'Upload a .gif. The original file is used so animation stays intact.', 'syria-oil-widgets' ),
			)
		);

		$this->add_control(
			'gif_url',
			array(
				'label'       => __( 'GIF URL', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://…/banner.gif',
				'default'     => array(
					'url' => 'https://www.syria-oil.com/wp-content/uploads/image/free-limousine-service-from-and-to-UAE-airports-by-fly-cham-1.gif',
				),
				'dynamic'     => array( 'active' => true ),
				'condition'   => array( 'source' => 'url' ),
			)
		);

		$this->add_control(
			'alt',
			array(
				'label'       => __( 'Alt text', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'خدمة الليموزين المجانية — Fly Cham', 'syria-oil-widgets' ),
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'caption',
			array(
				'label'       => __( 'Caption (optional)', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'link',
			array(
				'label'         => __( 'Link', 'syria-oil-widgets' ),
				'type'          => Controls_Manager::URL,
				'placeholder'   => 'https://…',
				'dynamic'       => array( 'active' => true ),
				'show_external' => true,
			)
		);

		$this->add_control(
			'lazy',
			array(
				'label'        => __( 'Lazy load', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'description'  => __( 'Off by default so the GIF animates immediately above the fold.', 'syria-oil-widgets' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Layout / alignment.
	 */
	protected function register_layout_controls() {
		$this->start_controls_section(
			'section_layout',
			array( 'label' => __( 'Layout', 'syria-oil-widgets' ) )
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'   => __( 'Alignment', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'left'   => array(
						'title' => __( 'Left', 'syria-oil-widgets' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'syria-oil-widgets' ),
						'icon'  => 'eicon-h-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'syria-oil-widgets' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'default'              => 'center',
				'toggle'               => false,
				'selectors_dictionary' => array(
					'left'   => 'flex-start',
					'center' => 'center',
					'right'  => 'flex-end',
				),
				'selectors'            => array(
					'{{WRAPPER}} .sow-gif' => 'align-items: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'width',
			array(
				'label'      => __( 'Width', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%', 'px', 'vw' ),
				'range'      => array(
					'%'  => array( 'min' => 10, 'max' => 100 ),
					'px' => array( 'min' => 120, 'max' => 1600 ),
					'vw' => array( 'min' => 10, 'max' => 100 ),
				),
				'default'    => array( 'unit' => '%', 'size' => 25 ),
				'selectors'  => array(
					'{{WRAPPER}} .sow-gif__frame' => 'width: {{SIZE}}{{UNIT}}; max-width: 100%;',
				),
			)
		);

		$this->add_responsive_control(
			'max_height',
			array(
				'label'      => __( 'Max height', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array( 'min' => 40, 'max' => 800 ),
					'vh' => array( 'min' => 5, 'max' => 80 ),
				),
				'selectors'  => array(
					'{{WRAPPER}} .sow-gif__img' => 'max-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'object_fit',
			array(
				'label'   => __( 'Object fit', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'contain',
				'options' => array(
					'contain'    => __( 'Contain', 'syria-oil-widgets' ),
					'cover'      => __( 'Cover', 'syria-oil-widgets' ),
					'fill'       => __( 'Fill', 'syria-oil-widgets' ),
					'none'       => __( 'None', 'syria-oil-widgets' ),
					'scale-down' => __( 'Scale down', 'syria-oil-widgets' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .sow-gif__img' => 'object-fit: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'object_position',
			array(
				'label'   => __( 'Object position', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'center center',
				'options' => array(
					'center center' => __( 'Center', 'syria-oil-widgets' ),
					'top center'    => __( 'Top', 'syria-oil-widgets' ),
					'bottom center' => __( 'Bottom', 'syria-oil-widgets' ),
					'center left'   => __( 'Left', 'syria-oil-widgets' ),
					'center right'  => __( 'Right', 'syria-oil-widgets' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .sow-gif__img' => 'object-position: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'full_bleed_mobile',
			array(
				'label'        => __( 'Full width on mobile', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_responsive_control(
			'content_padding',
			array(
				'label'      => __( 'Padding', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'rem', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .sow-gif' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style — image.
	 */
	protected function register_style_image() {
		$this->start_controls_section(
			'section_style_image',
			array(
				'label' => __( 'Image', 'syria-oil-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'img_opacity',
			array(
				'label'     => __( 'Opacity', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0.1, 'max' => 1, 'step' => 0.05 ) ),
				'default'   => array( 'size' => 1 ),
				'selectors' => array(
					'{{WRAPPER}} .sow-gif__img' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'css_filters',
				'selector' => '{{WRAPPER}} .sow-gif__img',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'img_border',
				'selector' => '{{WRAPPER}} .sow-gif__frame',
			)
		);

		$this->add_responsive_control(
			'img_radius',
			array(
				'label'      => __( 'Border radius', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .sow-gif__frame, {{WRAPPER}} .sow-gif__img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'img_shadow',
				'selector' => '{{WRAPPER}} .sow-gif__frame',
			)
		);

		$this->start_controls_tabs( 'img_hover_tabs' );

		$this->start_controls_tab( 'img_normal', array( 'label' => __( 'Normal', 'syria-oil-widgets' ) ) );
		$this->add_control(
			'img_transition',
			array(
				'label'     => __( 'Transition (ms)', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 250,
				'min'       => 0,
				'max'       => 2000,
				'selectors' => array(
					'{{WRAPPER}} .sow-gif__img, {{WRAPPER}} .sow-gif__frame' => 'transition: opacity {{VALUE}}ms ease, transform {{VALUE}}ms ease, box-shadow {{VALUE}}ms ease;',
				),
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'img_hover', array( 'label' => __( 'Hover', 'syria-oil-widgets' ) ) );
		$this->add_control(
			'img_opacity_hover',
			array(
				'label'     => __( 'Opacity', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0.1, 'max' => 1, 'step' => 0.05 ) ),
				'selectors' => array(
					'{{WRAPPER}} .sow-gif:hover .sow-gif__img' => 'opacity: {{SIZE}};',
				),
			)
		);
		$this->add_control(
			'img_scale_hover',
			array(
				'label'     => __( 'Scale', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 1, 'max' => 1.15, 'step' => 0.01 ) ),
				'selectors' => array(
					'{{WRAPPER}} .sow-gif:hover .sow-gif__img' => 'transform: scale({{SIZE}});',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'css_filters_hover',
				'selector' => '{{WRAPPER}} .sow-gif:hover .sow-gif__img',
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style — caption.
	 */
	protected function register_style_caption() {
		$this->start_controls_section(
			'section_style_caption',
			array(
				'label' => __( 'Caption', 'syria-oil-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'caption_color',
			array(
				'label'     => __( 'Color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .sow-gif__caption' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'caption_typo',
				'selector' => '{{WRAPPER}} .sow-gif__caption',
			)
		);

		$this->add_responsive_control(
			'caption_spacing',
			array(
				'label'      => __( 'Spacing', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 48 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .sow-gif__caption' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style — box / surface.
	 */
	protected function register_style_box() {
		$this->start_controls_section(
			'section_style_box',
			array(
				'label' => __( 'Box', 'syria-oil-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'box_bg',
			array(
				'label'     => __( 'Background', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .sow-gif' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'box_border',
				'selector' => '{{WRAPPER}} .sow-gif',
			)
		);

		$this->add_responsive_control(
			'box_radius',
			array(
				'label'      => __( 'Border radius', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .sow-gif' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'box_shadow',
				'selector' => '{{WRAPPER}} .sow-gif',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Resolve GIF src — always original URL (preserves animation).
	 *
	 * @param array $settings Settings.
	 * @return string
	 */
	protected function resolve_gif_src( $settings ) {
		$source = ! empty( $settings['source'] ) ? $settings['source'] : 'media';

		if ( 'url' === $source ) {
			return ! empty( $settings['gif_url']['url'] ) ? (string) $settings['gif_url']['url'] : '';
		}

		if ( ! empty( $settings['gif_media']['id'] ) ) {
			$full = wp_get_attachment_url( (int) $settings['gif_media']['id'] );
			if ( $full ) {
				return $full;
			}
		}

		return ! empty( $settings['gif_media']['url'] ) ? (string) $settings['gif_media']['url'] : '';
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$src      = $this->resolve_gif_src( $settings );

		if ( ! $src ) {
			if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="sow-gif sow-gif--empty">' . esc_html__( 'Select or paste a GIF.', 'syria-oil-widgets' ) . '</div>';
			}
			return;
		}

		$alt     = ! empty( $settings['alt'] ) ? $settings['alt'] : '';
		$caption = ! empty( $settings['caption'] ) ? $settings['caption'] : '';
		$lazy    = ! empty( $settings['lazy'] ) && 'yes' === $settings['lazy'];
		$bleed   = ! empty( $settings['full_bleed_mobile'] ) && 'yes' === $settings['full_bleed_mobile'];
		$link    = ! empty( $settings['link']['url'] ) ? $settings['link']['url'] : '';
		$target  = ! empty( $settings['link']['is_external'] ) ? '_blank' : '_self';
		$rel     = array();
		if ( ! empty( $settings['link']['is_external'] ) ) {
			$rel[] = 'noopener';
			$rel[] = 'noreferrer';
		}
		if ( ! empty( $settings['link']['nofollow'] ) ) {
			$rel[] = 'nofollow';
		}

		$classes = array( 'sow-gif' );
		if ( $bleed ) {
			$classes[] = 'sow-gif--bleed-mobile';
		}
		$anim = method_exists( $this, 'sor_animate_attrs' ) ? $this->sor_animate_attrs( $settings, 0 ) : '';

		$img = sprintf(
			'<img class="sow-gif__img" src="%1$s" alt="%2$s" decoding="async"%3$s />',
			esc_url( $src ),
			esc_attr( $alt ),
			$lazy ? ' loading="lazy"' : ' loading="eager"'
		);

		$inner = '<span class="sow-gif__frame">' . $img . '</span>';
		if ( $caption ) {
			$inner .= '<span class="sow-gif__caption">' . esc_html( $caption ) . '</span>';
		}

		echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '"' . $anim . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $link ) {
			printf(
				'<a class="sow-gif__link" href="%1$s" target="%2$s"%3$s>%4$s</a>',
				esc_url( $link ),
				esc_attr( $target ),
				$rel ? ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"' : '',
				$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts.
			);
		} else {
			echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';
	}
}
