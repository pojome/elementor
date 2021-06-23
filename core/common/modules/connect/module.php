<?php
namespace Elementor\Core\Common\Modules\Connect;

use Elementor\Utils;
use Elementor\Core\Base\Module as BaseModule;
use Elementor\Core\Common\Modules\Connect\Apps\Base_App;
use Elementor\Core\Common\Modules\Connect\Apps\Connect;
use Elementor\Core\Common\Modules\Connect\Apps\Library;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Module extends BaseModule {
	const ACCESS_LEVEL_CORE = 0;
	const ACCESS_LEVEL_PRO = 1;
	const ACCESS_LEVEL_EXPERT = 20;

	/**
	 * @since 2.3.0
	 * @access public
	 */
	public function get_name() {
		return 'connect';
	}

	/**
	 * @var array
	 */
	protected $registered_apps = [];

	/**
	 * Apps Instances.
	 *
	 * Holds the list of all the apps instances.
	 *
	 * @since 2.3.0
	 * @access protected
	 *
	 * @var Base_App[]
	 */
	protected $apps = [];

	/**
	 * Registered apps categories.
	 *
	 * Holds the list of all the registered apps categories.
	 *
	 * @since 2.3.0
	 * @access protected
	 *
	 * @var array
	 */
	protected $categories = [];

	protected $admin_page;

	/**
	 * @since 2.3.0
	 * @access public
	 */
	public function __construct() {
		$this->registered_apps = [
			'connect' => Connect::get_class_name(),
			'library' => Library::get_class_name(),
		];

		// When using REST API the parent module is construct after the action 'elementor/init'
		// so this part of code make sure to register the module "apps".
		if ( did_action( 'elementor/init' ) ) {
			$this->init();
		} else {
			// Note: The priority 11 is for allowing plugins to add their register callback on elementor init.
			add_action( 'elementor/init', [ $this, 'init' ], 11 );
		}
	}

	/**
	 * Register default apps.
	 *
	 * Registers the default apps.
	 *
	 * @since 2.3.0
	 * @access public
	 */
	public function init() {
		if ( is_admin() ) {
			$this->admin_page = new Admin();
		}

		/**
		 * Register Elementor apps.
		 *
		 * Fires after Elementor registers the default apps.
		 *
		 * @since 2.3.0
		 *
		 * @param self $this The apps manager instance.
		 */
		do_action( 'elementor/connect/apps/register', $this );

		foreach ( $this->registered_apps as $slug => $class ) {
			$this->apps[ $slug ] = new $class();
		}
	}

	/**
	 * @deprecated 3.1.0
	 */
	public function localize_settings() {
		Plugin::$instance->modules_manager->get_modules( 'dev-tools' )->deprecation->deprecated_function( __METHOD__, '3.1.0' );

		return [];
	}

	/**
	 * Register app.
	 *
	 * Registers an app.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param string $slug App slug.
	 * @param string $class App full class name.
	 *
	 * @return self The updated apps manager instance.
	 */
	public function register_app( $slug, $class ) {
		$this->registered_apps[ $slug ] = $class;

		return $this;
	}

	/**
	 * Get app instance.
	 *
	 * Retrieve the app instance.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param $slug
	 *
	 * @return Base_App|null
	 */
	public function get_app( $slug ) {
		if ( isset( $this->apps[ $slug ] ) ) {
			return $this->apps[ $slug ];
		}

		return null;
	}

	/**
	 * @since 2.3.0
	 * @access public
	 * @return Base_App[]
	 */
	public function get_apps() {
		return $this->apps;
	}

	/**
	 * @since 2.3.0
	 * @access public
	 */
	public function register_category( $slug, $args ) {
		$this->categories[ $slug ] = $args;
		return $this;
	}

	/**
	 * @since 2.3.0
	 * @access public
	 */
	public function get_categories() {
		return $this->categories;
	}

	/**
	 * @param $context
	 *
	 * @return array
	 */
	public function get_subscription_plans( $context ) {
		return [
			static::ACCESS_LEVEL_CORE => [
				'label' => null,
				'promotion_url' => null,
				'color' => null,
			],
			static::ACCESS_LEVEL_PRO => [
				'label' => 'Pro',
				'promotion_url' => Utils::get_pro_link( "https://elementor.com/pro/?utm_source={$context}&utm_medium=wp-dash&utm_campaign=gopro" ),
				'color' => '#92003B',
			],
			static::ACCESS_LEVEL_EXPERT => [
				'label' => 'Expert',
				'promotion_url' => Utils::get_pro_link( "https://elementor.com/pro/?utm_source={$context}&utm_medium=wp-dash&utm_campaign=goexpert" ),
				'color' => '#010051',
			],
		];
	}

	protected function get_init_settings() {
		/** @var Connect $connect */
		$connect = $this->get_app( 'library' );
		if ( ! $connect ) {
			return [];
		}
		return [
			'is_user_connected' => $connect->is_connected(),
			'connect_url' => $connect->get_admin_url( 'authorize' ),
		];
	}

}
