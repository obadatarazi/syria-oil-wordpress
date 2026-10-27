<?php
/**
 * News Ticker Elementor widget.
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
 * Scrolling news ticker (RTL-aware).
 */
class News_Ticker extends Widget_Base {

	use Post_Query_Trait;

	public function get_name() {
		return 'syria_oil_news_ticker';
	}

	public function get_title() {
		return __( 'News Ticker', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-animation-text';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_style_depends() {
		return array( 'syria-oil-news-ticker' );
	}

	public function get_script_depends() {
		return array( 'syria-oil-news-ticker' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			array( 'label' => __( 'Content', 'syria-oil-widgets' ) )
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Items from', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'query',
				'options' => array(
					'manual' => __( 'Manual items', 'syria-oil-widgets' ),
					'query'  => __( 'Latest posts', 'syria-oil-widgets' ),
					'prices' => __( 'Commodity prices', 'syria-oil-widgets' ),
				),
			)
		);

		$this->add_control(
			'label',
			array(
				'label'   => __( 'Ticker label', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::TEXT,
				'default' => function_exists( 'syria_oil_widgets_default' )
					? syria_oil_widgets_default( $this->get_name(), 'label', __( 'Latest News', 'syria-oil-widgets' ) )
					: __( 'Latest News', 'syria-oil-widgets' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'News headline', 'syria-oil-widgets' ),
				'dynamic' => array( 'active' => true ),
			)
		);
		$repeater->add_control(
			'url',
			array(
				'label'         => __( 'Link', 'syria-oil-widgets' ),
				'type'          => Controls_Manager::URL,
				'placeholder'   => 'https://',
				'show_external' => true,
				'dynamic'       => array( 'active' => true ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Items', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'text' => __( 'First headline', 'syria-oil-widgets' ) ),
					array( 'text' => __( 'Second headline', 'syria-oil-widgets' ) ),
				),
				'title_field' => '{{{ text }}}',
				'condition'   => array( 'source' => 'manual' ),
			)
		);

		$this->register_query_controls( 'query', array( 'source' => 'query' ) );

		$this->add_control(
			'prices_show_change',
			array(
				'label'        => __( 'Show price change %', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'source' => 'prices' ),
			)
		);

		$this->add_control(
			'speed',
			array(
				'label'   => __( 'Speed (seconds per loop)', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => array( 'size' => 30 ),
				'range'   => array(
					'px' => array(
						'min' => 10,
						'max' => 90,
					),
				),
			)
		);

		$this->add_control(
			'pause_on_hover',
			array(
				'label'        => __( 'Pause on hover', 'syria-oil-widgets' ),
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
			'bg_color',
			array(
				'label'       => __( 'Track background', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::COLOR,
				'description' => __( 'Leave empty to use Theme Options Ink (--color-ink).', 'syria-oil-widgets' ),
				'selectors'   => array( '{{WRAPPER}} .sow-ticker' => '--sow-ticker-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'       => __( 'Track text color', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::COLOR,
				'description' => __( 'Leave empty to use theme on-primary text.', 'syria-oil-widgets' ),
				'selectors'   => array( '{{WRAPPER}} .sow-ticker' => '--sow-ticker-fg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'label_bg',
			array(
				'label'       => __( 'عاجل / Label background', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::COLOR,
				'description' => __( 'Leave empty to follow Theme Options → Primary (brand green).', 'syria-oil-widgets' ),
				'selectors'   => array( '{{WRAPPER}} .sow-ticker' => '--sow-ticker-label-bg: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typo',
				'selector' => '{{WRAPPER}} .sow-ticker',
			)
		);

		$this->add_responsive_control(
			'padding',
			array(
				'label'      => __( 'Padding', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .sow-ticker' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$items    = array();

		if ( isset( $settings['source'] ) && 'query' === $settings['source'] ) {
			$args = $this->build_query_args( $settings, 'query' );
			// Latest News navbar: always the 6 newest post titles.
			$args['posts_per_page'] = 6;
			$query = new \WP_Query( $args );
			while ( $query->have_posts() ) {
				$query->the_post();
				$items[] = array(
					'text' => get_the_title(),
					'url'  => get_permalink(),
				);
			}
			wp_reset_postdata();
		} elseif ( isset( $settings['source'] ) && 'prices' === $settings['source'] ) {
			$cached = \Syria_Oil_Widgets\Price_Feed::get_prices();
			if ( $cached && ! empty( $cached['items'] ) ) {
				$show_change = isset( $settings['prices_show_change'] ) && 'yes' === $settings['prices_show_change'];
				foreach ( $cached['items'] as $row ) {
					$items[] = array(
						'text' => \Syria_Oil_Widgets\Price_Feed::format_item_text( $row, $show_change ),
						'url'  => '',
					);
				}
			}
		} elseif ( ! empty( $settings['items'] ) && is_array( $settings['items'] ) ) {
			foreach ( $settings['items'] as $row ) {
				$items[] = array(
					'text' => isset( $row['text'] ) ? $row['text'] : '',
					'url'  => ! empty( $row['url']['url'] ) ? $row['url']['url'] : '',
				);
			}
		}

		if ( empty( $items ) ) {
			if ( isset( $settings['source'] ) && 'prices' === $settings['source'] ) {
				$in_editor = class_exists( '\Elementor\Plugin' )
					&& isset( \Elementor\Plugin::$instance->editor )
					&& \Elementor\Plugin::$instance->editor->is_edit_mode();
				if ( $in_editor ) {
					echo '<div class="sow-ticker sow-ticker--placeholder"><span class="sow-ticker__label">'
						. esc_html__( 'Prices', 'syria-oil-widgets' )
						. '</span><span class="sow-ticker__item"><span>'
						. esc_html__( 'Prices updating…', 'syria-oil-widgets' )
						. '</span></span></div>';
				}
			}
			return;
		}

		$speed = ! empty( $settings['speed']['size'] ) ? absint( $settings['speed']['size'] ) : 30;
		$pause = ( isset( $settings['pause_on_hover'] ) && 'yes' === $settings['pause_on_hover'] ) ? '1' : '0';
		$label = isset( $settings['label'] ) ? $settings['label'] : '';
		$pump  = defined( 'SYRIA_OIL_URI' )
			? trailingslashit( SYRIA_OIL_URI ) . 'assets/images/oil-pumpjack.svg'
			: '';
		?>
		<div class="sow-ticker" data-speed="<?php echo esc_attr( (string) $speed ); ?>" data-pause="<?php echo esc_attr( $pause ); ?>" data-autoplay="1">
			<?php if ( $label ) : ?>
				<span class="sow-ticker__label">
					<?php if ( $pump ) : ?>
						<img class="sow-ticker__pump" src="<?php echo esc_url( $pump ); ?>" alt="" width="18" height="18" decoding="async" aria-hidden="true" />
					<?php endif; ?>
					<?php echo esc_html( $label ); ?>
				</span>
			<?php endif; ?>
			<div class="sow-ticker__track-wrap">
				<ul class="sow-ticker__track">
					<?php foreach ( array_merge( $items, $items ) as $item ) : ?>
						<li class="sow-ticker__item">
							<?php if ( ! empty( $item['url'] ) ) : ?>
								<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a>
							<?php else : ?>
								<span><?php echo esc_html( $item['text'] ); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<?php
	}
}
