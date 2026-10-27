<?php
/**
 * Category Sections Elementor widget — one section per category with layout variants.
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
 * Category Sections.
 */
class Category_Sections extends Widget_Base {

	use Animation_Controls_Trait;
	use News_Card_Style_Controls_Trait;
	use News_Card_Slider_Controls_Trait;

	public function get_name() {
		return 'syria_oil_category_sections';
	}

	public function get_title() {
		return __( 'Category Sections', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-posts-group';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_style_depends() {
		return array_merge( array( 'syria-oil-category-sections', 'syria-oil-news-card' ), $this->get_animation_style_depends() );
	}

	public function get_script_depends() {
		return array_merge( array( 'syria-oil-category-rail-slider' ), $this->get_animation_script_depends() );
	}

	/**
	 * Output slider prev/next arrow markup.
	 *
	 * @param string $dir prev|next.
	 */
	protected function news_card_slider_arrow( $dir ) {
		$is_prev = ( 'prev' === $dir );
		$label   = $is_prev ? __( 'Previous', 'syria-oil-widgets' ) : __( 'Next', 'syria-oil-widgets' );
		$rtl     = function_exists( 'is_rtl' ) && is_rtl();
		if ( $rtl ) {
			$glyph = $is_prev ? '&rsaquo;' : '&lsaquo;';
		} else {
			$glyph = $is_prev ? '&lsaquo;' : '&rsaquo;';
		}
		printf(
			'<button type="button" class="so-news-card-slider__arrow so-news-card-slider__arrow--%1$s sow-cat-rail-slider__arrow sow-cat-rail-slider__arrow--%1$s" aria-label="%2$s"><span aria-hidden="true">%3$s</span></button>',
			esc_attr( $dir ),
			esc_attr( $label ),
			$glyph // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static entity.
		);
	}

	/**
	 * Open a multi-card slider shell.
	 *
	 * @param string              $track_class Track element classes.
	 * @param string              $modifier    Optional root modifier class.
	 * @param array<string,mixed> $settings    Widget settings for data attrs.
	 */
	protected function news_card_slider_open( $track_class, $modifier = '', $settings = array() ) {
		$root_class = 'so-news-card-slider sow-cat-rail-slider';
		if ( $modifier ) {
			$root_class .= ' ' . sanitize_html_class( $modifier );
		}
		echo '<div class="' . esc_attr( $root_class ) . '"' . $this->news_card_slider_data_attrs( $settings ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$this->news_card_slider_arrow( 'prev' );
		echo '<div class="' . esc_attr( $track_class ) . ' so-news-card-slider__track">';
	}

	/**
	 * Close slider shell (track, next arrow, dots).
	 */
	protected function news_card_slider_close() {
		echo '</div>';
		$this->news_card_slider_arrow( 'next' );
		echo '<div class="so-news-card-slider__dots" aria-hidden="true"></div>';
		echo '</div>';
	}

	/**
	 * Display layout choices (Elementor select).
	 *
	 * @return array<string,string>
	 */
	protected function layout_options() {
		return array(
			'grid'     => __( 'خيار ١ — بطاقتان (شبكة)', 'syria-oil-widgets' ),
			'list'     => __( 'خيار ٢ — قائمة أفقية', 'syria-oil-widgets' ),
			'magazine' => __( 'خيار ٣ — مجلة (كبير + جانبي)', 'syria-oil-widgets' ),
			'rail'     => __( 'خيار ٤ — سكة تمرير', 'syria-oil-widgets' ),
			'video'    => __( 'خيار ٥ — فيديو (٣ أعمدة)', 'syria-oil-widgets' ),
			'gallery'  => __( 'خيار ٦ — معرض (٣ أعمدة)', 'syria-oil-widgets' ),
		);
	}

	/**
	 * Default post count per layout when row override is empty.
	 *
	 * @param string $layout Layout key.
	 * @return int
	 */
	protected function default_post_count_for_layout( $layout ) {
		$map = array(
			'grid'     => 2,
			'list'     => 4,
			'magazine' => 3,
			'rail'     => 6,
			'video'    => 3,
			'gallery'  => 3,
		);
		return isset( $map[ $layout ] ) ? (int) $map[ $layout ] : 2;
	}

	/**
	 * Sanitize layout key.
	 *
	 * @param string $layout Raw layout.
	 * @return string
	 */
	protected function sanitize_layout( $layout ) {
		$options = $this->layout_options();
		return isset( $options[ $layout ] ) ? $layout : 'grid';
	}

	/**
	 * Default portal sections with varied layouts.
	 *
	 * @return array<int,array<string,string>>
	 */
	protected function default_sections() {
		if ( function_exists( 'syria_oil_widget_category_map' ) && function_exists( 'syria_oil_elementor_category_sections_rows' ) ) {
			$map = syria_oil_widget_category_map();
			$rows = syria_oil_elementor_category_sections_rows( $map['homepage_sections'] ?? array() );
			if ( ! empty( $rows ) ) {
				return $rows;
			}
		}
		return array(
			array(
				'title'              => 'أخبار سوريا',
				'cat_name'           => 'أخبار سوريا',
				'secondary_cat_name' => '',
				'text'               => '',
				'layout'             => 'magazine',
				'posts_count'        => '',
			),
			array(
				'title'              => 'نفط وغاز عالمي',
				'cat_name'           => 'أخبار النفط والغاز العالمي',
				'secondary_cat_name' => '',
				'text'               => '',
				'layout'             => 'list',
				'posts_count'        => '',
			),
			array(
				'title'              => 'نفط وغاز عربي',
				'cat_name'           => 'أخبار النفط والغاز العربي',
				'secondary_cat_name' => '',
				'text'               => '',
				'layout'             => 'rail',
				'posts_count'        => '',
			),
			array(
				'title'              => 'المحروقات',
				'cat_name'           => 'المحروقات',
				'secondary_cat_name' => '',
				'text'               => '',
				'layout'             => 'video',
				'posts_count'        => '',
			),
			array(
				'title'              => 'قرارات',
				'cat_name'           => 'قرارات وأخبار وزارة النفط',
				'secondary_cat_name' => '',
				'text'               => '',
				'layout'             => 'list',
				'posts_count'        => '',
			),
			array(
				'title'              => 'الشركات',
				'cat_name'           => 'الشركات العاملة في سوريا',
				'secondary_cat_name' => 'أخبار شركات وزارة النفط',
				'text'               => '',
				'layout'             => 'gallery',
				'posts_count'        => '',
			),
			array(
				'title'              => 'مشاريع',
				'cat_name'           => 'أخبار المشاريع النفطية',
				'secondary_cat_name' => '',
				'text'               => '',
				'layout'             => 'magazine',
				'posts_count'        => '',
			),
			array(
				'title'              => 'طاقة الغد',
				'cat_name'           => 'أخبار الكهرباء والطاقة البديلة',
				'secondary_cat_name' => '',
				'text'               => '',
				'layout'             => 'grid',
				'posts_count'        => '',
			),
		);
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Sections', 'syria-oil-widgets' ) ) );

		$rep = new Repeater();
		$rep->add_control(
			'title',
			array(
				'label'   => __( 'Section title', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Category', 'syria-oil-widgets' ),
				'dynamic' => array( 'active' => true ),
			)
		);
		$rep->add_control(
			'layout',
			array(
				'label'   => __( 'طريقة العرض', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => $this->layout_options(),
			)
		);
		$rep->add_control(
			'posts_count',
			array(
				'label'       => __( 'عدد المقالات (اختياري)', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 12,
				'description' => __( 'اتركه 0 لاستخدام العدد الافتراضي لطريقة العرض.', 'syria-oil-widgets' ),
			)
		);
		$rep->add_control(
			'text',
			array(
				'label'   => __( 'Subtitle (optional)', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'dynamic' => array( 'active' => true ),
			)
		);
		$rep->add_control(
			'cat_name',
			array(
				'label'       => __( 'WordPress category name', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'Exact category name used to load posts.', 'syria-oil-widgets' ),
				'dynamic'     => array( 'active' => true ),
			)
		);
		$rep->add_control(
			'secondary_cat_name',
			array(
				'label'       => __( 'Second category (optional merge)', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'When set, posts from both categories are merged (e.g. operating + ministry companies).', 'syria-oil-widgets' ),
				'dynamic'     => array( 'active' => true ),
			)
		);
		$rep->add_control(
			'category_id',
			array(
				'label'       => __( 'Or category ID', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'description' => __( 'Optional. Overrides category name when set.', 'syria-oil-widgets' ),
			)
		);
		$rep->add_control(
			'link',
			array(
				'label'   => __( 'View all link (optional)', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'sections',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'default'     => $this->default_sections(),
				'title_field' => '{{{ title }}}',
			)
		);

		$this->add_control(
			'posts_per_section',
			array(
				'label'       => __( 'Posts per section (fallback)', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 2,
				'min'         => 1,
				'max'         => 12,
				'description' => __( 'Used only when a row has no layout default and posts count is 0.', 'syria-oil-widgets' ),
			)
		);

		$this->add_control(
			'view_all_text',
			array(
				'label'   => __( 'View all label', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'عرض الكل', 'syria-oil-widgets' ),
			)
		);

		$this->end_controls_section();

		$this->register_news_card_slider_controls();

		$this->start_controls_section( 'section_style', array( 'label' => __( 'Style', 'syria-oil-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typo', 'selector' => '{{WRAPPER}} .sow-cat-sec__head h3' ) );
		$this->end_controls_section();

		$this->register_news_card_style_controls(
			array(
				'wrapper'        => '.sow-cat-sections',
				'grid_selectors' => '{{WRAPPER}} .sow-cat-sec__grid, {{WRAPPER}} .sow-cat-sec__list, {{WRAPPER}} .sow-cat-sec__magazine, {{WRAPPER}} .sow-cat-sec__magazine-side, {{WRAPPER}} .sow-cat-sec__rail',
			)
		);

		$this->register_animation_controls();
	}

	/**
	 * Resolve a WP category term from section settings.
	 *
	 * @param array $row Section row.
	 * @return \WP_Term|null
	 */
	protected function resolve_term( $row ) {
		if ( ! empty( $row['category_id'] ) ) {
			$term = get_term( absint( $row['category_id'] ), 'category' );
			if ( $term && ! is_wp_error( $term ) ) {
				return $term;
			}
		}

		$names = array();
		if ( ! empty( $row['cat_name'] ) ) {
			$names[] = $row['cat_name'];
		}
		if ( ! empty( $row['title'] ) ) {
			$names[] = $row['title'];
		}

		foreach ( $names as $name ) {
			if ( function_exists( 'syria_oil_widgets_resolve_category' ) ) {
				$term = syria_oil_widgets_resolve_category( $name );
				if ( $term ) {
					return $term;
				}
			}
			$term = get_term_by( 'name', $name, 'category' );
			if ( $term && ! is_wp_error( $term ) ) {
				return $term;
			}
		}

		return null;
	}

	/**
	 * Posts count for a section row.
	 *
	 * @param array  $row           Repeater row.
	 * @param string $layout        Layout key.
	 * @param int    $global_fallback Widget-wide fallback.
	 * @return int
	 */
	protected function posts_count_for_row( $row, $layout, $global_fallback ) {
		if ( isset( $row['posts_count'] ) && '' !== $row['posts_count'] && absint( $row['posts_count'] ) > 0 ) {
			return absint( $row['posts_count'] );
		}
		$default = $this->default_post_count_for_layout( $layout );
		return $default > 0 ? $default : max( 1, absint( $global_fallback ) );
	}

	/**
	 * Query posts for a category term.
	 *
	 * @param \WP_Term $term  Category.
	 * @param int      $count Max posts.
	 * @return \WP_Post[]
	 */
	protected function query_posts( $term, $count, $extra_terms = array() ) {
		$term_ids = array();
		if ( $term && ! is_wp_error( $term ) ) {
			$term_ids[] = (int) $term->term_id;
		}
		foreach ( (array) $extra_terms as $extra ) {
			if ( $extra && ! is_wp_error( $extra ) ) {
				$term_ids[] = (int) $extra->term_id;
			}
		}
		$term_ids = array_values( array_unique( array_filter( $term_ids ) ) );
		if ( empty( $term_ids ) ) {
			return array();
		}

		$args = array(
			'posts_per_page'         => $count,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
		);
		if ( 1 === count( $term_ids ) ) {
			$args['cat'] = $term_ids[0];
		} else {
			$args['category__in'] = $term_ids;
		}

		$query = new \WP_Query( $args );
		$posts = $query->posts;
		wp_reset_postdata();
		return is_array( $posts ) ? $posts : array();
	}

	/**
	 * News card args for a layout.
	 *
	 * @param string $layout  Layout.
	 * @param string $anim    Animation attrs.
	 * @param string $variant lead|side|default.
	 * @param int    $index   Card index in section (lazy-load hint).
	 * @return array<string,mixed>
	 */
	protected function news_card_args_for_layout( $layout, $anim, $variant = 'default', $index = 0 ) {
		$args = array(
			'variant'        => 'stack',
			'show_badge'     => true,
			'show_date'      => true,
			'show_excerpt'   => false,
			'excerpt_words'  => 18,
			'image_size'     => 'syria-oil-card',
			'image_loading'  => 0 === $index ? 'eager' : 'lazy',
			'title_lines'    => 2,
			'animate_attrs'  => $anim,
			'extra_class'    => array(),
		);

		switch ( $layout ) {
			case 'list':
				$args['variant'] = 'row';
				break;
			case 'magazine':
				if ( 'lead' === $variant ) {
					$args['variant']       = 'featured';
					$args['show_excerpt']  = true;
					$args['excerpt_words'] = 24;
					$args['title_lines']   = 3;
					$args['image_loading'] = 'eager';
				} else {
					$args['variant'] = 'row-compact';
				}
				break;
			case 'video':
				$args['extra_class'][] = 'so-news-card--video';
				break;
			default:
				break;
		}

		return $args;
	}

	/**
	 * Render unified news card for the current post in the loop.
	 *
	 * @param string              $layout  Layout key.
	 * @param string              $anim    Animation attrs.
	 * @param string              $variant lead|side|default.
	 * @param int                 $index   Card index.
	 */
	protected function render_news_card( $layout, $anim, $variant = 'default', $index = 0 ) {
		$syria_oil_news_card_args = $this->news_card_args_for_layout( $layout, $anim, $variant, $index );
		include SYRIA_OIL_WIDGETS_DIR . '/includes/views/news-card.php';
	}

	/**
	 * @deprecated Use render_news_card().
	 * @param array<string,mixed> $args Card args.
	 */
	protected function render_card( $args ) {
		$syria_oil_card_args = $args;
		include SYRIA_OIL_WIDGETS_DIR . '/includes/views/card.php';
	}

	/**
	 * Render posts body for a layout.
	 *
	 * @param string     $layout   Layout.
	 * @param \WP_Post[] $posts    Posts.
	 * @param array      $settings Widget settings.
	 * @param string     $read_more Read more text.
	 */
	protected function render_layout_posts( $layout, $posts, $settings, $read_more ) {
		if ( empty( $posts ) ) {
			return;
		}

		if ( 'magazine' === $layout ) {
			$lead = array_shift( $posts );
			echo '<div class="sow-cat-sec__magazine">';
			echo '<div class="sow-cat-sec__magazine-lead">';
			if ( $lead ) {
				global $post;
				$post = $lead; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $post );
				$this->render_news_card( $layout, $this->sor_animate_attrs( $settings, 0 ), 'lead', 0 );
				wp_reset_postdata();
			}
			echo '</div>';
			if ( ! empty( $posts ) ) {
				echo '<div class="sow-cat-sec__magazine-side">';
				$i = 1;
				foreach ( $posts as $item ) {
					global $post;
					$post = $item; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					setup_postdata( $post );
					$this->render_news_card( $layout, $this->sor_animate_attrs( $settings, $i ), 'side', $i );
					wp_reset_postdata();
					++$i;
				}
				echo '</div>';
			}
			echo '</div>';
			return;
		}

		if ( 'rail' === $layout ) {
			$this->news_card_slider_open( 'sow-cat-sec__rail', '', $settings );
			$i = 0;
			foreach ( $posts as $item ) {
				global $post;
				$post = $item; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $post );
				$this->render_news_card( $layout, $this->sor_animate_attrs( $settings, $i ), 'default', $i );
				wp_reset_postdata();
				++$i;
			}
			$this->news_card_slider_close();
			return;
		}

		$wrap_class = 'sow-cat-sec__grid so-grid';
		if ( 'list' === $layout ) {
			$wrap_class = 'sow-cat-sec__list';
		}

		$use_slider = count( $posts ) > 1 && in_array( $layout, array( 'grid', 'list', 'video', 'gallery' ), true );
		$slider_mode = $use_slider ? $this->resolve_news_card_slider_mode( $settings, count( $posts ) ) : 'none';

		if ( 'slider' === $slider_mode ) {
			$this->news_card_slider_open( $wrap_class, 'so-news-card-slider--responsive', $settings );
		} elseif ( 'pair' === $slider_mode ) {
			echo '<div class="' . esc_attr( $wrap_class ) . ' so-card-grid--pair-mobile">';
		} else {
			echo '<div class="' . esc_attr( $wrap_class ) . '">';
		}
		$i = 0;
		foreach ( $posts as $item ) {
			global $post;
			$post = $item; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			setup_postdata( $post );
			$this->render_news_card( $layout, $this->sor_animate_attrs( $settings, $i ), 'default', $i );
			wp_reset_postdata();
			++$i;
		}
		if ( 'slider' === $slider_mode ) {
			$this->news_card_slider_close();
		} else {
			echo '</div>';
		}
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$sections = ! empty( $settings['sections'] ) ? $settings['sections'] : array();
		if ( empty( $sections ) ) {
			return;
		}

		$global_per = ! empty( $settings['posts_per_section'] ) ? absint( $settings['posts_per_section'] ) : 2;
		$view_txt   = ! empty( $settings['view_all_text'] ) ? $settings['view_all_text'] : __( 'عرض الكل', 'syria-oil-widgets' );
		$read_more  = __( 'اقرأ المزيد', 'syria-oil-widgets' );
		$skip_featured = function_exists( 'syria_oil_show_featured_category_section' ) && ! syria_oil_show_featured_category_section();
		$featured_labels = function_exists( 'syria_oil_featured_category_labels' ) ? syria_oil_featured_category_labels() : array( 'أخبار بارزة' );

		$scope = '.elementor-element-' . $this->get_id();
		$this->print_news_card_slider_breakpoint_css( $settings, $scope );

		echo '<div class="sow-cat-sections">';
		$si = 0;
		foreach ( $sections as $row ) {
			$title    = isset( $row['title'] ) ? $row['title'] : '';
			$cat_name = isset( $row['cat_name'] ) ? $row['cat_name'] : '';
			if ( $skip_featured && ( in_array( $title, $featured_labels, true ) || in_array( $cat_name, $featured_labels, true ) ) ) {
				continue;
			}

			$layout = $this->sanitize_layout( isset( $row['layout'] ) ? (string) $row['layout'] : 'grid' );
			$term   = $this->resolve_term( $row );
			$text   = isset( $row['text'] ) ? $row['text'] : '';
			$url    = ! empty( $row['link']['url'] ) ? $row['link']['url'] : '';

			if ( $term && ! $url ) {
				$link = get_term_link( $term );
				if ( ! is_wp_error( $link ) ) {
					$url = $link;
				}
			}

			$count = $this->posts_count_for_row( $row, $layout, $global_per );
			$secondary = null;
			if ( ! empty( $row['secondary_cat_name'] ) ) {
				$secondary = $this->resolve_term( array( 'cat_name' => $row['secondary_cat_name'] ) );
			}
			$posts = ( $term || $secondary ) ? $this->query_posts( $term, $count, $secondary ? array( $secondary ) : array() ) : array();

			$anim = $this->sor_animate_attrs( $settings, $si );
			++$si;

			$section_class = 'sow-cat-sec sow-cat-sec--layout-' . $layout;
			if ( $si % 2 === 0 ) {
				$section_class .= ' sow-cat-sec--rhythm-alt';
			}
			echo '<section class="' . esc_attr( $section_class ) . '"' . $anim . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="sow-cat-sec__head">';
			echo '<div class="sow-cat-sec__titles">';
			echo '<h2 class="sow-cat-sec__heading">' . esc_html( $title ) . '</h2>';
			if ( $text ) {
				echo '<p>' . esc_html( $text ) . '</p>';
			}
			if ( $url ) {
				echo '<a class="btn btn-secondary btn-sm sow-cat-sec__more" href="' . esc_url( $url ) . '">' . esc_html( $view_txt ) . '</a>';
			}
			echo '</div></div>';

			if ( ! empty( $posts ) ) {
				$this->render_layout_posts( $layout, $posts, $settings, $read_more );
			} elseif ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p class="text-muted">' . esc_html__( 'No posts in this category yet.', 'syria-oil-widgets' ) . '</p>';
			}

			echo '</section>';
		}
		echo '</div>';
	}
}
