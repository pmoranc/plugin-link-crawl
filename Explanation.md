1. Internal links play a crucial role in helping search engines like Google discover and index website content effectively. By providing a centralized view of all the links within a website, users gain valuable insights for making informed decisions regarding content creation and website structure.

2. A new Wordpress plugin was developed in order to achieve the outcome. Utilizing Object-Oriented Programming (OOP) principles, the code was elegantly structured (can be improved obviously), resulting in enhanced organization and improved performance.

3. The first file loaded is link-crawl.php, where it defines the plugin version and some constants. Then it loads the main.php file where the activation and deactivation methods live. Also there the plugin is initialize after the instantiation of the main class. In the init method the actions are added: the options page is added in the Dashboard, the handler of the Run Crawler button is configured and the cron action is added to be used later.

When the button "Run Crawler" is clicked, the homepage of the site is crawled looking for links using wp_remote_get() function and regex that will match anchors with href. The the matches are saved in the database, in a previously created table (created in the activation of the plugin). The sitemap is also created and stored in the directory wp-content/uploads/link-crawl.

When the plugin is deactivated, the table is erased, also the sitemap.html.

4. The links of the homepage are being saved and displayed in the page as requested. The html sitemap also works. I think the user will be pretty happy.
