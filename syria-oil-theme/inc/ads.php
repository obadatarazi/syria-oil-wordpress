<?php
/**
 * Side-margin ads: CPT, admin UI, front rails, and demo seed.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One-time: side rails only + vertical tier (premium top / normal mid / basic bottom).
 *
 * Legacy strip slots (top_premium, mid_page, premium, normal) become left + matching tier.
 */
function syria_oil_migrate_ad_side_tier_v1() {
	if ( get_option( 'syria_oil_ad_side_tier_v1' ) ) {
		return;
	}
	$q = new WP_Query(
		array(
			'post_type'              => 'syria_oil_ad',
			'post_status'            => 'any',
			'posts_per_page'         => 200,
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
		)
	);
	foreach ( $q->posts as $post ) {
		$pos  = (string) get_post_meta( $post->ID, '_syria_oil_ad_position', true );
		$tier = (string) get_post_meta( $post->ID, '_syria_oil_ad_tier', true );

		if ( in_array( $pos, array( 'top_premium', 'premium' ), true ) ) {
			update_post_meta( $post->ID, '_syria_oil_ad_position', 'left' );
			update_post_meta( $post->ID, '_syria_oil_ad_tier', 'premium' );
			continue;
		}
		if ( in_array( $pos, array( 'mid_page', 'normal' ), true ) ) {
			update_post_meta( $post->ID, '_syria_oil_ad_position', 'left' );
			update_post_meta( $post->ID, '_syria_oil_ad_tier', 'normal' );
			continue;
		}
		if ( in_array( $pos, array( 'left', 'right' ), true ) ) {
			if ( ! in_array( $tier, array( 'premium', 'normal', 'basic' ), true ) ) {
				update_post_meta( $post->ID, '_syria_oil_ad_tier', 'normal' );
			}
			continue;
		}
		update_post_meta( $post->ID, '_syria_oil_ad_position', 'left' );
		update_post_meta( $post->ID, '_syria_oil_ad_tier', 'normal' );
	}
	update_option( 'syria_oil_ad_side_tier_v1', '1', false );
	// Older one-shot flag (no longer used for strip labels).
	update_option( 'syria_oil_ad_pos_premium_normal_v1', '1', false );
}
add_action( 'init', 'syria_oil_migrate_ad_side_tier_v1', 26 );

/**
 * One-time: unlock old mobile ads limit (2/4) so the strip shows every ad with swipe.
 */
function syria_oil_migrate_mobile_ads_show_all() {
	if ( get_option( 'syria_oil_mobile_ads_all_v1' ) ) {
		return;
	}
	if ( ! function_exists( 'syria_oil_mod' ) || ! function_exists( 'syria_oil_set_mod' ) ) {
		return;
	}
	$limit = absint( syria_oil_mod( 'mobile_ads_limit', 0 ) );
	// Previous defaults capped the strip at 2–4 ads.
	if ( $limit >= 1 && $limit <= 4 ) {
		syria_oil_set_mod( 'mobile_ads_limit', 0 );
	}
	update_option( 'syria_oil_mobile_ads_all_v1', '1', false );
}
add_action( 'init', 'syria_oil_migrate_mobile_ads_show_all', 25 );

/**
 * Register Side Ads post type.
 */
function syria_oil_register_ad_cpt() {
	$labels = array(
		'name'               => __( 'Side Ads', 'syria-oil' ),
		'singular_name'      => __( 'Side Ad', 'syria-oil' ),
		'add_new'            => __( 'Add New', 'syria-oil' ),
		'add_new_item'       => __( 'Add New Side Ad', 'syria-oil' ),
		'edit_item'          => __( 'Edit Side Ad', 'syria-oil' ),
		'new_item'           => __( 'New Side Ad', 'syria-oil' ),
		'view_item'          => __( 'View Side Ad', 'syria-oil' ),
		'search_items'       => __( 'Search Side Ads', 'syria-oil' ),
		'not_found'          => __( 'No side ads found', 'syria-oil' ),
		'not_found_in_trash' => __( 'No side ads found in Trash', 'syria-oil' ),
		'menu_name'          => __( 'Side Ads', 'syria-oil' ),
	);

	register_post_type(
		'syria_oil_ad',
		array(
			'labels'              => $labels,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-megaphone',
			'menu_position'       => 26,
			'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
			'capability_type'     => 'post',
			'exclude_from_search' => true,
			'has_archive'         => false,
			'rewrite'             => false,
		)
	);
}
add_action( 'init', 'syria_oil_register_ad_cpt' );

/**
 * Meta box for ad settings.
 */
function syria_oil_ad_register_metabox() {
	add_meta_box(
		'syria_oil_ad_settings',
		__( 'Ad settings', 'syria-oil' ),
		'syria_oil_ad_render_metabox',
		'syria_oil_ad',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'syria_oil_ad_register_metabox' );

/**
 * Normalize ad side (left/right only).
 *
 * @param string $position Raw meta.
 * @return string left|right
 */
function syria_oil_normalize_ad_position( $position ) {
	$position = (string) $position;
	if ( 'right' === $position ) {
		return 'right';
	}
	return 'left';
}

/**
 * Normalize vertical tier on a side rail.
 *
 * @param string $tier Raw meta.
 * @return string premium|normal|basic
 */
function syria_oil_normalize_ad_tier( $tier ) {
	$tier = (string) $tier;
	$map  = array(
		'premium'     => 'premium',
		'top'         => 'premium',
		'up'          => 'premium',
		'normal'      => 'normal',
		'mid'         => 'normal',
		'middle'      => 'normal',
		'basic'       => 'basic',
		'less_normal' => 'basic',
		'down'        => 'basic',
		'bottom'      => 'basic',
	);
	return isset( $map[ $tier ] ) ? $map[ $tier ] : 'normal';
}

/**
 * Side options (left / right rail).
 *
 * @return array<string,string>
 */
function syria_oil_ad_position_options() {
	return array(
		'left'  => __( 'Left side', 'syria-oil' ),
		'right' => __( 'Right side', 'syria-oil' ),
	);
}

/**
 * Vertical placement on that side.
 *
 * @return array<string,string>
 */
function syria_oil_ad_tier_options() {
	return array(
		'premium' => __( 'Premium — top of side', 'syria-oil' ),
		'normal'  => __( 'Normal — middle of side', 'syria-oil' ),
		'basic'   => __( 'Basic — bottom of side', 'syria-oil' ),
	);
}

/**
 * Tier sort rank (lower = higher on the rail).
 *
 * @param string $tier Tier.
 * @return int
 */
function syria_oil_ad_tier_rank( $tier ) {
	$tier = syria_oil_normalize_ad_tier( $tier );
	$rank = array(
		'premium' => 0,
		'normal'  => 1,
		'basic'   => 2,
	);
	return isset( $rank[ $tier ] ) ? $rank[ $tier ] : 1;
}

/**
 * Read tier for an ad post.
 *
 * @param int $post_id Ad ID.
 * @return string premium|normal|basic
 */
function syria_oil_get_ad_tier( $post_id ) {
	return syria_oil_normalize_ad_tier( (string) get_post_meta( (int) $post_id, '_syria_oil_ad_tier', true ) );
}

/**
 * @param WP_Post $post Post.
 */
function syria_oil_ad_render_metabox( $post ) {
	wp_nonce_field( 'syria_oil_ad_save', 'syria_oil_ad_nonce' );

	$url      = (string) get_post_meta( $post->ID, '_syria_oil_ad_url', true );
	$position = syria_oil_normalize_ad_position( (string) get_post_meta( $post->ID, '_syria_oil_ad_position', true ) );
	$tier     = syria_oil_get_ad_tier( $post->ID );
	$display  = (string) get_post_meta( $post->ID, '_syria_oil_ad_display', true );
	$pages    = get_post_meta( $post->ID, '_syria_oil_ad_pages', true );
	$exclude  = get_post_meta( $post->ID, '_syria_oil_ad_exclude', true );

	if ( ! in_array( $display, array( 'everywhere', 'homepage', 'selected_pages' ), true ) ) {
		$display = 'homepage';
	}
	if ( ! is_array( $pages ) ) {
		$pages = array();
	}
	if ( ! is_array( $exclude ) ) {
		$exclude = array();
	}

	$all_pages = get_pages(
		array(
			'sort_column' => 'post_title',
			'sort_order'  => 'ASC',
		)
	);

	echo '<p><label for="syria_oil_ad_url"><strong>' . esc_html__( 'Click URL', 'syria-oil' ) . '</strong></label><br />';
	echo '<input type="url" class="widefat" id="syria_oil_ad_url" name="syria_oil_ad_url" value="' . esc_attr( $url ) . '" placeholder="https://" /></p>';

	echo '<p><label for="syria_oil_ad_position"><strong>' . esc_html__( 'Side', 'syria-oil' ) . '</strong></label><br />';
	echo '<select id="syria_oil_ad_position" name="syria_oil_ad_position" class="widefat">';
	foreach ( syria_oil_ad_position_options() as $val => $label ) {
		printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $val ), selected( $position, $val, false ), esc_html( $label ) );
	}
	echo '</select></p>';

	echo '<p><label for="syria_oil_ad_tier"><strong>' . esc_html__( 'Placement on that side', 'syria-oil' ) . '</strong></label><br />';
	echo '<select id="syria_oil_ad_tier" name="syria_oil_ad_tier" class="widefat">';
	foreach ( syria_oil_ad_tier_options() as $val => $label ) {
		printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $val ), selected( $tier, $val, false ), esc_html( $label ) );
	}
	echo '</select></p>';
	echo '<p class="description">' . esc_html__( 'Same left/right rails on desktop: Premium at the top of that side, Normal near the end of the page hero (auto-measured on every page), Basic toward the bottom. On mobile/tablet: Premium under the header, Normal after the hero, Basic near the end of the page. Order within the same tier uses the Order field in Publish.', 'syria-oil' ) . '</p>';

	echo '<p><label for="syria_oil_ad_display"><strong>' . esc_html__( 'Display on', 'syria-oil' ) . '</strong></label><br />';
	echo '<select id="syria_oil_ad_display" name="syria_oil_ad_display" class="widefat">';
	$display_opts = array(
		'everywhere'     => __( 'Everywhere', 'syria-oil' ),
		'homepage'       => __( 'Homepage only', 'syria-oil' ),
		'selected_pages' => __( 'Selected pages', 'syria-oil' ),
	);
	foreach ( $display_opts as $val => $label ) {
		printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $val ), selected( $display, $val, false ), esc_html( $label ) );
	}
	echo '</select></p>';

	echo '<p><label for="syria_oil_ad_pages"><strong>' . esc_html__( 'Selected pages', 'syria-oil' ) . '</strong></label><br />';
	echo '<select id="syria_oil_ad_pages" name="syria_oil_ad_pages[]" class="widefat" multiple size="6">';
	foreach ( $all_pages as $page ) {
		printf(
			'<option value="%1$d" %2$s>%3$s</option>',
			(int) $page->ID,
			selected( in_array( (int) $page->ID, array_map( 'intval', $pages ), true ), true, false ),
			esc_html( $page->post_title )
		);
	}
	echo '</select></p>';

	echo '<p><label for="syria_oil_ad_exclude"><strong>' . esc_html__( 'Exclude pages', 'syria-oil' ) . '</strong></label><br />';
	echo '<select id="syria_oil_ad_exclude" name="syria_oil_ad_exclude[]" class="widefat" multiple size="4">';
	foreach ( $all_pages as $page ) {
		printf(
			'<option value="%1$d" %2$s>%3$s</option>',
			(int) $page->ID,
			selected( in_array( (int) $page->ID, array_map( 'intval', $exclude ), true ), true, false ),
			esc_html( $page->post_title )
		);
	}
	echo '</select></p>';

	echo '<p class="description">' . esc_html__( 'Set a Featured Image as the ad creative — one brand per Side Ad (do not stack multiple ads in a single image). PNG, JPG, WebP, and animated GIF are supported. GIFs use the original file so animation stays smooth. Tall banners are constrained by Theme Options → Layout. On mobile/tablet (when side rails hide), left/right ads also appear in a swipe strip.', 'syria-oil' ) . '</p>';
}

/**
 * Save ad metabox.
 *
 * @param int $post_id Post ID.
 */
function syria_oil_ad_save_metabox( $post_id ) {
	if ( ! isset( $_POST['syria_oil_ad_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['syria_oil_ad_nonce'] ) ), 'syria_oil_ad_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( 'syria_oil_ad' !== get_post_type( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$url = isset( $_POST['syria_oil_ad_url'] ) ? esc_url_raw( wp_unslash( $_POST['syria_oil_ad_url'] ) ) : '';
	update_post_meta( $post_id, '_syria_oil_ad_url', $url );

	$position = isset( $_POST['syria_oil_ad_position'] ) ? sanitize_text_field( wp_unslash( $_POST['syria_oil_ad_position'] ) ) : 'left';
	$position = syria_oil_normalize_ad_position( $position );
	update_post_meta( $post_id, '_syria_oil_ad_position', $position );

	$tier = isset( $_POST['syria_oil_ad_tier'] ) ? sanitize_text_field( wp_unslash( $_POST['syria_oil_ad_tier'] ) ) : 'normal';
	$tier = syria_oil_normalize_ad_tier( $tier );
	update_post_meta( $post_id, '_syria_oil_ad_tier', $tier );

	$display = isset( $_POST['syria_oil_ad_display'] ) ? sanitize_text_field( wp_unslash( $_POST['syria_oil_ad_display'] ) ) : 'homepage';
	$display = in_array( $display, array( 'everywhere', 'homepage', 'selected_pages' ), true ) ? $display : 'homepage';
	update_post_meta( $post_id, '_syria_oil_ad_display', $display );

	$pages = isset( $_POST['syria_oil_ad_pages'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['syria_oil_ad_pages'] ) ) : array();
	$pages = array_values( array_filter( $pages ) );
	update_post_meta( $post_id, '_syria_oil_ad_pages', $pages );

	$exclude = isset( $_POST['syria_oil_ad_exclude'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['syria_oil_ad_exclude'] ) ) : array();
	$exclude = array_values( array_filter( $exclude ) );
	update_post_meta( $post_id, '_syria_oil_ad_exclude', $exclude );
}
add_action( 'save_post_syria_oil_ad', 'syria_oil_ad_save_metabox' );

/**
 * Admin list columns.
 *
 * @param array $columns Columns.
 * @return array
 */
function syria_oil_ad_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['syria_oil_ad_position'] = __( 'Side / placement', 'syria-oil' );
			$new['syria_oil_ad_display']  = __( 'Display', 'syria-oil' );
			$new['syria_oil_ad_order']    = __( 'Order', 'syria-oil' );
		}
	}
	return $new;
}
add_filter( 'manage_syria_oil_ad_posts_columns', 'syria_oil_ad_columns' );

/**
 * @param string $column Column.
 * @param int    $post_id Post ID.
 */
function syria_oil_ad_column_content( $column, $post_id ) {
	if ( 'syria_oil_ad_position' === $column ) {
		$pos   = syria_oil_normalize_ad_position( (string) get_post_meta( $post_id, '_syria_oil_ad_position', true ) );
		$tier  = syria_oil_get_ad_tier( $post_id );
		$sides = syria_oil_ad_position_options();
		$tiers = syria_oil_ad_tier_options();
		$side_label = isset( $sides[ $pos ] ) ? $sides[ $pos ] : $pos;
		$tier_label = isset( $tiers[ $tier ] ) ? $tiers[ $tier ] : $tier;
		echo esc_html( $side_label . ' · ' . $tier_label );
	}
	if ( 'syria_oil_ad_display' === $column ) {
		$display = get_post_meta( $post_id, '_syria_oil_ad_display', true );
		$map     = array(
			'everywhere'     => __( 'Everywhere', 'syria-oil' ),
			'homepage'       => __( 'Homepage', 'syria-oil' ),
			'selected_pages' => __( 'Selected pages', 'syria-oil' ),
		);
		echo esc_html( isset( $map[ $display ] ) ? $map[ $display ] : $display );
	}
	if ( 'syria_oil_ad_order' === $column ) {
		echo (int) get_post_field( 'menu_order', $post_id );
	}
}
add_action( 'manage_syria_oil_ad_posts_custom_column', 'syria_oil_ad_column_content', 10, 2 );

/**
 * Whether an ad should show on the current request.
 *
 * @param int $post_id Ad post ID.
 * @return bool
 */
function syria_oil_ad_matches_context( $post_id ) {
	$exclude = get_post_meta( $post_id, '_syria_oil_ad_exclude', true );
	if ( is_array( $exclude ) && ! empty( $exclude ) ) {
		$qid = (int) get_queried_object_id();
		if ( $qid && in_array( $qid, array_map( 'intval', $exclude ), true ) ) {
			return false;
		}
	}

	$display = (string) get_post_meta( $post_id, '_syria_oil_ad_display', true );
	if ( 'everywhere' === $display || '' === $display ) {
		return true;
	}
	if ( 'homepage' === $display ) {
		return is_front_page() || is_home();
	}
	if ( 'selected_pages' === $display ) {
		$pages = get_post_meta( $post_id, '_syria_oil_ad_pages', true );
		if ( ! is_array( $pages ) || empty( $pages ) ) {
			return false;
		}
		$qid = (int) get_queried_object_id();
		return $qid && in_array( $qid, array_map( 'intval', $pages ), true );
	}
	return false;
}

/**
 * Published ads for a side rail on the current page.
 *
 * Sorted: Premium (top) → Normal (middle) → Basic (bottom), then menu order.
 *
 * @param string $position left|right.
 * @return WP_Post[]
 */
function syria_oil_get_side_ads( $position ) {
	static $cache = array();

	$position = syria_oil_normalize_ad_position( $position );
	if ( isset( $cache[ $position ] ) ) {
		return $cache[ $position ];
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'syria_oil_ad',
			'post_status'            => 'publish',
			'posts_per_page'         => 40,
			'orderby'                => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array(
					'key'   => '_syria_oil_ad_position',
					'value' => $position,
				),
			),
		)
	);

	$ads = array();
	foreach ( $query->posts as $post ) {
		if ( syria_oil_ad_matches_context( $post->ID ) && syria_oil_ad_has_displayable_creative( $post ) ) {
			$ads[] = $post;
		}
	}

	usort(
		$ads,
		static function ( $a, $b ) {
			$rank = syria_oil_ad_tier_rank( syria_oil_get_ad_tier( $a->ID ) ) <=> syria_oil_ad_tier_rank( syria_oil_get_ad_tier( $b->ID ) );
			if ( 0 !== $rank ) {
				return $rank;
			}
			$order = (int) $a->menu_order <=> (int) $b->menu_order;
			if ( 0 !== $order ) {
				return $order;
			}
			return strcasecmp( (string) $a->post_title, (string) $b->post_title );
		}
	);

	$cache[ $position ] = $ads;
	return $ads;
}

/**
 * All matching side-rail ads for the current page.
 *
 * Ordered by tier (premium → normal → basic), then left before right, then menu order.
 *
 * @return WP_Post[]
 */
function syria_oil_get_all_side_ads() {
	$ads = array_merge( syria_oil_get_side_ads( 'left' ), syria_oil_get_side_ads( 'right' ) );
	usort(
		$ads,
		static function ( $a, $b ) {
			$rank = syria_oil_ad_tier_rank( syria_oil_get_ad_tier( $a->ID ) ) <=> syria_oil_ad_tier_rank( syria_oil_get_ad_tier( $b->ID ) );
			if ( 0 !== $rank ) {
				return $rank;
			}
			$side_a = syria_oil_normalize_ad_position( (string) get_post_meta( $a->ID, '_syria_oil_ad_position', true ) );
			$side_b = syria_oil_normalize_ad_position( (string) get_post_meta( $b->ID, '_syria_oil_ad_position', true ) );
			if ( $side_a !== $side_b ) {
				return ( 'left' === $side_a ) ? -1 : 1;
			}
			$order = (int) $a->menu_order <=> (int) $b->menu_order;
			if ( 0 !== $order ) {
				return $order;
			}
			return strcasecmp( (string) $a->post_title, (string) $b->post_title );
		}
	);
	return $ads;
}

/**
 * Ads for one vertical tier (both left + right rails combined).
 *
 * Used for mobile page strips: premium / normal / basic.
 *
 * @param string $tier premium|normal|basic.
 * @return WP_Post[]
 */
function syria_oil_get_ads_by_tier( $tier ) {
	$tier = syria_oil_normalize_ad_tier( $tier );
	$out  = array();
	foreach ( syria_oil_get_all_side_ads() as $ad ) {
		if ( syria_oil_get_ad_tier( $ad->ID ) === $tier ) {
			$out[] = $ad;
		}
	}
	return $out;
}

/**
 * Whether mobile/tablet page ad strips are enabled.
 *
 * @return bool
 */
function syria_oil_mobile_ads_enabled() {
	if ( function_exists( 'syria_oil_mod_enabled' ) ) {
		return syria_oil_mod_enabled( 'show_mobile_ads', true );
	}
	return (bool) ( function_exists( 'syria_oil_mod' ) ? syria_oil_mod( 'show_mobile_ads', true ) : true );
}

/**
 * Apply Theme Options mobile ads limit (0 = unlimited).
 *
 * @param WP_Post[] $ads Ads.
 * @return WP_Post[]
 */
function syria_oil_limit_mobile_ads( $ads ) {
	$limit = function_exists( 'syria_oil_mod' ) ? absint( syria_oil_mod( 'mobile_ads_limit', 0 ) ) : 0;
	if ( $limit < 1 ) {
		return $ads;
	}
	if ( $limit > 24 ) {
		$limit = 24;
	}
	return array_slice( $ads, 0, $limit );
}

/**
 * Pick left/right creatives for a balanced horizontal strip.
 *
 * Prefers one ad from each rail side. If a side is missing, fills from the
 * remaining same-tier ads. Empty sides stay null so a placeholder can render.
 *
 * @param WP_Post[] $ads Tier ads (already filtered/limited).
 * @return array{left:?WP_Post,right:?WP_Post,extras:WP_Post[]}
 */
function syria_oil_pick_strip_pair_ads( $ads ) {
	$left  = null;
	$right = null;
	$pool  = array();

	foreach ( $ads as $ad ) {
		if ( ! ( $ad instanceof WP_Post ) ) {
			continue;
		}
		$pos = syria_oil_normalize_ad_position( (string) get_post_meta( $ad->ID, '_syria_oil_ad_position', true ) );
		if ( 'right' === $pos && null === $right ) {
			$right = $ad;
		} elseif ( 'left' === $pos && null === $left ) {
			$left = $ad;
		} else {
			$pool[] = $ad;
		}
	}

	if ( null === $left && $pool ) {
		$left = array_shift( $pool );
	}
	if ( null === $right && $pool ) {
		$right = array_shift( $pool );
	}

	return array(
		'left'   => $left,
		'right'  => $right,
		'extras' => array_values( $pool ),
	);
}

/**
 * Empty strip slot so left/right stay balanced when one side has no creative.
 *
 * @param string $side left|right.
 */
function syria_oil_render_ad_strip_placeholder( $side ) {
	$side = syria_oil_normalize_ad_position( $side );
	printf(
		'<div class="side-ad side-ad--placeholder side-ad--strip side-ad--slot-%1$s" aria-hidden="true"><span class="side-ad__placeholder-label">%2$s</span></div>',
		esc_attr( $side ),
		esc_html__( 'Ad space', 'syria-oil' )
	);
}

/**
 * Horizontal strip for one tier (shown when side rails are hidden via CSS).
 *
 * Mid/laptop viewports: balanced left + right pair.
 * Phones: horizontal swipe including extras.
 *
 * @param string $tier        premium|normal|basic.
 * @param string $extra_class Extra CSS class.
 */
function syria_oil_render_tier_ad_strip( $tier, $extra_class = '' ) {
	if ( ! syria_oil_mobile_ads_enabled() ) {
		return;
	}

	$tier = syria_oil_normalize_ad_tier( $tier );
	$ads  = syria_oil_limit_mobile_ads( syria_oil_get_ads_by_tier( $tier ) );
	if ( empty( $ads ) ) {
		return;
	}

	$labels = array(
		'premium' => __( 'Premium advertisements', 'syria-oil' ),
		'normal'  => __( 'Advertisements', 'syria-oil' ),
		'basic'   => __( 'More advertisements', 'syria-oil' ),
	);
	$label = isset( $labels[ $tier ] ) ? $labels[ $tier ] : $labels['normal'];
	$class = trim(
		'site-shell__ad-strip site-shell__ad-strip--' . $tier .
		' site-shell__ad-strip--pair' .
		' site-shell__mobile-ads site-shell__mobile-ads--' . $tier .
		' ' . $extra_class
	);

	$picked = syria_oil_pick_strip_pair_ads( $ads );
	$index  = 0;

	echo '<aside class="' . esc_attr( $class ) . '" aria-label="' . esc_attr( $label ) . '" data-ad-tier="' . esc_attr( $tier ) . '">';
	echo '<div class="site-shell__mobile-ads-track site-shell__ad-strip-track site-shell__ad-strip-track--pair">';

	echo '<div class="site-shell__ad-strip-slot site-shell__ad-strip-slot--left">';
	if ( $picked['left'] instanceof WP_Post ) {
		syria_oil_render_ad_link(
			$picked['left'],
			'side-ad side-ad--mobile side-ad--strip side-ad--slot-left side-ad--tier-' . $tier,
			$index
		);
		++$index;
	} else {
		syria_oil_render_ad_strip_placeholder( 'left' );
	}
	echo '</div>';

	echo '<div class="site-shell__ad-strip-slot site-shell__ad-strip-slot--right">';
	if ( $picked['right'] instanceof WP_Post ) {
		syria_oil_render_ad_link(
			$picked['right'],
			'side-ad side-ad--mobile side-ad--strip side-ad--slot-right side-ad--tier-' . $tier,
			$index
		);
		++$index;
	} else {
		syria_oil_render_ad_strip_placeholder( 'right' );
	}
	echo '</div>';

	foreach ( $picked['extras'] as $extra ) {
		if ( ! ( $extra instanceof WP_Post ) ) {
			continue;
		}
		syria_oil_render_ad_link(
			$extra,
			'side-ad side-ad--mobile side-ad--strip side-ad--extra side-ad--tier-' . $tier,
			$index
		);
		++$index;
	}

	echo '</div></aside>';
}

/**
 * Legacy horizontal strips — now tiered mobile placements.
 *
 * @param string $position Unused legacy arg (premium|normal).
 * @param string $extra_class Extra class.
 */
function syria_oil_render_ad_strip( $position, $extra_class = '' ) {
	$tier = syria_oil_normalize_ad_tier( (string) $position );
	if ( ! in_array( $tier, array( 'premium', 'normal', 'basic' ), true ) ) {
		$tier = 'normal';
	}
	syria_oil_render_tier_ad_strip( $tier, $extra_class );
}

/**
 * Premium — top of side (desktop) / under header (mobile).
 */
function syria_oil_render_premium_ads() {
	syria_oil_render_tier_ad_strip( 'premium' );
}

/**
 * Normal — mid side near hero end (desktop) / after hero (mobile).
 *
 * Rendered once per request so shortcodes / templates do not duplicate.
 */
function syria_oil_render_mid_page_ads() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	syria_oil_render_tier_ad_strip( 'normal' );
}

/**
 * Basic — bottom of side (desktop) / near end of page (mobile).
 */
function syria_oil_render_basic_ads() {
	syria_oil_render_tier_ad_strip( 'basic' );
}

/**
 * Whether an attachment is a GIF (keeps animation when served as original file).
 *
 * @param int $attachment_id Attachment ID.
 * @return bool
 */
function syria_oil_attachment_is_gif( $attachment_id ) {
	$attachment_id = absint( $attachment_id );
	if ( ! $attachment_id ) {
		return false;
	}
	$mime = (string) get_post_mime_type( $attachment_id );
	return ( false !== strpos( $mime, 'gif' ) );
}

/**
 * Render a single ad link (one creative per card).
 *
 * GIFs always use the original file URL (no resized srcset) so animation works.
 *
 * @param WP_Post $ad    Ad post.
 * @param string  $class Extra class on the anchor.
 * @param int     $index Zero-based index in the rail (for stagger animation).
 */
function syria_oil_render_ad_link( $ad, $class = 'side-ad', $index = 0 ) {
	$url = (string) get_post_meta( $ad->ID, '_syria_oil_ad_url', true );
	if ( '' === $url ) {
		$url = '#';
	}
	$alt     = get_the_title( $ad );
	$rel     = ( '#' === $url ) ? 'noopener' : 'noopener sponsored';
	$image   = syria_oil_get_ad_image_data( $ad );
	$is_gif  = ! empty( $image['is_gif'] );
	$classes = trim( $class . ( $is_gif ? ' side-ad--gif' : '' ) );
	$delay   = max( 0, (int) $index ) * 70;

	printf(
		'<a class="%1$s" href="%2$s" rel="%3$s"%4$s style="--side-ad-delay:%5$dms">',
		esc_attr( $classes ),
		esc_url( $url ),
		esc_attr( $rel ),
		( '#' !== $url ) ? ' target="_blank"' : '',
		(int) $delay
	);

	if ( ! empty( $image['html'] ) ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core thumbnail markup.
		echo $image['html'];
	} elseif ( ! empty( $image['src'] ) ) {
		printf(
			'<img class="side-ad__img%1$s" src="%2$s" alt="%3$s" width="%4$d" height="%5$d" loading="%6$s" decoding="async" />',
			$is_gif ? ' side-ad__img--gif' : '',
			esc_url( $image['src'] ),
			esc_attr( ! empty( $image['alt'] ) ? $image['alt'] : $alt ),
			(int) ( $image['width'] ?? 176 ),
			(int) ( $image['height'] ?? 220 ),
			$is_gif ? 'eager' : 'lazy'
		);
	}

	echo '</a>';
}

/**
 * Render one ad rail (desktop gutters). Left/right only.
 *
 * @param string $position left|right.
 */
function syria_oil_render_ad_rail( $position ) {
	$position = syria_oil_normalize_ad_position( $position );
	$ads      = syria_oil_get_side_ads( $position );
	if ( empty( $ads ) ) {
		return;
	}

	$zones = array(
		'premium' => array(),
		'normal'  => array(),
		'basic'   => array(),
	);
	foreach ( $ads as $ad ) {
		$tier = syria_oil_get_ad_tier( $ad->ID );
		$zones[ $tier ][] = $ad;
	}

	$class = 'site-shell__rail site-shell__rail--' . $position;
	echo '<aside class="' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'Advertisements', 'syria-oil' ) . '">';
	echo '<div class="site-shell__rail-inner">';
	$i = 0;
	foreach ( $zones as $tier => $tier_ads ) {
		if ( empty( $tier_ads ) ) {
			continue;
		}
		echo '<div class="site-shell__rail-zone site-shell__rail-zone--' . esc_attr( $tier ) . '">';
		foreach ( $tier_ads as $ad ) {
			syria_oil_render_ad_link( $ad, 'side-ad side-ad--tier-' . $tier, (int) $i );
			++$i;
		}
		echo '</div>';
	}
	echo '</div></aside>';
}

/**
 * Mobile premium strip under the header (alias kept for older calls).
 */
function syria_oil_render_mobile_ads() {
	syria_oil_render_premium_ads();
}

/**
 * Bundled demo side-ad definitions (one creative per post).
 *
 * @return array<int,array<string,mixed>>
 */
function syria_oil_get_demo_side_ad_defs() {
	return array(
		array(
			'slug'     => 'demo-left-arfada',
			'title'    => 'ARFADA Petroleum',
			'file'     => 'arfada.gif',
			'position' => 'left',
			'tier'     => 'premium',
			'order'    => 1,
			'display'  => 'homepage',
			'url'      => 'https://www.syria-oil.com/',
			'status'   => 'publish',
		),
		array(
			'slug'     => 'demo-left-nofsco',
			'title'    => 'NOFSCO',
			'file'     => 'nofsco.jpg',
			'position' => 'left',
			'tier'     => 'normal',
			'order'    => 2,
			'display'  => 'homepage',
			'url'      => 'https://www.syria-oil.com/',
			'status'   => 'publish',
		),
		array(
			'slug'     => 'demo-left-qasioun-mall',
			'title'    => 'New Qasioun Mall',
			'file'     => 'qasioun-mall.gif',
			'position' => 'left',
			'tier'     => 'basic',
			'order'    => 3,
			'display'  => 'homepage',
			'url'      => 'https://www.syria-oil.com/',
			'status'   => 'publish',
		),
		array(
			'slug'     => 'demo-right-shell-syria',
			'title'    => 'Shell Syria',
			'file'     => 'shell-syria.jpg',
			'position' => 'right',
			'tier'     => 'premium',
			'order'    => 1,
			'display'  => 'homepage',
			'url'      => 'https://www.syria-oil.com/',
			'status'   => 'publish',
		),
		array(
			'slug'     => 'demo-right-syrpetro',
			'title'    => 'SyrPetro',
			'file'     => 'syrpetro.jpg',
			'position' => 'right',
			'tier'     => 'normal',
			'order'    => 2,
			'display'  => 'homepage',
			'url'      => 'https://www.syria-oil.com/',
			'status'   => 'publish',
		),
		array(
			'slug'     => 'demo-right-sps',
			'title'    => 'SPS',
			'file'     => 'sps.png',
			'position' => 'right',
			'tier'     => 'basic',
			'order'    => 3,
			'display'  => 'homepage',
			'url'      => 'https://www.syria-oil.com/',
			'status'   => 'publish',
		),
		array(
			'slug'     => 'demo-right-al-shamila',
			'title'    => 'الشاملة للخدمات النفطية',
			'file'     => 'al-shamila.png',
			'position' => 'right',
			'tier'     => 'basic',
			'order'    => 4,
			'display'  => 'homepage',
			'url'      => 'https://www.syria-oil.com/',
			'status'   => 'publish',
		),
		array(
			'slug'     => 'demo-selected-pages-example',
			'title'    => __( 'Example: selected pages (draft)', 'syria-oil' ),
			'file'     => 'sps.png',
			'position' => 'left',
			'tier'     => 'normal',
			'order'    => 99,
			'display'  => 'selected_pages',
			'url'      => 'https://www.syria-oil.com/',
			'status'   => 'draft',
		),
	);
}

/**
 * Demo creative filename for a demo slug.
 *
 * @param string $slug Demo slug meta value.
 * @return string Empty when unknown.
 */
function syria_oil_demo_ad_file_by_slug( $slug ) {
	$slug = (string) $slug;
	if ( '' === $slug ) {
		return '';
	}
	foreach ( syria_oil_get_demo_side_ad_defs() as $def ) {
		if ( isset( $def['slug'] ) && $def['slug'] === $slug && ! empty( $def['file'] ) ) {
			return (string) $def['file'];
		}
	}
	return '';
}

/**
 * Public URL for a bundled demo side-ad asset (theme directory).
 *
 * @param string $relative Path under assets/demo/side-ads/.
 * @return string
 */
function syria_oil_demo_ad_theme_url( $relative ) {
	$relative = ltrim( (string) $relative, '/' );
	if ( '' === $relative ) {
		return '';
	}
	$path = trailingslashit( SYRIA_OIL_DIR ) . 'assets/demo/side-ads/' . $relative;
	if ( ! is_readable( $path ) ) {
		return '';
	}
	return trailingslashit( get_template_directory_uri() ) . 'assets/demo/side-ads/' . ltrim( $relative, '/' );
}

/**
 * Whether an attachment lives in the legacy flat uploads/image/ folder (often 500s on some hosts).
 *
 * @param int $attachment_id Attachment ID.
 * @return bool
 */
function syria_oil_attachment_uses_legacy_image_dir( $attachment_id ) {
	$attachment_id = absint( $attachment_id );
	if ( ! $attachment_id ) {
		return false;
	}
	$path = get_attached_file( $attachment_id );
	if ( ! is_string( $path ) || '' === $path ) {
		$url = wp_get_attachment_url( $attachment_id );
		return is_string( $url ) && false !== strpos( $url, '/uploads/image/' );
	}
	$norm = wp_normalize_path( $path );
	return ( false !== strpos( $norm, '/uploads/image/' ) );
}

/**
 * Whether a media-library attachment file exists on disk.
 *
 * @param int $attachment_id Attachment ID.
 * @return bool
 */
function syria_oil_attachment_file_exists( $attachment_id ) {
	$attachment_id = absint( $attachment_id );
	if ( ! $attachment_id ) {
		return false;
	}
	$path = get_attached_file( $attachment_id );
	return is_string( $path ) && '' !== $path && is_readable( $path );
}

/**
 * Whether an ad has a renderable creative (library file or bundled demo fallback).
 *
 * @param WP_Post $post Ad post.
 * @return bool
 */
function syria_oil_ad_has_displayable_creative( $post ) {
	$file = syria_oil_demo_ad_file_by_slug( (string) get_post_meta( $post->ID, '_syria_oil_ad_demo_slug', true ) );
	if ( '' !== $file && syria_oil_demo_ad_theme_url( $file ) ) {
		return true;
	}
	$thumb = (int) get_post_thumbnail_id( $post );
	return $thumb && syria_oil_attachment_file_exists( $thumb ) && ! syria_oil_attachment_uses_legacy_image_dir( $thumb );
}

/**
 * Resolve image output for one side ad (media library, sideload repair, or theme fallback).
 *
 * @param WP_Post $ad Ad post.
 * @return array<string,mixed>|null
 */
function syria_oil_get_ad_image_data( $ad ) {
	$demo_slug = (string) get_post_meta( $ad->ID, '_syria_oil_ad_demo_slug', true );
	$demo_file = syria_oil_demo_ad_file_by_slug( $demo_slug );
	$theme_url = $demo_file ? syria_oil_demo_ad_theme_url( $demo_file ) : '';

	$thumb = (int) get_post_thumbnail_id( $ad );
	if ( $thumb && ( ! syria_oil_attachment_file_exists( $thumb ) || syria_oil_attachment_uses_legacy_image_dir( $thumb ) ) ) {
		$thumb = 0;
	}

	// Bundled demo creatives: serve from theme (legacy uploads/image/ URLs often 500 on live host).
	if ( $theme_url && ( ! $thumb || $demo_slug ) ) {
		return array(
			'src'    => $theme_url,
			'width'  => 176,
			'height' => 220,
			'is_gif' => (bool) preg_match( '/\.gif$/i', $demo_file ),
			'alt'    => get_the_title( $ad ),
		);
	}

	if ( ! $thumb && $demo_file && current_user_can( 'upload_files' ) ) {
		$repaired = syria_oil_sideload_demo_ad_image( $demo_file, get_the_title( $ad ) );
		if ( $repaired && ! syria_oil_attachment_uses_legacy_image_dir( $repaired ) ) {
			set_post_thumbnail( $ad->ID, $repaired );
			$thumb = $repaired;
		}
	}

	if ( $thumb ) {
		$is_gif = syria_oil_attachment_is_gif( $thumb );
		if ( $is_gif ) {
			$src = wp_get_attachment_url( $thumb );
			if ( $src ) {
				$meta = wp_get_attachment_metadata( $thumb );
				return array(
					'src'    => $src,
					'width'  => ! empty( $meta['width'] ) ? (int) $meta['width'] : 176,
					'height' => ! empty( $meta['height'] ) ? (int) $meta['height'] : 220,
					'is_gif' => true,
					'alt'    => get_the_title( $ad ),
				);
			}
		}

		$html = get_the_post_thumbnail(
			$ad,
			'full',
			array(
				'class'    => 'side-ad__img',
				'alt'      => get_the_title( $ad ),
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);
		if ( $html ) {
			return array(
				'html'   => $html,
				'is_gif' => false,
			);
		}
	}

	$file = syria_oil_demo_ad_file_by_slug( (string) get_post_meta( $ad->ID, '_syria_oil_ad_demo_slug', true ) );
	$url  = syria_oil_demo_ad_theme_url( $file );
	if ( $url ) {
		return array(
			'src'    => $url,
			'width'  => 176,
			'height' => 220,
			'is_gif' => (bool) preg_match( '/\.gif$/i', $file ),
			'alt'    => get_the_title( $ad ),
		);
	}

	return null;
}

/**
 * Whether any demo side ads are missing a valid featured image file.
 *
 * @return bool
 */
function syria_oil_side_ads_need_image_repair() {
	foreach ( syria_oil_get_demo_side_ad_defs() as $def ) {
		if ( empty( $def['slug'] ) || empty( $def['file'] ) ) {
			continue;
		}
		if ( 'publish' !== ( $def['status'] ?? 'publish' ) ) {
			continue;
		}
		$q = new WP_Query(
			array(
				'post_type'              => 'syria_oil_ad',
				'post_status'            => 'publish',
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'meta_key'               => '_syria_oil_ad_demo_slug',
				'meta_value'             => $def['slug'],
			)
		);
		if ( empty( $q->posts[0] ) ) {
			continue;
		}
		$post = get_post( (int) $q->posts[0] );
		if ( ! $post ) {
			continue;
		}
		$thumb = (int) get_post_thumbnail_id( $post );
		if ( $thumb && syria_oil_attachment_file_exists( $thumb ) && ! syria_oil_attachment_uses_legacy_image_dir( $thumb ) ) {
			continue;
		}
		if ( syria_oil_demo_ad_theme_url( $def['file'] ) ) {
			return true;
		}
		if ( $post && ! syria_oil_ad_has_displayable_creative( $post ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Re-sideload missing/broken featured images for bundled demo side ads.
 *
 * @return int Number of ads repaired.
 */
function syria_oil_repair_side_ad_images() {
	$repaired = 0;
	foreach ( syria_oil_get_demo_side_ad_defs() as $def ) {
		if ( empty( $def['slug'] ) || empty( $def['file'] ) ) {
			continue;
		}
		$q = new WP_Query(
			array(
				'post_type'              => 'syria_oil_ad',
				'post_status'            => 'any',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'meta_key'               => '_syria_oil_ad_demo_slug',
				'meta_value'             => $def['slug'],
			)
		);
		if ( empty( $q->posts[0] ) ) {
			continue;
		}
		$post_id = (int) $q->posts[0]->ID;
		$thumb   = (int) get_post_thumbnail_id( $post_id );
		if ( $thumb && syria_oil_attachment_file_exists( $thumb ) && ! syria_oil_attachment_uses_legacy_image_dir( $thumb ) ) {
			continue;
		}
		if ( $thumb && syria_oil_attachment_uses_legacy_image_dir( $thumb ) ) {
			wp_delete_attachment( $thumb, true );
		}
		$attach = syria_oil_sideload_demo_ad_image( $def['file'], $def['title'] );
		if ( $attach ) {
			set_post_thumbnail( $post_id, $attach );
			++$repaired;
		}
	}
	return $repaired;
}

/**
 * Sideload a local theme demo image into the media library.
 *
 * Reuses an existing attachment tagged with the same demo relative path.
 *
 * @param string $relative Path under theme assets/demo/side-ads/.
 * @param string $title    Attachment title.
 * @return int Attachment ID or 0.
 */
function syria_oil_sideload_demo_ad_image( $relative, $title ) {
	$relative = ltrim( (string) $relative, '/' );

	// Reuse prior upload for this demo file (avoids Media Library bloat on re-seed).
	$existing_q = new WP_Query(
		array(
			'post_type'              => 'attachment',
			'post_status'            => 'inherit',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_key'               => '_syria_oil_demo_ad_file',
			'meta_value'             => $relative,
		)
	);
	if ( ! empty( $existing_q->posts[0] ) ) {
		$existing_id = (int) $existing_q->posts[0];
		if ( syria_oil_attachment_file_exists( $existing_id ) ) {
			return $existing_id;
		}
		wp_delete_attachment( $existing_id, true );
	}

	$path = trailingslashit( SYRIA_OIL_DIR ) . 'assets/demo/side-ads/' . $relative;
	if ( ! is_readable( $path ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$filename = basename( $path );
	$bits     = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( false === $bits ) {
		return 0;
	}

	$upload = wp_upload_bits( $filename, null, $bits );
	if ( ! empty( $upload['error'] ) || empty( $upload['file'] ) ) {
		return 0;
	}

	$filetype = wp_check_filetype( $filename, null );
	$mime     = ! empty( $filetype['type'] ) ? $filetype['type'] : 'image/jpeg';

	// Prefer real mime from file bytes (GIF mislabeled as PNG, etc.).
	if ( function_exists( 'wp_get_image_mime' ) ) {
		$detected = wp_get_image_mime( $upload['file'] );
		if ( $detected ) {
			$mime = $detected;
		}
	}

	$attach_id = wp_insert_attachment(
		array(
			'post_mime_type' => $mime,
			'post_title'     => $title,
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$upload['file']
	);

	if ( is_wp_error( $attach_id ) || ! $attach_id ) {
		return 0;
	}

	$meta = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
	wp_update_attachment_metadata( $attach_id, $meta );
	update_post_meta( $attach_id, '_syria_oil_demo_ad_file', $relative );

	return (int) $attach_id;
}

/**
 * Count published Side Ad posts.
 *
 * @return int
 */
function syria_oil_published_side_ad_count() {
	$counts = wp_count_posts( 'syria_oil_ad' );
	if ( ! $counts ) {
		return 0;
	}
	return (int) $counts->publish;
}

/**
 * Seed demo side ads — one brand/creative per Side Ad (both rails).
 *
 * @param bool $force Re-seed even if already done.
 * @return array{seeded:int,skipped:bool}
 */
function syria_oil_seed_demo_side_ads( $force = false ) {
	$existing = function_exists( 'syria_oil_published_side_ad_count' ) ? syria_oil_published_side_ad_count() : 0;
	if ( ! $force && get_option( 'syria_oil_side_ads_demo_seeded' ) && $existing > 0 && ! syria_oil_side_ads_need_image_repair() ) {
		return array(
			'seeded'          => 0,
			'skipped'         => true,
			'images_repaired' => 0,
		);
	}

	$defs = syria_oil_get_demo_side_ad_defs();

	// Retire old stacked/composite demo creatives.
	$legacy_slugs = array(
		'demo-left-qasioun-arfada-nofsco',
		'demo-right-sps-shell',
		'demo-right-asaar-syrpetro',
	);
	if ( $force ) {
		foreach ( $legacy_slugs as $legacy ) {
			$legacy_q = new WP_Query(
				array(
					'post_type'              => 'syria_oil_ad',
					'post_status'            => 'any',
					'posts_per_page'         => 5,
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'meta_key'               => '_syria_oil_ad_demo_slug',
					'meta_value'             => $legacy,
				)
			);
			foreach ( $legacy_q->posts as $legacy_post ) {
				wp_update_post(
					array(
						'ID'          => (int) $legacy_post->ID,
						'post_status' => 'trash',
					)
				);
			}
		}
	}

	$seeded   = 0;
	$repaired = 0;
	foreach ( $defs as $def ) {
		$existing_q = new WP_Query(
			array(
				'post_type'              => 'syria_oil_ad',
				'post_status'            => 'any',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'meta_key'               => '_syria_oil_ad_demo_slug',
				'meta_value'             => $def['slug'],
			)
		);
		$existing = ! empty( $existing_q->posts[0] ) ? $existing_q->posts[0] : null;

		$post_id = $existing ? (int) $existing->ID : 0;
		if ( ! $post_id ) {
			$post_id = wp_insert_post(
				array(
					'post_type'   => 'syria_oil_ad',
					'post_title'  => $def['title'],
					'post_status' => $def['status'],
					'menu_order'  => (int) $def['order'],
				),
				true
			);
			if ( is_wp_error( $post_id ) || ! $post_id ) {
				continue;
			}
		} else {
			wp_update_post(
				array(
					'ID'          => $post_id,
					'post_title'  => $def['title'],
					'post_status' => $def['status'],
					'menu_order'  => (int) $def['order'],
				)
			);
		}

		update_post_meta( $post_id, '_syria_oil_ad_demo_slug', $def['slug'] );
		update_post_meta( $post_id, '_syria_oil_ad_url', $def['url'] );
		update_post_meta( $post_id, '_syria_oil_ad_position', syria_oil_normalize_ad_position( $def['position'] ) );
		update_post_meta( $post_id, '_syria_oil_ad_tier', syria_oil_normalize_ad_tier( isset( $def['tier'] ) ? $def['tier'] : 'normal' ) );
		update_post_meta( $post_id, '_syria_oil_ad_display', $def['display'] );
		update_post_meta( $post_id, '_syria_oil_ad_pages', array() );
		update_post_meta( $post_id, '_syria_oil_ad_exclude', array() );

		// Sideload when missing, legacy uploads/image/, or when the library file was deleted.
		$thumb = (int) get_post_thumbnail_id( $post_id );
		$needs = $force || ! $thumb || ! syria_oil_attachment_file_exists( $thumb ) || syria_oil_attachment_uses_legacy_image_dir( $thumb );
		if ( $needs ) {
			if ( $thumb && syria_oil_attachment_uses_legacy_image_dir( $thumb ) ) {
				wp_delete_attachment( $thumb, true );
				$thumb = 0;
			}
			$attach = syria_oil_sideload_demo_ad_image( $def['file'], $def['title'] );
			if ( $attach ) {
				set_post_thumbnail( $post_id, $attach );
				if ( $thumb && $thumb !== $attach ) {
					++$repaired;
				}
			}
		}

		++$seeded;
	}

	update_option( 'syria_oil_side_ads_demo_seeded', time(), false );
	return array(
		'seeded'          => $seeded,
		'skipped'         => false,
		'images_repaired' => $repaired,
	);
}

/**
 * Admin URL that triggers demo side-ad seeding.
 *
 * @param string $redirect Redirect target: ads|options|setup.
 * @return string
 */
function syria_oil_seed_demo_ads_url( $redirect = 'ads' ) {
	$redirect = in_array( $redirect, array( 'ads', 'options', 'setup' ), true ) ? $redirect : 'ads';
	return wp_nonce_url(
		add_query_arg(
			array(
				'syria_oil_seed_side_ads' => '1',
				'syria_oil_seed_redirect' => $redirect,
			),
			admin_url( 'admin.php' )
		),
		'syria_oil_seed_side_ads'
	);
}

/**
 * Admin action: seed demo side ads.
 */
function syria_oil_handle_seed_demo_ads() {
	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	if ( empty( $_GET['syria_oil_seed_side_ads'] ) || empty( $_GET['_wpnonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'syria_oil_seed_side_ads' ) ) {
		return;
	}

	$result   = syria_oil_seed_demo_side_ads( true );
	$redirect = isset( $_GET['syria_oil_seed_redirect'] ) ? sanitize_key( wp_unslash( $_GET['syria_oil_seed_redirect'] ) ) : 'ads';

	if ( 'setup' === $redirect ) {
		$target = add_query_arg(
			array(
				'page'                 => 'syria-oil-setup',
				'syria_oil_ads_seeded' => (int) $result['seeded'],
				'syria_oil_ads_repaired' => (int) ( $result['images_repaired'] ?? 0 ),
			),
			admin_url( 'admin.php' )
		);
	} elseif ( 'options' === $redirect ) {
		$target = add_query_arg(
			array(
				'page'                 => 'syria-oil-options',
				'syria_oil_ads_seeded' => (int) $result['seeded'],
			),
			admin_url( 'admin.php' )
		);
	} else {
		$target = add_query_arg(
			array(
				'post_type'            => 'syria_oil_ad',
				'syria_oil_ads_seeded' => (int) $result['seeded'],
			),
			admin_url( 'edit.php' )
		);
	}

	wp_safe_redirect( $target );
	exit;
}
add_action( 'admin_init', 'syria_oil_handle_seed_demo_ads' );

/**
 * Notice after seeding.
 */
function syria_oil_demo_ads_admin_notice() {
	if ( empty( $_GET['syria_oil_ads_seeded'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$count = absint( $_GET['syria_oil_ads_seeded'] );
	printf(
		'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
		esc_html(
			sprintf(
				/* translators: %d: number of ads */
				__( 'Seeded %d demo side ad(s). Check Side Ads in the admin menu.', 'syria-oil' ),
				$count
			)
		)
	);
}
add_action( 'admin_notices', 'syria_oil_demo_ads_admin_notice' );

/**
 * Prominent seed button on the Side Ads list screen.
 *
 * @param string $views Views.
 * @return string
 */
function syria_oil_ad_list_seed_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-syria_oil_ad' !== $screen->id ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$url    = syria_oil_seed_demo_ads_url( 'ads' );
	$count  = (int) wp_count_posts( 'syria_oil_ad' )->publish;
	$repair = function_exists( 'syria_oil_side_ads_need_image_repair' ) && syria_oil_side_ads_need_image_repair();
	?>
	<div class="notice notice-<?php echo $repair ? 'warning' : 'info'; ?>" style="padding:12px 16px;display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between">
		<div>
			<strong><?php esc_html_e( 'Demo side ads', 'syria-oil' ); ?></strong>
			<p style="margin:4px 0 0">
				<?php
				if ( $repair ) {
					esc_html_e( 'Some ads are missing featured image files. Re-seed to upload creatives into the Media Library (front end can also use bundled theme images).', 'syria-oil' );
				} else {
					echo esc_html(
						$count > 0
							? __( 'Re-import individual left/right creatives (one brand per ad, GIF-friendly). Old stacked demo banners are moved to Trash.', 'syria-oil' )
							: __( 'No published side ads yet. Import the bundled individual left/right creatives (and mobile strip).', 'syria-oil' )
					);
				}
				?>
			</p>
		</div>
		<p style="margin:0">
			<a class="button button-primary" href="<?php echo esc_url( $url ); ?>">
				<?php echo $repair ? esc_html__( 'Repair side ad images', 'syria-oil' ) : esc_html__( 'Seed demo side ads', 'syria-oil' ); ?>
			</a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'syria_oil_ad_list_seed_notice' );

/**
 * Shortcode: [syria_oil_mid_ads] — Normal tier (after hero on mobile).
 *
 * @return string
 */
function syria_oil_mid_ads_shortcode() {
	ob_start();
	syria_oil_render_mid_page_ads();
	return (string) ob_get_clean();
}
add_shortcode( 'syria_oil_mid_ads', 'syria_oil_mid_ads_shortcode' );

/**
 * Shortcode: [syria_oil_premium_ads] — Premium tier (under header on mobile).
 *
 * @return string
 */
function syria_oil_premium_ads_shortcode() {
	ob_start();
	syria_oil_render_premium_ads();
	return (string) ob_get_clean();
}
add_shortcode( 'syria_oil_premium_ads', 'syria_oil_premium_ads_shortcode' );

/**
 * Shortcode: [syria_oil_basic_ads] — Basic tier (near end of page on mobile).
 *
 * @return string
 */
function syria_oil_basic_ads_shortcode() {
	ob_start();
	syria_oil_render_basic_ads();
	return (string) ob_get_clean();
}
add_shortcode( 'syria_oil_basic_ads', 'syria_oil_basic_ads_shortcode' );

/**
 * Keep GIF animation intact: never serve intermediate sizes for GIF attachments.
 *
 * @param array|false  $out  Intermediate data or false.
 * @param int          $id   Attachment ID.
 * @param string|int[] $size Requested size.
 * @return array|false
 */
function syria_oil_gif_keep_full_size( $out, $id, $size ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( syria_oil_attachment_is_gif( $id ) ) {
		$url = wp_get_attachment_url( $id );
		if ( ! $url ) {
			return $out;
		}
		$meta = wp_get_attachment_metadata( $id );
		return array(
			$url,
			! empty( $meta['width'] ) ? (int) $meta['width'] : null,
			! empty( $meta['height'] ) ? (int) $meta['height'] : null,
			false,
		);
	}
	return $out;
}
add_filter( 'image_downsize', 'syria_oil_gif_keep_full_size', 10, 3 );
