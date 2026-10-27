<?php
/**
 * Project Cards Elementor widget.
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

class Project_Cards extends Widget_Base {

	use Animation_Controls_Trait;

	public function get_name() {
		return 'syria_oil_project_cards';
	}

	public function get_title() {
		return __( 'Project Cards', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-posts-masonry';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_style_depends() {
		return array_merge( array( 'syria-oil-portal-sections' ), $this->get_animation_style_depends() );
	}

	public function get_script_depends() {
		return $this->get_animation_script_depends();
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'syria-oil-widgets' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'image', array( 'label' => __( 'Image', 'syria-oil-widgets' ), 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => Utils::get_placeholder_image_src() ), 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'title', array( 'label' => __( 'Title', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Project', 'syria-oil-widgets' ), 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'meta', array( 'label' => __( 'Meta', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXT, 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'text', array( 'label' => __( 'Excerpt', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXTAREA, 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'progress', array( 'label' => __( 'Progress %', 'syria-oil-widgets' ), 'type' => Controls_Manager::NUMBER, 'min' => 0, 'max' => 100, 'default' => 60 ) );
		$rep->add_control( 'link', array( 'label' => __( 'Link', 'syria-oil-widgets' ), 'type' => Controls_Manager::URL, 'dynamic' => array( 'active' => true ) ) );
		$this->add_control( 'items', array(
			'type' => Controls_Manager::REPEATER,
			'fields' => $rep->get_controls(),
			'default' => array(
				array( 'title' => __( 'تأهيل الحقول', 'syria-oil-widgets' ), 'meta' => __( 'الإنتاج', 'syria-oil-widgets' ), 'progress' => 72 ),
				array( 'title' => __( 'تطوير المصفاة', 'syria-oil-widgets' ), 'meta' => __( 'التكرير', 'syria-oil-widgets' ), 'progress' => 45 ),
				array( 'title' => __( 'سلامة خطوط الأنابيب', 'syria-oil-widgets' ), 'meta' => __( 'النقل', 'syria-oil-widgets' ), 'progress' => 88 ),
			),
			'title_field' => '{{{ title }}}',
		) );
		$this->add_responsive_control( 'columns', array(
			'label' => __( 'Columns', 'syria-oil-widgets' ),
			'type' => Controls_Manager::NUMBER,
			'default' => 3, 'tablet_default' => 2, 'mobile_default' => 1,
			'selectors' => array( '{{WRAPPER}} .sow-projects' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));' ),
		) );
		$this->end_controls_section();
		$this->start_controls_section( 'section_style', array( 'label' => __( 'Style', 'syria-oil-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'progress_color', array( 'label' => __( 'Progress color', 'syria-oil-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .sow-progress > span' => 'background-color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typo', 'selector' => '{{WRAPPER}} .sow-project-card h3' ) );
		$this->end_controls_section();

		$this->register_animation_controls( array( 'stagger' => true ) );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$items = ! empty( $s['items'] ) ? $s['items'] : array();
		if ( empty( $items ) ) {
			return;
		}
		echo '<div class="sow-projects">';
		foreach ( $items as $i => $item ) {
			$url = ! empty( $item['link']['url'] ) ? $item['link']['url'] : '#';
			$img = ! empty( $item['image']['url'] ) ? $item['image']['url'] : '';
			$pct = isset( $item['progress'] ) ? max( 0, min( 100, (int) $item['progress'] ) ) : 0;
			echo '<a class="sow-project-card" href="' . esc_url( $url ) . '"' . $this->sor_animate_attrs( $s, (int) $i ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			if ( $img ) {
				echo '<img src="' . esc_url( $img ) . '" alt="' . esc_attr( isset( $item['title'] ) ? $item['title'] : '' ) . '" loading="lazy" decoding="async" />';
			}
			echo '<div class="sow-project-card__body">';
			if ( ! empty( $item['meta'] ) ) {
				echo '<div class="meta">' . esc_html( $item['meta'] ) . '</div>';
			}
			echo '<h3>' . esc_html( ! empty( $item['title'] ) ? $item['title'] : __( '(Untitled)', 'syria-oil-widgets' ) ) . '</h3>';
			if ( ! empty( $item['text'] ) ) {
				echo '<p>' . esc_html( $item['text'] ) . '</p>';
			}
			echo '<div class="sow-progress" role="progressbar" aria-valuenow="' . esc_attr( (string) $pct ) . '" aria-valuemin="0" aria-valuemax="100"><span style="width:' . esc_attr( (string) $pct ) . '%"></span></div>';
			echo '</div></a>';
		}
		echo '</div>';
	}
}
