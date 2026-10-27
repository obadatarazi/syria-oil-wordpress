<?php
/**
 * Walker for the app-like mobile bottom navigation.
 *
 * Outputs bare <a> elements (no <ul>/<li>) with icon + label so the
 * fixed tab bar CSS can lay them out as equal grid cells.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bottom nav walker.
 */
class Syria_Oil_Bottom_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Running index of top-level items (for default icon order).
	 *
	 * @var int
	 */
	protected $item_index = 0;

	/**
	 * Default icons by slot (Home, News, Projects, Services).
	 *
	 * @var string[]
	 */
	protected $default_icons = array( '⌂', '☰', '◆', '▤' );

	/**
	 * Starts the list before the elements are added — intentionally empty.
	 *
	 * @param string   $output Used to append additional content.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   Menu args.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * Ends the list — intentionally empty.
	 *
	 * @param string   $output Used to append additional content.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   Menu args.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * Start the element output.
	 *
	 * @param string   $output Used to append additional content.
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   Menu args.
	 * @param int      $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		if ( $this->item_index >= 4 ) {
			return;
		}

		$title = isset( $item->title ) ? $item->title : '';
		$label = $this->shorten_label( $title );
		$url   = ! empty( $item->url ) ? $item->url : '#';
		$icon  = $this->resolve_icon( $title, $this->item_index );
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$active  = in_array( 'current-menu-item', $classes, true )
			|| in_array( 'current_page_item', $classes, true )
			|| in_array( 'current-menu-ancestor', $classes, true );

		$link_class = $active ? ' is-active' : '';

		$output .= sprintf(
			'<a class="%1$s" href="%2$s"%3$s><span class="bn-ico" aria-hidden="true">%4$s</span><span class="bn-label">%5$s</span></a>',
			esc_attr( trim( 'bn-link' . $link_class ) ),
			esc_url( $url ),
			$active ? ' aria-current="page"' : '',
			esc_html( $icon ),
			esc_html( $label )
		);

		++$this->item_index;
	}

	/**
	 * Ends the element — intentionally empty (anchor is self-closing in start_el).
	 *
	 * @param string   $output Used to append additional content.
	 * @param WP_Post  $item   Page data object.
	 * @param int      $depth  Depth of page.
	 * @param stdClass $args   Menu args.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}

	/**
	 * Short labels so five tabs fit without clipping on small phones.
	 *
	 * @param string $title Menu title.
	 * @return string
	 */
	protected function shorten_label( $title ) {
		$title = is_string( $title ) ? trim( $title ) : '';
		if ( '' === $title ) {
			return $title;
		}

		$exact = array(
			'الخدمات الإلكترونية' => 'الخدمات',
			'الخدمات الالكترونية' => 'الخدمات',
			'Electronic Services'  => 'Services',
			'E-Services'           => 'Services',
		);
		if ( isset( $exact[ $title ] ) ) {
			return $exact[ $title ];
		}

		$t = function_exists( 'mb_strtolower' ) ? mb_strtolower( $title ) : strtolower( $title );
		if ( false !== strpos( $t, 'خدمات' ) || false !== strpos( $t, 'خدمه' ) || false !== strpos( $t, 'إلكتر' ) || false !== strpos( $t, 'الكتر' ) ) {
			return 'الخدمات';
		}
		if ( false !== strpos( $t, 'service' ) || false !== strpos( $t, 'electronic' ) ) {
			return 'Services';
		}

		$len = function_exists( 'mb_strlen' ) ? mb_strlen( $title ) : strlen( $title );
		if ( $len > 12 ) {
			return function_exists( 'mb_substr' )
				? mb_substr( $title, 0, 10 ) . '…'
				: substr( $title, 0, 10 ) . '…';
		}

		return $title;
	}

	/**
	 * Pick an icon from the title or fall back to slot order.
	 *
	 * @param string $title Title.
	 * @param int    $index Slot index.
	 * @return string
	 */
	protected function resolve_icon( $title, $index ) {
		$t = function_exists( 'mb_strtolower' ) ? mb_strtolower( $title ) : strtolower( $title );

		$map = array(
			'⌂' => array( 'home', 'رئيس', 'الرئيسية', 'الرئيسه' ),
			'☰' => array( 'news', 'أخبار', 'اخبار', 'blog', 'مقال' ),
			'◆' => array( 'project', 'مشروع', 'مشاريع' ),
			/* Document / portal mark — clearer than the old nested-square ▣ */
			'▤' => array( 'service', 'خدمات', 'خدمه', 'إلكتر', 'الكتر', 'قسم', 'أقسام', 'اقسام', 'electronic', 'categor', 'portal' ),
			'⌕' => array( 'search', 'بحث' ),
			'$' => array( 'price', 'أسعار', 'اسعار' ),
		);

		foreach ( $map as $icon => $needles ) {
			foreach ( $needles as $needle ) {
				if ( false !== strpos( $t, $needle ) ) {
					return $icon;
				}
			}
		}

		return isset( $this->default_icons[ $index ] ) ? $this->default_icons[ $index ] : '●';
	}
}
