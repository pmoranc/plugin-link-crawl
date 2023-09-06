<?php

use LinkCrawl\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create the main table when the plugin is activated
 */
function link_crawl_activate_plugin() {
	global $wpdb;
	$table_name      = $wpdb->prefix . 'link_crawls';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id INT NOT NULL AUTO_INCREMENT,
			link_url VARCHAR(255) NOT NULL,
			created_date DATETIME NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

/**
 * Actions when plugin is deactivated
 */
function link_crawl_deactivate_plugin() {
	wp_clear_scheduled_hook( 'crawl_homepage_links_and_save_every_hour' );
	Plugin::delete_db_table();
	Plugin::delete_files();
}

/**
 * Load the plugin
 */
function link_crawl_load_plugin() {
	$link_crawl = new Plugin();
	$link_crawl->init();
}

add_action( 'plugins_loaded', 'link_crawl_load_plugin' );
register_activation_hook( LINK_CRAWL_FILE, 'link_crawl_activate_plugin' );
register_deactivation_hook( LINK_CRAWL_FILE, 'link_crawl_deactivate_plugin' );
