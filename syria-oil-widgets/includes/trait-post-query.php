<?php
/**
 * Shared WP_Query controls + arg builder for Syria Oil widgets.
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

/**
 * Trait providing reusable query controls and a WP_Query args builder.
 */
trait Post_Query_Trait {

	/**
	 * Register a standard set of query controls inside an open section.
	 *
	 * @param string $prefix    Control ID prefix to avoid collisions.
	 * @param array  $condition Optional Elementor condition applied to every control.
	 */
	protected function register_query_controls( $prefix = 'query', $condition = array() ) {
		$this->add_control(
			$prefix . '_post_type',
			array(
				'label'     => __( 'Source', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'post',
				'options'   => $this->syria_oil_get_post_types(),
				'condition' => $condition,
			)
		);

		$this->add_control(
			$prefix . '_categories',
			array(
				'label'       => __( 'Categories', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->syria_oil_get_category_options(),
				'description' => __( 'Leave empty to include all categories.', 'syria-oil-widgets' ),
				'condition'   => $condition,
			)
		);

		$this->add_control(
			$prefix . '_posts_per_page',
			array(
				'label'     => __( 'Number of posts', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => function_exists( 'syria_oil_widgets_default' )
					? syria_oil_widgets_default( $this->get_name(), 'query_posts_per_page', 6 )
					: 6,
				'min'       => 1,
				'max'       => 24,
				'condition' => $condition,
			)
		);

		$this->add_control(
			$prefix . '_orderby',
			array(
				'label'     => __( 'Order by', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'date',
				'options'   => array(
					'date'       => __( 'Date', 'syria-oil-widgets' ),
					'title'      => __( 'Title', 'syria-oil-widgets' ),
					'menu_order' => __( 'Menu order', 'syria-oil-widgets' ),
					'rand'       => __( 'Random', 'syria-oil-widgets' ),
					'comment_count' => __( 'Most commented', 'syria-oil-widgets' ),
				),
				'condition' => $condition,
			)
		);

		$this->add_control(
			$prefix . '_order',
			array(
				'label'     => __( 'Order', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'DESC',
				'options'   => array(
					'DESC' => __( 'Descending', 'syria-oil-widgets' ),
					'ASC'  => __( 'Ascending', 'syria-oil-widgets' ),
				),
				'condition' => $condition,
			)
		);

		$this->add_control(
			$prefix . '_offset',
			array(
				'label'     => __( 'Offset', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 0,
				'min'       => 0,
				'condition' => $condition,
			)
		);
	}

	/**
	 * Build WP_Query args from widget settings.
	 *
	 * Always skips SQL_CALC_FOUND_ROWS (no pagination in widgets).
	 * Optionally skips term/meta caches when the caller does not need them.
	 *
	 * @param array  $settings Widget settings.
	 * @param string $prefix   Control ID prefix used in register_query_controls().
	 * @param array  $extra    Extra WP_Query args (cache flags, etc.).
	 * @return array
	 */
	protected function build_query_args( $settings, $prefix = 'query', $extra = array() ) {
		$post_type = ! empty( $settings[ $prefix . '_post_type' ] ) ? sanitize_key( $settings[ $prefix . '_post_type' ] ) : 'post';
		$orderby   = ! empty( $settings[ $prefix . '_orderby' ] ) ? sanitize_key( $settings[ $prefix . '_orderby' ] ) : 'date';
		$order     = ! empty( $settings[ $prefix . '_order' ] ) && 'ASC' === strtoupper( $settings[ $prefix . '_order' ] ) ? 'ASC' : 'DESC';

		$args = array(
			'post_type'              => $post_type,
			'posts_per_page'         => ! empty( $settings[ $prefix . '_posts_per_page' ] ) ? absint( $settings[ $prefix . '_posts_per_page' ] ) : 6,
			'orderby'                => $orderby,
			'order'                  => $order,
			'offset'                 => ! empty( $settings[ $prefix . '_offset' ] ) ? absint( $settings[ $prefix . '_offset' ] ) : 0,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => true,
		);

		if ( ! empty( $settings[ $prefix . '_categories' ] ) ) {
			$args['category__in'] = array_map( 'absint', (array) $settings[ $prefix . '_categories' ] );
		}

		return array_merge( $args, $extra );
	}

	/**
	 * Public post types available as widget sources.
	 *
	 * @return array
	 */
	protected function syria_oil_get_post_types() {
		$types  = get_post_types( array( 'public' => true ), 'objects' );
		$output = array();
		foreach ( $types as $type ) {
			if ( 'attachment' === $type->name ) {
				continue;
			}
			$output[ $type->name ] = $type->label;
		}
		return $output;
	}

	/**
	 * Category options keyed by term ID.
	 *
	 * @return array
	 */
	protected function syria_oil_get_category_options() {
		$options    = array();
		$categories = get_categories( array( 'hide_empty' => false ) );
		foreach ( $categories as $category ) {
			$options[ $category->term_id ] = $category->name;
		}
		return $options;
	}
}
