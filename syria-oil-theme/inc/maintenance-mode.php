<?php
/**
 * Maintenance Mode functionality.
 *
 * @package Syria_Oil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if maintenance mode is enabled.
 *
 * @return bool
 */
function syria_oil_is_maintenance_mode() {
	$opts = function_exists( 'syria_oil_get_options_raw' ) ? syria_oil_get_options_raw() : array();
	return ! empty( $opts['maintenance_mode_enabled'] );
}

/**
 * Check if current user can bypass maintenance mode.
 *
 * @return bool
 */
function syria_oil_can_bypass_maintenance() {
	// Admins and editors can always access.
	if ( current_user_can( 'edit_posts' ) ) {
		return true;
	}
	
	// Check if preview mode is enabled via query string.
	if ( ! empty( $_GET['maintenance_preview'] ) && current_user_can( 'manage_options' ) ) {
		return false; // Allow preview for admins.
	}
	
	return false;
}

/**
 * Display maintenance mode page.
 */
function syria_oil_show_maintenance_page() {
	if ( ! syria_oil_is_maintenance_mode() || syria_oil_can_bypass_maintenance() ) {
		return;
	}
	
	// Get theme options for styling.
	$opts = function_exists( 'syria_oil_get_options_raw' ) ? syria_oil_get_options_raw() : array();
	
	$title       = ! empty( $opts['maintenance_mode_title'] ) ? $opts['maintenance_mode_title'] : __( 'Site Under Maintenance', 'syria-oil' );
	$message     = ! empty( $opts['maintenance_mode_message'] ) ? $opts['maintenance_mode_message'] : __( 'We are currently performing scheduled maintenance. We will be back shortly.', 'syria-oil' );
	$show_timer  = ! empty( $opts['maintenance_mode_show_countdown'] );
	$end_time    = ! empty( $opts['maintenance_mode_end_time'] ) ? $opts['maintenance_mode_end_time'] : '';
	$custom_code = ! empty( $opts['maintenance_mode_custom_code'] ) ? $opts['maintenance_mode_custom_code'] : '';
	
	// Get colors from theme settings.
	$primary_color     = ! empty( $opts['color_primary'] ) ? $opts['color_primary'] : '#007a3d';
	$secondary_color   = ! empty( $opts['color_secondary'] ) ? $opts['color_secondary'] : '#d97706';
	$text_color        = ! empty( $opts['color_text'] ) ? $opts['color_text'] : '#0f1419';
	$bg_color          = ! empty( $opts['color_background'] ) ? $opts['color_background'] : '#ffffff';
	$heading_font      = ! empty( $opts['font_headings'] ) ? $opts['font_headings'] : 'IBM Plex Sans Arabic';
	$body_font         = ! empty( $opts['font_body'] ) ? $opts['font_body'] : 'IBM Plex Sans Arabic';
	
	// Get logo.
	$logo_id  = ! empty( $opts['site_logo'] ) ? $opts['site_logo'] : get_theme_mod( 'custom_logo' );
	$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
	
	// Set 503 header.
	status_header( 503 );
	nocache_headers();
	
	?>
	<!DOCTYPE html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex, nofollow">
		<title><?php echo esc_html( $title ); ?> - <?php bloginfo( 'name' ); ?></title>
		<style>
			:root {
				--primary: <?php echo esc_attr( $primary_color ); ?>;
				--secondary: <?php echo esc_attr( $secondary_color ); ?>;
				--text: <?php echo esc_attr( $text_color ); ?>;
				--bg: <?php echo esc_attr( $bg_color ); ?>;
			}
			
			* {
				margin: 0;
				padding: 0;
				box-sizing: border-box;
			}
			
			body {
				font-family: <?php echo esc_attr( $body_font ); ?>, system-ui, -apple-system, sans-serif;
				background: var(--bg);
				color: var(--text);
				line-height: 1.6;
				min-height: 100vh;
				display: flex;
				align-items: center;
				justify-content: center;
				padding: 2rem 1rem;
				direction: <?php echo is_rtl() ? 'rtl' : 'ltr'; ?>;
			}
			
			.maintenance-container {
				max-width: 600px;
				width: 100%;
				text-align: center;
				animation: fadeIn 0.6s ease-out;
			}
			
			@keyframes fadeIn {
				from {
					opacity: 0;
					transform: translateY(20px);
				}
				to {
					opacity: 1;
					transform: translateY(0);
				}
			}
			
			.maintenance-logo {
				margin-bottom: 2rem;
			}
			
			.maintenance-logo img {
				max-width: 180px;
				height: auto;
			}
			
			.maintenance-icon {
				width: 120px;
				height: 120px;
				margin: 0 auto 2rem;
				background: linear-gradient(135deg, var(--primary), var(--secondary));
				border-radius: 50%;
				display: flex;
				align-items: center;
				justify-content: center;
				box-shadow: 0 10px 30px rgba(0, 122, 61, 0.2);
			}
			
			.maintenance-icon svg {
				width: 60px;
				height: 60px;
				fill: #fff;
				animation: pulse 2s ease-in-out infinite;
			}
			
			@keyframes pulse {
				0%, 100% {
					transform: scale(1);
				}
				50% {
					transform: scale(1.1);
				}
			}
			
			h1 {
				font-family: <?php echo esc_attr( $heading_font ); ?>, system-ui, sans-serif;
				font-size: 2rem;
				font-weight: 700;
				color: var(--primary);
				margin-bottom: 1rem;
				line-height: 1.2;
			}
			
			.maintenance-message {
				font-size: 1.1rem;
				color: var(--text);
				margin-bottom: 2rem;
				opacity: 0.85;
			}
			
			.maintenance-countdown {
				display: inline-flex;
				gap: 1rem;
				padding: 1.5rem 2rem;
				background: linear-gradient(135deg, rgba(0, 122, 61, 0.08), rgba(217, 119, 6, 0.08));
				border-radius: 12px;
				margin-bottom: 2rem;
				border: 1px solid rgba(0, 122, 61, 0.15);
			}
			
			.countdown-item {
				display: flex;
				flex-direction: column;
				align-items: center;
				min-width: 60px;
			}
			
			.countdown-value {
				font-size: 2rem;
				font-weight: 700;
				color: var(--primary);
				font-family: <?php echo esc_attr( $heading_font ); ?>, monospace;
			}
			
			.countdown-label {
				font-size: 0.75rem;
				text-transform: uppercase;
				letter-spacing: 0.05em;
				color: var(--text);
				opacity: 0.7;
				margin-top: 0.25rem;
			}
			
			.maintenance-footer {
				margin-top: 3rem;
				padding-top: 2rem;
				border-top: 1px solid rgba(0, 0, 0, 0.08);
			}
			
			.maintenance-footer p {
				font-size: 0.9rem;
				opacity: 0.7;
			}
			
			.maintenance-social {
				display: flex;
				gap: 1rem;
				justify-content: center;
				margin-top: 1.5rem;
			}
			
			.maintenance-social a {
				width: 40px;
				height: 40px;
				border-radius: 50%;
				background: rgba(0, 122, 61, 0.1);
				display: flex;
				align-items: center;
				justify-content: center;
				color: var(--primary);
				text-decoration: none;
				transition: all 0.3s ease;
			}
			
			.maintenance-social a:hover {
				background: var(--primary);
				color: #fff;
				transform: translateY(-3px);
			}
			
			@media (max-width: 640px) {
				h1 {
					font-size: 1.5rem;
				}
				
				.maintenance-message {
					font-size: 1rem;
				}
				
				.maintenance-countdown {
					gap: 0.5rem;
					padding: 1rem 1.5rem;
				}
				
				.countdown-item {
					min-width: 50px;
				}
				
				.countdown-value {
					font-size: 1.5rem;
				}
				
				.countdown-label {
					font-size: 0.65rem;
				}
			}
		</style>
	</head>
	<body>
		<div class="maintenance-container">
			<?php if ( $logo_url ) : ?>
				<div class="maintenance-logo">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php bloginfo( 'name' ); ?>">
				</div>
			<?php else : ?>
				<div class="maintenance-icon">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
						<path d="M22.7 19l-9.1-9.1c.9-2.3.4-5-1.5-6.9-2-2-5-2.4-7.4-1.3L9 6 6 9 1.6 4.7C.4 7.1.9 10.1 2.9 12.1c1.9 1.9 4.6 2.4 6.9 1.5l9.1 9.1c.4.4 1 .4 1.4 0l2.3-2.3c.5-.4.5-1.1.1-1.4z"/>
					</svg>
				</div>
			<?php endif; ?>
			
			<h1><?php echo esc_html( $title ); ?></h1>
			<div class="maintenance-message">
				<?php echo wp_kses_post( wpautop( $message ) ); ?>
			</div>
			
			<?php if ( $show_timer && $end_time ) : ?>
				<div class="maintenance-countdown" id="countdown">
					<div class="countdown-item">
						<span class="countdown-value" id="days">00</span>
						<span class="countdown-label"><?php esc_html_e( 'Days', 'syria-oil' ); ?></span>
					</div>
					<div class="countdown-item">
						<span class="countdown-value" id="hours">00</span>
						<span class="countdown-label"><?php esc_html_e( 'Hours', 'syria-oil' ); ?></span>
					</div>
					<div class="countdown-item">
						<span class="countdown-value" id="minutes">00</span>
						<span class="countdown-label"><?php esc_html_e( 'Minutes', 'syria-oil' ); ?></span>
					</div>
					<div class="countdown-item">
						<span class="countdown-value" id="seconds">00</span>
						<span class="countdown-label"><?php esc_html_e( 'Seconds', 'syria-oil' ); ?></span>
					</div>
				</div>
				
				<script>
				const countdownDate = new Date('<?php echo esc_js( $end_time ); ?>').getTime();
				
				const updateCountdown = () => {
					const now = new Date().getTime();
					const distance = countdownDate - now;
					
					if (distance < 0) {
						document.getElementById('countdown').innerHTML = '<p style="color: var(--secondary); font-weight: 600;"><?php esc_html_e( 'Maintenance completed. Refreshing...', 'syria-oil' ); ?></p>';
						setTimeout(() => location.reload(), 3000);
						return;
					}
					
					const days = Math.floor(distance / (1000 * 60 * 60 * 24));
					const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
					const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
					const seconds = Math.floor((distance % (1000 * 60)) / 1000);
					
					document.getElementById('days').textContent = String(days).padStart(2, '0');
					document.getElementById('hours').textContent = String(hours).padStart(2, '0');
					document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
					document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
				};
				
				updateCountdown();
				setInterval(updateCountdown, 1000);
				</script>
			<?php endif; ?>
			
			<?php if ( $custom_code ) : ?>
				<?php echo wp_kses_post( $custom_code ); ?>
			<?php endif; ?>
			
			<div class="maintenance-footer">
				<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'syria-oil' ); ?></p>
			</div>
		</div>
	</body>
	</html>
	<?php
	exit;
}
add_action( 'template_redirect', 'syria_oil_show_maintenance_page', 1 );

/**
 * Add admin bar notice when maintenance mode is active.
 */
function syria_oil_maintenance_admin_bar_notice( $wp_admin_bar ) {
	if ( ! syria_oil_is_maintenance_mode() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	$preview_url = add_query_arg( 'maintenance_preview', '1', home_url( '/' ) );
	
	$wp_admin_bar->add_node(
		array(
			'id'    => 'maintenance-mode',
			'title' => '<span style="color:#f59e0b;">⚠</span> ' . __( 'Maintenance Mode Active', 'syria-oil' ),
			'href'  => admin_url( 'admin.php?page=syria-oil-options&tab=maintenance' ),
			'meta'  => array(
				'class' => 'maintenance-mode-notice',
				'title' => __( 'Click to manage maintenance mode', 'syria-oil' ),
			),
		)
	);
	
	$wp_admin_bar->add_node(
		array(
			'id'     => 'maintenance-mode-preview',
			'parent' => 'maintenance-mode',
			'title'  => __( 'Preview Maintenance Page', 'syria-oil' ),
			'href'   => $preview_url,
			'meta'   => array(
				'target' => '_blank',
			),
		)
	);
}
add_action( 'admin_bar_menu', 'syria_oil_maintenance_admin_bar_notice', 999 );

/**
 * Dashboard notice when maintenance mode is active.
 */
function syria_oil_maintenance_admin_notice() {
	if ( ! syria_oil_is_maintenance_mode() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	$screen = get_current_screen();
	if ( $screen && false !== strpos( $screen->id, 'syria-oil-options' ) ) {
		return; // Already on settings page.
	}
	
	?>
	<div class="notice notice-warning is-dismissible">
		<p>
			<strong><?php esc_html_e( '⚠ Maintenance Mode is Active', 'syria-oil' ); ?></strong><br>
			<?php esc_html_e( 'Your site is currently in maintenance mode. Visitors see a maintenance page. Only logged-in administrators can access the site.', 'syria-oil' ); ?>
		</p>
		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=syria-oil-options&tab=maintenance' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Manage Maintenance Mode', 'syria-oil' ); ?>
			</a>
			<a href="<?php echo esc_url( add_query_arg( 'maintenance_preview', '1', home_url( '/' ) ) ); ?>" class="button button-secondary" target="_blank">
				<?php esc_html_e( 'Preview Maintenance Page', 'syria-oil' ); ?>
			</a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'syria_oil_maintenance_admin_notice' );
