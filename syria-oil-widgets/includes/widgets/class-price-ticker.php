<?php
/**
 * Price Ticker Elementor widget.
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
use Elementor\Group_Control_Border;
use Syria_Oil_Widgets\Price_API;
use Syria_Oil_Widgets\Price_Feed;

/**
 * Commodity price ticker / static row (cache-first).
 */
class Price_Ticker extends Widget_Base {

	use Animation_Controls_Trait;

	public function get_name() {
		return 'syria_oil_price_ticker';
	}

	public function get_title() {
		return __( 'Price Ticker', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-price-list';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_style_depends() {
		return array_merge( array( 'syria-oil-price-ticker' ), $this->get_animation_style_depends() );
	}

	public function get_script_depends() {
		return array_merge( array( 'syria-oil-price-ticker' ), $this->get_counter_script_depends() );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			array( 'label' => __( 'Content', 'syria-oil-widgets' ) )
		);

		$commodity_options = array();
		foreach ( Price_API::commodities() as $id => $meta ) {
			$commodity_options[ $id ] = $meta['label'];
		}

		$this->add_control(
			'commodities',
			array(
				'label'       => __( 'Commodities', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $commodity_options,
				'default'     => array_keys( $commodity_options ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'currency_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Prices display in USD as returned by the provider (no conversion).', 'syria-oil-widgets' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			)
		);

		$this->add_control(
			'show_change',
			array(
				'label'        => __( 'Show daily change', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_updated',
			array(
				'label'        => __( 'Show last updated', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'ticker',
				'options' => array(
					'ticker' => __( 'Scrolling ticker', 'syria-oil-widgets' ),
					'static' => __( 'Static row', 'syria-oil-widgets' ),
				),
			)
		);

		$this->add_control(
			'speed',
			array(
				'label'     => __( 'Speed (seconds per loop)', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array( 'size' => 30 ),
				'range'     => array(
					'px' => array(
						'min' => 10,
						'max' => 90,
					),
				),
				'condition' => array( 'layout' => 'ticker' ),
			)
		);

		$this->add_control(
			'pause_on_hover',
			array(
				'label'        => __( 'Pause on hover', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'layout' => 'ticker' ),
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
				'label'     => __( 'Background', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f1419',
				'selectors' => array( '{{WRAPPER}} .sow-prices' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'Text color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array( '{{WRAPPER}} .sow-prices' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'up_color',
			array(
				'label'     => __( 'Up change color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#3dd68c',
				'selectors' => array( '{{WRAPPER}} .sow-prices__change--up' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'down_color',
			array(
				'label'     => __( 'Down change color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f87171',
				'selectors' => array( '{{WRAPPER}} .sow-prices__change--down' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typo',
				'selector' => '{{WRAPPER}} .sow-prices',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'border',
				'selector' => '{{WRAPPER}} .sow-prices',
			)
		);

		$this->add_responsive_control(
			'padding',
			array(
				'label'      => __( 'Padding', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .sow-prices' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Item gap', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 8,
						'max' => 64,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 24,
				),
				'selectors'  => array(
					'{{WRAPPER}} .sow-prices__list' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->register_counter_controls();
	}

	/**
	 * Build display rows from cache for selected commodities.
	 *
	 * @param array<string, mixed> $settings Widget settings.
	 * @return array{rows: array<int, array>, last_updated: int}|null
	 */
	protected function get_display_data( $settings ) {
		$cached = Price_Feed::get_prices();
		if ( null === $cached ) {
			return null;
		}

		$selected = ! empty( $settings['commodities'] ) && is_array( $settings['commodities'] )
			? $settings['commodities']
			: array_keys( Price_API::commodities() );

		$rows = array();
		foreach ( $selected as $id ) {
			if ( empty( $cached['items'][ $id ] ) || ! is_array( $cached['items'][ $id ] ) ) {
				continue;
			}
			$rows[] = $cached['items'][ $id ];
		}

		if ( empty( $rows ) ) {
			return null;
		}

		return array(
			'rows'         => $rows,
			'last_updated' => $cached['last_updated'],
		);
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$data     = $this->get_display_data( $settings );

		if ( null === $data ) {
			// Avoid an empty dark strip on the live site until EIA prices are cached.
			$in_editor = class_exists( '\Elementor\Plugin' )
				&& isset( \Elementor\Plugin::$instance->editor )
				&& \Elementor\Plugin::$instance->editor->is_edit_mode();
			if ( ! $in_editor ) {
				return;
			}
			echo '<div class="sow-prices sow-prices--placeholder">';
			echo '<p class="sow-prices__placeholder">' . esc_html__( 'Prices updating…', 'syria-oil-widgets' ) . '</p>';
			echo '<p class="sow-prices__hint">' . esc_html__( 'Set an EIA API key under Syria Oil → Oil & Gas Prices, then click Refresh now.', 'syria-oil-widgets' ) . '</p>';
			echo '</div>';
			return;
		}

		$show_change  = isset( $settings['show_change'] ) && 'yes' === $settings['show_change'];
		$show_updated = isset( $settings['show_updated'] ) && 'yes' === $settings['show_updated'];
		$layout       = ( isset( $settings['layout'] ) && 'static' === $settings['layout'] ) ? 'static' : 'ticker';
		$speed        = ! empty( $settings['speed']['size'] ) ? absint( $settings['speed']['size'] ) : 30;
		$pause        = ( isset( $settings['pause_on_hover'] ) && 'yes' === $settings['pause_on_hover'] ) ? '1' : '0';
		$counter_on   = ! empty( $settings['sor_counter'] ) && 'yes' === $settings['sor_counter'];
		$duration     = ! empty( $settings['sor_counter_duration'] ) ? absint( $settings['sor_counter_duration'] ) : 1100;
		$duration     = max( 800, min( 1600, $duration ) );

		$classes = array( 'sow-prices', 'sow-prices--' . $layout );
		?>
		<div
			class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
			<?php if ( 'ticker' === $layout ) : ?>
				data-speed="<?php echo esc_attr( (string) $speed ); ?>"
				data-pause="<?php echo esc_attr( $pause ); ?>"
			<?php endif; ?>
		>
			<?php if ( 'ticker' === $layout ) : ?>
				<div class="sow-prices__track-wrap">
					<ul class="sow-prices__list sow-prices__track">
						<?php foreach ( $data['rows'] as $row ) : ?>
							<?php $this->render_item( $row, $show_change, $counter_on, $duration ); ?>
						<?php endforeach; ?>
						<?php foreach ( $data['rows'] as $row ) : ?>
							<?php $this->render_item( $row, $show_change, false, $duration ); ?>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php else : ?>
				<ul class="sow-prices__list">
					<?php foreach ( $data['rows'] as $row ) : ?>
						<?php $this->render_item( $row, $show_change, $counter_on, $duration ); ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $show_updated && ! empty( $data['last_updated'] ) ) : ?>
				<p class="sow-prices__updated">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: time */
							__( 'As of %s', 'syria-oil-widgets' ),
							wp_date( get_option( 'time_format' ), (int) $data['last_updated'] )
						)
					);
					?>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render one price list item.
	 *
	 * @param array<string, mixed> $row         Price row.
	 * @param bool                 $show_change Show change.
	 * @param bool                 $counter_on  Animate value changes.
	 * @param int                  $duration    Counter duration ms.
	 */
	protected function render_item( $row, $show_change, $counter_on = false, $duration = 1100 ) {
		$label = isset( $row['label'] ) ? (string) $row['label'] : '';
		$id    = isset( $row['id'] ) ? (string) $row['id'] : sanitize_title( $label );
		$raw   = isset( $row['price'] ) && is_numeric( $row['price'] ) ? (float) $row['price'] : null;
		$price = null !== $raw ? syria_oil_widgets_format_western_number( $raw, 2 ) : '—';
		$unit  = isset( $row['unit'] ) ? (string) $row['unit'] : '';
		$change_class = '';
		$change_text  = '';

		if ( $show_change && isset( $row['change_pct'] ) && null !== $row['change_pct'] && is_numeric( $row['change_pct'] ) ) {
			$pct = (float) $row['change_pct'];
			if ( $pct > 0 ) {
				$change_class = 'sow-prices__change--up';
				$change_text  = '▲ +' . syria_oil_widgets_format_western_number( $pct, 2 ) . '%';
			} elseif ( $pct < 0 ) {
				$change_class = 'sow-prices__change--down';
				$change_text  = '▼ ' . syria_oil_widgets_format_western_number( $pct, 2 ) . '%';
			} else {
				$change_text = syria_oil_widgets_format_western_number( 0, 2 ) . '%';
			}
		}
		?>
		<li class="sow-prices__item">
			<span class="sow-prices__name"><?php echo esc_html( $label ); ?></span>
			<span class="sow-prices__value" dir="ltr">
				<span class="sow-prices__currency">USD </span>
				<?php if ( $counter_on && null !== $raw ) : ?>
					<span
						class="sow-prices__num"
						data-so-count="<?php echo esc_attr( (string) $raw ); ?>"
						data-so-decimals="2"
						data-so-grouping="1"
						data-so-locale="en-US"
						data-so-duration="<?php echo esc_attr( (string) $duration ); ?>"
						data-so-trigger="change"
						data-so-from-previous="1"
						data-so-key="<?php echo esc_attr( 'price-' . $id ); ?>"
					><?php echo esc_html( $price ); ?></span>
				<?php else : ?>
					<span class="sow-prices__num"><?php echo esc_html( $price ); ?></span>
				<?php endif; ?>
				<?php if ( $unit ) : ?>
					<span class="sow-prices__unit"><?php echo esc_html( $unit ); ?></span>
				<?php endif; ?>
			</span>
			<?php if ( $change_text ) : ?>
				<span class="sow-prices__change <?php echo esc_attr( $change_class ); ?>"><?php echo esc_html( $change_text ); ?></span>
			<?php endif; ?>
		</li>
		<?php
	}
}
