<?php
namespace Elementor\Modules\ElementsColorPicker;

use Elementor\Core\Experiments\Manager;
use Elementor\Core\Base\Module as BaseModule;
use Elementor\Core\Experiments\Manager as Experiments_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Module extends BaseModule {

	public function get_name() {
		return 'elements-color-picker';
	}

	public static function get_experimental_data() {
		return [
			'name' => 'elements-color-picker',
			'title' => __( 'Elements Color Picker', 'elementor' ),
			'default' => Manager::STATE_ACTIVE,
			'release_status' => Experiments_Manager::RELEASE_STATUS_BETA,
			'description' => __( 'Adds a new color picker functionality that allows choose a color from other elements settings.', 'elementor' ),
		];
	}

	/**
	 * Scripts for module.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'color-thief',
			$this->get_js_assets_url( 'color-thief', 'assets/lib/color-thief/', true ),
			[ 'elementor-editor' ],
			ELEMENTOR_VERSION,
			true
		);
	}

	public function __construct() {
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}
}
