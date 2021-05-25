<?php
namespace Elementor\Core\App\Modules\ImportExport;

use Elementor\Core\App\Modules\ImportExport\Directories\Root;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Import extends Iterator {

	final public function run() {
		$this->temp_dir = $this->get_settings( 'directory' );

		$settings = $this->read_json_file( 'manifest' );

		$root_directory = new Root( $this );

		$import_result = $root_directory->run_import( $settings );

		return $import_result;
	}

	final public function read_json_file( $name ) {
		$name = $this->get_archive_file_full_path( $name . '.json' );

		return json_decode( file_get_contents( $name, true ), true );
	}
}
