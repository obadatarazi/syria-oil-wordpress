<?php
/**
 * Minister / Featured Quote Block widget.
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
use Elementor\Utils;

class Minister_Block extends Widget_Base {

	use Animation_Controls_Trait;

	public function get_name() {
		return 'syria_oil_minister_block';
	}

	public function get_title() {
		return __( 'Minister Block', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-person';
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
		$this->add_control( 'image', array( 'label' => __( 'Photo', 'syria-oil-widgets' ), 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => Utils::get_placeholder_image_src() ), 'dynamic' => array( 'active' => true ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Leadership', 'syria-oil-widgets' ), 'dynamic' => array( 'active' => true ) ) );
		$this->add_control( 'title', array( 'label' => __( 'Title', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Minister of Oil', 'syria-oil-widgets' ), 'dynamic' => array( 'active' => true ) ) );
		$this->add_control( 'quote', array( 'label' => __( 'Quote / bio', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXTAREA, 'default' => __( 'A message from leadership about the energy sector.', 'syria-oil-widgets' ), 'dynamic' => array( 'active' => true ) ) );
		$this->add_control( 'cta_text', array( 'label' => __( 'Button text', 'syria-oil-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Read more', 'syria-oil-widgets' ), 'dynamic' => array( 'active' => true ) ) );
		$this->add_control( 'cta_link', array( 'label' => __( 'Button link', 'syria-oil-widgets' ), 'type' => Controls_Manager::URL, 'dynamic' => array( 'active' => true ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'section_style', array( 'label' => __( 'Style', 'syria-oil-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'bg', array( 'label' => __( 'Background', 'syria-oil-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .sow-minister' => 'background-color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typo', 'selector' => '{{WRAPPER}} .sow-minister__body h2' ) );
		$this->add_responsive_control( 'padding', array( 'label' => __( 'Body padding', 'syria-oil-widgets' ), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', 'em' ), 'selectors' => array( '{{WRAPPER}} .sow-minister__body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->end_controls_section();
		$this->register_animation_controls();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$img = ! empty( $s['image']['url'] ) ? $s['image']['url'] : '';
		$cta_url = ! empty( $s['cta_link']['url'] ) ? $s['cta_link']['url'] : '';
		$is_placeholder = $img && false !== strpos( $img, 'placeholder' );
		$has_real_image = $img && ! $is_placeholder;
		$media_class = 'sow-minister__media' . ( $has_real_image ? '' : ' sow-minister__media--fallback' );
		?>
		<div class="sow-minister"<?php echo $this->sor_animate_attrs( $s, 0 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<div class="<?php echo esc_attr( $media_class ); ?>">
				<?php if ( $has_real_image ) : ?>
					<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( isset( $s['title'] ) ? $s['title'] : '' ); ?>" loading="lazy" decoding="async" />
				<?php endif; ?>
			</div>
			<div class="sow-minister__body">
				<?php if ( ! empty( $s['eyebrow'] ) ) : ?><div class="eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></div><?php endif; ?>
				<?php if ( ! empty( $s['title'] ) ) : ?><h2><?php echo esc_html( $s['title'] ); ?></h2><?php endif; ?>
				<?php if ( ! empty( $s['quote'] ) ) : ?><p class="quote"><?php echo esc_html( $s['quote'] ); ?></p><?php endif; ?>
				<?php if ( $cta_url && ! empty( $s['cta_text'] ) ) : ?>
					<a class="btn btn-primary" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $s['cta_text'] ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
