<?php
/**
 * Contact CTA strip widget.
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

class Contact_Cta extends Widget_Base {

	public function get_name() {
		return 'syria_oil_contact_cta';
	}

	public function get_title() {
		return __( 'Contact CTA', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-tel-field';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_style_depends() {
		return array( 'syria-oil-portal-sections' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'syria-oil-widgets' ) ) );
		$this->add_control( 'phone', array( 'label' => __( 'Phone', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXT, 'dynamic' => array( 'active' => true ) ) );
		$this->add_control( 'email', array( 'label' => __( 'Email', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXT, 'dynamic' => array( 'active' => true ) ) );
		$this->add_control( 'address', array( 'label' => __( 'Address', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXTAREA, 'dynamic' => array( 'active' => true ) ) );
		$this->add_control( 'cta_text', array( 'label' => __( 'Button text', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Contact us', 'syria-oil-widgets' ), 'dynamic' => array( 'active' => true ) ) );
		$this->add_control( 'cta_link', array( 'label' => __( 'Button link', 'syria-oil-widgets' ), 'type' => Controls_Manager::URL, 'dynamic' => array( 'active' => true ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'section_style', array( 'label' => __( 'Style', 'syria-oil-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'bg', array( 'label' => __( 'Background', 'syria-oil-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .sow-contact-cta' => 'background-color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'text_typo', 'selector' => '{{WRAPPER}} .sow-contact-cta' ) );
		$this->add_responsive_control( 'padding', array( 'label' => __( 'Padding', 'syria-oil-widgets' ), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', 'em' ), 'selectors' => array( '{{WRAPPER}} .sow-contact-cta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$phone = isset( $s['phone'] ) ? $s['phone'] : '';
		$email = isset( $s['email'] ) ? $s['email'] : '';
		$address = isset( $s['address'] ) ? $s['address'] : '';
		if ( ! $phone && function_exists( 'syria_oil_mod' ) ) {
			$phone = syria_oil_mod( 'contact_phone' );
		}
		if ( ! $email && function_exists( 'syria_oil_mod' ) ) {
			$email = syria_oil_mod( 'contact_email' );
		}
		$cta_url = ! empty( $s['cta_link']['url'] ) ? $s['cta_link']['url'] : '';
		?>
		<div class="sow-contact-cta">
			<?php if ( $phone ) : ?>
				<div class="sow-contact-cta__item">
					<span class="icon" aria-hidden="true">☎</span>
					<div><strong><?php esc_html_e( 'Phone', 'syria-oil-widgets' ); ?></strong><br />
					<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></div>
				</div>
			<?php endif; ?>
			<?php if ( $email ) : ?>
				<div class="sow-contact-cta__item">
					<span class="icon" aria-hidden="true">✉</span>
					<div><strong><?php esc_html_e( 'Email', 'syria-oil-widgets' ); ?></strong><br />
					<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></div>
				</div>
			<?php endif; ?>
			<?php if ( $address || $cta_url ) : ?>
				<div class="sow-contact-cta__item">
					<span class="icon" aria-hidden="true">⌖</span>
					<div>
						<?php if ( $address ) : ?><strong><?php esc_html_e( 'Address', 'syria-oil-widgets' ); ?></strong><br /><span><?php echo esc_html( $address ); ?></span><br /><?php endif; ?>
						<?php if ( $cta_url && ! empty( $s['cta_text'] ) ) : ?>
							<a class="btn btn-primary btn-sm" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $s['cta_text'] ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
