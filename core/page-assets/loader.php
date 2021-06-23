<?php
namespace Elementor\Core\Page_Assets;

use Elementor\Core\Base\Module;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Elementor assets loader.
 *
 * A class that is responsible for conditionally enqueuing styles and script assets that were pre-enabled.
 *
 * @since 3.3.0
 */
class Loader extends Module {
	private $assets;

	public function get_name() {
		return 'assets-loader';
	}

	private function init_assets() {
		$this->assets = [
			'styles' => [
				'e-animations' => [
					'src' => $this->get_css_assets_url( 'animations', 'assets/lib/animations/', true ),
					'version' => ELEMENTOR_VERSION,
					'dependencies' => [],
				],
			],
			'scripts' => [],
		];
	}

	public function get_assets() {
		if ( ! $this->assets ) {
			$this->init_assets();
		}

		return $this->assets;
	}

	/**
	 * @param array $assets {
	 *     @type array 'styles'
	 *     @type array 'scripts'
	 * }
	 */
	public function enable_assets( array $assets_data ) {
		if ( ! $this->assets ) {
			$this->init_assets();
		}

		foreach ( $assets_data as $assets_type => $assets_list ) {
			foreach ( $assets_list as $asset_name ) {
				$this->assets[ $assets_type ][ $asset_name ]['enabled'] = true;
			}
		}
	}

	/**
	 * @param array $assets {
	 *     @type array 'styles'
	 *     @type array 'scripts'
	 * }
	 */
	public function add_assets( array $assets ) {
		if ( ! $this->assets ) {
			$this->init_assets();
		}

		$this->assets = array_replace_recursive( $this->assets, $assets );
	}

	public function enqueue_assets() {
		$assets = $this->get_assets();
		$is_preview_mode = Plugin::$instance->preview->is_preview_mode();
		$is_optimized_assets_loading = Plugin::$instance->experiments->is_feature_active( 'e_optimized_assets_loading' );

		foreach ( $assets as $assets_type => $assets_type_data ) {
			foreach ( $assets_type_data as $asset_name => $asset_data ) {
				if ( ! empty( $asset_data['enabled'] ) || $is_preview_mode || ! $is_optimized_assets_loading ) {
					if ( 'scripts' === $assets_type ) {
						wp_enqueue_script( $asset_name, $asset_data['src'], $asset_data['dependencies'], $asset_data['version'], true );
					} else {
						wp_enqueue_style( $asset_name, $asset_data['src'], $asset_data['dependencies'], $asset_data['version'] );
					}
				}
			}
		}
	}

	private function register_assets() {
		$assets = $this->get_assets();

		foreach ( $assets as $assets_type => $assets_type_data ) {
			foreach ( $assets_type_data as $asset_name => $asset_data ) {
				if ( 'scripts' === $assets_type ) {
					wp_register_script( $asset_name, $asset_data['src'], $asset_data['dependencies'], $asset_data['version'], true );
				} else {
					wp_register_style( $asset_name, $asset_data['src'], $asset_data['dependencies'], $asset_data['version'] );
				}
			}
		}
	}

	public function __construct() {
		parent::__construct();

		$this->register_assets();
	}
}
