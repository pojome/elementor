<?php

namespace Elementor\Modules\AdminTopBar;

use Elementor\Core\Base\App as BaseApp;
use Elementor\Core\Experiments\Manager;
use Elementor\Plugin;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Module extends BaseApp {

	/**
	 * @return bool
	 */
	public static function is_active() {
		return is_admin();
	}

	public static function get_experimental_data() {
		return [
			'name' => 'admin-top-bar',
			'title' => __( 'Admin Top Bar', 'elementor' ),
			'description' => __( 'Adds a top bar to elementors pages in admin area.', 'elementor' ),
			'release_status' => Manager::RELEASE_STATUS_BETA,
			'new_site' => [
				'default_active' => true,
			],
		];
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return 'admin-top-bar';
	}

	private function render_admin_top_bar() {
		?>
		<div id="e-admin-top-bar-root">
		</div>
		<?php
	}
	protected function get_init_settings() {
		return [
			'is_administrator' => current_user_can( 'manage_options' ),
		];
	}

	/**
	 * Enqueue admin scripts
	 */
	private function enqueue_scripts() {
		wp_enqueue_style( 'elementor-admin-top-bar-fonts', 'https://fonts.googleapis.com/css2?family=Roboto&display=swap', [], ELEMENTOR_VERSION );

		wp_enqueue_style( 'elementor-admin-top-bar', $this->get_css_assets_url( 'admin-top-bar', null, 'default', true ), [], ELEMENTOR_VERSION );

		wp_enqueue_script( 'elementor-admin-top-bar', $this->get_js_assets_url( 'admin-top-bar' ), [
			'elementor-common',
			'react',
			'react-dom',
		], ELEMENTOR_VERSION, true );

		$min_suffix = Utils::is_script_debug() ? '' : '.min';

		wp_enqueue_script( 'tipsy', ELEMENTOR_ASSETS_URL . 'lib/tipsy/tipsy' . $min_suffix . '.js', [
			'jquery',
		], '1.0.0', true );

		$this->print_config();
	}

	/**
	 * Register dashboard widgets.
	 *
	 * Adds a new Elementor widgets to WordPress dashboard.
	 *
	 * Fired by `wp_dashboard_setup` action.
	 *
	 * @since 1.9.0
	 * @access public
	 */
	public function register_dashboard_widgets() {
		wp_add_dashboard_widget( 'e-dashboard-widget-admin-top-bar', __( 'Elementor Top Bar', 'elementor' ), function () {} );
	}

	/**
	 * Module constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'in_admin_header', function () {
			$this->render_admin_top_bar();
		} );

		add_action( 'admin_enqueue_scripts', function () {
			$this->enqueue_scripts();
		} );

		add_action( 'wp_dashboard_setup', function () {
			$this->register_dashboard_widgets();
		} );
	}
}
