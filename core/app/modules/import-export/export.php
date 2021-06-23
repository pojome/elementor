<?php
namespace Elementor\Core\App\Modules\ImportExport;

use Elementor\Core\App\Modules\ImportExport\Directories\Root;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Export extends Iterator {

	/**
	 * @var \ZipArchive
	 */
	private $zip_archive;

	private $archive_file_name;

	final public function run() {
		ob_start();

		$this->init_zip_archive();

		$root_directory = new Root( $this );

		$manifest_data = $root_directory->run_export();

		$manifest_data = apply_filters( 'elementor/kit/export/manifest-data', $manifest_data, $this );

		$this->set_current_archive_path( '' );

		$this->add_json_file( 'manifest', $manifest_data );

		$this->zip_archive->close();

		return [
			'manifest' => $manifest_data,
			'file_name' => $this->archive_file_name,
		];
	}

	public function add_json_file( $name, $content, $json_flags = null ) {
		$this->add_file( $name . '.json', wp_json_encode( $content, $json_flags ) );
	}

	public function add_file( $file_name, $content ) {
		$this->zip_archive->addFromString( $this->get_archive_file_path( $file_name ), $content );
	}

	private function init_zip_archive() {
		$zip_archive = new \ZipArchive();

		$this->temp_dir = Plugin::$instance->uploads_manager->create_unique_dir();

		$this->archive_file_name = $this->temp_dir . 'kit.zip';

		$zip_archive->open( $this->archive_file_name, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );

		$this->zip_archive = $zip_archive;
	}
}
