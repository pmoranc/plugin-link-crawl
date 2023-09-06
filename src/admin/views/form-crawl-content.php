<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Crawl Links', 'link_crawl' ); ?></h1>
	<hr class="wp-header-end">

	<?php if ( ! empty( $data ) ) { ?>
		<div style="display: flex; gap: 10px; align-items: center">
			<h3>Last crawled links</h3>
			<small><?php echo esc_html( $data[0]->created_date ); ?></small> |
			<p>Open last <a target="_blank" href="/wp-content/uploads/link_crawl/sitemap.html">Sitemap HTML</a></p> |
			<p>Open last <a target="_blank" href="/wp-content/uploads/link_crawl/homepage.html">Homepage HTML</a></p>
		</div>
		<ul>
			<?php foreach ( $data as $link_crawl_item ) { ?>
				<li>
					<a target="_blank" href="<?php echo esc_attr( $link_crawl_item->link_url ); ?>">
						<?php echo esc_html( $link_crawl_item->link_url ); ?>
					</a>
				</li>
			<?php } ?>
		</ul>
	<?php } ?>						
	<hr>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'link_crawl_action_nonce', 'link_crawl_action_nonce' ); ?>
		<input type="hidden" name="action" value="link_crawl_action">
		<button type="submit" class="button button-primary">Run Crawler</button>
	</form>
</div>
