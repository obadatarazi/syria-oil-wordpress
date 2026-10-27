<?php
/**
 * FAQ Accordion Elementor widget.
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

/**
 * FAQ accordion (single-open).
 */
class Faq_Accordion extends Widget_Base {

	use Animation_Controls_Trait;

	public function get_name() {
		return 'syria_oil_faq_accordion';
	}

	public function get_title() {
		return __( 'FAQ Accordion', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-accordion';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_style_depends() {
		return array_merge( array( 'syria-oil-faq-accordion' ), $this->get_animation_style_depends() );
	}

	public function get_script_depends() {
		return array_merge( array( 'syria-oil-faq-accordion' ), $this->get_animation_script_depends() );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			array( 'label' => __( 'Content', 'syria-oil-widgets' ) )
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'question',
			array(
				'label'   => __( 'Question', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Frequently asked question?', 'syria-oil-widgets' ),
				'dynamic' => array( 'active' => true ),
			)
		);
		$repeater->add_control(
			'answer',
			array(
				'label'   => __( 'Answer', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::WYSIWYG,
				'default' => __( 'Answer text goes here.', 'syria-oil-widgets' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'FAQ items', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'question' => __( 'What is this portal?', 'syria-oil-widgets' ),
						'answer'   => __( 'Official oil and gas news and resources.', 'syria-oil-widgets' ),
					),
				),
				'title_field' => '{{{ question }}}',
			)
		);

		$this->add_control(
			'first_open',
			array(
				'label'        => __( 'Open first item', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => __( 'Style', 'syria-oil-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'item_bg',
			array(
				'label'     => __( 'Item background', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sow-accordion-item' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'question_color',
			array(
				'label'     => __( 'Question color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sow-accordion-btn' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'icon_bg',
			array(
				'label'     => __( 'Open icon background', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sow-accordion-item.is-open .sow-accordion-btn .icon' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'question_typo',
				'selector' => '{{WRAPPER}} .sow-accordion-btn',
			)
		);

		$this->add_responsive_control(
			'item_spacing',
			array(
				'label'      => __( 'Item spacing', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .sow-accordion' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'item_padding',
			array(
				'label'      => __( 'Item padding', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .sow-accordion-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->register_animation_controls( array( 'stagger' => true ) );
	}

	protected function render() {
		$settings   = $this->get_settings_for_display();
		$items      = ! empty( $settings['items'] ) && is_array( $settings['items'] ) ? $settings['items'] : array();
		$first_open = ( isset( $settings['first_open'] ) && 'yes' === $settings['first_open'] );
		if ( empty( $items ) ) {
			$empty = function_exists( 'syria_oil_widgets_default' )
				? syria_oil_widgets_default( $this->get_name(), 'empty_text', '' )
				: '';
			if ( $empty ) {
				echo '<p class="sow-faq-empty">' . esc_html( $empty ) . '</p>';
			}
			return;
		}
		$uid = $this->get_id();
		?>
		<div class="sow-accordion" data-single-open="1">
			<?php foreach ( $items as $i => $item ) : ?>
				<?php
				$open = ( $first_open && 0 === (int) $i );
				$qid  = 'sow-faq-q-' . esc_attr( $uid ) . '-' . (int) $i;
				$aid  = 'sow-faq-a-' . esc_attr( $uid ) . '-' . (int) $i;
				$q    = isset( $item['question'] ) ? $item['question'] : '';
				$a    = isset( $item['answer'] ) ? $item['answer'] : '';
				?>
				<div class="sow-accordion-item<?php echo $open ? ' is-open' : ''; ?>"<?php echo $this->sor_animate_attrs( $settings, (int) $i ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<button
						type="button"
						class="sow-accordion-btn"
						id="<?php echo esc_attr( $qid ); ?>"
						aria-expanded="<?php echo $open ? 'true' : 'false'; ?>"
						aria-controls="<?php echo esc_attr( $aid ); ?>"
					>
						<span><?php echo esc_html( $q ); ?></span>
						<span class="icon" aria-hidden="true"><?php echo $open ? '−' : '+'; ?></span>
					</button>
					<div
						class="sow-accordion-panel"
						id="<?php echo esc_attr( $aid ); ?>"
						role="region"
						aria-labelledby="<?php echo esc_attr( $qid ); ?>"
						<?php echo $open ? '' : ' hidden'; ?>
					>
						<?php echo wp_kses_post( $a ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
