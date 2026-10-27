<?php
/**
 * Newsletter CTA Elementor widget.
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

/**
 * Newsletter band — CTAs or email form (nonce + honeypot).
 */
class Newsletter_Cta extends Widget_Base {

	public function get_name() {
		return 'syria_oil_newsletter_cta';
	}

	public function get_title() {
		return __( 'Newsletter CTA', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-email-field';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_style_depends() {
		return array( 'syria-oil-newsletter-cta' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			array( 'label' => __( 'Content', 'syria-oil-widgets' ) )
		);

		$this->add_control(
			'mode',
			array(
				'label'   => __( 'Mode', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'buttons',
				'options' => array(
					'buttons' => __( 'CTA buttons', 'syria-oil-widgets' ),
					'form'    => __( 'Email form', 'syria-oil-widgets' ),
				),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::TEXT,
				'default' => function_exists( 'syria_oil_widgets_default' )
					? syria_oil_widgets_default( $this->get_name(), 'title', __( 'Stay informed', 'syria-oil-widgets' ) )
					: __( 'Stay informed', 'syria-oil-widgets' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'text',
			array(
				'label'   => __( 'Supporting text', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => function_exists( 'syria_oil_widgets_default' )
					? syria_oil_widgets_default( $this->get_name(), 'text', __( 'Get the latest oil and gas updates in your inbox.', 'syria-oil-widgets' ) )
					: __( 'Get the latest oil and gas updates in your inbox.', 'syria-oil-widgets' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'primary_text',
			array(
				'label'     => __( 'Primary button text', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Subscribe', 'syria-oil-widgets' ),
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'mode' => 'buttons' ),
			)
		);

		$this->add_control(
			'primary_link',
			array(
				'label'     => __( 'Primary button link', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::URL,
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'mode' => 'buttons' ),
			)
		);

		$this->add_control(
			'secondary_text',
			array(
				'label'     => __( 'Secondary button text', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXT,
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'mode' => 'buttons' ),
			)
		);

		$this->add_control(
			'secondary_link',
			array(
				'label'     => __( 'Secondary button link', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::URL,
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'mode' => 'buttons' ),
			)
		);

		$this->add_control(
			'form_action',
			array(
				'label'       => __( 'External form action URL (optional)', 'syria-oil-widgets' ),
				'description' => __( 'Leave empty to use the built-in secure handler.', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::URL,
				'condition'   => array( 'mode' => 'form' ),
			)
		);

		$this->add_control(
			'submit_text',
			array(
				'label'     => __( 'Submit button text', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Subscribe', 'syria-oil-widgets' ),
				'condition' => array( 'mode' => 'form' ),
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
			'bg_from',
			array(
				'label'       => __( 'Gradient start', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::COLOR,
				'description' => __( 'Leave empty to use Theme Options (--sow-nl-from / primary dark).', 'syria-oil-widgets' ),
				'selectors'   => array(
					'{{WRAPPER}} .sow-newsletter' => '--sow-nl-from: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'bg_to',
			array(
				'label'       => __( 'Gradient end', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::COLOR,
				'description' => __( 'Leave empty to use Theme Options (--sow-nl-to / primary).', 'syria-oil-widgets' ),
				'selectors'   => array(
					'{{WRAPPER}} .sow-newsletter' => '--sow-nl-to: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'       => __( 'Text color', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::COLOR,
				'description' => __( 'Leave empty to use --color-on-primary from the theme.', 'syria-oil-widgets' ),
				'selectors'   => array( '{{WRAPPER}} .sow-newsletter' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typo',
				'selector' => '{{WRAPPER}} .sow-newsletter h2',
			)
		);

		$this->add_responsive_control(
			'padding',
			array(
				'label'      => __( 'Padding', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .sow-newsletter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$title    = isset( $settings['title'] ) ? $settings['title'] : '';
		$text     = isset( $settings['text'] ) ? $settings['text'] : '';
		$mode     = isset( $settings['mode'] ) ? $settings['mode'] : 'buttons';
		?>
		<section class="sow-newsletter">
			<div class="sow-newsletter__copy">
				<?php if ( $title ) : ?>
					<h2><?php echo esc_html( $title ); ?></h2>
				<?php endif; ?>
				<?php if ( $text ) : ?>
					<p><?php echo esc_html( $text ); ?></p>
				<?php endif; ?>
			</div>
			<div class="sow-newsletter__actions">
				<?php if ( 'form' === $mode ) : ?>
					<?php
					$action = ! empty( $settings['form_action']['url'] )
						? $settings['form_action']['url']
						: admin_url( 'admin-post.php' );
					?>
					<form class="sow-newsletter-form" method="post" action="<?php echo esc_url( $action ); ?>">
						<?php if ( empty( $settings['form_action']['url'] ) ) : ?>
							<input type="hidden" name="action" value="syria_oil_newsletter" />
							<?php wp_nonce_field( 'syria_oil_newsletter' ); ?>
						<?php endif; ?>
						<label class="screen-reader-text" for="sow-nl-email-<?php echo esc_attr( $this->get_id() ); ?>"><?php esc_html_e( 'Email', 'syria-oil-widgets' ); ?></label>
						<input
							id="sow-nl-email-<?php echo esc_attr( $this->get_id() ); ?>"
							type="email"
							name="email"
							required
							placeholder="<?php esc_attr_e( 'you@example.com', 'syria-oil-widgets' ); ?>"
						/>
						<input type="text" name="syria_oil_hp" value="" tabindex="-1" autocomplete="off" class="sow-hp" aria-hidden="true" />
						<button type="submit" class="btn btn-accent">
							<?php echo esc_html( ! empty( $settings['submit_text'] ) ? $settings['submit_text'] : __( 'Subscribe', 'syria-oil-widgets' ) ); ?>
						</button>
					</form>
				<?php else : ?>
					<?php if ( ! empty( $settings['primary_text'] ) && ! empty( $settings['primary_link']['url'] ) ) : ?>
						<a class="btn btn-accent" href="<?php echo esc_url( $settings['primary_link']['url'] ); ?>"><?php echo esc_html( $settings['primary_text'] ); ?></a>
					<?php endif; ?>
					<?php if ( ! empty( $settings['secondary_text'] ) && ! empty( $settings['secondary_link']['url'] ) ) : ?>
						<a class="btn btn-ghost" href="<?php echo esc_url( $settings['secondary_link']['url'] ); ?>"><?php echo esc_html( $settings['secondary_text'] ); ?></a>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
