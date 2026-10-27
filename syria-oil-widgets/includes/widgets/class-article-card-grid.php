<?php
/**
 * Article Card Grid Elementor widget.
 *
 * Deep Content + Style controls. Empty color/typography fall back to theme
 * CSS variables / Elementor Global Settings via the widget stylesheet.
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
use Elementor\Group_Control_Box_Shadow;

/**
 * Article Card Grid.
 */
class Article_Card_Grid extends Widget_Base {

	use Post_Query_Trait;
	use Animation_Controls_Trait;
	use News_Card_Style_Controls_Trait;
	use News_Card_Slider_Controls_Trait;

	public function get_name() {
		return 'syria_oil_article_card_grid';
	}

	public function get_title() {
		return __( 'Article Card Grid', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_keywords() {
		return array( 'posts', 'grid', 'cards', 'news', 'articles', 'syria' );
	}

	public function get_style_depends() {
		return array_merge( array( 'syria-oil-news-card', 'syria-oil-article-grid' ), $this->get_animation_style_depends() );
	}

	public function get_script_depends() {
		return array_merge( array( 'syria-oil-category-rail-slider' ), $this->get_animation_script_depends() );
	}

	protected function register_controls() {
		$this->register_header_controls();
		$this->register_query_section();
		$this->register_display_controls();
		$this->register_news_card_slider_controls();
		$this->register_style_section_header();
		$this->register_news_card_style_controls( array( 'wrapper' => '.so-card-grid' ) );
		$this->register_style_read_more();
		$this->register_animation_controls( array( 'stagger' => true ) );
	}

	protected function register_header_controls() {
		$this->start_controls_section(
			'section_header',
			array( 'label' => __( 'Section header', 'syria-oil-widgets' ) )
		);

		$this->add_control(
			'show_header',
			array(
				'label'        => __( 'Show heading', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'eyebrow',
			array(
				'label'     => __( 'Eyebrow', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Newsroom', 'syria-oil-widgets' ),
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'show_header' => 'yes' ),
			)
		);

		$this->add_control(
			'heading',
			array(
				'label'     => __( 'Heading', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Latest news', 'syria-oil-widgets' ),
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'show_header' => 'yes' ),
			)
		);

		$this->add_control(
			'view_all_text',
			array(
				'label'     => __( 'View-all label', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'All news', 'syria-oil-widgets' ),
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'show_header' => 'yes' ),
			)
		);

		$this->add_control(
			'view_all_link',
			array(
				'label'       => __( 'View-all link', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => __( 'https://…', 'syria-oil-widgets' ),
				'dynamic'     => array( 'active' => true ),
				'condition'   => array( 'show_header' => 'yes' ),
			)
		);

		$this->add_control(
			'read_more_text',
			array(
				'label'   => __( 'Read-more button text', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Read more', 'syria-oil-widgets' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->end_controls_section();
	}

	protected function register_query_section() {
		$this->start_controls_section(
			'section_query',
			array( 'label' => __( 'Query', 'syria-oil-widgets' ) )
		);
		$this->register_query_controls( 'query' );
		$this->end_controls_section();
	}

	protected function register_display_controls() {
		$this->start_controls_section(
			'section_display',
			array( 'label' => __( 'Display', 'syria-oil-widgets' ) )
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Columns', 'syria-oil-widgets' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'selectors'      => array(
					'{{WRAPPER}} .so-grid:not(.so-news-card-slider__track)' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Gap', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 64 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 24 ),
				'selectors'  => array(
					'{{WRAPPER}} .so-grid' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'show_image',
			array(
				'label'        => __( 'Show image', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_date',
			array(
				'label'        => __( 'Show date', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_excerpt',
			array(
				'label'        => __( 'Show excerpt', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'excerpt_words',
			array(
				'label'     => __( 'Excerpt words', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 24,
				'min'       => 5,
				'max'       => 60,
				'condition' => array( 'show_excerpt' => 'yes' ),
			)
		);

		$this->add_control(
			'show_more',
			array(
				'label'        => __( 'Show read-more button', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_section_header() {
		$this->start_controls_section(
			'section_style_header',
			array(
				'label'     => __( 'Section Header', 'syria-oil-widgets' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_header' => 'yes' ),
			)
		);

		$this->add_control(
			'eyebrow_color',
			array(
				'label'     => __( 'Eyebrow color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .section-head .eyebrow' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'eyebrow_typography',
				'selector' => '{{WRAPPER}} .section-head .eyebrow',
			)
		);

		$this->add_control(
			'heading_color',
			array(
				'label'     => __( 'Heading color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .section-head h2' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'heading_typography',
				'selector' => '{{WRAPPER}} .section-head h2',
			)
		);

		$this->end_controls_section();
	}

	protected function register_style_read_more() {
		$this->start_controls_section(
			'section_style_more',
			array(
				'label'     => __( 'Read More Button', 'syria-oil-widgets' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_more' => 'yes' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'more_typography',
				'selector' => '{{WRAPPER}} .so-card-grid .card .btn',
			)
		);

		$this->start_controls_tabs( 'more_tabs' );

		$this->start_controls_tab( 'more_normal', array( 'label' => __( 'Normal', 'syria-oil-widgets' ) ) );
		$this->add_control(
			'more_color',
			array(
				'label'     => __( 'Text color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .so-card-grid .card .btn' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'more_bg',
			array(
				'label'     => __( 'Background', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .so-card-grid .card .btn' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'more_border_color',
			array(
				'label'     => __( 'Border color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .so-card-grid .card .btn' => 'border-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'more_hover', array( 'label' => __( 'Hover', 'syria-oil-widgets' ) ) );
		$this->add_control(
			'more_color_hover',
			array(
				'label'     => __( 'Text color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .so-card-grid .card .btn:hover' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'more_bg_hover',
			array(
				'label'     => __( 'Background', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .so-card-grid .card .btn:hover' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		if ( ! is_array( $settings ) ) {
			return;
		}

		$show_image = true;
		$show_date  = ( ! isset( $settings['show_date'] ) || 'yes' === $settings['show_date'] );
		$show_excerpt = ( ! isset( $settings['show_excerpt'] ) || 'yes' === $settings['show_excerpt'] );
		$show_more  = ( ! isset( $settings['show_more'] ) || 'yes' === $settings['show_more'] );
		$show_header = ( isset( $settings['show_header'] ) && 'yes' === $settings['show_header'] );

		$query = new \WP_Query(
			$this->build_query_args(
				$settings,
				'query',
				array(
					'update_post_meta_cache' => $show_image,
					'update_post_term_cache' => true,
				)
			)
		);

		if ( ! $query->have_posts() ) {
			if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="alert alert-info">' . esc_html__( 'No posts match this query yet.', 'syria-oil-widgets' ) . '</div>';
			}
			wp_reset_postdata();
			return;
		}

		$post_count  = (int) $query->post_count;
		$slider_mode = $this->resolve_news_card_slider_mode( $settings, $post_count );
		$scope       = '.elementor-element-' . $this->get_id();
		$this->print_news_card_slider_breakpoint_css( $settings, $scope );

		$heading  = isset( $settings['heading'] ) ? $settings['heading'] : '';
		$eyebrow  = isset( $settings['eyebrow'] ) ? $settings['eyebrow'] : '';
		$view_txt = isset( $settings['view_all_text'] ) ? $settings['view_all_text'] : '';
		$view_url = ( ! empty( $settings['view_all_link']['url'] ) ) ? $settings['view_all_link']['url'] : '';
		$view_ext = ! empty( $settings['view_all_link']['is_external'] );

		if ( function_exists( 'syria_oil_widgets_localize_button' ) ) {
			$heading  = syria_oil_widgets_localize_button( $heading );
			$eyebrow  = syria_oil_widgets_localize_button( $eyebrow );
			$view_txt = syria_oil_widgets_localize_button( $view_txt );
		}

		$read_more = ! empty( $settings['read_more_text'] ) ? $settings['read_more_text'] : __( 'Read more', 'syria-oil-widgets' );
		if ( function_exists( 'syria_oil_widgets_localize_button' ) ) {
			$read_more = syria_oil_widgets_localize_button( $read_more );
		}

		$slider_attrs = $this->news_card_slider_data_attrs( $settings );
		$rtl          = function_exists( 'is_rtl' ) && is_rtl();
		$prev_glyph   = $rtl ? '&rsaquo;' : '&lsaquo;';
		$next_glyph   = $rtl ? '&lsaquo;' : '&rsaquo;';

		?>
		<section class="so-card-grid section">
			<div class="container">
				<?php if ( $show_header && ( $heading || $eyebrow ) ) : ?>
					<div class="section-head">
						<div>
							<?php if ( $eyebrow ) : ?>
								<div class="eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
							<?php endif; ?>
							<?php if ( $heading ) : ?>
								<h2><?php echo esc_html( $heading ); ?></h2>
							<?php endif; ?>
						</div>
						<?php if ( $view_url && $view_txt ) : ?>
							<a class="btn btn-secondary" href="<?php echo esc_url( $view_url ); ?>"<?php echo $view_ext ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $view_txt ); ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( 'slider' === $slider_mode ) : ?>
					<div class="so-card-grid-slider so-news-card-slider so-news-card-slider--responsive"<?php echo $slider_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<button type="button" class="so-news-card-slider__arrow so-news-card-slider__arrow--prev sow-cat-rail-slider__arrow sow-cat-rail-slider__arrow--prev" aria-label="<?php echo esc_attr__( 'Previous', 'syria-oil-widgets' ); ?>"><span aria-hidden="true"><?php echo $prev_glyph; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></button>
						<div class="so-card-grid__track so-grid so-grid--news-cards so-news-card-slider__track">
				<?php elseif ( 'pair' === $slider_mode ) : ?>
					<div class="so-grid so-grid--news-cards so-card-grid--pair-mobile">
				<?php else : ?>
					<div class="so-grid so-grid--news-cards">
				<?php endif; ?>
					<?php
					$card_i = 0;
					while ( $query->have_posts() ) :
						$query->the_post();
						$syria_oil_news_card_args = array(
							'variant'        => 'stack',
							'show_badge'     => true,
							'show_date'      => $show_date,
							'show_excerpt'   => $show_excerpt,
							'excerpt_words'  => ! empty( $settings['excerpt_words'] ) ? absint( $settings['excerpt_words'] ) : 18,
							'image_size'     => 'syria-oil-card',
							'image_loading'  => 0 === $card_i ? 'eager' : 'lazy',
							'title_lines'    => 2,
							'animate_attrs'  => $this->sor_animate_attrs( $settings, $card_i ),
						);
						include SYRIA_OIL_WIDGETS_DIR . '/includes/views/news-card.php';
						++$card_i;
					endwhile;
					wp_reset_postdata();
					?>
				<?php if ( 'slider' === $slider_mode ) : ?>
						</div>
						<button type="button" class="so-news-card-slider__arrow so-news-card-slider__arrow--next sow-cat-rail-slider__arrow sow-cat-rail-slider__arrow--next" aria-label="<?php echo esc_attr__( 'Next', 'syria-oil-widgets' ); ?>"><span aria-hidden="true"><?php echo $next_glyph; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></button>
						<div class="so-news-card-slider__dots" aria-hidden="true"></div>
					</div>
				<?php else : ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
