<?php
namespace Elementor\Core\Editor;

use Elementor\Api;
use Elementor\Core\Breakpoints\Breakpoint;
use Elementor\Core\Breakpoints\Manager as Breakpoints_Manager;
use Elementor\Core\Common\Modules\Ajax\Module;
use Elementor\Core\Common\Modules\Ajax\Module as Ajax;
use Elementor\Core\Debug\Loading_Inspection_Manager;
use Elementor\Core\Files\Assets\Files_Upload_Handler;
use Elementor\Core\Schemes\Manager as Schemes_Manager;
use Elementor\Core\Settings\Manager as SettingsManager;
use Elementor\Icons_Manager;
use Elementor\Plugin;
use Elementor\Settings;
use Elementor\Shapes;
use Elementor\TemplateLibrary\Source_Local;
use Elementor\Tools;
use Elementor\User;
use Elementor\Utils;
use Elementor\Core\Editor\Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor editor.
 *
 * Elementor editor handler class is responsible for initializing Elementor
 * editor and register all the actions needed to display the editor.
 *
 * @since 1.0.0
 */
class Editor {

	/**
	 * The nonce key for Elementor editor.
	 * @deprecated 2.3.0
	 */
	const EDITING_NONCE_KEY = 'elementor-editing';

	/**
	 * User capability required to access Elementor editor.
	 */
	const EDITING_CAPABILITY = 'edit_posts';

	/**
	 * Post ID.
	 *
	 * Holds the ID of the current post being edited.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var int Post ID.
	 */
	private $post_id;

	/**
	 * Whether the edit mode is active.
	 *
	 * Used to determine whether we are in edit mode.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var bool Whether the edit mode is active.
	 */
	private $is_edit_mode;

	/**
	 * @var Notice_Bar
	 */
	public $notice_bar;

	/**
	 * Init.
	 *
	 * Initialize Elementor editor. Registers all needed actions to run Elementor,
	 * removes conflicting actions etc.
	 *
	 * Fired by `admin_action_elementor` action.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool $die Optional. Whether to die at the end. Default is `true`.
	 */
	public function init( $die = true ) {
		if ( empty( $_REQUEST['post'] ) ) {
			return;
		}

		$this->set_post_id( absint( $_REQUEST['post'] ) );

		if ( ! $this->is_edit_mode( $this->post_id ) ) {
			return;
		}

		// BC: From 2.9.0, the editor shouldn't handle the global post / current document.
		// Use requested id and not the global in order to avoid conflicts with plugins that changes the global post.
		query_posts( [
			'p' => $this->post_id,
			'post_type' => get_post_type( $this->post_id ),
		] );

		Plugin::$instance->db->switch_to_post( $this->post_id );

		$document = Plugin::$instance->documents->get( $this->post_id );

		Plugin::$instance->documents->switch_to_document( $document );

		// Change mode to Builder
		$document->set_is_built_with_elementor( true );

		// End BC.

		Loading_Inspection_Manager::instance()->register_inspections();

		// Send MIME Type header like WP admin-header.
		@header( 'Content-Type: ' . get_option( 'html_type' ) . '; charset=' . get_option( 'blog_charset' ) );

		add_filter( 'show_admin_bar', '__return_false' );

		// Remove all WordPress actions
		remove_all_actions( 'wp_head' );
		remove_all_actions( 'wp_print_styles' );
		remove_all_actions( 'wp_print_head_scripts' );
		remove_all_actions( 'wp_footer' );

		// Handle `wp_head`
		add_action( 'wp_head', 'wp_enqueue_scripts', 1 );
		add_action( 'wp_head', 'wp_print_styles', 8 );
		add_action( 'wp_head', 'wp_print_head_scripts', 9 );
		add_action( 'wp_head', 'wp_site_icon' );
		add_action( 'wp_head', [ $this, 'editor_head_trigger' ], 30 );

		// Handle `wp_footer`
		add_action( 'wp_footer', 'wp_print_footer_scripts', 20 );
		add_action( 'wp_footer', 'wp_auth_check_html', 30 );
		add_action( 'wp_footer', [ $this, 'wp_footer' ] );

		// Handle `wp_enqueue_scripts`
		remove_all_actions( 'wp_enqueue_scripts' );

		// Also remove all scripts hooked into after_wp_tiny_mce.
		remove_all_actions( 'after_wp_tiny_mce' );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 999999 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ], 999999 );

		// Setup default heartbeat options
		add_filter( 'heartbeat_settings', function( $settings ) {
			$settings['interval'] = 15;
			return $settings;
		} );

		// Tell to WP Cache plugins do not cache this request.
		Utils::do_not_cache();

		do_action( 'elementor/editor/init' );

		$this->print_editor_template();

		// From the action it's an empty string, from tests its `false`
		if ( false !== $die ) {
			die;
		}
	}

	/**
	 * Retrieve post ID.
	 *
	 * Get the ID of the current post.
	 *
	 * @since 1.8.0
	 * @access public
	 *
	 * @return int Post ID.
	 */
	public function get_post_id() {
		return $this->post_id;
	}

	/**
	 * Redirect to new URL.
	 *
	 * Used as a fallback function for the old URL structure of Elementor page
	 * edit URL.
	 *
	 * Fired by `template_redirect` action.
	 *
	 * @since 1.6.0
	 * @access public
	 */
	public function redirect_to_new_url() {
		if ( ! isset( $_GET['elementor'] ) ) {
			return;
		}

		$document = Plugin::$instance->documents->get( get_the_ID() );

		if ( ! $document ) {
			wp_die( __( 'Document not found.', 'elementor' ) );
		}

		if ( ! $document->is_editable_by_current_user() || ! $document->is_built_with_elementor() ) {
			return;
		}

		wp_safe_redirect( $document->get_edit_url() );
		die;
	}

	/**
	 * Whether the edit mode is active.
	 *
	 * Used to determine whether we are in the edit mode.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $post_id Optional. Post ID. Default is `null`, the current
	 *                     post ID.
	 *
	 * @return bool Whether the edit mode is active.
	 */
	public function is_edit_mode( $post_id = null ) {
		if ( null !== $this->is_edit_mode ) {
			return $this->is_edit_mode;
		}

		if ( empty( $post_id ) ) {
			$post_id = $this->post_id;
		}

		$document = Plugin::$instance->documents->get( $post_id );

		if ( ! $document || ! $document->is_editable_by_current_user() ) {
			return false;
		}

		/** @var Module ajax */
		$ajax_data = Plugin::$instance->common->get_component( 'ajax' )->get_current_action_data();

		if ( ! empty( $ajax_data ) && 'get_document_config' === $ajax_data['action'] ) {
			return true;
		}

		// Ajax request as Editor mode
		$actions = [
			'elementor',

			// Templates
			'elementor_get_templates',
			'elementor_save_template',
			'elementor_get_template',
			'elementor_delete_template',
			'elementor_import_template',
			'elementor_library_direct_actions',
		];

		if ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], $actions ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Lock post.
	 *
	 * Mark the post as currently being edited by the current user.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $post_id The ID of the post being edited.
	 */
	public function lock_post( $post_id ) {
		if ( ! function_exists( 'wp_set_post_lock' ) ) {
			require_once ABSPATH . 'wp-admin/includes/post.php';
		}

		wp_set_post_lock( $post_id );
	}

	/**
	 * Get locked user.
	 *
	 * Check what user is currently editing the post.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $post_id The ID of the post being edited.
	 *
	 * @return \WP_User|false User information or false if the post is not locked.
	 */
	public function get_locked_user( $post_id ) {
		if ( ! function_exists( 'wp_check_post_lock' ) ) {
			require_once ABSPATH . 'wp-admin/includes/post.php';
		}

		$locked_user = wp_check_post_lock( $post_id );
		if ( ! $locked_user ) {
			return false;
		}

		return get_user_by( 'id', $locked_user );
	}

	/**
	 * Print Editor Template.
	 *
	 * Include the wrapper template of the editor.
	 *
	 * @since 2.2.0
	 * @access public
	 */
	public function print_editor_template() {
		include ELEMENTOR_PATH . 'includes/editor-templates/editor-wrapper.php';
	}

	/**
	 * Enqueue scripts.
	 *
	 * Registers all the editor scripts and enqueues them.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_scripts() {
		remove_action( 'wp_enqueue_scripts', [ $this, __FUNCTION__ ], 999999 );

		global $wp_styles, $wp_scripts;

		$plugin = Plugin::$instance;

		// Reset global variable
		$wp_styles = new \WP_Styles(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wp_scripts = new \WP_Scripts(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'ELEMENTOR_TESTS' ) && ELEMENTOR_TESTS ) ? '' : '.min';

		wp_register_script(
			'elementor-editor-modules',
			ELEMENTOR_ASSETS_URL . 'js/editor-modules' . $suffix . '.js',
			[
				'elementor-common-modules',
			],
			ELEMENTOR_VERSION,
			true
		);

		wp_register_script(
			'elementor-editor-document',
			ELEMENTOR_ASSETS_URL . 'js/editor-document' . $suffix . '.js',
			[
				'elementor-common-modules',
			],
			ELEMENTOR_VERSION,
			true
		);
		// Hack for waypoint with editor mode.
		wp_register_script(
			'elementor-waypoints',
			ELEMENTOR_ASSETS_URL . 'lib/waypoints/waypoints-for-editor.js',
			[
				'jquery',
			],
			'4.0.2',
			true
		);

		wp_register_script(
			'perfect-scrollbar',
			ELEMENTOR_ASSETS_URL . 'lib/perfect-scrollbar/js/perfect-scrollbar' . $suffix . '.js',
			[],
			'1.4.0',
			true
		);

		wp_register_script(
			'jquery-easing',
			ELEMENTOR_ASSETS_URL . 'lib/jquery-easing/jquery-easing' . $suffix . '.js',
			[
				'jquery',
			],
			'1.3.2',
			true
		);

		wp_register_script(
			'nprogress',
			ELEMENTOR_ASSETS_URL . 'lib/nprogress/nprogress' . $suffix . '.js',
			[],
			'0.2.0',
			true
		);

		wp_register_script(
			'tipsy',
			ELEMENTOR_ASSETS_URL . 'lib/tipsy/tipsy' . $suffix . '.js',
			[
				'jquery',
			],
			'1.0.0',
			true
		);

		wp_register_script(
			'jquery-elementor-select2',
			ELEMENTOR_ASSETS_URL . 'lib/e-select2/js/e-select2.full' . $suffix . '.js',
			[
				'jquery',
			],
			'4.0.6-rc.1',
			true
		);

		wp_register_script(
			'flatpickr',
			ELEMENTOR_ASSETS_URL . 'lib/flatpickr/flatpickr' . $suffix . '.js',
			[
				'jquery',
			],
			'1.12.0',
			true
		);

		wp_register_script(
			'ace',
			'https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.5/ace.js',
			[],
			'1.2.5',
			true
		);

		wp_register_script(
			'ace-language-tools',
			'https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.5/ext-language_tools.js',
			[
				'ace',
			],
			'1.2.5',
			true
		);

		wp_register_script(
			'jquery-hover-intent',
			ELEMENTOR_ASSETS_URL . 'lib/jquery-hover-intent/jquery-hover-intent' . $suffix . '.js',
			[],
			'1.0.0',
			true
		);

		wp_register_script(
			'nouislider',
			ELEMENTOR_ASSETS_URL . 'lib/nouislider/nouislider' . $suffix . '.js',
			[],
			'13.0.0',
			true
		);

		wp_register_script(
			'pickr',
			ELEMENTOR_ASSETS_URL . 'lib/pickr/pickr.min.js',
			[],
			'1.5.0',
			true
		);

		wp_register_script(
			'elementor-editor',
			ELEMENTOR_ASSETS_URL . 'js/editor' . $suffix . '.js',
			[
				'elementor-common',
				'elementor-editor-modules',
				'elementor-editor-document',
				'wp-auth-check',
				'jquery-ui-sortable',
				'jquery-ui-resizable',
				'perfect-scrollbar',
				'nprogress',
				'tipsy',
				'imagesloaded',
				'heartbeat',
				'jquery-elementor-select2',
				'flatpickr',
				'ace',
				'ace-language-tools',
				'jquery-hover-intent',
				'nouislider',
				'pickr',
				'react',
				'react-dom',
			],
			ELEMENTOR_VERSION,
			true
		);

		/**
		 * Before editor enqueue scripts.
		 *
		 * Fires before Elementor editor scripts are enqueued.
		 *
		 * @since 1.0.0
		 */
		do_action( 'elementor/editor/before_enqueue_scripts' );

		// Tweak for WP Admin menu icons
		wp_print_styles( 'editor-buttons' );

		$settings = SettingsManager::get_settings_managers_config();
		// Moved to document since 2.9.0.
		unset( $settings['page'] );

		$document = Plugin::$instance->documents->get_doc_or_auto_save( $this->post_id );
		$kits_manager = Plugin::$instance->kits_manager;

		$page_title_selector = $kits_manager->get_current_settings( 'page_title_selector' );

		$page_title_selector .= ', .elementor-page-title .elementor-heading-title';

		$config = [
			'initial_document' => $document->get_config(),
			'version' => ELEMENTOR_VERSION,
			'home_url' => home_url(),
			'admin_settings_url' => admin_url( 'admin.php?page=' . Settings::PAGE_ID ),
			'admin_tools_url' => admin_url( 'admin.php?page=' . Tools::PAGE_ID ),
			'autosave_interval' => AUTOSAVE_INTERVAL,
			'tabs' => $plugin->controls_manager->get_tabs(),
			'controls' => $plugin->controls_manager->get_controls_data(),
			'elements' => $plugin->elements_manager->get_element_types_config(),
			'schemes' => [
				'items' => $plugin->schemes_manager->get_registered_schemes_data(),
				'enabled_schemes' => Schemes_Manager::get_enabled_schemes(),
			],
			'globals' => [
				'defaults_enabled' => [
					'colors' => $kits_manager->is_custom_colors_enabled(),
					'typography' => $kits_manager->is_custom_typography_enabled(),
				],
			],
			'icons' => [
				'libraries' => Icons_Manager::get_icon_manager_tabs_config(),
				'goProURL' => Utils::get_pro_link( 'https://elementor.com/pro/?utm_source=icon-library&utm_campaign=gopro&utm_medium=wp-dash' ),
			],
			'filesUpload' => [
				'unfilteredFiles' => Files_Upload_Handler::is_enabled(),
			],
			'fa4_to_fa5_mapping_url' => ELEMENTOR_ASSETS_URL . 'lib/font-awesome/migration/mapping.js',
			'default_schemes' => $plugin->schemes_manager->get_schemes_defaults(),
			'settings' => $settings,
			'system_schemes' => $plugin->schemes_manager->get_system_schemes(),
			'wp_editor' => $this->get_wp_editor_config(),
			'settings_page_link' => Settings::get_url(),
			'tools_page_link' => Tools::get_url(),
			'elementor_site' => 'https://go.elementor.com/about-elementor/',
			'docs_elementor_site' => 'https://go.elementor.com/docs/',
			'help_the_content_url' => 'https://go.elementor.com/the-content-missing/',
			'help_flexbox_bc_url' => 'https://go.elementor.com/flexbox-layout-bc/',
			'elementPromotionURL' => 'https://go.elementor.com/go-pro-%s',
			'dynamicPromotionURL' => 'https://go.elementor.com/go-pro-dynamic-tag',
			'additional_shapes' => Shapes::get_additional_shapes_for_config(),
			'user' => [
				'restrictions' => $plugin->role_manager->get_user_restrictions_array(),
				'is_administrator' => current_user_can( 'manage_options' ),
				'introduction' => User::get_introduction_meta(),
			],
			'preview' => [
				'help_preview_error_url' => 'https://go.elementor.com/preview-not-loaded/',
				'help_preview_http_error_url' => 'https://go.elementor.com/preview-not-loaded/#permissions',
				'help_preview_http_error_500_url' => 'https://go.elementor.com/500-error/',
				'debug_data' => Loading_Inspection_Manager::instance()->run_inspections(),
			],
			'locale' => get_locale(),
			'rich_editing_enabled' => filter_var( get_user_meta( get_current_user_id(), 'rich_editing', true ), FILTER_VALIDATE_BOOLEAN ),
			'page_title_selector' => $page_title_selector,
			'tinymceHasCustomConfig' => class_exists( 'Tinymce_Advanced' ) || class_exists( 'Advanced_Editor_Tools' ),
			'inlineEditing' => Plugin::$instance->widgets_manager->get_inline_editing_config(),
			'dynamicTags' => Plugin::$instance->dynamic_tags->get_config(),
			'ui' => [
				'darkModeStylesheetURL' => ELEMENTOR_ASSETS_URL . 'css/editor-dark-mode' . $suffix . '.css',
				'defaultGenericFonts' => $kits_manager->get_current_settings( 'default_generic_fonts' ),
			],
			// Empty array for BC to avoid errors.
			'i18n' => [],
		];

		if ( ! Utils::has_pro() && current_user_can( 'manage_options' ) ) {
			$config['promotionWidgets'] = Api::get_promotion_widgets();
		}

		$this->bc_move_document_filters();

		/**
		 * Localize editor settings.
		 *
		 * Filters the editor localized settings.
		 *
		 * @since 1.0.0
		 *
		 * @param array $config  Editor configuration.
		 * @param int   $post_id The ID of the current post being edited.
		 */
		$config = apply_filters( 'elementor/editor/localize_settings', $config );

		Utils::print_js_config( 'elementor-editor', 'ElementorConfig', $config );

		wp_enqueue_script( 'elementor-editor' );

		wp_set_script_translations( 'elementor-editor', 'elementor' );

		$plugin->controls_manager->enqueue_control_scripts();

		/**
		 * After editor enqueue scripts.
		 *
		 * Fires after Elementor editor scripts are enqueued.
		 *
		 * @since 1.0.0
		 */
		do_action( 'elementor/editor/after_enqueue_scripts' );
	}

	/**
	 * Enqueue styles.
	 *
	 * Registers all the editor styles and enqueues them.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_styles() {
		/**
		 * Before editor enqueue styles.
		 *
		 * Fires before Elementor editor styles are enqueued.
		 *
		 * @since 1.0.0
		 */
		do_action( 'elementor/editor/before_enqueue_styles' );

		$suffix = Utils::is_script_debug() ? '' : '.min';

		$direction_suffix = is_rtl() ? '-rtl' : '';

		wp_register_style(
			'font-awesome',
			ELEMENTOR_ASSETS_URL . 'lib/font-awesome/css/font-awesome' . $suffix . '.css',
			[],
			'4.7.0'
		);

		wp_register_style(
			'elementor-select2',
			ELEMENTOR_ASSETS_URL . 'lib/e-select2/css/e-select2' . $suffix . '.css',
			[],
			'4.0.6-rc.1'
		);

		wp_register_style(
			'google-font-roboto',
			'https://fonts.googleapis.com/css?family=Roboto:300,400,500,700',
			[],
			ELEMENTOR_VERSION
		);

		wp_register_style(
			'flatpickr',
			ELEMENTOR_ASSETS_URL . 'lib/flatpickr/flatpickr' . $suffix . '.css',
			[],
			'1.12.0'
		);

		wp_register_style(
			'pickr',
			ELEMENTOR_ASSETS_URL . 'lib/pickr/themes/monolith.min.css',
			[],
			'1.5.0'
		);

		wp_register_style(
			'elementor-editor',
			ELEMENTOR_ASSETS_URL . 'css/editor' . $direction_suffix . $suffix . '.css',
			[
				'elementor-common',
				'elementor-select2',
				'elementor-icons',
				'wp-auth-check',
				'google-font-roboto',
				'flatpickr',
				'pickr',
			],
			ELEMENTOR_VERSION
		);

		wp_enqueue_style( 'elementor-editor' );

		$ui_theme = SettingsManager::get_settings_managers( 'editorPreferences' )->get_model()->get_settings( 'ui_theme' );

		if ( 'light' !== $ui_theme ) {
			$ui_theme_media_queries = 'all';

			if ( 'auto' === $ui_theme ) {
				$ui_theme_media_queries = '(prefers-color-scheme: dark)';
			}

			wp_enqueue_style(
				'elementor-editor-dark-mode',
				ELEMENTOR_ASSETS_URL . 'css/editor-dark-mode' . $suffix . '.css',
				[
					'elementor-editor',
				],
				ELEMENTOR_VERSION,
				$ui_theme_media_queries
			);
		}

		$breakpoints = Plugin::$instance->breakpoints->get_breakpoints();

		// The two breakpoints under 'tablet' need to be checked for values.
		if ( $breakpoints[ Breakpoints_Manager::BREAKPOINT_KEY_MOBILE ]->is_custom() || $breakpoints[ Breakpoints_Manager::BREAKPOINT_KEY_MOBILE_EXTRA ]->is_enabled() ) {
			wp_add_inline_style(
				'elementor-editor',
				'.elementor-device-tablet #elementor-preview-responsive-wrapper { width: ' . Plugin::$instance->breakpoints->get_device_min_breakpoint( Breakpoints_Manager::BREAKPOINT_KEY_TABLET ) . 'px; }'
			);
		}

		/**
		 * After editor enqueue styles.
		 *
		 * Fires after Elementor editor styles are enqueued.
		 *
		 * @since 1.0.0
		 */
		do_action( 'elementor/editor/after_enqueue_styles' );
	}

	/**
	 * Get WordPress editor config.
	 *
	 * Config the default WordPress editor with custom settings for Elementor use.
	 *
	 * @since 1.9.0
	 * @access private
	 */
	private function get_wp_editor_config() {
		// Remove all TinyMCE plugins.
		remove_all_filters( 'mce_buttons', 10 );
		remove_all_filters( 'mce_external_plugins', 10 );

		if ( ! class_exists( '\_WP_Editors', false ) ) {
			require ABSPATH . WPINC . '/class-wp-editor.php';
		}

		// WordPress 4.8 and higher
		if ( method_exists( '\_WP_Editors', 'print_tinymce_scripts' ) ) {
			\_WP_Editors::print_default_editor_scripts();
			\_WP_Editors::print_tinymce_scripts();
		}
		ob_start();

		wp_editor(
			'%%EDITORCONTENT%%',
			'elementorwpeditor',
			[
				'editor_class' => 'elementor-wp-editor',
				'editor_height' => 250,
				'drag_drop_upload' => true,
			]
		);

		$config = ob_get_clean();

		// Don't call \_WP_Editors methods again
		remove_action( 'admin_print_footer_scripts', [ '_WP_Editors', 'editor_js' ], 50 );
		remove_action( 'admin_print_footer_scripts', [ '_WP_Editors', 'print_default_editor_scripts' ], 45 );

		\_WP_Editors::editor_js();

		return $config;
	}

	/**
	 * Editor head trigger.
	 *
	 * Fires the 'elementor/editor/wp_head' action in the head tag in Elementor
	 * editor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function editor_head_trigger() {
		/**
		 * Elementor editor head.
		 *
		 * Fires on Elementor editor head tag.
		 *
		 * Used to prints scripts or any other data in the head tag.
		 *
		 * @since 1.0.0
		 */
		do_action( 'elementor/editor/wp_head' );
	}

	/**
	 * WP footer.
	 *
	 * Prints Elementor editor with all the editor templates, and render controls,
	 * widgets and content elements.
	 *
	 * Fired by `wp_footer` action.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function wp_footer() {
		$plugin = Plugin::$instance;

		$plugin->controls_manager->render_controls();
		$plugin->widgets_manager->render_widgets_content();
		$plugin->elements_manager->render_elements_content();

		$plugin->schemes_manager->print_schemes_templates();

		$plugin->dynamic_tags->print_templates();

		$this->init_editor_templates();

		/**
		 * Elementor editor footer.
		 *
		 * Fires on Elementor editor before closing the body tag.
		 *
		 * Used to prints scripts or any other HTML before closing the body tag.
		 *
		 * @since 1.0.0
		 */
		do_action( 'elementor/editor/footer' );
	}

	/**
	 * Set edit mode.
	 *
	 * Used to update the edit mode.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool $edit_mode Whether the edit mode is active.
	 */
	public function set_edit_mode( $edit_mode ) {
		$this->is_edit_mode = $edit_mode;
	}

	/**
	 * Editor constructor.
	 *
	 * Initializing Elementor editor and redirect from old URL structure of
	 * Elementor editor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		Plugin::$instance->data_manager->register_controller( Data\Globals\Controller::class );

		$this->notice_bar = new Notice_Bar();

		add_action( 'admin_action_elementor', [ $this, 'init' ] );
		add_action( 'template_redirect', [ $this, 'redirect_to_new_url' ] );

		// Handle autocomplete feature for URL control.
		add_filter( 'wp_link_query_args', [ $this, 'filter_wp_link_query_args' ] );
		add_filter( 'wp_link_query', [ $this, 'filter_wp_link_query' ] );
	}

	/**
	 * @since 2.2.0
	 * @access public
	 */
	public function filter_wp_link_query_args( $query ) {
		$library_cpt_key = array_search( Source_Local::CPT, $query['post_type'], true );
		if ( false !== $library_cpt_key ) {
			unset( $query['post_type'][ $library_cpt_key ] );
		}

		return $query;
	}

	/**
	 * @since 2.2.0
	 * @access public
	 */
	public function filter_wp_link_query( $results ) {
		if ( isset( $_POST['editor'] ) && 'elementor' === $_POST['editor'] ) {
			$post_type_object = get_post_type_object( 'post' );
			$post_label = $post_type_object->labels->singular_name;

			foreach ( $results as & $result ) {
				if ( 'post' === get_post_type( $result['ID'] ) ) {
					$result['info'] = $post_label;
				}
			}
		}

		return $results;
	}

	/**
	 * Init editor templates.
	 *
	 * Initialize default elementor templates used in the editor panel.
	 *
	 * @since 1.7.0
	 * @access private
	 */
	private function init_editor_templates() {
		$template_names = [
			'global',
			'panel',
			'panel-elements',
			'repeater',
			'templates',
			'navigator',
			'hotkeys',
			'responsive-bar',
		];

		foreach ( $template_names as $template_name ) {
			Plugin::$instance->common->add_template( ELEMENTOR_PATH . "includes/editor-templates/$template_name.php" );
		}
	}

	private function bc_move_document_filters() {
		global $wp_filter;

		$old_tag = 'elementor/editor/localize_settings';
		$new_tag = 'elementor/document/config';

		if ( ! has_filter( $old_tag ) ) {
			return;
		}

		foreach ( $wp_filter[ $old_tag ] as $priority => $filters ) {
			foreach ( $filters as $filter_id => $filter_args ) {
				if ( 2 === $filter_args['accepted_args'] ) {
					remove_filter( $old_tag, $filter_id, $priority );

					add_filter( $new_tag, $filter_args['function'], $priority, 2 );

					// TODO: Hard deprecation
					// _deprecated_hook( '`' . $old_tag . ` is no longer using post_id', '2.9.0', $new_tag' );
				}
			}
		}
	}

	public function set_post_id( $post_id ) {
		$this->post_id = $post_id;
	}
}
