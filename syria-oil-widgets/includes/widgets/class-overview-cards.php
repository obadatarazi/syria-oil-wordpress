<?php
/**
 * Overview Cards Elementor widget.
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
 * Overview Cards.
 */
class Overview_Cards extends Widget_Base {

	use Animation_Controls_Trait;

	public function get_name() {
		return 'syria_oil_overview_cards';
	}

	public function get_title() {
		return __( 'Overview Cards', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
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
		$rep->add_control( 'icon', array( 'label' => __( 'Icon / number', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => '01', 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'title', array( 'label' => __( 'Title', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Feature', 'syria-oil-widgets' ), 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'text', array( 'label' => __( 'Text', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXTAREA, 'dynamic' => array( 'active' => true ) ) );
		$rep->add_control( 'link', array( 'label' => __( 'Link', 'syria-oil-widgets' ), 'type' => Controls_Manager::URL, 'dynamic' => array( 'active' => true ) ) );
		$this->add_control( 'items', array(
			'label' => __( 'Cards', 'syria-oil-widgets' ),
			'type' => Controls_Manager::REPEATER,
			'fields' => $rep->get_controls(),
			'default' => array(
				array( 'icon' => '1', 'title' => __( 'تغطية متخصصة', 'syria-oil-widgets' ), 'text' => __( 'نفط وغاز وتكرير ومشاريع الطاقة.', 'syria-oil-widgets' ) ),
				array( 'icon' => '2', 'title' => __( 'أرشيف عميق', 'syria-oil-widgets' ), 'text' => __( 'آلاف المقالات المصنّفة.', 'syria-oil-widgets' ) ),
				array( 'icon' => '3', 'title' => __( 'تحديثات رسمية', 'syria-oil-widgets' ), 'text' => __( 'قرارات الوزارة والمناقصات.', 'syria-oil-widgets' ) ),
				array( 'icon' => '4', 'title' => __( 'شركاء القطاع', 'syria-oil-widgets' ), 'text' => __( 'شركات وطنية ودولية.', 'syria-oil-widgets' ) ),
			),
			'title_field' => '{{{ title }}}',
		) );
		$this->add_responsive_control( 'columns', array(
			'label' => __( 'Columns', 'syria-oil-widgets' ),
			'type' => Controls_Manager::NUMBER,
			'default' => 4, 'tablet_default' => 2, 'mobile_default' => 2,
			'min' => 1, 'max' => 4,
			'selectors' => array( '{{WRAPPER}} .sow-overview' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));' ),
		) );
		$this->end_controls_section();
		$this->start_controls_section( 'section_style', array( 'label' => __( 'Style', 'syria-oil-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'card_bg', array( 'label' => __( 'Card background', 'syria-oil-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .sow-overview-card' => 'background-color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typo', 'selector' => '{{WRAPPER}} .sow-overview-card h3' ) );
		$this->add_responsive_control( 'gap', array( 'label' => __( 'Gap', 'syria-oil-widgets' ), 'type' => Controls_Manager::SLIDER, 'selectors' => array( '{{WRAPPER}} .sow-overview' => 'gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();

		$this->register_animation_controls( array( 'stagger' => true ) );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$items = ! empty( $settings['items'] ) && is_array( $settings['items'] ) ? $settings['items'] : array();
		$cols = ! empty( $settings['columns'] ) ? absint( $settings['columns'] ) : 4;
		if ( empty( $items ) ) { return; }
		?>
		<div class="sow-overview" style="--sow-cols:<?php echo esc_attr( (string) $cols ); ?>">
			<?php foreach ( $items as $i => $item ) :
				$icon = isset( $item['icon'] ) ? $item['icon'] : '';
				$title = isset( $item['title'] ) ? $item['title'] : '';
				$text = isset( $item['text'] ) ? $item['text'] : '';
				$url = ! empty( $item['link']['url'] ) ? $item['link']['url'] : '';
				$tag = $url ? 'a' : 'article';
				$href = $url ? ' href="' . esc_url( $url ) . '"' : '';
				$anim = $this->sor_animate_attrs( $settings, (int) $i );
			?>
				<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="sow-overview-card"<?php echo $href . $anim; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php if ( $icon ) : ?><div class="icon"><?php echo esc_html( $icon ); ?></div><?php endif; ?>
					<h3><?php echo esc_html( $title ? $title : __( '(Untitled)', 'syria-oil-widgets' ) ); ?></h3>
					<?php if ( $text ) : ?><p class="sow-overview-card__text"><?php echo esc_html( $text ); ?></p><?php endif; ?>
				</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php endforeach; ?>
		</div>
		<?php

	}
}
