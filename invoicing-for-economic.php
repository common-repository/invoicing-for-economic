<?php
/*
Plugin Name: Invoicing for economic
Description: Send orders from your Woocommerce based webshop to e-conomic as invoice drafts
Version: 1.0.1
Author: postechdk
Author URI: https://postech.dk
Plugin URI: https://postech.dk/
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: inv-eco
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Main class
 */
class Iwe {
	function __construct() {
		register_activation_hook( __FILE__, array( $this, 'iwe_activation' ) );
		register_deactivation_hook( __FILE__ , array( $this, 'iwe_deactivation') );
		define ( "IWE_VERSION", "1.0.1" );
		define ( "IWE_PLUGIN_PATH", plugin_dir_path( __FILE__) );
		define ( "IWE_PLUGIN_URL", plugin_dir_url( __FILE__ ) );
		$this->do_includes();
		add_action('init', array( $this, 'init_classes' ) );
	}

	/**
	 * Option key prefix
	 *
	 * @var string
	 */
	public static $option_key = '_iwe';

	/**
	 * iwe_activation
	 *
	 * @return void
	 */
	public static function iwe_activation() {
		if ( get_option( '_iwep_settings_plugin_active' ) === 'yes' ) {
			die( __( 'Premium version is already active!', 'inv-eco' ) );
		} else {
			update_option( '_iwe_settings_plugin_active', 'yes' );
		}
	}

	/**
	 * iwe_deactivation
	 *
	 * @return void
	 */
	public static function iwe_deactivation() {
		update_option( '_iwe_settings_plugin_active', 'no' );
	}

	/**
	 * do_includes
	 *
	 * @return void
	 */
	private static function do_includes() {
		include_once IWE_PLUGIN_PATH . '/classes/class-http.php';
		include_once IWE_PLUGIN_PATH . '/classes/class-wc-settings-tab.php';
		include_once IWE_PLUGIN_PATH . '/classes/class-order.php';
		include_once IWE_PLUGIN_PATH . '/classes/class-order-meta.php';
	}

	/**
	 * init_classes
	 *
	 * @return void
	 */
	public static function init_classes() {
		if ( class_exists( 'WooCommerce' ) ) {
			new Iwe_Settings_Tab();
			new Iwe_HTTP();
			new Iwe_Order_Meta();
		}
	}
}

new Iwe();