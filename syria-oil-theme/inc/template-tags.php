<?php
/**
 * Reusable template helpers.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize a post's publish timestamp (handles corrupt ISO strings in post_date).
 *
 * @param int|WP_Post|null $post Post ID or object.
 * @return int Unix timestamp.
 */
function syria_oil_get_post_timestamp( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return time();
	}

	$candidates = array( $post->post_date_gmt, $post->post_date, $post->post_modified_gmt, $post->post_modified );
	foreach ( $candidates as $raw ) {
		$raw = (string) $raw;
		if ( '' === $raw || '0000-00-00 00:00:00' === $raw ) {
			continue;
		}

		$ts = strtotime( $raw );
		if ( $ts ) {
			return $ts;
		}

		// RSS/ISO accidentally stored in post_date (e.g. 2021-09-07T02:24:52-06:00…).
		if ( preg_match( '/^(\d{4}-\d{2}-\d{2})[T\s](\d{2}:\d{2}:\d{2})/', $raw, $m ) ) {
			$ts = strtotime( $m[1] . ' ' . $m[2] );
			if ( $ts ) {
				return $ts;
			}
		}
	}

	return time();
}

/**
 * Short localized weekday name (e.g. سبت, أحد / Sat, Sun).
 *
 * @param int|null $timestamp Unix timestamp; defaults to now.
 * @return string
 */
function syria_oil_day_name( $timestamp = null ) {
	$ts  = null === $timestamp ? time() : (int) $timestamp;
	$idx = (int) wp_date( 'w', $ts, wp_timezone() );

	$locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
	if ( 0 === strpos( (string) $locale, 'ar' ) ) {
		$days = array( 'أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت' );
	} else {
		$days = array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );
	}

	return isset( $days[ $idx ] ) ? $days[ $idx ] : '';
}

/**
 * Localized calendar date with weekday prefix for front-end display.
 *
 * @param int    $timestamp Unix timestamp.
 * @param string $format    Optional PHP date format for the date portion only.
 * @return string
 */
function syria_oil_format_timestamp( $timestamp, $format = '' ) {
	$ts = (int) $timestamp;
	if ( '' === $format ) {
		$format = (string) get_option( 'date_format', 'j F Y' );
	}
	if ( in_array( $format, array( 'c', 'r', 'U', DATE_ATOM, DATE_RFC2822 ), true ) ) {
		$format = 'j F Y';
	}

	$date_part = wp_date( $format, $ts, wp_timezone() );
	$day       = syria_oil_day_name( $ts );
	if ( '' === $day ) {
		return $date_part;
	}

	$locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
	if ( 0 === strpos( (string) $locale, 'ar' ) ) {
		return $day . '، ' . $date_part;
	}

	return $day . ', ' . $date_part;
}

/**
 * Localized post date for display (never raw ISO on cards).
 *
 * @param int|WP_Post|null $post   Post ID or object.
 * @param string           $format Optional PHP date format (weekday omitted when set).
 * @return string
 */
function syria_oil_format_post_date( $post = null, $format = '' ) {
	$ts = syria_oil_get_post_timestamp( $post );
	if ( '' !== $format ) {
		if ( in_array( $format, array( 'c', 'r', 'U', DATE_ATOM, DATE_RFC2822 ), true ) ) {
			$format = 'j F Y';
		}
		return wp_date( $format, $ts, wp_timezone() );
	}

	return syria_oil_format_timestamp( $ts );
}

/**
 * Output the localized post date as a <time> element.
 *
 * @param int|WP_Post|null $post Optional post.
 */
function syria_oil_posted_on( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return;
	}
	$ts = syria_oil_get_post_timestamp( $post );
	printf(
		'<time class="posted-on" datetime="%1$s">%2$s</time>',
		esc_attr( wp_date( 'c', $ts, wp_timezone() ) ),
		esc_html( syria_oil_format_post_date( $post ) )
	);
}

/**
 * Output the primary category as a badge.
 *
 * @param int|WP_Post|null $post   Optional post.
 * @param bool             $linked Whether to wrap in a category archive link (avoid nesting inside other links).
 */
function syria_oil_category_badge( $post = null, $linked = true ) {
	$post       = get_post( $post );
	$categories = $post ? get_the_category( $post->ID ) : array();
	if ( empty( $categories ) ) {
		return;
	}
	$cat = $categories[0];
	if ( $linked ) {
		printf(
			'<a class="badge" href="%1$s">%2$s</a>',
			esc_url( get_category_link( $cat->term_id ) ),
			esc_html( $cat->name )
		);
		return;
	}
	printf( '<span class="badge">%s</span>', esc_html( $cat->name ) );
}

/**
 * Render a lightweight breadcrumb trail.
 */
function syria_oil_breadcrumb() {
	if ( is_front_page() ) {
		return;
	}

	if ( function_exists( 'syria_oil_mod' ) && ! syria_oil_mod( 'show_breadcrumbs', true ) ) {
		return;
	}

	echo '<nav class="breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'syria-oil' ) . '">';
	printf(
		'<a href="%1$s">%2$s</a><span class="sep">/</span>',
		esc_url( home_url( '/' ) ),
		esc_html__( 'Home', 'syria-oil' )
	);

	if ( is_singular( 'post' ) ) {
		$categories = get_the_category();
		if ( ! empty( $categories ) ) {
			printf(
				'<a href="%1$s">%2$s</a><span class="sep">/</span>',
				esc_url( get_category_link( $categories[0]->term_id ) ),
				esc_html( $categories[0]->name )
			);
		}
		echo '<span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_category() || is_tax() || is_tag() ) {
		echo '<span aria-current="page">' . esc_html( single_term_title( '', false ) ) . '</span>';
	} elseif ( is_search() ) {
		echo '<span aria-current="page">' . esc_html__( 'Search results', 'syria-oil' ) . '</span>';
	} elseif ( is_page() ) {
		echo '<span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	} else {
		echo '<span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	}

	echo '</nav>';
}

/**
 * Trim an excerpt to a word count with an ellipsis.
 *
 * @param int              $words Word limit.
 * @param int|WP_Post|null $post  Optional post.
 * @return string
 */
function syria_oil_get_excerpt( $words = 24, $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	$text = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_strip_all_tags( $post->post_content );
	return wp_trim_words( $text, absint( $words ), '…' );
}

/**
 * Output brand logo with retina / dark / mobile variants from Theme Options.
 *
 * @param string $context default|dark|mobile.
 */
function syria_oil_the_brand_logo( $context = 'default' ) {
	$primary_id = absint( syria_oil_mod( 'logo_primary', 0 ) );
	$mobile_id  = absint( syria_oil_mod( 'logo_mobile', 0 ) );
	$dark_id    = absint( syria_oil_mod( 'logo_dark', 0 ) );
	$retina_id  = absint( syria_oil_mod( 'logo_retina', 0 ) );

	$attachment_id = 0;
	if ( 'mobile' === $context && $mobile_id ) {
		$attachment_id = $mobile_id;
	} elseif ( 'mobile' === $context && $primary_id ) {
		// Mobile context with no dedicated mobile logo — still prefer the primary image.
		$attachment_id = $primary_id;
	} elseif ( 'dark' === $context && $dark_id ) {
		$attachment_id = $dark_id;
	} elseif ( $primary_id ) {
		$attachment_id = $primary_id;
	} elseif ( has_custom_logo() ) {
		$attachment_id = absint( get_theme_mod( 'custom_logo' ) );
	}

	if ( ! $attachment_id ) {
		// Bundled brand logo until Theme Options media is set.
		if ( 'dark' === $context && function_exists( 'syria_oil_brand_logo_white_url' ) && is_readable( syria_oil_brand_logo_white_path() ) ) {
			printf(
				'<a class="brand brand-logo brand-logo--stacked brand-logo--dark" href="%1$s"><img src="%2$s" alt="%3$s" width="120" height="120" loading="eager" decoding="async" /></a>',
				esc_url( home_url( '/' ) ),
				esc_url( syria_oil_brand_logo_white_url() ),
				esc_attr( get_bloginfo( 'name' ) )
			);
			return;
		}
		$brand = function_exists( 'syria_oil_brand_logo_url' ) ? syria_oil_brand_logo_url() : '';
		if ( $brand && is_readable( syria_oil_brand_logo_path() ) ) {
			printf(
				'<a class="brand brand-logo brand-logo--stacked" href="%1$s"><img src="%2$s" alt="%3$s" width="96" height="88" loading="eager" decoding="async" /></a>',
				esc_url( home_url( '/' ) ),
				esc_url( $brand ),
				esc_attr( get_bloginfo( 'name' ) )
			);
			return;
		}
		$emblem = function_exists( 'syria_oil_syrian_emblem_url' ) ? syria_oil_syrian_emblem_url() : '';
		if ( $emblem ) {
			printf(
				'<a class="brand brand-logo brand-logo--emblem" href="%1$s"><img src="%2$s" alt="%3$s" width="56" height="56" loading="eager" decoding="async" /></a>',
				esc_url( home_url( '/' ) ),
				esc_url( $emblem ),
				esc_attr( get_bloginfo( 'name' ) )
			);
			return;
		}
		printf(
			'<a class="brand" href="%1$s"><span class="brand-text"><strong>%2$s</strong>%3$s</span></a>',
			esc_url( home_url( '/' ) ),
			esc_html( get_bloginfo( 'name' ) ),
			get_bloginfo( 'description' ) ? '<span>' . esc_html( get_bloginfo( 'description' ) ) . '</span>' : ''
		);
		return;
	}

	$src    = wp_get_attachment_image_url( $attachment_id, 'full' );
	$alt    = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
	$srcset = '';

	if ( $retina_id && 'dark' !== $context ) {
		$retina = wp_get_attachment_image_url( $retina_id, 'full' );
		if ( $src && $retina ) {
			$srcset = esc_attr( $src . ' 1x, ' . $retina . ' 2x' );
		}
	}

	$classes = 'brand brand-logo brand-logo--stacked';
	if ( 'dark' === $context ) {
		$classes .= ' brand-logo--dark';
	}
	if ( 'mobile' === $context ) {
		$classes .= ' brand-logo--mobile';
	}

	printf(
		'<a class="%1$s" href="%2$s"><img src="%3$s"%4$s alt="%5$s" width="96" height="88" loading="eager" decoding="async" /></a>',
		esc_attr( $classes ),
		esc_url( home_url( '/' ) ),
		esc_url( $src ? $src : '' ),
		$srcset ? ' srcset="' . $srcset . '"' : '',
		esc_attr( $alt ? $alt : get_bloginfo( 'name' ) )
	);

	// Optional mobile override sibling for CSS swap.
	if ( 'default' === $context && $mobile_id && $mobile_id !== $attachment_id ) {
		$m_src = wp_get_attachment_image_url( $mobile_id, 'full' );
		if ( $m_src ) {
			printf(
				'<a class="brand brand-logo brand-logo--stacked brand-logo--mobile-only" href="%1$s"><img src="%2$s" alt="%3$s" width="96" height="88" loading="eager" decoding="async" /></a>',
				esc_url( home_url( '/' ) ),
				esc_url( $m_src ),
				esc_attr( $alt ? $alt : get_bloginfo( 'name' ) )
			);
		}
	}
}

/**
 * Resolve brand logo image URL (primary / mobile / dark / bundled).
 *
 * @param string $context default|dark|mobile.
 * @return string
 */
function syria_oil_get_brand_logo_src( $context = 'default' ) {
	$primary_id = absint( syria_oil_mod( 'logo_primary', 0 ) );
	$mobile_id  = absint( syria_oil_mod( 'logo_mobile', 0 ) );
	$dark_id    = absint( syria_oil_mod( 'logo_dark', 0 ) );

	$attachment_id = 0;
	if ( 'mobile' === $context && $mobile_id ) {
		$attachment_id = $mobile_id;
	} elseif ( 'mobile' === $context && $primary_id ) {
		$attachment_id = $primary_id;
	} elseif ( 'dark' === $context && $dark_id ) {
		$attachment_id = $dark_id;
	} elseif ( $primary_id ) {
		$attachment_id = $primary_id;
	} elseif ( has_custom_logo() ) {
		$attachment_id = absint( get_theme_mod( 'custom_logo' ) );
	}

	if ( $attachment_id ) {
		$url = wp_get_attachment_image_url( $attachment_id, 'full' );
		if ( $url ) {
			return $url;
		}
	}

	if ( function_exists( 'syria_oil_brand_logo_url' ) ) {
		if ( 'dark' === $context && function_exists( 'syria_oil_brand_logo_white_url' ) && is_readable( syria_oil_brand_logo_white_path() ) ) {
			return syria_oil_brand_logo_white_url();
		}
		$brand = syria_oil_brand_logo_url();
		if ( $brand && function_exists( 'syria_oil_brand_logo_path' ) && is_readable( syria_oil_brand_logo_path() ) ) {
			return $brand;
		}
	}

	return '';
}

/**
 * Logo block for the mobile off-canvas menu drawer.
 */
function syria_oil_the_drawer_logo() {
	$src = syria_oil_get_brand_logo_src( 'mobile' );
	if ( ! $src ) {
		$src = syria_oil_get_brand_logo_src( 'default' );
	}
	if ( ! $src ) {
		printf(
			'<a class="nav-drawer-brand__link" href="%1$s"><span class="nav-drawer-brand__text">%2$s</span></a>',
			esc_url( home_url( '/' ) ),
			esc_html( get_bloginfo( 'name' ) )
		);
		return;
	}

	printf(
		'<a class="nav-drawer-brand__link" href="%1$s"><img class="nav-drawer-brand__logo" src="%2$s" alt="%3$s" width="120" height="120" loading="eager" decoding="async" /></a>',
		esc_url( home_url( '/' ) ),
		esc_url( $src ),
		esc_attr( get_bloginfo( 'name' ) )
	);
}

/**
 * Markup for the UI-only AR/EN language toggle.
 *
 * @return string
 */
function syria_oil_lang_toggle_markup() {
	ob_start();
	?>
	<div class="lang-switch" role="group" aria-label="<?php esc_attr_e( 'Language', 'syria-oil' ); ?>" data-syria-oil-lang-switch>
		<button type="button" class="lang-switch__btn is-active" data-lang="ar" aria-pressed="true"><?php esc_html_e( 'AR', 'syria-oil' ); ?></button>
		<button type="button" class="lang-switch__btn" data-lang="en" aria-pressed="false"><?php esc_html_e( 'EN', 'syria-oil' ); ?></button>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Shortcode: [syria_oil_lang_toggle] for Elementor Theme Builder headers.
 *
 * @return string
 */
function syria_oil_lang_toggle_shortcode() {
	return syria_oil_lang_toggle_markup();
}
add_shortcode( 'syria_oil_lang_toggle', 'syria_oil_lang_toggle_shortcode' );
