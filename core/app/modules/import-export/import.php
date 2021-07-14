<?php
namespace Elementor\Core\App\Modules\ImportExport;

use Elementor\Core\App\Modules\ImportExport\Compatibility\Base_Adapter;
use Elementor\Core\App\Modules\ImportExport\Compatibility\Envato;
use Elementor\Core\App\Modules\ImportExport\Compatibility\Kit_Library;
use Elementor\Core\App\Modules\ImportExport\Directories\Root;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Import extends Iterator {

	/**
	 * @var Base_Adapter[]
	 */
	private $adapters = [];

	final public function run() {
		$this->temp_dir = $this->get_settings( 'directory' );

		$manifest_data = $this->read_json_file( 'manifest' );

		$manifest_data = $this->adapt_manifest_structure( $manifest_data );

		$root_directory = new Root( $this );

		return $root_directory->run_import( $manifest_data );
	}

	final public function read_json_file( $name ) {
		$name = $this->get_archive_file_full_path( $name . '.json' );

		return json_decode( file_get_contents( $name, true ), true );
	}

	final public function get_adapters() {
		return $this->adapters;
	}

	final public function adapt_manifest_structure( array $manifest_data ) {
		$this->init_adapters( $manifest_data );

		foreach ( $this->adapters as $adapter ) {
			$manifest_data = $adapter->get_manifest_data( $manifest_data );
		}

		return $manifest_data;
	}

	private function init_adapters( array $manifest_data ) {
		/** @var Base_Adapter[] $adapter_types */
		$adapter_types = [ Envato::class, Kit_Library::class ];

		foreach ( $adapter_types as $adapter_type ) {
			if ( $adapter_type::is_compatibility_needed( $manifest_data, $this->get_settings() ) ) {
				$this->adapters[] = new $adapter_type( $this );
			}
		}
	}
}
