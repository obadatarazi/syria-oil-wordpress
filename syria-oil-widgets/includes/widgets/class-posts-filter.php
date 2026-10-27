<?php
/**
 * Posts Filter Hub — featured category cards + chips + filtered post grid.
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Interactive Arabic category filters for posts.
 */
class Posts_Filter extends Widget_Base {

	public function get_name() {
		return 'syria_oil_posts_filter';
	}

	public function get_title() {
		return __( 'Posts Filter', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-filter';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_style_depends() {
		return array( 'syria-oil-posts-filter', 'syria-oil-article-grid' );
	}

	public function get_script_depends() {
		return array( 'syria-oil-posts-filter' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'syria-oil-widgets' ) ) );

		$this->add_control(
			'heading',
			array(
				'label'   => __( 'Heading', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'تصفح حسب التصنيف', 'syria-oil-widgets' ),
			)
		);

		$this->add_control(
			'show_featured',
			array(
				'label'        => __( 'Show featured category cards', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'featured_count',
			array(
				'label'     => __( 'Featured cards', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 4,
				'min'       => 2,
				'max'       => 8,
				'condition' => array( 'show_featured' => 'yes' ),
			)
		);

		$this->add_control(
			'show_chips',
			array(
				'label'        => __( 'Show category chips', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'chip_limit',
			array(
				'label'     => __( 'Max chips', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 16,
				'min'       => 4,
				'max'       => 40,
				'condition' => array( 'show_chips' => 'yes' ),
			)
		);

		$this->add_control(
			'posts_per_page',
			array(
				'label'   => __( 'Posts to show', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 9,
				'min'     => 3,
				'max'     => 24,
			)
		);

		$this->add_control(
			'empty_text',
			array(
				'label'   => __( 'Empty state', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'لا توجد مقالات في هذا التصنيف حالياً.', 'syria-oil-widgets' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Curated Arabic blurbs for known syria-oil categories.
	 *
	 * @return array<string,string>
	 */
	private function category_blurbs() {
		if ( function_exists( 'syria_oil_production_category_blurbs' ) ) {
			return syria_oil_production_category_blurbs();
		}
		return array(
			'أخبار بارزة'                 => 'تحديثات عاجلة من القطاع',
			'اخبار بارزة'                 => 'تحديثات عاجلة من القطاع',
			'أخبار النفط العالمي'         => 'الأسواق الدولية',
			'أخبار النفط والغاز العالمي'  => 'الأسواق الدولية',
			'أخبار النفط والغاز العربي'   => 'تغطية إقليمية',
			'المحروقات'                   => 'التوزيع والأسعار',
			'أخبار المشاريع النفطية'      => 'مشاريع وحقول الإنتاج',
			'قرارات وأخبار وزارة النفط'   => 'قرارات رسمية',
			'مناقصات الشركات'             => 'فرص تعاقد ومناقصات',
			'الشركات العاملة في سوريا'    => 'شركات وطنية ودولية',
			'مقالات'                      => 'تحليلات ودراسات',
			'معارض وندوات'                => 'فعاليات القطاع',
			'صناعة التكرير'               => 'مصافٍ ووحدات التكرير',
			'الثروات المعدنية'            => 'فوسفات ومعادن',
			'Blog'                        => 'مدونة الموقع',
			'blog'                        => 'مدونة الموقع',
		);
	}

	/**
	 * Display label overrides (e.g. Blog → localized Blog / مدونة via gettext).
	 *
	 * @param string $name Term name.
	 * @return string
	 */
	private function display_label( $name ) {
		if ( 'Blog' === $name || 'blog' === $name ) {
			return __( 'Blog', 'syria-oil-widgets' );
		}
		return $name;
	}

	/**
	 * @return array<int,\WP_Term>
	 */
	private function get_categories_sorted( $limit = 0 ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => true,
				'orderby'    => 'count',
				'order'      => 'DESC',
				'number'     => $limit > 0 ? absint( $limit ) : 0,
			)
		);
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return array();
		}
		return $terms;
	}

	/**
	 * Prefer curated featured categories when present.
	 *
	 * @param \WP_Term[] $all All terms.
	 * @param int        $n   Count.
	 * @return \WP_Term[]
	 */
	private function pick_featured( array $all, $n ) {
		$preferred = array(
			'أخبار بارزة',
			'أخبار النفط والغاز العالمي',
			'أخبار النفط العالمي',
			'أخبار النفط والغاز العربي',
			'المحروقات',
			'أخبار المشاريع النفطية',
			'قرارات وأخبار وزارة النفط',
			'مقالات',
		);
		if ( function_exists( 'syria_oil_widget_category_map' ) ) {
			$map = syria_oil_widget_category_map();
			if ( ! empty( $map['posts_filter_featured'] ) && is_array( $map['posts_filter_featured'] ) ) {
				$preferred = $map['posts_filter_featured'];
			}
		}
		$by_name = array();
		foreach ( $all as $term ) {
			$by_name[ $term->name ] = $term;
		}
		$picked = array();
		foreach ( $preferred as $name ) {
			$term = null;
			if ( isset( $by_name[ $name ] ) ) {
				$term = $by_name[ $name ];
			} elseif ( function_exists( 'syria_oil_widgets_resolve_category' ) ) {
				$term = syria_oil_widgets_resolve_category( $name );
			}
			if ( $term ) {
				$already = false;
				foreach ( $picked as $p ) {
					if ( (int) $p->term_id === (int) $term->term_id ) {
						$already = true;
						break;
					}
				}
				if ( ! $already ) {
					$picked[] = $term;
				}
			}
			if ( count( $picked ) >= $n ) {
				return $picked;
			}
		}
		foreach ( $all as $term ) {
			if ( count( $picked ) >= $n ) {
				break;
			}
			$already = false;
			foreach ( $picked as $p ) {
				if ( (int) $p->term_id === (int) $term->term_id ) {
					$already = true;
					break;
				}
			}
			if ( ! $already ) {
				$picked[] = $term;
			}
		}
		return $picked;
	}

	protected function render() {
		$s           = $this->get_settings_for_display();
		$show_feat   = isset( $s['show_featured'] ) && 'yes' === $s['show_featured'];
		$show_chips  = isset( $s['show_chips'] ) && 'yes' === $s['show_chips'];
		$feat_n      = ! empty( $s['featured_count'] ) ? absint( $s['featured_count'] ) : 4;
		$chip_n      = ! empty( $s['chip_limit'] ) ? absint( $s['chip_limit'] ) : 16;
		$ppp         = ! empty( $s['posts_per_page'] ) ? absint( $s['posts_per_page'] ) : 9;
		$empty       = ! empty( $s['empty_text'] ) ? $s['empty_text'] : __( 'لا توجد مقالات في هذا التصنيف حالياً.', 'syria-oil-widgets' );
		$blurbs      = $this->category_blurbs();

		$all_terms   = $this->get_categories_sorted( 0 );
		$featured    = $show_feat ? $this->pick_featured( $all_terms, $feat_n ) : array();
		$chip_terms  = $show_chips ? array_slice( $all_terms, 0, $chip_n ) : array();

		$active_slug = isset( $_GET['sow_cat'] ) ? sanitize_title( wp_unslash( $_GET['sow_cat'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$active_id   = 0;
		if ( $active_slug ) {
			$t = get_term_by( 'slug', $active_slug, 'category' );
			if ( $t && ! is_wp_error( $t ) ) {
				$active_id = (int) $t->term_id;
			}
		}

		$query_args = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $ppp,
			'ignore_sticky_posts' => true,
		);
		if ( $active_id ) {
			$query_args['cat'] = $active_id;
		}

		$q = new \WP_Query( $query_args );

		// Preload posts for client-side filtering (same page set).
		$all_q = new \WP_Query(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => min( 48, max( $ppp * 2, 18 ) ),
				'ignore_sticky_posts' => true,
			)
		);
		?>
		<section class="sow-posts-filter" data-sow-filter="1" data-empty="<?php echo esc_attr( $empty ); ?>">
			<?php if ( ! empty( $s['heading'] ) ) : ?>
				<header class="sow-posts-filter__head">
					<h2><?php echo esc_html( $s['heading'] ); ?></h2>
					<p class="sow-posts-filter__hint"><?php esc_html_e( 'اضغط على بطاقة أو وسم لتصفية الأخبار فوراً.', 'syria-oil-widgets' ); ?></p>
				</header>
			<?php endif; ?>

			<?php if ( $featured ) : ?>
				<div class="sow-posts-filter__featured" role="list">
					<?php foreach ( $featured as $term ) : ?>
						<?php
						$label = $this->display_label( $term->name );
						$blurb = isset( $blurbs[ $term->name ] ) ? $blurbs[ $term->name ] : __( 'تصفية الأخبار', 'syria-oil-widgets' );
						$is_on = ( $active_id && (int) $term->term_id === $active_id );
						?>
						<button
							type="button"
							class="sow-posts-filter__card<?php echo $is_on ? ' is-active' : ''; ?>"
							role="listitem"
							data-cat-id="<?php echo esc_attr( (string) $term->term_id ); ?>"
							data-cat-slug="<?php echo esc_attr( $term->slug ); ?>"
							aria-pressed="<?php echo $is_on ? 'true' : 'false'; ?>"
						>
							<span class="sow-posts-filter__count"><?php echo esc_html( (string) $term->count ); ?></span>
							<span class="sow-posts-filter__card-title"><?php echo esc_html( $label ); ?></span>
							<span class="sow-posts-filter__card-text"><?php echo esc_html( $blurb ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $chip_terms ) : ?>
				<div class="sow-posts-filter__chips" role="toolbar" aria-label="<?php esc_attr_e( 'تصنيفات الأخبار', 'syria-oil-widgets' ); ?>">
					<button
						type="button"
						class="sow-posts-filter__chip<?php echo $active_id ? '' : ' is-active'; ?>"
						data-cat-id="0"
						data-cat-slug=""
						aria-pressed="<?php echo $active_id ? 'false' : 'true'; ?>"
					><?php esc_html_e( 'الكل', 'syria-oil-widgets' ); ?></button>
					<?php foreach ( $chip_terms as $term ) : ?>
						<?php
						$label = $this->display_label( $term->name );
						$is_on = ( $active_id && (int) $term->term_id === $active_id );
						?>
						<button
							type="button"
							class="sow-posts-filter__chip<?php echo $is_on ? ' is-active' : ''; ?>"
							data-cat-id="<?php echo esc_attr( (string) $term->term_id ); ?>"
							data-cat-slug="<?php echo esc_attr( $term->slug ); ?>"
							aria-pressed="<?php echo $is_on ? 'true' : 'false'; ?>"
						>
							<?php echo esc_html( $label ); ?>
							<small>(<?php echo esc_html( (string) $term->count ); ?>)</small>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="sow-posts-filter__meta">
				<span class="sow-posts-filter__status" data-sow-status>
					<?php
					if ( $active_id ) {
						$t = get_term( $active_id );
						printf(
							/* translators: %s: category name */
							esc_html__( 'عرض: %s', 'syria-oil-widgets' ),
							esc_html( $t && ! is_wp_error( $t ) ? $this->display_label( $t->name ) : '' )
						);
					} else {
						esc_html_e( 'عرض: كل الأخبار', 'syria-oil-widgets' );
					}
					?>
				</span>
			</div>

			<div class="sow-posts-filter__grid so-grid" data-sow-grid>
				<?php
				$render_card = static function () {
					$cats = get_the_category();
					$ids  = array();
					foreach ( $cats as $c ) {
						$ids[] = (string) $c->term_id;
					}
					$syria_oil_card_args = array(
						'show_image'   => true,
						'show_badge'   => true,
						'show_date'    => true,
						'show_excerpt' => true,
						'show_more'    => true,
					);
					echo '<div class="sow-posts-filter__item" data-cat-ids="' . esc_attr( implode( ',', $ids ) ) . '">';
					$view = SYRIA_OIL_WIDGETS_DIR . '/includes/views/card.php';
					if ( file_exists( $view ) ) {
						include $view;
					}
					echo '</div>';
				};

				if ( $all_q->have_posts() ) {
					while ( $all_q->have_posts() ) {
						$all_q->the_post();
						$render_card();
					}
					wp_reset_postdata();
				} elseif ( $q->have_posts() ) {
					while ( $q->have_posts() ) {
						$q->the_post();
						$render_card();
					}
					wp_reset_postdata();
				} else {
					echo '<p class="sow-posts-filter__empty">' . esc_html( $empty ) . '</p>';
				}
				?>
			</div>
		</section>
		<?php
	}
}
