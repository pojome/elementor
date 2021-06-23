<?php
namespace Elementor\Core\App;

use Elementor\Core\Base\App as BaseApp;
use Elementor\Core\Settings\Manager as SettingsManager;
use Elementor\Plugin;
use Elementor\TemplateLibrary\Source_Local;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class App extends BaseApp {

	const PAGE_ID = 'elementor-app';

	/**
	 * Get module name.
	 *
	 * Retrieve the module name.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'app';
	}

	public function get_base_url() {
		return admin_url( 'admin.php?page=' . self::PAGE_ID . '&ver=' . ELEMENTOR_VERSION );
	}

	public function register_admin_menu() {
		add_submenu_page(
			Source_Local::ADMIN_MENU_SLUG,
			__( 'Theme Builder', 'elementor' ),
			__( 'Theme Builder', 'elementor' ),
			'manage_options',
			self::PAGE_ID
		);
	}

	public function fix_submenu( $menu ) {
		global $submenu;

		if ( is_multisite() && is_network_admin() ) {
			return $menu;
		}

		// Non admin role / custom wp menu.
		if ( empty( $submenu[ Source_Local::ADMIN_MENU_SLUG ] ) ) {
			return $menu;
		}

		// Hack to add a link to sub menu.
		foreach ( $submenu[ Source_Local::ADMIN_MENU_SLUG ] as &$item ) {
			if ( self::PAGE_ID === $item[2] ) {
				$item[2] = $this->get_settings( 'menu_url' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$item[4] = 'elementor-app-link'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}
		}

		return $menu;
	}

	public function is_current() {
		return ( ! empty( $_GET['page'] ) && self::PAGE_ID === $_GET['page'] );
	}

	public function admin_init() {
		do_action( 'elementor/app/init', $this );

		$this->enqueue_assets();

		// Setup default heartbeat options
		// TODO: Enable heartbeat.
		add_filter( 'heartbeat_settings', function( $settings ) {
			$settings['interval'] = 15;
			return $settings;
		} );

		$this->render();
		die;
	}

	protected function get_init_settings() {
		return [
			'menu_url' => $this->get_base_url() . '#site-editor/promotion',
			'assets_url' => ELEMENTOR_ASSETS_URL,
			'return_url' => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : admin_url(),
			'hasPro' => Utils::has_pro(),
		];
	}

	private function render() {
		require __DIR__ . '/view.php';
	}

	/**
	 * Get Elementor UI theme preference.
	 *
	 * Retrieve the user UI theme preference as defined by editor preferences manager.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return string Preferred UI theme.
	 */
	private function get_elementor_ui_theme_preference() {
		$editor_preferences = SettingsManager::get_settings_managers( 'editorPreferences' );

		return $editor_preferences->get_model()->get_settings( 'ui_theme' );
	}

	/**
	 * Enqueue dark theme detection script.
	 *
	 * Enqueues an inline script that detects user-agent settings for dark mode and adds a complimentary class to the body tag.
	 *
	 * @since 3.0.0
	 * @access private
	 */
	private function enqueue_dark_theme_detection_script() {
		if ( 'auto' === $this->get_elementor_ui_theme_preference() ) {
			wp_add_inline_script( 'elementor-app',
				'if ( window.matchMedia && window.matchMedia( `(prefers-color-scheme: dark)` ).matches )
							{ document.body.classList.add( `eps-theme-dark` ); }' );
		}
	}

	private function enqueue_assets() {
		Plugin::$instance->init_common();
		Plugin::$instance->common->register_scripts();

		wp_register_style(
			'select2',
			$this->get_css_assets_url( 'e-select2', 'assets/lib/e-select2/css/' ),
			[],
			'4.0.6-rc.1'
		);

		wp_register_style(
			'elementor-icons',
			$this->get_css_assets_url( 'elementor-icons', 'assets/lib/eicons/css/' ),
			[],
			'5.11.0'
		);

		wp_register_style(
			'elementor-common',
			$this->get_css_assets_url( 'common', null, 'default', true ),
			[],
			ELEMENTOR_VERSION
		);

		wp_register_style(
			'select2',
			ELEMENTOR_ASSETS_URL . 'lib/e-select2/css/e-select2.css',
			[],
			'4.0.6-rc.1'
		);

		wp_enqueue_style(
			'elementor-app',
			$this->get_css_assets_url( 'app', null, 'default', true ),
			[
				'select2',
				'elementor-icons',
				'elementor-common',
				'select2',
			],
			ELEMENTOR_VERSION
		);

		wp_enqueue_script(
			'elementor-app-packages',
			$this->get_js_assets_url( 'app-packages' ),
			[
				'wp-i18n',
				'react',
			],
			ELEMENTOR_VERSION,
			true
		);

		wp_register_script(
			'select2',
			$this->get_js_assets_url( 'e-select2.full', 'assets/lib/e-select2/js/' ),
			[
				'jquery',
			],
			'4.0.6-rc.1',
			true
		);

		wp_enqueue_script(
			'elementor-app',
			$this->get_js_assets_url( 'app' ),
			[
				'wp-url',
				'wp-i18n',
				'react',
				'react-dom',
				'select2',
			],
			ELEMENTOR_VERSION,
			true
		);

		$this->enqueue_dark_theme_detection_script();

		wp_set_script_translations( 'elementor-app-packages', 'elementor' );
		wp_set_script_translations( 'elementor-app', 'elementor' );

		$this->print_config();
	}

	public function enqueue_app_loader() {
		wp_enqueue_script(
			'elementor-app-loader',
			$this->get_js_assets_url( 'app-loader' ),
			[
				'elementor-common',
			],
			ELEMENTOR_VERSION,
			true
		);

		$this->print_config( 'elementor-app-loader' );
	}

	public function __construct() {
		$this->add_component( 'site-editor', new Modules\SiteEditor\Module() );

		if ( current_user_can( 'manage_options' ) && Plugin::$instance->experiments->is_feature_active( 'e_import_export' ) || Utils::is_wp_cli() ) {
			$this->add_component( 'import-export', new Modules\ImportExport\Module() );

			// Kit library is depended on import-export
			$this->add_component( 'kit-library', new Modules\KitLibrary\Module() );
		}

		add_action( 'admin_menu', [ $this, 'register_admin_menu' ], 21 /* after Elementor page */ );

		// Happens after WP plugin page validation.
		add_filter( 'add_menu_classes', [ $this, 'fix_submenu' ] );

		if ( $this->is_current() ) {
			add_action( 'admin_init', [ $this, 'admin_init' ], 0 );
		} else {
			add_action( 'elementor/common/after_register_scripts', [ $this, 'enqueue_app_loader' ] );
		}
	}
}
