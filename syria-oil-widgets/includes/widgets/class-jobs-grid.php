<?php
/**
 * Jobs Grid Elementor widget.
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
 * Jobs Grid.
 */
class Jobs_Grid extends Widget_Base {

	use Animation_Controls_Trait;

	public function get_name() {
		return 'syria_oil_jobs_grid';
	}

	public function get_title() {
		return __( 'Jobs Grid', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-person';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_style_depends() {
		$deps = array( 'syria-oil-jobs-grid' );
		if ( defined( 'SYRIA_OIL_URI' ) ) {
			$deps[] = 'syria-oil-careers';
		}
		return array_merge( $deps, $this->get_animation_style_depends() );
	}

	public function get_script_depends() {
		return $this->get_animation_script_depends();
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'syria-oil-widgets' ) ) );

		$this->add_control(
			'show_header',
			array(
				'label'        => __( 'Show heading', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'syria-oil-widgets' ),
				'label_off'    => __( 'No', 'syria-oil-widgets' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'heading',
			array(
				'label'     => __( 'Heading', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'فرص العمل المتاحة', 'syria-oil-widgets' ),
				'condition' => array( 'show_header' => 'yes' ),
			)
		);
		$this->add_control(
			'subtitle',
			array(
				'label'     => __( 'Subtitle', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXTAREA,
				'rows'      => 2,
				'default'   => '',
				'condition' => array( 'show_header' => 'yes' ),
			)
		);
		$this->add_control(
			'show_benefits',
			array(
				'label'        => __( 'Show Theme Options benefits blurb', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);
		$this->add_control(
			'posts_count',
			array(
				'label'   => __( 'Number of jobs', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 6,
				'min'     => 1,
				'max'     => 24,
			)
		);
		$this->add_control(
			'open_only',
			array(
				'label'        => __( 'Open jobs only', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'featured_only',
			array(
				'label'        => __( 'Featured only', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);
		$this->add_control(
			'for_page',
			array(
				'label'        => __( 'Careers page listings only', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'columns',
			array(
				'label'   => __( 'Columns', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '3',
				'options' => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
				),
			)
		);
		$this->add_control(
			'view_all_text',
			array(
				'label'   => __( 'View all label', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'كل الفرص', 'syria-oil-widgets' ),
			)
		);
		$this->add_control(
			'view_all_link',
			array(
				'label' => __( 'View all URL', 'syria-oil-widgets' ),
				'type'  => Controls_Manager::URL,
			)
		);
		$this->add_control(
			'empty_text',
			array(
				'label'   => __( 'Empty state', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'لا توجد فرص عمل متاحة حالياً.', 'syria-oil-widgets' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_style', array( 'label' => __( 'Style', 'syria-oil-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'heading_typo', 'selector' => '{{WRAPPER}} .section-head h2' ) );
		$this->end_controls_section();

		$this->register_animation_controls();
	}

	protected function render() {
		if ( function_exists( 'syria_oil_careers_enabled' ) && ! syria_oil_careers_enabled() ) {
			if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p class="text-muted">' . esc_html__( 'Careers module is disabled in Theme Options.', 'syria-oil-widgets' ) . '</p>';
			}
			return;
		}

		$settings = $this->get_settings_for_display();
		$limit    = ! empty( $settings['posts_count'] ) ? absint( $settings['posts_count'] ) : 6;
		if ( function_exists( 'syria_oil_careers_show_home_teaser' ) && ! empty( $settings['featured_only'] ) && 'yes' === $settings['featured_only'] ) {
			if ( ! syria_oil_careers_show_home_teaser() && ! ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) ) {
				return;
			}
			if ( function_exists( 'syria_oil_mod' ) ) {
				$home_limit = absint( syria_oil_mod( 'careers_home_limit', 3 ) );
				if ( $home_limit > 0 ) {
					$limit = $home_limit;
				}
			}
		}

		$jobs = function_exists( 'syria_oil_get_jobs' )
			? syria_oil_get_jobs(
				array(
					'limit'         => $limit,
					'open_only'     => ( empty( $settings['open_only'] ) || 'yes' === $settings['open_only'] ),
					'featured_only' => ( ! empty( $settings['featured_only'] ) && 'yes' === $settings['featured_only'] ),
					'for_page'      => ( ! empty( $settings['for_page'] ) && 'yes' === $settings['for_page'] ),
				)
			)
			: array();

		$cols = isset( $settings['columns'] ) ? absint( $settings['columns'] ) : 3;
		if ( $cols < 1 || $cols > 3 ) {
			$cols = 3;
		}

		$anim = $this->sor_animate_attrs( $settings, 0 );
		echo '<div class="so-jobs sow-jobs-grid"' . $anim . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( ! empty( $settings['show_header'] ) && 'yes' === $settings['show_header'] ) {
			echo '<div class="section-head"><div>';
			if ( ! empty( $settings['heading'] ) ) {
				echo '<h2>' . esc_html( $settings['heading'] ) . '</h2>';
			}
			if ( ! empty( $settings['subtitle'] ) ) {
				echo '<p class="lede text-muted">' . esc_html( $settings['subtitle'] ) . '</p>';
			}
			echo '</div>';
			$view_url = ! empty( $settings['view_all_link']['url'] ) ? $settings['view_all_link']['url'] : '';
			if ( ! $view_url ) {
				$page = get_page_by_path( 'careers' );
				$view_url = $page ? get_permalink( $page ) : '';
			}
			if ( $view_url && ! empty( $settings['view_all_text'] ) ) {
				printf( '<a class="btn btn-secondary" href="%1$s">%2$s</a>', esc_url( $view_url ), esc_html( $settings['view_all_text'] ) );
			}
			echo '</div>';
		}

		if ( ! empty( $settings['show_benefits'] ) && 'yes' === $settings['show_benefits'] && function_exists( 'syria_oil_mod' ) ) {
			$benefits = trim( (string) syria_oil_mod( 'careers_benefits', '' ) );
			if ( $benefits ) {
				$lines = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $benefits ) ) );
				echo '<div class="so-careers-benefits">';
				foreach ( $lines as $line ) {
					echo '<p>' . esc_html( $line ) . '</p>';
				}
				echo '</div>';
			}
		}

		if ( empty( $jobs ) ) {
			$empty = ! empty( $settings['empty_text'] ) ? $settings['empty_text'] : __( 'لا توجد فرص عمل متاحة حالياً.', 'syria-oil-widgets' );
			echo '<p class="so-jobs__empty text-muted text-center">' . esc_html( $empty ) . '</p>';
		} else {
			echo '<div class="so-jobs__grid so-job-card-grid" style="--so-jobs-cols:' . esc_attr( (string) $cols ) . '">';
			foreach ( $jobs as $i => $job ) {
				if ( function_exists( 'syria_oil_render_job_card' ) ) {
					syria_oil_render_job_card( $job );
				}
			}
			echo '</div>';
		}

		echo '</div>';
	}
}
