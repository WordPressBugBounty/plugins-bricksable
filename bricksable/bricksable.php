<?php
/**
 * Plugin Name: Bricksable
 * Version: 1.6.74
 * Plugin URI: https://bricksable.com/
 * Description: Elevate your website game with the Bricksable collection of premium elements for Bricks Builder. Designed to speed up your workflow, our customizable and fully responsive elements will take your website to the next level in no time.
 * Author: Bricksable
 * Author URI: https://bricksable.com/about-us/
 * Requires at least: 5.6
 * Tested up to: 6.7
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

// Load plugin class files.
require_once 'includes/class-bricksable.php';
require_once 'includes/class-bricksable-settings.php';
require_once 'includes/class-bricksable-review.php';
require_once 'includes/class-bricksable-helper.php';

// Load plugin libraries.
require_once 'includes/lib/class-bricksable-admin-api.php';
require_once 'includes/lib/class-bricksable-post-type.php';
require_once 'includes/lib/class-bricksable-taxonomy.php';

/**
 * Returns the main instance of Bricksable to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Bricksable
 */
function bricksable() {
	$instance = Bricksable::instance( __FILE__, '1.6.74' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Bricksable_Settings::instance( $instance );
	}

	return $instance;
}

bricksable();
