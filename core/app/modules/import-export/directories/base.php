<?php
namespace Elementor\Core\App\Modules\ImportExport\Directories;

use Elementor\Core\App\Modules\ImportExport\Export;
use Elementor\Core\App\Modules\ImportExport\Import;
use Elementor\Core\App\Modules\ImportExport\Iterator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Base {

	/**
	 * @var Base[]
	 */
	private $sub_directories = [];

	/**
	 * @var Base
	 */
	private $parent;

	/**
	 * @var Iterator
	 */
	protected $iterator;

	/**
	 * @var Export
	 */
	protected $exporter;

	/**
	 * @var Import
	 */
	protected $importer;

	abstract protected function get_name();

	public function __construct( Iterator $iterator, Base $parent = null ) {
		$this->iterator = $iterator;

		if ( $iterator instanceof Export ) {
			$this->exporter = $iterator;
		} else {
			$this->importer = $iterator;
		}

		$this->parent = $parent;

		$this->register_directories();
	}

	final public function get_path() {
		$path = $this->get_name();

		if ( $this->parent ) {
			$parent_name = $this->parent->get_name();

			if ( $parent_name ) {
				$parent_name .= '/';
			}

			$path = $parent_name . $path;
		}

		return $path;
	}

	final public function run_export() {
		$this->exporter->set_current_archive_path( $this->get_path() );

		$manifest_data = $this->export();

		foreach ( $this->sub_directories as $sub_directory ) {
			$manifest_data[ $sub_directory->get_name() ] = $sub_directory->run_export();
		}

		return $manifest_data;
	}

	final public function run_import( array $settings ) {
		$this->importer->set_current_archive_path( $this->get_path() );

		$meta_data = $this->import( $settings );

		foreach ( $this->sub_directories as $sub_directory ) {
			$sub_directory_name = $sub_directory->get_name();

			if ( ! isset( $settings[ $sub_directory_name ] ) ) {
				continue;
			}

			$meta_data[ $sub_directory_name ] = $sub_directory->run_import( $settings[ $sub_directory_name ] );
		}

		return $meta_data;
	}

	/**
	 * @return array
	 */
	protected function export() {
		return [];
	}

	/**
	 * @param array $import_settings
	 *
	 * @return array
	 */
	protected function import( array $import_settings ) {
		return [];
	}

	protected function get_default_sub_directories() {
		return [];
	}

	private function register_directories() {
		$sub_directories = $this->get_default_sub_directories();

		$this->sub_directories = apply_filters( 'elementor/kit/import-export/directory/' . $this->get_path(), $sub_directories, $this );
	}
}
