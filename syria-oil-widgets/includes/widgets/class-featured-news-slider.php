<?php
/**
 * Hero / Featured Slider Elementor widget.
 *
 * Default: manual slides (image, kicker, title, excerpt, button).
 * Optional: dynamic WP_Query for post-driven slides.
 * Style: overlay, typography, arrows, dots, CTA — empty controls fall back to
 * theme CSS variables / Elementor Global Settings.
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
 * Featured News Slider.
 */
class Featured_News_Slider extends Widget_Base {

	use Post_Query_Trait;

	public function get_name() {
		return 'syria_oil_featured_news_slider';
	}

	public function get_title() {
		return __( 'Hero Slider', 'syria-oil-widgets' );
	}

	public function get_icon() {
		return 'eicon-slides';
	}

	public function get_categories() {
		return array( 'syria-oil-widgets' );
	}

	public function get_keywords() {
		return array( 'slider', 'hero', 'featured', 'news', 'carousel', 'syria' );
	}

	public function get_style_depends() {
		return array( 'syria-oil-featured-slider' );
	}

	public function get_script_depends() {
		return array( 'syria-oil-featured-slider' );
	}

	protected function register_controls() {
		$this->register_content_controls();
		$this->register_settings_controls();
		$this->register_style_overlay();
		$this->register_style_content_typo();
		$this->register_style_cta();
		$this->register_style_arrows();
		$this->register_style_dots();
	}

	/**
	 * CONTENT tab.
	 */
	protected function register_content_controls() {
		$this->start_controls_section(
			'section_source',
			array( 'label' => __( 'Content', 'syria-oil-widgets' ) )
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Slides from', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'manual',
				'options' => array(
					'manual' => __( 'Manual slides (image + text + button)', 'syria-oil-widgets' ),
					'query'  => __( 'Latest posts (dynamic)', 'syria-oil-widgets' ),
				),
			)
		);

		$this->add_control(
			'query_heading',
			array(
				'label'     => __( 'Query', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => array( 'source' => 'query' ),
			)
		);

		$this->register_query_controls( 'query', array( 'source' => 'query' ) );

		$this->add_control(
			'query_cta_text',
			array(
				'label'     => __( 'Button label (query slides)', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
				'default'   => __( 'Read the story', 'syria-oil-widgets' ),
				'condition' => array( 'source' => 'query' ),
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'image',
			array(
				'label'   => __( 'Background image', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array( 'url' => Utils::get_placeholder_image_src() ),
			)
		);

		$repeater->add_control(
			'kicker',
			array(
				'label'       => __( 'Kicker / eyebrow', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
				'default'     => __( 'وزارة النفط', 'syria-oil-widgets' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => __( 'عنوان الشريحة الرئيسية', 'syria-oil-widgets' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'excerpt',
			array(
				'label'       => __( 'Text', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => __( 'نص قصير يصف الشريحة أو يدعو الزائر لاتخاذ إجراء.', 'syria-oil-widgets' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'link',
			array(
				'label'       => __( 'Button link', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => __( 'https://…', 'syria-oil-widgets' ),
			)
		);

		$repeater->add_control(
			'cta_text',
			array(
				'label'   => __( 'Button text', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
				'default' => __( 'اعرف المزيد', 'syria-oil-widgets' ),
			)
		);

		// Optional hero stats per slide (manual mode).
		$repeater->add_control(
			'show_stats',
			array(
				'label'        => __( 'Show stats row', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			)
		);

		$repeater->add_control(
			'stat_1_value',
			array(
				'label'     => __( 'Stat 1 value', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array( 'show_stats' => 'yes' ),
			)
		);
		$repeater->add_control(
			'stat_1_label',
			array(
				'label'     => __( 'Stat 1 label', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array( 'show_stats' => 'yes' ),
			)
		);
		$repeater->add_control(
			'stat_2_value',
			array(
				'label'     => __( 'Stat 2 value', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array( 'show_stats' => 'yes' ),
			)
		);
		$repeater->add_control(
			'stat_2_label',
			array(
				'label'     => __( 'Stat 2 label', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array( 'show_stats' => 'yes' ),
			)
		);
		$repeater->add_control(
			'stat_3_value',
			array(
				'label'     => __( 'Stat 3 value', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array( 'show_stats' => 'yes' ),
			)
		);
		$repeater->add_control(
			'stat_3_label',
			array(
				'label'     => __( 'Stat 3 label', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array( 'show_stats' => 'yes' ),
			)
		);
		$repeater->add_control(
			'stat_4_value',
			array(
				'label'     => __( 'Stat 4 value', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array( 'show_stats' => 'yes' ),
			)
		);
		$repeater->add_control(
			'stat_4_label',
			array(
				'label'     => __( 'Stat 4 label', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array( 'show_stats' => 'yes' ),
			)
		);

		$this->add_control(
			'slides',
			array(
				'label'       => __( 'Slides', 'syria-oil-widgets' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
				'condition'   => array( 'source' => 'manual' ),
				'default'     => array(
					array(
						'image'    => array(
							'url' => 'https://www.syria-oil.com/wp-content/uploads/image/IMG-20260730-WA0089-scaled.jpg',
						),
						'kicker'   => __( 'أخبار بارزة', 'syria-oil-widgets' ),
						'title'    => __( 'بخبرة تتجاوز 30 عاماً واكتشافات عالمية.. “آسبيكت إنرجي” الأمريكية تدرس دخول السوق السورية وتطوير حقول النفط والغاز', 'syria-oil-widgets' ),
						'excerpt'  => __( 'في خطوة تعكس عودة اهتمام شركات الطاقة الدولية بالسوق السورية، أبدت شركة “آسبيكت إنرجي” الأمريكية اهتماماً جدياً بدراسة الفرص الاستثمارية والدخول إلى قطاع النفط والغاز في سوريا.', 'syria-oil-widgets' ),
						'cta_text' => __( 'اقرأ التفاصيل', 'syria-oil-widgets' ),
						'link'     => array( 'url' => 'https://www.syria-oil.com/20577/' ),
					),
					array(
						'image'    => array(
							'url' => 'https://www.syria-oil.com/wp-content/uploads/image/FB_IMG_1785151142379.jpg',
						),
						'kicker'   => __( 'المحروقات', 'syria-oil-widgets' ),
						'title'    => __( 'سوريا تستورد شحنتي مازوت وغاز منزلي بإجمالي يقترب من مليون برميل.. وبدء التفريغ خلال ساعات', 'syria-oil-widgets' ),
						'excerpt'  => __( 'استقبل مصب بانياس البحري ناقلة مازوت وناقلة غاز مسال في إطار استمرار تأمين احتياجات السوق المحلية من المشتقات النفطية.', 'syria-oil-widgets' ),
						'cta_text' => __( 'اقرأ التفاصيل', 'syria-oil-widgets' ),
						'link'     => array( 'url' => 'https://www.syria-oil.com/20573/' ),
					),
					array(
						'image'    => array(
							'url' => 'https://www.syria-oil.com/wp-content/uploads/image/IMG_20260718_093125_314-scaled.jpg',
						),
						'kicker'   => __( 'تصريحات', 'syria-oil-widgets' ),
						'title'    => __( 'قبلاوي: مذكرتا التفاهم خطوة جديدة نحو تعزيز التكامل الإقليمي في قطاع الطاقة', 'syria-oil-widgets' ),
						'excerpt'  => __( 'إحياء خط الحديثة – بانياس ليس مجرد نقل نفط، بل مشروع استراتيجي يعيد رسم خريطة التعاون الإقليمي في الطاقة.', 'syria-oil-widgets' ),
						'cta_text' => __( 'اقرأ التفاصيل', 'syria-oil-widgets' ),
						'link'     => array( 'url' => 'https://www.syria-oil.com/20569/' ),
					),
					array(
						'image'    => array(
							'url' => 'https://www.syria-oil.com/wp-content/uploads/image/IMG_20260718_000529_522.jpg',
						),
						'kicker'   => __( 'أخبار بارزة', 'syria-oil-widgets' ),
						'title'    => __( 'سوريا توقّع مذكرتي تفاهم لإحياء مشروع خط الحديثة – بانياس بالشراكة مع العراق وائتلاف شركات دولي', 'syria-oil-widgets' ),
						'excerpt'  => __( 'توقيع مذكرة تفاهم لتأهيل وإعادة إحياء خط نقل النفط الحديثة – بانياس بالشراكة مع العراق وائتلاف شركات دولي.', 'syria-oil-widgets' ),
						'cta_text' => __( 'اقرأ التفاصيل', 'syria-oil-widgets' ),
						'link'     => array( 'url' => 'https://www.syria-oil.com/20565/' ),
					),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * SETTINGS: behaviour + responsive height.
	 */
	protected function register_settings_controls() {
		$this->start_controls_section(
			'section_settings',
			array( 'label' => __( 'Slider settings', 'syria-oil-widgets' ) )
		);

		$this->add_control(
			'autoplay',
			array(
				'label'        => __( 'Autoplay', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'autoplay_delay',
			array(
				'label'     => __( 'Autoplay delay (ms)', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 6000,
				'min'       => 2000,
				'max'       => 15000,
				'step'      => 500,
				'condition' => array( 'autoplay' => 'yes' ),
			)
		);

		$this->add_control(
			'show_arrows',
			array(
				'label'        => __( 'Show arrows', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_dots',
			array(
				'label'        => __( 'Show dots', 'syria-oil-widgets' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_responsive_control(
			'slide_height',
			array(
				'label'      => __( 'Slide height', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array( 'min' => 320, 'max' => 900 ),
					'vh' => array( 'min' => 40, 'max' => 100 ),
				),
				'default'        => array( 'unit' => 'px', 'size' => 640 ),
				'tablet_default' => array( 'unit' => 'px', 'size' => 520 ),
				'mobile_default' => array( 'unit' => 'px', 'size' => 440 ),
				'selectors'      => array(
					'{{WRAPPER}} .so-slider__slide' => 'min-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'content_padding',
			array(
				'label'      => __( 'Content padding', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'rem', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .so-slider__content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'content_layout_heading',
			array(
				'label'     => __( 'Content layout', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'content_horizontal',
			array(
				'label'   => __( 'Horizontal position', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'left'   => array(
						'title' => __( 'Left', 'syria-oil-widgets' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'syria-oil-widgets' ),
						'icon'  => 'eicon-h-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'syria-oil-widgets' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'default'              => 'right',
				'toggle'               => false,
				'selectors_dictionary' => array(
					'left'   => 'flex-start',
					'center' => 'center',
					'right'  => 'flex-end',
				),
				'selectors'            => array(
					'{{WRAPPER}} .so-slider__slide' => 'align-items: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'content_vertical',
			array(
				'label'   => __( 'Vertical position', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'top'    => array(
						'title' => __( 'Top', 'syria-oil-widgets' ),
						'icon'  => 'eicon-v-align-top',
					),
					'middle' => array(
						'title' => __( 'Middle', 'syria-oil-widgets' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'bottom' => array(
						'title' => __( 'Bottom', 'syria-oil-widgets' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'default'              => 'middle',
				'toggle'               => false,
				'selectors_dictionary' => array(
					'top'    => 'flex-start',
					'middle' => 'center',
					'bottom' => 'flex-end',
				),
				'selectors'            => array(
					'{{WRAPPER}} .so-slider__slide' => 'justify-content: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'content_text_align',
			array(
				'label'   => __( 'Text align', 'syria-oil-widgets' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'left'   => array(
						'title' => __( 'Left', 'syria-oil-widgets' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'syria-oil-widgets' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'syria-oil-widgets' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'              => 'right',
				'toggle'               => false,
				'selectors_dictionary' => array(
					'left'   => 'start',
					'center' => 'center',
					'right'  => 'end',
				),
				'selectors'            => array(
					'{{WRAPPER}} .so-slider__content' => 'text-align: {{VALUE}}; align-items: {{VALUE}};',
					'{{WRAPPER}} .so-slider__actions' => 'justify-content: {{VALUE}};',
					'{{WRAPPER}} .so-slider .flag-line' => 'align-self: {{VALUE}};',
					'{{WRAPPER}} .so-slider__kicker' => 'align-self: {{VALUE}};',
					'{{WRAPPER}} .so-slider__title' => 'align-self: {{VALUE}};',
					'{{WRAPPER}} .so-slider__lede' => 'align-self: {{VALUE}};',
					'{{WRAPPER}} .so-slider__stats' => 'align-self: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'content_width',
			array(
				'label'      => __( 'Content box width', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'rem' ),
				'range'      => array(
					'px'  => array( 'min' => 240, 'max' => 900 ),
					'%'   => array( 'min' => 40, 'max' => 100 ),
					'rem' => array( 'min' => 16, 'max' => 50 ),
				),
				'default'    => array( 'unit' => '%', 'size' => 56 ),
				'selectors'  => array(
					'{{WRAPPER}} .so-slider__content' => 'width: min(100%, {{SIZE}}{{UNIT}}); max-width: min(100%, {{SIZE}}{{UNIT}});',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * STYLE: Overlay — no default color so empty = CSS var fallback.
	 */
	protected function register_style_overlay() {
		$this->start_controls_section(
			'section_style_overlay',
			array(
				'label' => __( 'Overlay', 'syria-oil-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'overlay_color',
			array(
				'label'     => __( 'Overlay color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array( 'default' => '' ),
				'selectors' => array(
					'{{WRAPPER}} .so-slider__overlay' => 'background: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'overlay_opacity',
			array(
				'label'      => __( 'Overlay opacity', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 1, 'step' => 0.05 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .so-slider__overlay' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * STYLE: Kicker, title, excerpt typography/colors.
	 */
	protected function register_style_content_typo() {
		$this->start_controls_section(
			'section_style_content',
			array(
				'label' => __( 'Content Typography', 'syria-oil-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'kicker_heading',
			array(
				'label' => __( 'Kicker', 'syria-oil-widgets' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'kicker_color',
			array(
				'label'     => __( 'Kicker color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .so-slider__kicker' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'kicker_bg',
			array(
				'label'     => __( 'Kicker background', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .so-slider__kicker' => 'background: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'kicker_typography',
				'selector' => '{{WRAPPER}} .so-slider__kicker',
			)
		);

		$this->add_control(
			'title_heading',
			array(
				'label'     => __( 'Title', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Title color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .so-slider__title, {{WRAPPER}} .so-slider__title a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .so-slider__title',
			)
		);

		$this->add_responsive_control(
			'title_size',
			array(
				'label'      => __( 'Title size', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'rem' ),
				'range'      => array( 'px' => array( 'min' => 16, 'max' => 64 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .so-slider__title' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'excerpt_heading',
			array(
				'label'     => __( 'Excerpt', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'excerpt_color',
			array(
				'label'     => __( 'Excerpt color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .so-slider__lede' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'excerpt_typography',
				'selector' => '{{WRAPPER}} .so-slider__lede',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * STYLE: CTA button normal/hover.
	 */
	protected function register_style_cta() {
		$this->start_controls_section(
			'section_style_cta',
			array(
				'label' => __( 'CTA Button', 'syria-oil-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'cta_typography',
				'selector' => '{{WRAPPER}} .so-slider__actions .btn',
			)
		);

		$this->start_controls_tabs( 'cta_tabs' );

		$this->start_controls_tab( 'cta_normal', array( 'label' => __( 'Normal', 'syria-oil-widgets' ) ) );
		$this->add_control(
			'cta_color',
			array(
				'label'     => __( 'Text color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .so-slider__actions .btn' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'cta_bg',
			array(
				'label'     => __( 'Background', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .so-slider__actions .btn' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'cta_hover', array( 'label' => __( 'Hover', 'syria-oil-widgets' ) ) );
		$this->add_control(
			'cta_color_hover',
			array(
				'label'     => __( 'Text color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .so-slider__actions .btn:hover' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'cta_bg_hover',
			array(
				'label'     => __( 'Background', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .so-slider__actions .btn:hover' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'cta_padding',
			array(
				'label'      => __( 'Padding', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'separator'  => 'before',
				'selectors'  => array(
					'{{WRAPPER}} .so-slider__actions .btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'cta_radius',
			array(
				'label'      => __( 'Border radius', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .so-slider__actions .btn' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * STYLE: Arrows.
	 */
	protected function register_style_arrows() {
		$this->start_controls_section(
			'section_style_arrows',
			array(
				'label'     => __( 'Arrows', 'syria-oil-widgets' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_arrows' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'arrow_size',
			array(
				'label'      => __( 'Size', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 28, 'max' => 80 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .so-slider__arrow' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'arrow_offset',
			array(
				'label'      => __( 'Horizontal offset', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .so-slider__arrow--prev' => 'inset-inline-end: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .so-slider__arrow--next' => 'inset-inline-start: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'arrow_radius',
			array(
				'label'      => __( 'Border radius', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 100 ),
					'%'  => array( 'min' => 0, 'max' => 50 ),
				),
				'selectors'  => array(
					'{{WRAPPER}} .so-slider__arrow' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'arrow_tabs' );

		$this->start_controls_tab( 'arrow_normal', array( 'label' => __( 'Normal', 'syria-oil-widgets' ) ) );
		$this->add_control(
			'arrow_color',
			array(
				'label'     => __( 'Icon color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .so-slider__arrow' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'arrow_bg',
			array(
				'label'     => __( 'Background', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .so-slider__arrow' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'arrow_border_color',
			array(
				'label'     => __( 'Border color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .so-slider__arrow' => 'border-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'arrow_hover', array( 'label' => __( 'Hover', 'syria-oil-widgets' ) ) );
		$this->add_control(
			'arrow_color_hover',
			array(
				'label'     => __( 'Icon color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .so-slider__arrow:hover' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'arrow_bg_hover',
			array(
				'label'     => __( 'Background', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .so-slider__arrow:hover' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * STYLE: Dots / pagination.
	 */
	protected function register_style_dots() {
		$this->start_controls_section(
			'section_style_dots',
			array(
				'label'     => __( 'Dots', 'syria-oil-widgets' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_dots' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'dot_size',
			array(
				'label'      => __( 'Size', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 6, 'max' => 28 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .so-slider__dot' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'dot_gap',
			array(
				'label'      => __( 'Spacing', 'syria-oil-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 2, 'max' => 24 ) ),
				'selectors'  => array(
					'{{WRAPPER}} .so-slider__dots' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'dot_color',
			array(
				'label'     => __( 'Inactive color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .so-slider__dot' => 'border-color: {{VALUE}}; background: transparent;',
				),
			)
		);

		$this->add_control(
			'dot_active_color',
			array(
				'label'     => __( 'Active color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .so-slider__dot.is-active' => 'background: {{VALUE}}; border-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'dot_hover_color',
			array(
				'label'     => __( 'Hover color', 'syria-oil-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .so-slider__dot:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Normalize slides from query or manual repeater.
	 *
	 * @param array $settings Widget settings.
	 * @return array
	 */
	protected function get_slides( $settings ) {
		$slides = array();
		$source = isset( $settings['source'] ) ? $settings['source'] : 'manual';

		if ( 'manual' === $source && ! empty( $settings['slides'] ) && is_array( $settings['slides'] ) ) {
			foreach ( $settings['slides'] as $slide ) {
				if ( ! is_array( $slide ) ) {
					continue;
				}

				$stats = array();
				if ( ! empty( $slide['show_stats'] ) && 'yes' === $slide['show_stats'] ) {
					for ( $n = 1; $n <= 4; $n++ ) {
						$v = isset( $slide[ "stat_{$n}_value" ] ) ? $slide[ "stat_{$n}_value" ] : '';
						$l = isset( $slide[ "stat_{$n}_label" ] ) ? $slide[ "stat_{$n}_label" ] : '';
						if ( '' !== $v || '' !== $l ) {
							$stats[] = array( 'value' => $v, 'label' => $l );
						}
					}
				}

				$image_id  = ! empty( $slide['image']['id'] ) ? absint( $slide['image']['id'] ) : 0;
				$image_url = ! empty( $slide['image']['url'] ) ? $slide['image']['url'] : '';
				$width     = 1600;
				$height    = 900;

				if ( $image_id ) {
					$data = syria_oil_get_attachment_image_data( $image_id, 'syria-oil-hero' );
					if ( $data ) {
						$image_url = $data['src'];
						$width     = $data['width'];
						$height    = $data['height'];
					}
				}

				$title = isset( $slide['title'] ) ? $slide['title'] : '';
				if ( '' === $title && '' === $image_url ) {
					continue;
				}

				$slides[] = array(
					'image'   => $image_url,
					'width'   => $width,
					'height'  => $height,
					'kicker'  => isset( $slide['kicker'] ) ? $slide['kicker'] : '',
					'title'   => $title,
					'excerpt' => isset( $slide['excerpt'] ) ? $slide['excerpt'] : '',
					'url'     => ! empty( $slide['link']['url'] ) ? $slide['link']['url'] : '',
					'target'  => ! empty( $slide['link']['is_external'] ) ? '_blank' : '_self',
					'cta'     => isset( $slide['cta_text'] ) ? $slide['cta_text'] : '',
					'stats'   => $stats,
				);
			}
			return $slides;
		}

		$cta = ! empty( $settings['query_cta_text'] ) ? $settings['query_cta_text'] : __( 'Read the story', 'syria-oil-widgets' );
		if ( function_exists( 'syria_oil_widgets_localize_button' ) ) {
			$cta = syria_oil_widgets_localize_button( $cta );
		}

		$query = new \WP_Query(
			$this->build_query_args(
				$settings,
				'query',
				array(
					'update_post_meta_cache' => true,
					'update_post_term_cache' => true,
				)
			)
		);

		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id  = get_the_ID();
			$thumb_id = get_post_thumbnail_id( $post_id );
			$image    = '';
			$width    = 1600;
			$height   = 900;

			if ( $thumb_id ) {
				$data = syria_oil_get_attachment_image_data( $thumb_id, 'syria-oil-hero' );
				if ( $data ) {
					$image  = $data['src'];
					$width  = $data['width'];
					$height = $data['height'];
				}
			}

			$cats = get_the_category( $post_id );
			$slides[] = array(
				'image'   => $image,
				'width'   => $width,
				'height'  => $height,
				'kicker'  => ( ! empty( $cats ) && isset( $cats[0]->name ) ) ? $cats[0]->name : '',
				'title'   => get_the_title( $post_id ),
				'excerpt' => syria_oil_get_excerpt( 32, $post_id ),
				'url'     => get_permalink( $post_id ),
				'target'  => '_self',
				'cta'     => $cta,
				'stats'   => array(),
			);
		}
		wp_reset_postdata();

		return $slides;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$slides   = $this->get_slides( $settings );

		if ( empty( $slides ) ) {
			if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="so-slider__empty">' . esc_html__( 'No slides found. Add slides with an image, title, and button.', 'syria-oil-widgets' ) . '</div>';
			}
			return;
		}

		$autoplay = ! isset( $settings['autoplay'] ) || 'yes' === $settings['autoplay'];
		$delay    = ! empty( $settings['autoplay_delay'] ) ? absint( $settings['autoplay_delay'] ) : 6000;
		$total    = count( $slides );
		$split    = function_exists( 'syria_oil_hero_uses_split_layout' ) && syria_oil_hero_uses_split_layout();

		if ( $split ) {
			echo '<div class="so-hero-split">';
			echo '<div class="so-hero-split__main">';
		}

		?>
		<div class="so-slider<?php echo $split ? ' so-slider--split' : ''; ?>"
			data-autoplay="<?php echo $autoplay ? '1' : '0'; ?>"
			data-delay="<?php echo esc_attr( (string) $delay ); ?>">
			<div class="so-slider__track">
				<?php foreach ( $slides as $i => $slide ) : ?>
					<?php
					$is_first = ( 0 === $i );
					$title    = isset( $slide['title'] ) ? $slide['title'] : '';
					$kicker   = isset( $slide['kicker'] ) ? $slide['kicker'] : '';
					$excerpt  = isset( $slide['excerpt'] ) ? $slide['excerpt'] : '';
					$url      = isset( $slide['url'] ) ? $slide['url'] : '';
					$target   = isset( $slide['target'] ) ? $slide['target'] : '_self';
					$cta      = isset( $slide['cta'] ) ? $slide['cta'] : '';
					if ( function_exists( 'syria_oil_widgets_localize_button' ) && $cta ) {
						$cta = syria_oil_widgets_localize_button( $cta );
					}
					$image    = isset( $slide['image'] ) ? $slide['image'] : '';
					$width    = ! empty( $slide['width'] ) ? (int) $slide['width'] : 1600;
					$height   = ! empty( $slide['height'] ) ? (int) $slide['height'] : 900;
					?>
					<div class="so-slider__slide<?php echo $is_first ? ' is-active' : ''; ?>" role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( '%1$d / %2$d', $i + 1, $total ) ); ?>">
						<?php if ( $image ) : ?>
							<div class="so-slider__media">
								<?php
								$img_attrs = array(
									'src'      => $image,
									'width'    => $width,
									'height'   => $height,
									'alt'      => $title,
									'decoding' => 'async',
								);
								if ( $is_first ) {
									$img_attrs['loading']       = 'eager';
									$img_attrs['fetchpriority'] = 'high';
								} else {
									$img_attrs['loading'] = 'lazy';
								}
								syria_oil_print_img( $img_attrs );
								?>
							</div>
						<?php else : ?>
							<div class="so-slider__media so-slider__media--fallback" aria-hidden="true"></div>
						<?php endif; ?>
						<div class="so-slider__overlay" aria-hidden="true"></div>
						<div class="so-slider__content">
							<div class="flag-line" aria-hidden="true"><span></span><span></span><span></span></div>
							<?php if ( $kicker ) : ?>
								<p class="so-slider__kicker"><?php echo esc_html( $kicker ); ?></p>
							<?php endif; ?>
							<h2 class="so-slider__title">
								<?php
								$slide_title = $title ? $title : __( '(Untitled)', 'syria-oil-widgets' );
								if ( $url ) :
									?>
									<a href="<?php echo esc_url( $url ); ?>" target="<?php echo esc_attr( $target ); ?>"<?php echo ( '_blank' === $target ) ? ' rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $slide_title ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $slide_title ); ?>
								<?php endif; ?>
							</h2>
							<?php if ( $excerpt ) : ?>
								<p class="so-slider__lede"><?php echo esc_html( $excerpt ); ?></p>
							<?php endif; ?>
							<?php if ( $url && $cta ) : ?>
								<div class="so-slider__actions">
									<a class="btn btn-primary btn-lg" href="<?php echo esc_url( $url ); ?>" target="<?php echo esc_attr( $target ); ?>"<?php echo ( '_blank' === $target ) ? ' rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $cta ); ?></a>
								</div>
							<?php endif; ?>
							<?php if ( ! empty( $slide['stats'] ) && is_array( $slide['stats'] ) ) : ?>
								<div class="so-slider__stats">
									<?php foreach ( $slide['stats'] as $stat ) : ?>
										<div class="so-slider__stat">
											<strong><?php echo esc_html( isset( $stat['value'] ) ? $stat['value'] : '' ); ?></strong>
											<span><?php echo esc_html( isset( $stat['label'] ) ? $stat['label'] : '' ); ?></span>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<?php if ( isset( $settings['show_arrows'] ) && 'yes' === $settings['show_arrows'] && $total > 1 ) : ?>
				<button class="so-slider__arrow so-slider__arrow--prev" type="button" aria-label="<?php esc_attr_e( 'Previous slide', 'syria-oil-widgets' ); ?>">&#8250;</button>
				<button class="so-slider__arrow so-slider__arrow--next" type="button" aria-label="<?php esc_attr_e( 'Next slide', 'syria-oil-widgets' ); ?>">&#8249;</button>
			<?php endif; ?>

			<?php if ( isset( $settings['show_dots'] ) && 'yes' === $settings['show_dots'] && $total > 1 ) : ?>
				<div class="so-slider__dots" role="tablist" aria-label="<?php esc_attr_e( 'Slides', 'syria-oil-widgets' ); ?>">
					<?php for ( $i = 0; $i < $total; $i++ ) : ?>
						<button class="so-slider__dot<?php echo 0 === $i ? ' is-active' : ''; ?>" type="button" role="tab" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: slide number */ __( 'Go to slide %d', 'syria-oil-widgets' ), $i + 1 ) ); ?>" data-index="<?php echo esc_attr( (string) $i ); ?>"></button>
					<?php endfor; ?>
				</div>
			<?php endif; ?>

			<?php if ( $total > 1 ) : ?>
				<div class="so-slider__progress" aria-hidden="true">
					<span class="so-slider__progress-bar"></span>
				</div>
			<?php endif; ?>
		</div>
		<?php
		if ( $split ) {
			echo '</div>';
			$this->render_split_rail( $slides );
			echo '</div>';
		}
	}

	/**
	 * Split layout sidebar — أخبار بارزة list.
	 *
	 * @param array<int,array<string,mixed>> $slides Slide data.
	 */
	protected function render_split_rail( $slides ) {
		$heading = __( 'أخبار بارزة', 'syria-oil-widgets' );
		echo '<aside class="so-hero-split__rail" aria-label="' . esc_attr( $heading ) . '">';
		echo '<h3 class="so-hero-split__rail-title">' . esc_html( $heading ) . '</h3>';
		echo '<ul class="so-hero-split__list" role="tablist">';
		foreach ( $slides as $i => $slide ) {
			$title = isset( $slide['title'] ) ? $slide['title'] : '';
			$image = isset( $slide['image'] ) ? $slide['image'] : '';
			$kicker = isset( $slide['kicker'] ) ? $slide['kicker'] : '';
			$active = ( 0 === $i ) ? ' is-active' : '';
			echo '<li class="so-hero-split__item' . esc_attr( $active ) . '" role="presentation">';
			echo '<button type="button" class="so-hero-split__btn" role="tab" aria-selected="' . ( 0 === $i ? 'true' : 'false' ) . '" data-index="' . esc_attr( (string) $i ) . '">';
			if ( $image ) {
				echo '<span class="so-hero-split__thumb"><img src="' . esc_url( $image ) . '" alt="" width="80" height="56" loading="lazy" decoding="async" /></span>';
			}
			echo '<span class="so-hero-split__meta">';
			if ( $kicker ) {
				echo '<span class="so-hero-split__kicker">' . esc_html( $kicker ) . '</span>';
			}
			echo '<span class="so-hero-split__headline">' . esc_html( $title ) . '</span>';
			echo '</span></button></li>';
		}
		echo '</ul></aside>';
	}
}
