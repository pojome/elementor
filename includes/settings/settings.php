<?php
namespace Elementor;

use Elementor\TemplateLibrary\Source_Local;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor "Settings" page in WordPress Dashboard.
 *
 * Elementor settings page handler class responsible for creating and displaying
 * Elementor "Settings" page in WordPress dashboard.
 *
 * @since 1.0.0
 */
class Settings extends Settings_Page {

	/**
	 * Settings page ID for Elementor settings.
	 */
	const PAGE_ID = 'elementor';

	/**
	 * Go Pro menu priority.
	 */
	const MENU_PRIORITY_GO_PRO = 502;

	/**
	 * Settings page field for update time.
	 */
	const UPDATE_TIME_FIELD = '_elementor_settings_update_time';

	/**
	 * Settings page general tab slug.
	 */
	const TAB_GENERAL = 'general';

	/**
	 * Settings page style tab slug.
	 */
	const TAB_STYLE = 'style';

	/**
	 * Settings page integrations tab slug.
	 */
	const TAB_INTEGRATIONS = 'integrations';

	/**
	 * Settings page advanced tab slug.
	 */
	const TAB_ADVANCED = 'advanced';

	/**
	 * Register admin menu.
	 *
	 * Add new Elementor Settings admin menu.
	 *
	 * Fired by `admin_menu` action.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_admin_menu() {
		global $menu;

		$menu[] = [ '', 'read', 'separator-elementor', '', 'wp-menu-separator elementor' ]; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_menu_page(
			__( 'Elementor', 'elementor' ),
			__( 'Elementor', 'elementor' ),
			'manage_options',
			self::PAGE_ID,
			[ $this, 'display_settings_page' ],
			'',
			'58.5'
		);
	}

	/**
	 * Reorder the Elementor menu items in admin.
	 * Based on WC.
	 *
	 * @since 2.4.0
	 *
	 * @param array $menu_order Menu order.
	 * @return array
	 */
	public function menu_order( $menu_order ) {
		// Initialize our custom order array.
		$elementor_menu_order = [];

		// Get the index of our custom separator.
		$elementor_separator = array_search( 'separator-elementor', $menu_order, true );

		// Get index of library menu.
		$elementor_library = array_search( Source_Local::ADMIN_MENU_SLUG, $menu_order, true );

		// Loop through menu order and do some rearranging.
		foreach ( $menu_order as $index => $item ) {
			if ( 'elementor' === $item ) {
				$elementor_menu_order[] = 'separator-elementor';
				$elementor_menu_order[] = $item;
				$elementor_menu_order[] = Source_Local::ADMIN_MENU_SLUG;

				unset( $menu_order[ $elementor_separator ] );
				unset( $menu_order[ $elementor_library ] );
			} elseif ( ! in_array( $item, [ 'separator-elementor' ], true ) ) {
				$elementor_menu_order[] = $item;
			}
		}

		// Return order.
		return $elementor_menu_order;
	}

	/**
	 * Register Elementor Pro sub-menu.
	 *
	 * Add new Elementor Pro sub-menu under the main Elementor menu.
	 *
	 * Fired by `admin_menu` action.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_pro_menu() {
		add_submenu_page(
			self::PAGE_ID,
			__( 'Submissions', 'elementor' ),
			__( 'Submissions', 'elementor' ),
			'manage_options',
			'e-form-submissions',
			function() {
				$this->elementor_form_submissions();
			}
		);

		add_submenu_page(
			self::PAGE_ID,
			__( 'Custom Fonts', 'elementor' ),
			__( 'Custom Fonts', 'elementor' ),
			'manage_options',
			'elementor_custom_fonts',
			[ $this, 'elementor_custom_fonts' ]
		);

		add_submenu_page(
			self::PAGE_ID,
			__( 'Custom Icons', 'elementor' ),
			__( 'Custom Icons', 'elementor' ),
			'manage_options',
			'elementor_custom_icons',
			[ $this, 'elementor_custom_icons' ]
		);

		add_submenu_page(
			self::PAGE_ID,
			'',
			'<span class="dashicons dashicons-star-filled" style="font-size: 17px"></span> ' . __( 'Go Pro', 'elementor' ),
			'manage_options',
			'go_elementor_pro',
			[ $this, 'handle_external_redirects' ]
		);

		add_submenu_page( Source_Local::ADMIN_MENU_SLUG, __( 'Popups', 'elementor' ), __( 'Popups', 'elementor' ), 'manage_options', 'popup_templates', [ $this, 'elementor_popups' ] );
	}

	/**
	 * Register Elementor knowledge base sub-menu.
	 *
	 * Add new Elementor knowledge base sub-menu under the main Elementor menu.
	 *
	 * Fired by `admin_menu` action.
	 *
	 * @since 2.0.3
	 * @access public
	 */
	public function register_knowledge_base_menu() {
		add_submenu_page(
			self::PAGE_ID,
			'',
			__( 'Getting Started', 'elementor' ),
			'manage_options',
			'elementor-getting-started',
			[ $this, 'elementor_getting_started' ]
		);

		add_submenu_page(
			self::PAGE_ID,
			'',
			__( 'Get Help', 'elementor' ),
			'manage_options',
			'go_knowledge_base_site',
			[ $this, 'handle_external_redirects' ]
		);
	}

	/**
	 * Go Elementor Pro.
	 *
	 * Redirect the Elementor Pro page the clicking the Elementor Pro menu link.
	 *
	 * Fired by `admin_init` action.
	 *
	 * @since 2.0.3
	 * @access public
	 */
	public function handle_external_redirects() {
		if ( empty( $_GET['page'] ) ) {
			return;
		}

		if ( 'go_elementor_pro' === $_GET['page'] ) {
			wp_redirect( Utils::get_pro_link( 'https://elementor.com/pro/?utm_source=wp-menu&utm_campaign=gopro&utm_medium=wp-dash' ) );
			die;
		}

		if ( 'go_knowledge_base_site' === $_GET['page'] ) {
			wp_redirect( 'https://go.elementor.com/docs-admin-menu/' );
			die;
		}
	}

	/**
	 * Display settings page.
	 *
	 * Output the content for the getting started page.
	 *
	 * @since 2.2.0
	 * @access public
	 */
	public function elementor_getting_started() {
		if ( User::is_current_user_can_edit_post_type( 'page' ) ) {
			$create_new_label = __( 'Create Your First Page', 'elementor' );
			$create_new_cpt = 'page';
		} elseif ( User::is_current_user_can_edit_post_type( 'post' ) ) {
			$create_new_label = __( 'Create Your First Post', 'elementor' );
			$create_new_cpt = 'post';
		}

		?>
		<div class="wrap">
			<div class="e-getting-started">
				<div class="e-getting-started__box postbox">
					<div class="e-getting-started__header">
						<div class="e-getting-started__title">
							<div class="e-logo-wrapper">
								<i class="eicon-elementor"></i>
							</div>
							<?php echo __( 'Getting Started', 'elementor' ); ?>
						</div>
						<a class="e-getting-started__skip" href="<?php echo esc_url( admin_url() ); ?>">
							<i class="eicon-close" aria-hidden="true" title="<?php esc_attr_e( 'Skip', 'elementor' ); ?>"></i>
							<span class="elementor-screen-only"><?php echo __( 'Skip', 'elementor' ); ?></span>
						</a>
					</div>
					<div class="e-getting-started__content">
						<div class="e-getting-started__content--narrow">
							<h2><?php echo __( 'Welcome to Elementor', 'elementor' ); ?></h2>
							<p><?php echo __( 'Get introduced to Elementor by watching our "Getting Started" video series. It will guide you through the steps needed to create your website. Then click to create your first page.', 'elementor' ); ?></p>
						</div>

						<div class="e-getting-started__video">
							<iframe width="620" height="350" src="https://www.youtube-nocookie.com/embed/videoseries?list=PLZyp9H25CboH8b_wsNyOmstckiOE8aUBg&amp;controls=1&amp;modestbranding=1" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
						</div>

						<div class="e-getting-started__actions e-getting-started__content--narrow">
							<?php if ( ! empty( $create_new_cpt ) ) : ?>
							<a href="<?php echo esc_url( Plugin::$instance->documents->get_create_new_post_url( $create_new_cpt ) ); ?>" class="button button-primary button-hero"><?php echo esc_html( $create_new_label ); ?></a>
							<?php endif; ?>

							<a href="https://go.elementor.com/getting-started/" target="_blank" class="button button-secondary button-hero"><?php echo __( 'Watch the Full Guide', 'elementor' ); ?></a>
						</div>
					</div>
				</div>
			</div>
		</div><!-- /.wrap -->
		<?php
	}

	/**
	 * Display settings page.
	 *
	 * Output the content for the custom fonts page.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function elementor_custom_fonts() {
		?>
		<div class="wrap">
			<div class="elementor-blank_state">
				<img src="<?php echo ELEMENTOR_ASSETS_URL . 'images/go-pro-wp-dashboard.svg'; ?>" />
				<h2><?php echo __( 'Add Your Custom Fonts', 'elementor' ); ?></h2>
				<p><?php echo __( 'Custom Fonts allows you to add your self-hosted fonts and use them on your Elementor projects to create a unique brand language.', 'elementor' ); ?></p>
				<a class="elementor-button elementor-button-default elementor-button-go-pro" target="_blank" href="<?php echo Utils::get_pro_link( 'https://elementor.com/pro/?utm_source=wp-custom-fonts&utm_campaign=gopro&utm_medium=wp-dash' ); ?>"><?php echo __( 'Go Pro', 'elementor' ); ?></a>
			</div>
		</div><!-- /.wrap -->
		<?php
	}

	/**
	 * Display settings page.
	 *
	 * Output the content for the custom icons page.
	 *
	 * @since 2.8.0
	 * @access public
	 */
	public function elementor_custom_icons() {
		?>
		<div class="wrap">
			<div class="elementor-blank_state">
				<img src="<?php echo ELEMENTOR_ASSETS_URL . 'images/go-pro-wp-dashboard.svg'; ?>" />
				<h2><?php echo __( 'Add Your Custom Icons', 'elementor' ); ?></h2>
				<p><?php echo __( 'Don\'t rely solely on the FontAwesome icons everyone else is using! Differentiate your website and your style with custom icons you can upload from your favorite icons source.', 'elementor' ); ?></p>
				<a class="elementor-button elementor-button-default elementor-button-go-pro" target="_blank" href="<?php echo Utils::get_pro_link( 'https://elementor.com/pro/?utm_source=wp-custom-icons&utm_campaign=gopro&utm_medium=wp-dash' ); ?>"><?php echo __( 'Go Pro', 'elementor' ); ?></a>
			</div>
		</div><!-- /.wrap -->
		<?php
	}

	/**
	 * Display settings page.
	 *
	 * Output the content for the Popups page.
	 *
	 * @since 2.4.0
	 * @access public
	 */
	public function elementor_popups() {
		?>
		<div class="wrap">
			<div class="elementor-blank_state">
				<img src="<?php echo ELEMENTOR_ASSETS_URL . 'images/go-pro-wp-dashboard.svg'; ?>" />
				<h2><?php echo __( 'Get Popup Builder', 'elementor' ); ?></h2>
				<p><?php echo __( 'Popup Builder lets you take advantage of all the amazing features in Elementor, so you can build beautiful & highly converting popups. Go pro and start designing your popups today.', 'elementor' ); ?></p>
				<a class="elementor-button elementor-button-default elementor-button-go-pro" target="_blank" href="<?php echo Utils::get_pro_link( 'https://elementor.com/popup-builder/?utm_source=popup-templates&utm_campaign=gopro&utm_medium=wp-dash' ); ?>"><?php echo __( 'Go Pro', 'elementor' ); ?></a>
			</div>
		</div><!-- /.wrap -->
		<?php
	}

	public function elementor_form_submissions() {
		?>
		<div class="wrap">
			<div class="elementor-blank_state">
				<img src="<?php echo ELEMENTOR_ASSETS_URL . 'images/go-pro-wp-dashboard.svg'; ?>" />
				<h2><?php echo __( 'Collect Your Form Submissions', 'elementor' ); ?></h2>
				<p>
					<?php echo __( 'Save and manage all of your form submissions in one single place.
All within a simple, intuitive place.', 'elementor' ); ?>
					<a href="http://go.elementor.com/wp-dash-submissions" target="_blank" rel="nofollow">
						<?php echo __( 'Learn More', 'elementor' ); ?>
					</a>
				</p>
				<a class="elementor-button elementor-button-default elementor-button-go-pro" target="_blank" href="<?php echo Utils::get_pro_link( 'https://go.elementor.com/go-pro-submissions' ); ?>"><?php echo __( 'Go Pro', 'elementor' ); ?></a>
			</div>
		</div><!-- /.wrap -->
		<?php
	}

	/**
	 * On admin init.
	 *
	 * Preform actions on WordPress admin initialization.
	 *
	 * Fired by `admin_init` action.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function on_admin_init() {
		$this->handle_external_redirects();

		$this->maybe_remove_all_admin_notices();
	}

	/**
	 * Change "Settings" menu name.
	 *
	 * Update the name of the Settings admin menu from "Elementor" to "Settings".
	 *
	 * Fired by `admin_menu` action.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_menu_change_name() {
		Utils::change_submenu_first_item_label( 'elementor', __( 'Settings', 'elementor' ) );
	}

	/**
	 * Update CSS print method.
	 *
	 * Clear post CSS cache.
	 *
	 * Fired by `add_option_elementor_css_print_method` and
	 * `update_option_elementor_css_print_method` actions.
	 *
	 * @since 1.7.5
	 * @access public
	 * @deprecated 3.0.0
	 */
	public function update_css_print_method() {
		Plugin::$instance->files_manager->clear_cache();
	}

	/**
	 * Create tabs.
	 *
	 * Return the settings page tabs, sections and fields.
	 *
	 * @since 1.5.0
	 * @access protected
	 *
	 * @return array An array with the settings page tabs, sections and fields.
	 */
	protected function create_tabs() {
		$validations_class_name = __NAMESPACE__ . '\Settings_Validations';

		return [
			self::TAB_GENERAL => [
				'label' => __( 'General', 'elementor' ),
				'sections' => [
					'general' => [
						'fields' => [
							self::UPDATE_TIME_FIELD => [
								'full_field_id' => self::UPDATE_TIME_FIELD,
								'field_args' => [
									'type' => 'hidden',
								],
								'setting_args' => [ $validations_class_name, 'current_time' ],
							],
							'cpt_support' => [
								'label' => __( 'Post Types', 'elementor' ),
								'field_args' => [
									'type' => 'checkbox_list_cpt',
									'std' => [ 'page', 'post' ],
									'exclude' => [ 'attachment', 'elementor_library' ],
								],
								'setting_args' => [ $validations_class_name, 'checkbox_list' ],
							],
							'disable_color_schemes' => [
								'label' => __( 'Disable Default Colors', 'elementor' ),
								'field_args' => [
									'type' => 'checkbox',
									'value' => 'yes',
									'sub_desc' => __( 'Checking this box will disable Elementor\'s Default Colors, and make Elementor inherit the colors from your theme.', 'elementor' ),
								],
							],
							'disable_typography_schemes' => [
								'label' => __( 'Disable Default Fonts', 'elementor' ),
								'field_args' => [
									'type' => 'checkbox',
									'value' => 'yes',
									'sub_desc' => __( 'Checking this box will disable Elementor\'s Default Fonts, and make Elementor inherit the fonts from your theme.', 'elementor' ),
								],
							],
						],
					],
					'usage' => [
						'label' => __( 'Improve Elementor', 'elementor' ),
						'fields' => $this->get_usage_fields(),
					],
				],
			],
			self::TAB_STYLE => [
				'label' => __( 'Style', 'elementor' ),
				'sections' => [
					'style' => [
						'fields' => [
							'notice' => [
								'label' => __( 'Looking for the Style settings?', 'elementor' ),
								'field_args' => [
									'type' => 'raw_html',
									'html' => __( 'The Style settings changed its location and can now be found within Elementor Editor\'s <b>Panel > Hamburger Menu > Site Settings</b>.<br>You can use the Site Settings to make changes and see them live!', 'elementor' ) . sprintf( ' <a target="_blank" href="http://go.elementor.com/panel-layout-settings">%s</a>', __( 'Learn More', 'elementor' ) ),
								],
							],
						],
					],
				],
			],
			self::TAB_INTEGRATIONS => [
				'label' => __( 'Integrations', 'elementor' ),
				'sections' => [
					'google_maps' => [
						'label' => __( 'Google Maps Embed API', 'elementor' ),
						'callback' => function() {
							printf( __( 'Google Maps Embed API is a free service by Google that allows embedding Google Maps in your site. For more details, visit Google Maps\' <a href="%s" target="_blank">Using API Keys</a> page.', 'elementor' ), esc_url( 'https://developers.google.com/maps/documentation/embed/get-api-key' ) );
						},
						'fields' => [
							'google_maps_api_key' => [
								'label' => __( 'API Key', 'elementor' ),
								'field_args' => [
									'class' => 'elementor_google_maps_api_key',
									'type' => 'text',
								],
							],
						],
					],
				],
			],
			self::TAB_ADVANCED => [
				'label' => __( 'Advanced', 'elementor' ),
				'sections' => [
					'advanced' => [
						'fields' => [
							'css_print_method' => [
								'label' => __( 'CSS Print Method', 'elementor' ),
								'field_args' => [
									'class' => 'elementor_css_print_method',
									'type' => 'select',
									'options' => [
										'external' => __( 'External File', 'elementor' ),
										'internal' => __( 'Internal Embedding', 'elementor' ),
									],
									'desc' => '<div class="elementor-css-print-method-description" data-value="external" style="display: none">' . __( 'Use external CSS files for all generated stylesheets. Choose this setting for better performance (recommended).', 'elementor' ) . '</div><div class="elementor-css-print-method-description" data-value="internal" style="display: none">' . __( 'Use internal CSS that is embedded in the head of the page. For troubleshooting server configuration conflicts and managing development environments.', 'elementor' ) . '</div>',
								],
							],
							'editor_break_lines' => [
								'label' => __( 'Switch Editor Loader Method', 'elementor' ),
								'field_args' => [
									'type' => 'select',
									'options' => [
										'' => __( 'Disable', 'elementor' ),
										1 => __( 'Enable', 'elementor' ),
									],
									'desc' => __( 'For troubleshooting server configuration conflicts.', 'elementor' ),
								],
							],
							'unfiltered_files_upload' => [
								'label' => __( 'Enable Unfiltered File Uploads', 'elementor' ),
								'field_args' => [
									'type' => 'select',
									'std' => '',
									'options' => [
										'' => __( 'Disable', 'elementor' ),
										1 => __( 'Enable', 'elementor' ),
									],
									'desc' => __( 'Please note! Allowing uploads of any files (SVG & JSON included) is a potential security risk.', 'elementor' ) . '<br>' . __( 'Elementor will try to sanitize the unfiltered files, removing potential malicious code and scripts.', 'elementor' ) . '<br>' . __( 'We recommend you only enable this feature if you understand the security risks involved.', 'elementor' ),
								],
							],
							'font_display' => [
								'label' => __( 'Google Fonts Load', 'elementor' ),
								'field_args' => [
									'type' => 'select',
									'std' => 'auto',
									'options' => [
										'auto' => __( 'Default', 'elementor' ),
										'block' => __( 'Blocking', 'elementor' ),
										'swap' => __( 'Swap', 'elementor' ),
										'fallback' => __( 'Fallback', 'elementor' ),
										'optional' => __( 'Optional', 'elementor' ),
									],
									'desc' => __( 'Font-display property defines how font files are loaded and displayed by the browser.', 'elementor' ) . '<br>' . __( 'Set the way Google Fonts are being loaded by selecting the font-display property (Default: Auto).', 'elementor' ),
								],
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Get settings page title.
	 *
	 * Retrieve the title for the settings page.
	 *
	 * @since 1.5.0
	 * @access protected
	 *
	 * @return string Settings page title.
	 */
	protected function get_page_title() {
		return __( 'Elementor', 'elementor' );
	}

	/**
	 * @since 2.2.0
	 * @access private
	 */
	private function maybe_remove_all_admin_notices() {
		$elementor_pages = [
			'elementor-getting-started',
			'elementor_custom_fonts',
			'elementor_custom_icons',
			'elementor-license',
			'popup_templates',
		];

		if ( empty( $_GET['page'] ) || ! in_array( $_GET['page'], $elementor_pages, true ) ) {
			return;
		}

		remove_all_actions( 'admin_notices' );
	}

	/**
	 * Settings page constructor.
	 *
	 * Initializing Elementor "Settings" page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'admin_init', [ $this, 'on_admin_init' ] );
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ], 20 );
		add_action( 'admin_menu', [ $this, 'admin_menu_change_name' ], 200 );
		add_action( 'admin_menu', [ $this, 'register_pro_menu' ], self::MENU_PRIORITY_GO_PRO );
		add_action( 'admin_menu', [ $this, 'register_knowledge_base_menu' ], 501 );

		$clear_cache_callback = [ Plugin::$instance->files_manager, 'clear_cache' ];

		// Clear CSS Meta after change css related methods.
		$css_settings = [
			'elementor_disable_color_schemes',
			'elementor_disable_typography_schemes',
			'elementor_css_print_method',
		];

		foreach ( $css_settings as $option_name ) {
			add_action( "add_option_{$option_name}", $clear_cache_callback );
			add_action( "update_option_{$option_name}", $clear_cache_callback );
		}

		add_filter( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', [ $this, 'menu_order' ] );
	}
}
