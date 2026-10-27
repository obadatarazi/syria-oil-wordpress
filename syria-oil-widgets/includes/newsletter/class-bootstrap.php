<?php
/**
 * Newsletter system bootstrap.
 *
 * @package Syria_Oil_Widgets
 */

namespace Syria_Oil_Widgets\Newsletter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads subscribers, campaigns, mailer, admin.
 */
class Bootstrap {

	/**
	 * Boot.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_types' ), 5 );
		Mailer::register();
		Admin::register();
	}

	/**
	 * Register CPTs on init.
	 */
	public static function register_types() {
		Subscribers::register();
		Campaigns::register();
	}

	/**
	 * Activation helpers.
	 */
	public static function activate() {
		self::register_types();
		flush_rewrite_rules( false );
	}

	/**
	 * Deactivation helpers.
	 */
	public static function deactivate() {
		Mailer::clear_cron();
	}
}
