<?php
/**
 * Page Hero Elementor widget.
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

/**
 * Page Hero.
 */
class Page_Hero extends Widget_Base {

	public function get_name() {
		return 'syria_oil_page_hero';
	}

	public function get_title() {
		return __( 'Page Hero', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-archive-title';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_style_depends() {
		return array( 'syria-oil-portal-sections' );
	}

	protected function register_controls() {

		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'syria-oil-widgets' ) ) );
		$this->add_control( 'title', array(
			'label' => __( 'Title', 'syria-oil-widgets' ),
			'type' => Controls_Manager::TEXT,
			'placeholder' => __( 'Leave empty to use page title', 'syria-oil-widgets' ),
			'dynamic' => array( 'active' => true ),
		) );
		$this->add_control( 'subtitle', array(
			'label' => __( 'Subtitle', 'syria-oil-widgets' ),
			'type' => Controls_Manager::TEXTAREA,
			'dynamic' => array( 'active' => true ),
		) );
		$this->add_control( 'background', array(
			'label' => __( 'Background image', 'syria-oil-widgets' ),
			'type' => Controls_Manager::MEDIA,
			'dynamic' => array( 'active' => true ),
		) );
		$this->add_control( 'show_breadcrumb', array(
			'label' => __( 'Show breadcrumb', 'syria-oil-widgets' ),
			'type' => Controls_Manager::SWITCHER,
			'default' => 'yes',
			'return_value' => 'yes',
		) );
		$this->end_controls_section();
		$this->start_controls_section( 'section_style', array( 'label' => __( 'Style', 'syria-oil-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'overlay_color', array(
			'label' => __( 'Overlay / background', 'syria-oil-widgets' ),
			'type' => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .sow-page-hero' => 'background: {{VALUE}};' ),
		) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typo', 'selector' => '{{WRAPPER}} .sow-page-hero h1' ) );
		$this->add_responsive_control( 'padding', array(
			'label' => __( 'Padding', 'syria-oil-widgets' ),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', 'em' ),
			'selectors' => array( '{{WRAPPER}} .sow-page-hero' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
		) );
		$this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$title = isset( $settings['title'] ) ? $settings['title'] : '';
		if ( '' === $title ) {
			$title = get_the_title();
		}
		$subtitle = isset( $settings['subtitle'] ) ? $settings['subtitle'] : '';
		$bg = ! empty( $settings['background']['url'] ) ? $settings['background']['url'] : '';
		$show_bc = ( isset( $settings['show_breadcrumb'] ) && 'yes' === $settings['show_breadcrumb'] );
		?>
		<section class="sow-page-hero">
			<?php if ( function_exists( 'syria_oil_the_syrian_emblem' ) ) : ?>
				<?php syria_oil_the_syrian_emblem( 'so-emblem-mark' ); ?>
			<?php endif; ?>
			<?php if ( $bg ) : ?>
				<div class="sow-page-hero__bg" style="background-image:url(<?php echo esc_url( $bg ); ?>)"></div>
			<?php endif; ?>
			<div class="container">
				<?php if ( $show_bc && function_exists( 'syria_oil_breadcrumb' ) ) : ?>
					<?php syria_oil_breadcrumb(); ?>
				<?php endif; ?>
				<h1><?php echo esc_html( $title ); ?></h1>
				<?php if ( $subtitle ) : ?>
					<p class="lede"><?php echo esc_html( $subtitle ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php

	}
}
