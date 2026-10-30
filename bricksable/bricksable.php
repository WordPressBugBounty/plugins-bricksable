<?php
/**
 * Plugin Name: Bricksable
 * Version: 1.6.85
 * Plugin URI: https://bricksable.com/
 * Description: Elevate your website game with the Bricksable collection of premium elements for Bricks Builder. Designed to speed up your workflow, our customizable and fully responsive elements will take your website to the next level in no time.
 * Author: Bricksable
 * Author URI: https://bricksable.com/about-us/
 * Requires at least: 5.6
 * Tested up to: 7.1.4
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: bricksable
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Bricksable
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether any Bricksable Pro copy is already running.
 *
 * Pro may live outside the canonical bricksable-pro/ folder (zip upload
 * or incomplete update). Folder slug alone is not a reliable check.
 *
 * @return bool
 */
if ( ! function_exists( 'bricksable_pro_is_active' ) ) {
	function bricksable_pro_is_active() {
		if ( defined( 'BRICKSABLE_PRO_ACTIVE' ) && BRICKSABLE_PRO_ACTIVE ) {
			return true;
		}

		if ( ! function_exists( 'get_option' ) ) {
			return false;
		}

		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$network_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
			$active_plugins  = array_merge( $active_plugins, array_keys( $network_plugins ) );
		}

		foreach ( $active_plugins as $plugin ) {
			if ( 'bricksable-pro.php' === basename( (string) $plugin ) ) {
				return true;
			}
		}

		return false;
	}
}

if ( bricksable_pro_is_active() ) {
	return;
}

// Load plugin class files.
require_once 'includes/class-bricksable.php';
require_once 'includes/class-bricksable-settings.php';
require_once 'includes/class-bricksable-review.php';
require_once 'includes/class-bricksable-helper.php';
require_once 'includes/class-bricksable-migration.php';

// Load plugin libraries.
require_once 'includes/lib/class-bricksable-admin-api.php';
require_once 'includes/lib/class-bricksable-post-type.php';
require_once 'includes/lib/class-bricksable-taxonomy.php';
require_once 'includes/lib/class-persist-admin-notices-dismissal.php';

/**
 * Returns the main instance of Bricksable to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Bricksable
 */
if ( ! function_exists( 'bricksable' ) ) {
	function bricksable() {
		$instance = Bricksable::instance( __FILE__, '1.6.85' );
		define( 'BRICKSABLE_PLUGIN_ASSET_URL', plugins_url( '/assets', __FILE__ ) );

		if ( is_null( $instance->settings ) ) {
			$instance->settings = Bricksable_Settings::instance( $instance );
		}

		// Initialize data migration (Action Scheduler).
		Bricksable_Migration::init();

		return $instance;
	}
}

bricksable();
