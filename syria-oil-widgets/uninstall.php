<?php
/**
 * Cleanup on plugin delete (not deactivation).
 *
 * @package Syria_Oil_Widgets
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'syria_oil_widgets_activated' );
delete_option( 'syria_oil_widgets_presets_imported' );
delete_option( 'syria_oil_eia_api_key' );
delete_transient( 'syria_oil_widgets_presets_notice' );
delete_transient( 'syria_oil_commodity_prices' );
wp_clear_scheduled_hook( 'syria_oil_fetch_commodity_prices' );
// Do not delete Elementor library templates or page content.
