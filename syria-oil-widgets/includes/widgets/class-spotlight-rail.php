<?php
/**
 * Spotlight / Hero Rail Elementor widget.
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
use Elementor\Repeater;
use Elementor\Utils;

/**
 * Rail or spotlight grid of featured stories.
 */
class Spotlight_Rail extends Widget_Base {

	use Post_Query_Trait;
	use Animation_Controls_Trait;

	public function get_name() {
		return 'syria_oil_spotlight_rail';
	}

	public function get_title() {
		return __( 'Spotlight / Hero Rail', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_style_depends() {
		return array_merge( array( 'syria-oil-spotlight-rail' ), $this->get_animation_style_depends() );
	}

	public function get_script_depends() {
		return $this->get_animation_script_depends();
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			array( 'label' => __( 'Content', 'syria-oil-widgets' ) )
		);

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'rail',
				'options' => array(
					'rail'      => __( 'Hero rail', 'syria-oil-widgets' ),
					'spotlight' => __( 'Spotlight grid', 'syria-oil-widgets' ),
				),
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Items from', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'query',
				'options' => array(
					'query'  => __( 'Latest posts', 'syria-oil-widgets' ),
					'manual' => __( 'Manual items', 'syria-oil-widgets' ),
				),
			)
		);

		$this->add_control(
			'heading',
			array(
				'label'     => __( 'Section heading', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Featured coverage', 'syria-oil-widgets' ),
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'layout' => 'spotlight' ),
			)
		);

		$this->add_control(
			'eyebrow',
			array(
				'label'     => __( 'Eyebrow', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXT,
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'layout' => 'spotlight' ),
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'image',
			array(
				'label'   => __( 'Image', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array( 'url' => Utils::get_placeholder_image_src() ),
				'dynamic' => array( 'active' => true ),
			)
		);
		$repeater->add_control(
			'pill',
			array(
				'label'   => __( 'Pill / badge', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
			)
		);
		$repeater->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Story title', 'syria-oil-widgets' ),
				'dynamic' => array( 'active' => true ),
			)
		);
		$repeater->add_control(
			'excerpt',
			array(
				'label'   => __( 'Excerpt', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::TEXTAREA,
				'dynamic' => array( 'active' => true ),
			)
		);
		$repeater->add_control(
			'date',
			array(
				'label'   => __( 'Date', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
			)
		);
		$repeater->add_control(
			'link',
			array(
				'label'   => __( 'Link', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Items', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
				'condition'   => array( 'source' => 'manual' ),
			)
		);

		$this->register_query_controls( 'query', array( 'source' => 'query' ) );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => __( 'Style', 'syria-oil-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Columns', 'syria-oil-widgets' ),
				'type'           => Controls_Manager::NUMBER,
				'default'        => 3,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'min'            => 1,
				'max'            => 4,
				'selectors'      => array(
					'{{WRAPPER}} .sow-spotlight__grid, {{WRAPPER}} .sow-rail' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Gap', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 48 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .sow-spotlight__grid, {{WRAPPER}} .sow-rail' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Title color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .sow-rail-item h2, {{WRAPPER}} .sow-spotlight-card h3' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typo',
				'selector' => '{{WRAPPER}} .sow-rail-item h2, {{WRAPPER}} .sow-spotlight-card h3',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .sow-rail-item, {{WRAPPER}} .sow-spotlight-card',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_shadow',
				'selector' => '{{WRAPPER}} .sow-rail-item, {{WRAPPER}} .sow-spotlight-card',
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => __( 'Card padding', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .sow-rail-item .sow-rail-item__body, {{WRAPPER}} .sow-spotlight-card .body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->register_animation_controls( array( 'stagger' => true ) );
	}

	/**
	 * Collect items for render.
	 *
	 * @param array $settings Settings.
	 * @return array
	 */
	protected function collect_items( $settings ) {
		$items = array();
		if ( isset( $settings['source'] ) && 'manual' === $settings['source'] && ! empty( $settings['items'] ) ) {
			foreach ( $settings['items'] as $row ) {
				$items[] = array(
					'title'   => isset( $row['title'] ) ? $row['title'] : '',
					'excerpt' => isset( $row['excerpt'] ) ? $row['excerpt'] : '',
					'date'    => isset( $row['date'] ) ? $row['date'] : '',
					'pill'    => isset( $row['pill'] ) ? $row['pill'] : '',
					'url'     => ! empty( $row['link']['url'] ) ? $row['link']['url'] : '',
					'image'   => ! empty( $row['image']['url'] ) ? $row['image']['url'] : '',
				);
			}
			return $items;
		}

		$query = new \WP_Query( $this->build_query_args( $settings, 'query' ) );
		while ( $query->have_posts() ) {
			$query->the_post();
			$cats = get_the_category();
			$items[] = array(
				'title'   => get_the_title(),
				'excerpt' => wp_trim_words( get_the_excerpt(), 18, '…' ),
				'date'    => function_exists( 'syria_oil_format_post_date' ) ? syria_oil_format_post_date() : get_the_date( 'j F Y' ),
				'pill'    => ! empty( $cats[0] ) ? $cats[0]->name : '',
				'url'     => get_permalink(),
				'image'   => get_the_post_thumbnail_url( get_the_ID(), 'medium_large' ),
			);
		}
		wp_reset_postdata();
		return $items;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$items    = $this->collect_items( $settings );
		if ( empty( $items ) ) {
			return;
		}

		$layout = isset( $settings['layout'] ) ? $settings['layout'] : 'rail';

		if ( 'spotlight' === $layout ) {
			$this->render_spotlight( $settings, $items );
			return;
		}

		echo '<section class="sow-rail">';
		foreach ( $items as $i => $item ) {
			$url       = ! empty( $item['url'] ) ? $item['url'] : '#';
			$has_image = ! empty( $item['image'] );
			$title     = isset( $item['title'] ) ? $item['title'] : '';
			echo '<article class="sow-rail-item' . ( $has_image ? '' : ' sow-rail-item--no-media' ) . '"' . $this->sor_animate_attrs( $settings, (int) $i ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<a href="' . esc_url( $url ) . '">';
			if ( $has_image ) {
				printf( '<img src="%s" alt="%s" loading="lazy" decoding="async" />', esc_url( $item['image'] ), esc_attr( $title ) );
			} else {
				echo '<span class="sow-rail-item__thumb" aria-hidden="true"></span>';
			}
			echo '<div class="sow-rail-item__body">';
			if ( ! empty( $item['pill'] ) ) {
				echo '<span class="sow-rail-item__badge">' . esc_html( $item['pill'] ) . '</span>';
			}
			if ( ! empty( $item['date'] ) ) {
				echo '<span class="sow-rail-item__date">' . esc_html( $item['date'] ) . '</span>';
			}
			echo '<h2>' . esc_html( $title ? $title : __( '(Untitled)', 'syria-oil-widgets' ) ) . '</h2>';
			echo '</div></a></article>';
		}
		echo '</section>';
	}

	/**
	 * Spotlight layout.
	 *
	 * @param array $settings Settings.
	 * @param array $items    Items.
	 */
	protected function render_spotlight( $settings, $items ) {
		$heading = isset( $settings['heading'] ) ? $settings['heading'] : '';
		$eyebrow = isset( $settings['eyebrow'] ) ? $settings['eyebrow'] : '';
		?>
		<section class="sow-spotlight">
			<?php if ( $heading || $eyebrow ) : ?>
				<div class="sow-spotlight__head">
					<?php if ( $eyebrow ) : ?>
						<div class="eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
					<?php endif; ?>
					<?php if ( $heading ) : ?>
						<h2><?php echo esc_html( $heading ); ?></h2>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="sow-spotlight__grid">
				<?php foreach ( $items as $i => $item ) : ?>
					<?php $url = ! empty( $item['url'] ) ? $item['url'] : '#'; ?>
					<a class="sow-spotlight-card" href="<?php echo esc_url( $url ); ?>"<?php echo $this->sor_animate_attrs( $settings, (int) $i ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<div class="media<?php echo empty( $item['image'] ) ? ' media--fallback' : ''; ?>">
							<?php if ( ! empty( $item['image'] ) ) : ?>
								<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy" decoding="async" />
							<?php endif; ?>
							<?php if ( ! empty( $item['pill'] ) ) : ?>
								<span class="pill"><?php echo esc_html( $item['pill'] ); ?></span>
							<?php endif; ?>
						</div>
						<div class="body">
							<?php if ( ! empty( $item['date'] ) ) : ?>
								<div class="meta"><?php echo esc_html( $item['date'] ); ?></div>
							<?php endif; ?>
							<h3><?php echo esc_html( ! empty( $item['title'] ) ? $item['title'] : __( '(Untitled)', 'syria-oil-widgets' ) ); ?></h3>
							<?php if ( ! empty( $item['excerpt'] ) ) : ?>
								<p class="excerpt"><?php echo esc_html( $item['excerpt'] ); ?></p>
							<?php endif; ?>
							<span class="more"><?php esc_html_e( 'Read more', 'syria-oil-widgets' ); ?></span>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
