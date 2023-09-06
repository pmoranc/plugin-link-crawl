<?php
/**
 * Plugin Name:       Link Crawl
 * Plugin URI:        https://github.com/pmoranc/plugin-link-crawl
 * Description:       Find how my website web pages are linked to my home page so that I can manually search for ways to improve my SEO rankings.
 * Version:           1.0
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            Pablo Morán
 * Author URI:        https://github.com/pmoranc
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LINK_CRAWL_VERSION', '1.0' );
define( 'LINK_CRAWL_FILE', __FILE__ );
define( 'LINK_CRAWL_PATH', realpath( plugin_dir_path( LINK_CRAWL_FILE ) ) . '/' );

if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

require_once plugin_dir_path( __FILE__ ) . 'src/main.php';
