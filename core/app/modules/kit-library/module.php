<?php
namespace Elementor\Core\App\Modules\KitLibrary;

use Elementor\Plugin;
use Elementor\TemplateLibrary\Source_Local;
use Elementor\Core\Base\Module as BaseModule;
use Elementor\Core\App\Modules\KitLibrary\Connect\Kit_Library;
use Elementor\Core\Common\Modules\Connect\Module as ConnectModule;
use Elementor\Core\App\Modules\KitLibrary\Data\Kits\Controller as Kits_Controller;
use Elementor\Core\App\Modules\KitLibrary\Data\Taxonomies\Controller as Taxonomies_Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Module extends BaseModule {
	/**
	 * Get name.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function get_name() {
		return 'kit-library';
	}

	/**
	 * Register the admin menu.
	 */
	private function register_admin_menu() {
		add_submenu_page(
			Source_Local::ADMIN_MENU_SLUG,
			__( 'Kit Library', 'elementor' ),
			__( 'Kit Library', 'elementor' ),
			'manage_options',
			Plugin::$instance->app->get_base_url() . '#/kit-library'
		);
	}

	private function set_kit_library_settings() {
		if ( ! Plugin::$instance->common ) {
			return;
		}

		/** @var ConnectModule $connect */
		$connect = Plugin::$instance->common->get_component( 'connect' );

		/** @var Kit_Library $kit_library */
		$kit_library = $connect->get_app( 'kit-library' );

		Plugin::$instance->app->set_settings( 'kit-library', [
			'has_access_to_module' => current_user_can( 'administrator' ),
			'subscription_plans' => $connect->get_subscription_plans( 'wp-kit-library' ),
			'is_pro' => false,
			'is_library_connected' => $kit_library->is_connected(),
			'library_connect_url'  => $kit_library->get_admin_url( 'authorize' ),
			'access_level' => ConnectModule::ACCESS_LEVEL_CORE,
		] );
	}

	/**
	 * Module constructor.
	 */
	public function __construct() {
		Plugin::$instance->data_manager->register_controller( Kits_Controller::class );
		Plugin::$instance->data_manager->register_controller( Taxonomies_Controller::class );

		add_action( 'admin_menu', function () {
			$this->register_admin_menu();
		}, 50 /* after Elementor page */ );

		add_action( 'elementor/connect/apps/register', function ( ConnectModule $connect_module ) {
			$connect_module->register_app( 'kit-library', Kit_Library::get_class_name() );
		} );

		add_action( 'elementor/init', function () {
			$this->set_kit_library_settings();
		}, 12 /** after the initiation of the connect kit library */ );
	}
}
