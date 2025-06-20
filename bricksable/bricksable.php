<?php
/**
 * Plugin Name: Bricksable
 * Version: 1.6.81
 * Plugin URI: https://bricksable.com/
 * Description: Elevate your website game with the Bricksable collection of premium elements for Bricks Builder. Designed to speed up your workflow, our customizable and fully responsive elements will take your website to the next level in no time.
 * Author: Bricksable
 * Author URI: https://bricksable.com/about-us/
 * Requires at least: 5.6
 * Tested up to: 6.8
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
if ( file_exists( WP_PLUGIN_DIR . '/bricksable-pro/bricksable-pro.php' ) ) {
	// Check if the bricksable pro plugin is active.
	if ( is_plugin_active( 'bricksable-pro/bricksable-pro.php' ) ) {
		return;
	}
}
// Load plugin class files.
require_once 'includes/class-bricksable.php';
require_once 'includes/class-bricksable-settings.php';
require_once 'includes/class-bricksable-review.php';
require_once 'includes/class-bricksable-helper.php';

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
function bricksable() {
	$instance = Bricksable::instance( __FILE__, '1.6.81' );
	define( 'BRICKSABLE_PLUGIN_ASSET_URL', plugins_url( '/assets', __FILE__ ) );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Bricksable_Settings::instance( $instance );
	}

	return $instance;
}

bricksable();
