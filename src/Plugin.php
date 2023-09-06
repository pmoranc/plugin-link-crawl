<?php

namespace LinkCrawl;

class Plugin {

	/**
	 * Initialize the plugin
	 */
	public function init() {
		if ( is_admin() ) {
			add_action( 'admin_menu', [ $this, 'add_option_page' ] );
		}
		add_action( 'admin_post_link_crawl_action', [ $this, 'handle_link_crawl_action' ] );
		add_action( 'crawl_homepage_links_and_save_every_hour', [ $this, 'crawl_homepage_links_and_save' ] );
	}

	/**
	 * Create the option page in WP Dashboard
	 */
	public function add_option_page() {
		add_options_page(
			'Crawl Links',
			'Crawl Links',
			'manage_options',
			'link-crawl',
			[ $this, 'admin_page_callback' ]
		);
	}

	/**
	 * Include the admin page content
	 */
	public function admin_page_callback() {

		global $wpdb;
		$table_name = $wpdb->prefix . 'link_crawls';
		$data       = $wpdb->get_results( "SELECT * FROM {$table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery

		echo $this->get_template( 'form-crawl-content', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Handle the form action
	 */
	public function handle_link_crawl_action() {
		check_admin_referer( 'link_crawl_action_nonce', 'link_crawl_action_nonce' );
		$this->crawl_homepage_links_and_save();

		if ( ! wp_next_scheduled( 'crawl_homepage_links_and_save_every_hour' ) ) {
			wp_schedule_event( time(), 'hourly', 'crawl_homepage_links_and_save_every_hour' );
		}

		$referer = wp_get_referer();
		wp_safe_redirect( $referer );
		exit;
	}

	/**
	 * Crawl the home page for links and save to database
	 */
	public function crawl_homepage_links_and_save() {
		try {
			global $wpdb;

			$links      = $this->crawl_homepage_links();
			$table_name = $wpdb->prefix . 'link_crawls';

			// empty table first.
			$wpdb->query( 'TRUNCATE TABLE ' . $table_name ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery

			foreach ( $links as $link ) {
				$wpdb->insert(
					$table_name,
					[
						'link_url'     => $link,
						'created_date' => gmdate( 'Y-m-d H:i:s' ),
					],
					[ '%s', '%s' ]
				); // db call ok.
			}

			$this->create_sitemap( $links );
			$this->create_homepage_html();

		} catch ( \Exception $e ) {
			return $e->getMessage();
		}
	}

	/**
	 * Find the homepage links
	 */
	public function crawl_homepage_links() {
		$homepage_url     = home_url();
		$homepage_content = wp_remote_get( $homepage_url );

		$links = [];
		preg_match_all( '/<a\s+href=["\']([^"\']+)["\'].*?>/i', $homepage_content['body'], $matches );

		if ( ! empty( $matches[1] ) ) {
			$links = $matches[1];
		}

		return $links;
	}

	/**
	 * Create and save sitemap
	 *
	 * @param array $links Links to save in sitemap.
	 * @throws \Exception If unable to open the file.
	 */
	public function create_sitemap( $links ) {
		$html_sitemap  = '<html>';
		$html_sitemap .= '<head>';
		$html_sitemap .= '<title>Sitemap</title>';
		$html_sitemap .= '</head>';
		$html_sitemap .= '<body>';
		$html_sitemap .= '<h1>Sitemap</h1>';
		$html_sitemap .= '<ul>';

		foreach ( $links as $link ) {
			$html_sitemap .= '<li><a target="_blank" href="' . $link . '">' . $link . '</a></li>';
		}

		$html_sitemap .= '</ul>';
		$html_sitemap .= '</body>';
		$html_sitemap .= '</html>';

		$filename    = 'sitemap.html';
		$uploads_dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'link_crawl';
		wp_mkdir_p( $uploads_dir );
		$file_path = $uploads_dir . '/' . $filename;

		$file = fopen( $file_path, 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen

		if ( false === $file ) {
			throw new \Exception( 'Unable to open the file.' );
		} else {
			if ( fwrite( $file, $html_sitemap ) === false ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
				throw new \Exception( 'Unable to write to the file.' );
			} else {
				fclose( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
			}
		}
	}

	/**
	 * Create the homepage in html format
	 *
	 * @throws \Exception If unable to open the file.
	 */
	public function create_homepage_html() {
		$homepage_url     = home_url();
		$homepage_content = wp_remote_get( $homepage_url );

		$filename    = 'homepage.html';
		$uploads_dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'link_crawl';
		wp_mkdir_p( $uploads_dir );
		$file_path = $uploads_dir . '/' . $filename;

		$file = fopen( $file_path, 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen

		if ( false === $file ) {
			throw new \Exception( 'Unable to open the file.' );
		} else {
			if ( fwrite( $file, $homepage_content['body'] ) === false ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
				throw new \Exception( 'Unable to write to the file.' );
			} else {
				fclose( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
			}
		}
	}


	/**
	 * Delete sitemap
	 */
	public static function delete_files() {
		$uploads_dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'link_crawl';
		$file_path   = $uploads_dir . '/sitemap.html';
		if ( file_exists( $file_path ) ) {
			unlink( $file_path );
		}
		$file_path = $uploads_dir . '/homepage.html';
		if ( file_exists( $file_path ) ) {
			unlink( $file_path );
		}
	}

	/**
	 * Get a template contents.
	 *
	 * @param  string $template The template name.
	 * @param  mixed  $data     Some data to pass to the template.
	 * @return string|bool      The page contents. False if the template doesn't exist.
	 */
	public function get_template( $template, $data = [] ) {
		$file_path = LINK_CRAWL_PATH . 'src/admin/views/' . $template . '.php';
		if ( ! file_exists( $file_path ) ) {
			return false;
		}
		ob_start();
		include $file_path;
		$contents = ob_get_clean();

		return trim( (string) $contents );
	}

	/**
	 * Delete main table
	 */
	public static function delete_db_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'link_crawls';
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $table_name ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery
	}
}
