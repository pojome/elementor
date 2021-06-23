const config = {
	/**
	 * Set DB params for settings
	 */
	db_name: 'wordpress_test',
	db_user: 'root',
	db_pass: 'root',
	db_host: 'localhost',
	/**
	 * Set WP params for settings
	 */
	wp_version: 'latest',
	wp_locale: 'en_US',
	wp_user: 'test',
	wp_user_pass: 'test',
	wp_user_email: 'user@example.org',
	wp_site_name: 'test',
	/**
	 * Current plugin (where the PR)
	 */
	name: 'elementor',
	/**
	 * When needed to used things that exists in yours plugin zip url and not exists in wordpress market plugin folder,
	 * Than use "pluginUrl" : "emptyVrsion"
	 * When the download link of plugin included version, leave the version empty otherwise the process can fail
	 *
	 * Example - "pluginUrl": ""
	 * Download plugin with version from github -> "https://github.com/elementor/elementor/archive/v3.0.15.zip": ""
	 *
	 * Example - "pluginName": "version"
	 * Download plugin with version from wordpress market -> "elementor": "3.0.15"
	 */
	plugins: {},
	theme: {
		'hello-elementor': '2.3.1',
	},
	/**
	 * Declare an array of templates to import for testing
	 * The name of template must be same as the post_name
	 */
	templates: [
		'accordion',
		'alert',
		'audio',
		'buttons',
		'canvas',
		'counter',
		'dividers',
		'global-settings',
		'headings',
		'html',
		'icons',
		'icons-box',
		'icons-list',
		'image',
		'image-box',
		'image-carousel',
		'image-gallery',
		'progress-bar',
		'shortcode',
		'social-icons',
		'spacer',
		'star-rating',
		'tabs',
		'testimonials',
		'text-editor',
		'text-path',
		'toggle',
		'video',
		'wp-widget-text',
	],
	/**
	 * Declare the url origin of local server for testing
	 */
	url_origin: 'http://localhost:8080',
	/**
	 * An array of screen size objects your DOM will be tested against. Add as many as you like -- but add at least one.
	 */
	tests_viewports: [
		{
			label: 'phone',
			width: 767,
			height: 575,
		},
		{
			label: 'tablet',
			width: 1024,
			height: 768,
		},
		{
			label: 'desktop',
			width: 1366,
			height: 768,
		},
	],
};
module.exports = config;
