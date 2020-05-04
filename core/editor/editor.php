<?php
namespace Elementor\Core\Editor;

use Elementor\Api;
use Elementor\Core\Common\Modules\Ajax\Module;
use Elementor\Core\Common\Modules\Ajax\Module as Ajax;
use Elementor\Core\Debug\Loading_Inspection_Manager;
use Elementor\Core\Responsive\Responsive;
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
		if ( empty( $_REQUEST['post'] ) ) { // WPCS: CSRF ok.
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
		Plugin::$instance->db->set_is_elementor_page( $this->post_id );

		// End BC.

		Loading_Inspection_Manager::instance()->register_inspections();

		// Send MIME Type header like WP admin-header.
		@header( 'Content-Type: ' . get_option( 'html_type' ) . '; charset=' . get_option( 'blog_charset' ) );

		// Temp: Allow plugins to know that the editor route is ready. TODO: Remove on 2.7.3.
		define( 'ELEMENTOR_EDITOR_USE_ROUTER', true );

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
		$wp_styles = new \WP_Styles(); // WPCS: override ok.
		$wp_scripts = new \WP_Scripts(); // WPCS: override ok.

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

		$page_title_selector = Plugin::$instance->kits_manager->get_active_kit_for_fronend()->get_settings( 'page_title_selector' );

		$page_title_selector .= ', .elementor-page-title';

		$settings = SettingsManager::get_settings_managers_config();
		// Moved to document since 2.9.0.
		unset( $settings['page'] );

		$document = Plugin::$instance->documents->get_doc_or_auto_save( $this->post_id );

		$config = [
			'initial_document' => $document->get_config(),
			'version' => ELEMENTOR_VERSION,
			'home_url' => home_url(),
			'autosave_interval' => AUTOSAVE_INTERVAL,
			'tabs' => $plugin->controls_manager->get_tabs(),
			'controls' => $plugin->controls_manager->get_controls_data(),
			'elements' => $plugin->elements_manager->get_element_types_config(),
			'schemes' => [
				'items' => $plugin->schemes_manager->get_registered_schemes_data(),
				'enabled_schemes' => Schemes_Manager::get_enabled_schemes(),
			],
			'icons' => [
				'libraries' => Icons_Manager::get_icon_manager_tabs_config(),
				'goProURL' => Utils::get_pro_link( 'https://elementor.com/pro/?utm_source=icon-library&utm_campaign=gopro&utm_medium=wp-dash' ),
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
			'help_right_click_url' => 'https://go.elementor.com/meet-right-click/',
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
			'tinymceHasCustomConfig' => class_exists( 'Tinymce_Advanced' ),
			'inlineEditing' => Plugin::$instance->widgets_manager->get_inline_editing_config(),
			'dynamicTags' => Plugin::$instance->dynamic_tags->get_config(),
			'ui' => [
				'darkModeStylesheetURL' => ELEMENTOR_ASSETS_URL . 'css/themes/dark' . $suffix . '.css',
				'lightModeStylesheetURL' => ELEMENTOR_ASSETS_URL . 'css/themes/light' . $suffix . '.css',
			],
			'i18n' => [
				'elementor' => __( 'Elementor', 'elementor' ),
				'edit' => __( 'Edit', 'elementor' ),
				'delete' => __( 'Delete', 'elementor' ),
				'cancel' => __( 'Cancel', 'elementor' ),
				'clear' => __( 'Clear', 'elementor' ),
				'done' => __( 'Done', 'elementor' ),
				'got_it' => __( 'Got It', 'elementor' ),
				/* translators: %s: Element type. */
				'add_element' => __( 'Add %s', 'elementor' ),
				/* translators: %s: Element name. */
				'edit_element' => __( 'Edit %s', 'elementor' ),
				/* translators: %s: Element type. */
				'duplicate_element' => __( 'Duplicate %s', 'elementor' ),
				/* translators: %s: Element type. */
				'delete_element' => __( 'Delete %s', 'elementor' ),
				'flexbox_attention_header' => __( 'Note: Flexbox Changes', 'elementor' ),
				'flexbox_attention_message' => __( 'Elementor 2.5 introduces key changes to the layout using CSS Flexbox. Your existing pages might have been affected, please review your page before publishing.', 'elementor' ),
				'add_picked_color' => __( 'Add Picked Color', 'elementor' ),
				'saved_colors' => __( 'Saved Colors', 'elementor' ),
				'drag_to_delete' => __( 'Drag To Delete', 'elementor' ),

				// Menu.
				'about_elementor' => __( 'About Elementor', 'elementor' ),
				'elementor_settings' => __( 'Dashboard Settings', 'elementor' ),
				'global_colors' => __( 'Default Colors', 'elementor' ),
				'global_fonts' => __( 'Default Fonts', 'elementor' ),
				'global_style' => __( 'Global Style', 'elementor' ),
				'global_settings' => __( 'Global Settings', 'elementor' ),
				'preferences' => __( 'Preferences', 'elementor' ),
				'settings' => __( 'Settings', 'elementor' ),
				'more' => __( 'More', 'elementor' ),
				'view_page' => __( 'View Page', 'elementor' ),
				'exit_to_dashboard' => __( 'Exit To Dashboard', 'elementor' ),

				// Elements.
				'inner_section' => __( 'Inner Section', 'elementor' ),

				// Control Order.
				'asc' => __( 'Ascending order', 'elementor' ),
				'desc' => __( 'Descending order', 'elementor' ),

				// Clear Page.
				'clear_page' => __( 'Delete All Content', 'elementor' ),
				'dialog_confirm_clear_page' => __( 'Attention: We are going to DELETE ALL CONTENT from this page. Are you sure you want to do that?', 'elementor' ),

				// Enable SVG uploads.
				'enable_svg' => __( 'Enable SVG Uploads', 'elementor' ),
				'dialog_confirm_enable_svg' => __( 'Before you enable SVG upload, note that SVG files include a security risk. Elementor does run a process to remove possible malicious code, but there is still risk involved when using such files.', 'elementor' ),

				// Enable fontawesome 5 if needed.
				'enable_fa5' => __( 'Elementor\'s New Icon Library', 'elementor' ),
				'dialog_confirm_enable_fa5' => __( 'Elementor v2.6 includes an upgrade from Font Awesome 4 to 5. In order to continue using icons, be sure to click "Upgrade".', 'elementor' ) . ' <a href="https://go.elementor.com/fontawesome-migration/" target="_blank">' . __( 'Learn More', 'elementor' ) . '</a>',

				// Panel Preview Mode.
				'back_to_editor' => __( 'Show Panel', 'elementor' ),
				'preview' => __( 'Hide Panel', 'elementor' ),

				// Inline Editing.
				'type_here' => __( 'Type Here', 'elementor' ),

				// Library.
				'an_error_occurred' => __( 'An error occurred', 'elementor' ),
				'category' => __( 'Category', 'elementor' ),
				'delete_template' => __( 'Delete Template', 'elementor' ),
				'delete_template_confirm' => __( 'Are you sure you want to delete this template?', 'elementor' ),
				'import_template_dialog_header' => __( 'Import Document Settings', 'elementor' ),
				'import_template_dialog_message' => __( 'Do you want to also import the document settings of the template?', 'elementor' ),
				'import_template_dialog_message_attention' => __( 'Attention: Importing may override previous settings.', 'elementor' ),
				'library' => __( 'Library', 'elementor' ),
				'no' => __( 'No', 'elementor' ),
				'page' => __( 'Page', 'elementor' ),
				/* translators: %s: Template type. */
				'save_your_template' => __( 'Save Your %s to Library', 'elementor' ),
				'save_your_template_description' => __( 'Your designs will be available for export and reuse on any page or website', 'elementor' ),
				'section' => __( 'Section', 'elementor' ),
				'templates_empty_message' => __( 'This is where your templates should be. Design it. Save it. Reuse it.', 'elementor' ),
				'templates_empty_title' => __( 'Haven’t Saved Templates Yet?', 'elementor' ),
				'templates_no_favorites_message' => __( 'You can mark any pre-designed template as a favorite.', 'elementor' ),
				'templates_no_favorites_title' => __( 'No Favorite Templates', 'elementor' ),
				'templates_no_results_message' => __( 'Please make sure your search is spelled correctly or try a different words.', 'elementor' ),
				'templates_no_results_title' => __( 'No Results Found', 'elementor' ),
				'templates_request_error' => __( 'The following error(s) occurred while processing the request:', 'elementor' ),
				'yes' => __( 'Yes', 'elementor' ),
				'blocks' => __( 'Blocks', 'elementor' ),
				'pages' => __( 'Pages', 'elementor' ),
				'my_templates' => __( 'My Templates', 'elementor' ),

				// Incompatible Device.
				'device_incompatible_header' => __( 'Your browser isn\'t compatible', 'elementor' ),
				'device_incompatible_message' => __( 'Your browser isn\'t compatible with all of Elementor\'s editing features. We recommend you switch to another browser like Chrome or Firefox.', 'elementor' ),
				'proceed_anyway' => __( 'Proceed Anyway', 'elementor' ),

				// Preview not loaded.
				'learn_more' => __( 'Learn More', 'elementor' ),
				'preview_el_not_found_header' => __( 'Sorry, the content area was not found in your page.', 'elementor' ),
				'preview_el_not_found_message' => __( 'You must call \'the_content\' function in the current template, in order for Elementor to work on this page.', 'elementor' ),

				// Gallery.
				'delete_gallery' => __( 'Reset Gallery', 'elementor' ),
				'dialog_confirm_gallery_delete' => __( 'Are you sure you want to reset this gallery?', 'elementor' ),
				/* translators: %s: The number of images. */
				'gallery_images_selected' => __( '%s Images Selected', 'elementor' ),
				'gallery_no_images_selected' => __( 'No Images Selected', 'elementor' ),
				'insert_media' => __( 'Insert Media', 'elementor' ),

				// Take Over.
				/* translators: %s: User name. */
				'dialog_user_taken_over' => __( '%s has taken over and is currently editing. Do you want to take over this page editing?', 'elementor' ),
				'go_back' => __( 'Go Back', 'elementor' ),
				'take_over' => __( 'Take Over', 'elementor' ),

				// Revisions.
				/* translators: %s: Template type. */
				'dialog_confirm_delete' => __( 'Are you sure you want to remove this %s?', 'elementor' ),

				// Saver.
				'before_unload_alert' => __( 'Please note: All unsaved changes will be lost.', 'elementor' ),
				'published' => __( 'Published', 'elementor' ),
				'publish' => __( 'Publish', 'elementor' ),
				'save' => __( 'Save', 'elementor' ),
				'saved' => __( 'Saved', 'elementor' ),
				'update' => __( 'Update', 'elementor' ),
				'enable' => __( 'Enable', 'elementor' ),
				'submit' => __( 'Submit', 'elementor' ),
				'working_on_draft_notification' => __( 'This is just a draft. Play around and when you\'re done - click update.', 'elementor' ),
				'keep_editing' => __( 'Keep Editing', 'elementor' ),
				'have_a_look' => __( 'Have a look', 'elementor' ),
				'view_all_revisions' => __( 'View All Revisions', 'elementor' ),
				'dismiss' => __( 'Dismiss', 'elementor' ),
				'saving_disabled' => __( 'Saving has been disabled until you’re reconnected.', 'elementor' ),

				// Ajax
				'server_error' => __( 'Server Error', 'elementor' ),
				'server_connection_lost' => __( 'Connection Lost', 'elementor' ),
				'unknown_error' => __( 'Unknown Error', 'elementor' ),

				// Context Menu
				'duplicate' => __( 'Duplicate', 'elementor' ),
				'copy' => __( 'Copy', 'elementor' ),
				'paste' => __( 'Paste', 'elementor' ),
				'copy_style' => __( 'Copy Style', 'elementor' ),
				'paste_style' => __( 'Paste Style', 'elementor' ),
				'reset_style' => __( 'Reset Style', 'elementor' ),
				'save_as_global' => __( 'Save as a Global', 'elementor' ),
				'save_as_block' => __( 'Save as Template', 'elementor' ),
				'new_column' => __( 'Add New Column', 'elementor' ),
				'copy_all_content' => __( 'Copy All Content', 'elementor' ),
				'delete_all_content' => __( 'Delete All Content', 'elementor' ),
				'navigator' => __( 'Navigator', 'elementor' ),

				// Right Click Introduction
				'meet_right_click_header' => __( 'Meet Right Click', 'elementor' ),
				'meet_right_click_message' => __( 'Now you can access all editing actions using right click.', 'elementor' ),

				// Hotkeys screen
				'keyboard_shortcuts' => __( 'Keyboard Shortcuts', 'elementor' ),

				// Deprecated Control
				'deprecated_notice' => __( 'The <strong>%1$s</strong> widget has been deprecated since %2$s %3$s.', 'elementor' ),
				'deprecated_notice_replacement' => __( 'It has been replaced by <strong>%1$s</strong>.', 'elementor' ),
				'deprecated_notice_last' => __( 'Note that %1$s will be completely removed once %2$s %3$s is released.', 'elementor' ),

				//Preview Debug
				'preview_debug_link_text' => __( 'Click here for preview debug', 'elementor' ),

				'icon_library' => __( 'Icon Library', 'elementor' ),
				'my_libraries' => __( 'My Libraries', 'elementor' ),
				'upload' => __( 'Upload', 'elementor' ),
				'icons_promotion' => __( 'Become a Pro user to upload unlimited font icon folders to your website.', 'elementor' ),
				'go_pro' => __( 'Go Pro', 'elementor' ),
				'custom_positioning' => __( 'Custom Positioning', 'elementor' ),

				'element_promotion_dialog_header' => __( '%s Widget', 'elementor' ),
				'element_promotion_dialog_message' => __( 'Use %s widget and dozens more pro features to extend your toolbox and build sites faster and better.', 'elementor' ),
				'see_it_in_action' => __( 'See it in Action', 'elementor' ),
				'dynamic_content' => __( 'Dynamic Content', 'elementor' ),
				'dynamic_promotion_message' => __( 'Create more personalized and dynamic sites by populating data from various sources with dozens of dynamic tags to choose from.', 'elementor' ),
				'available_in_pro_v29' => __( 'Available in Pro V2.9.', 'elementor' ),

				// TODO: Remove.
				'autosave' => __( 'Autosave', 'elementor' ),
				'elementor_docs' => __( 'Documentation', 'elementor' ),
				'reload_page' => __( 'Reload Page', 'elementor' ),
				'session_expired_header' => __( 'Timeout', 'elementor' ),
				'session_expired_message' => __( 'Your session has expired. Please reload the page to continue editing.', 'elementor' ),
				'soon' => __( 'Soon', 'elementor' ),
				'unknown_value' => __( 'Unknown Value', 'elementor' ),
			],
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

		$editor_preferences_model = SettingsManager::get_settings_managers( 'editorPreferences' )->get_model();
		$ui_theme = $editor_preferences_model->get_settings( 'ui_theme' );
		$ui_themes = [ 'light', 'dark' ];
		$color_theme_dependencise = [];

		if ( 'auto' === $ui_theme ) {
			foreach ( $ui_themes as $theme ) {
				$handle = 'elementor-editor-' . $theme . '-mode-css';
				wp_register_style(
					$handle,
					ELEMENTOR_ASSETS_URL . 'css/themes/' . $theme . $suffix . '.css',
					[],
					ELEMENTOR_VERSION,
					'(prefers-color-scheme: ' . $theme . ')'
				);
				array_push( $color_theme_dependencise, $handle );
			}
		} else {
			$handle = 'elementor-editor-' . $ui_theme . '-mode-css';
			wp_register_style(
				$handle,
				ELEMENTOR_ASSETS_URL . 'css/themes/' . $ui_theme . $suffix . '.css',
				[],
				ELEMENTOR_VERSION,
				'all'
			);
			$color_theme_dependencise = [ $handle ];
		}

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

		$editor_style_dependencies = array_merge(
			[
				'elementor-common',
				'elementor-select2',
				'elementor-icons',
				'wp-auth-check',
				'google-font-roboto',
				'flatpickr',
				'pickr',
			],
			$color_theme_dependencise
		);

		wp_register_style(
			'elementor-editor',
			ELEMENTOR_ASSETS_URL . 'css/editor' . $direction_suffix . $suffix . '.css',
			$editor_style_dependencies,
			ELEMENTOR_VERSION
		);

		wp_enqueue_style( 'elementor-editor' );

		if ( Responsive::has_custom_breakpoints() ) {
			$breakpoints = Responsive::get_breakpoints();

			wp_add_inline_style( 'elementor-editor', '.elementor-device-tablet #elementor-preview-responsive-wrapper { width: ' . $breakpoints['md'] . 'px; }' );
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
	 * Add editor template.
	 *
	 * Registers new editor templates.
	 *
	 * @since 1.0.0
	 * @deprecated 2.3.0 Use `Plugin::$instance->common->add_template()`
	 * @access public
	 *
	 * @param string $template Can be either a link to template file or template
	 *                         HTML content.
	 * @param string $type     Optional. Whether to handle the template as path
	 *                         or text. Default is `path`.
	 */
	public function add_editor_template( $template, $type = 'path' ) {
		_deprecated_function( __METHOD__, '2.3.0', 'Plugin::$instance->common->add_template()' );

		$common = Plugin::$instance->common;

		if ( $common ) {
			Plugin::$instance->common->add_template( $template, $type );
		}
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
	 * Create nonce.
	 *
	 * If the user has edit capabilities, it creates a cryptographic token to
	 * give him access to Elementor editor.
	 *
	 * @since 1.8.1
	 * @since 1.8.7 The `$post_type` parameter was introduces.
	 * @deprecated 2.3.0 Use `Plugin::$instance->common->get_component( 'ajax' )->create_nonce()` instead
	 * @access public
	 *
	 * @param string $post_type The post type to check capabilities.
	 *
	 * @return null|string The nonce token, or `null` if the user has no edit
	 *                     capabilities.
	 */
	public function create_nonce( $post_type ) {
		_deprecated_function( __METHOD__, '2.3.0', 'Plugin::$instance->common->get_component( \'ajax\' )->create_nonce()' );

		/** @var Ajax $ajax */
		$ajax = Plugin::$instance->common->get_component( 'ajax' );

		return $ajax->create_nonce();
	}

	/**
	 * Verify nonce.
	 *
	 * The user is given an amount of time to use the token, so therefore, since
	 * the user ID and `$action` remain the same, the independent variable is
	 * the time.
	 *
	 * @since 1.8.1
	 * @deprecated 2.3.0
	 * @access public
	 *
	 * @param string $nonce Nonce to verify.
	 *
	 * @return false|int If the nonce is invalid it returns `false`. If the
	 *                   nonce is valid and generated between 0-12 hours ago it
	 *                   returns `1`. If the nonce is valid and generated
	 *                   between 12-24 hours ago it returns `2`.
	 */
	public function verify_nonce( $nonce ) {
		_deprecated_function( __METHOD__, '2.3.0', 'wp_verify_nonce()' );

		return wp_verify_nonce( $nonce );
	}

	/**
	 * Verify request nonce.
	 *
	 * Whether the request nonce verified or not.
	 *
	 * @since 1.8.1
	 * @deprecated 2.3.0 Use `Plugin::$instance->common->get_component( 'ajax' )->verify_request_nonce()` instead
	 * @access public
	 *
	 * @return bool True if request nonce verified, False otherwise.
	 */
	public function verify_request_nonce() {
		_deprecated_function( __METHOD__, '2.3.0', 'Plugin::$instance->common->get_component( \'ajax\' )->verify_request_nonce()' );

		/** @var Ajax $ajax */
		$ajax = Plugin::$instance->common->get_component( 'ajax' );

		return $ajax->verify_request_nonce();
	}

	/**
	 * Verify ajax nonce.
	 *
	 * Verify request nonce and send a JSON request, if not verified returns an
	 * error.
	 *
	 * @since 1.9.0
	 * @deprecated 2.3.0
	 * @access public
	 */
	public function verify_ajax_nonce() {
		_deprecated_function( __METHOD__, '2.3.0' );

		/** @var Ajax $ajax */
		$ajax = Plugin::$instance->common->get_component( 'ajax' );

		if ( ! $ajax->verify_request_nonce() ) {
			wp_send_json_error( new \WP_Error( 'token_expired', 'Nonce token expired.' ) );
		}
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
