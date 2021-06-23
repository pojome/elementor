<?php
namespace Elementor\Core\App\Modules\ImportExport;

use Elementor\Core\Base\Base_Object;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Iterator extends Base_Object {

	protected $temp_dir;

	private $current_archive_path = '';

	abstract public function run();

	protected function get_archive_file_path( $file_name ) {
		return $this->get_current_archive_path() . $file_name;
	}

	public function get_archive_file_full_path( $file_name ) {
		return $this->temp_dir . $this->get_archive_file_path( $file_name );
	}

	public function get_current_archive_path() {
		return $this->current_archive_path;
	}

	public function set_current_archive_path( $path ) {
		if ( $path ) {
			$path .= '/';
		}

		$this->current_archive_path = $path;
	}

	public function __construct( array $settings ) {
		if ( ! class_exists( '\ZipArchive' ) ) {
			throw new \Error( 'ZipArchive module is not installed on the server. You must install this module to perform the process.' );
		}

		$this->set_settings( $settings );
	}
}
