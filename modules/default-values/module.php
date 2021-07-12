<?php
namespace Elementor\Modules\DefaultValues;

use Elementor\Plugin;
use Elementor\Core\Experiments\Manager;
use Elementor\Core\Files\CSS\Global_CSS;
use Elementor\Core\Base\Module as BaseModule;
use Elementor\Modules\DefaultValues\Data\Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Module extends BaseModule {
	const DEFAULT_VALUES_META_KEY = '_elementor_default_values';

	/**
	 * @return string
	 */
	public function get_name() {
		return 'default-values';
	}

	/**
	 * @return array
	 */
	public static function get_experimental_data() {
		return [
			'name' => 'default-values',
			'title' => __( 'Default Values', 'elementor' ),
			'description' => __( 'This is default values!', 'elementor' ),
			'release_status' => Manager::RELEASE_STATUS_ALPHA,
			'default' => Manager::STATE_INACTIVE,
		];
	}

	private function enqueue_scripts() {
		wp_enqueue_script(
			'editor-default-values',
			$this->get_js_assets_url( 'editor-default-values' ),
			[ 'elementor-common' ],
			ELEMENTOR_VERSION,
			true
		);
	}

	public function __construct() {
		parent::__construct();

		add_action( 'elementor/editor/before_enqueue_scripts', function () {
			$this->enqueue_scripts();
		} );

//		add_action( 'elementor/css-file/global/parse', function ( Global_CSS $global_css ) {
//
//		} );

		Plugin::$instance->data_manager->register_controller( Controller::class );
	}
}
