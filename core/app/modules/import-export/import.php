<?php
namespace Elementor\Core\App\Modules\ImportExport;

use Elementor\Core\App\Modules\ImportExport\Directories\Root;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Import extends Iterator {

	final public function run() {
		$zip = new \ZipArchive();

		$zip->open( $this->get_settings( 'file_name' ) );

		$temp_dir = $this->get_temp_dir();

		$zip->extractTo( $temp_dir );

		$settings = $this->read_json_file( 'manifest' );

		if ( ! empty( $settings['manifest_version'] ) ) {
			// The user is attempting to import one of the thousands of Template Kits available from https://themeforest.net/category/template-kits
			// These template kits have a slightly different JSON and file structure to the ones exported by the new beta Elementor Export feature.
			// To make this compatible we slightly adjust the format of the Envato manifest file.
			// This works for majority of cases, but there are still a few that fail such as:
			// - When required plugins are missing
			// - When there is a global Kit style
			$original_templates    = $settings['templates'];
			$settings['templates'] = [];
			foreach ( $original_templates as $original_template ) {
				$new_template = $original_template;
				// Elementor looks for "doc_type" instead of "type"
				$new_template['doc_type'] = $original_template['type'];
				// Envato template kits store their global kit styles as a separate JSON file, these need to be imported as a "kit" document type
				if ( ! empty( $original_template['metadata']['template_type'] ) && $original_template['metadata']['template_type'] === 'global-styles' ) {
					$new_template['doc_type'] = 'kit';
				}
				// Elementor looks for "title" instead of "name"
				$new_template['title'] = $original_template['name'];
				// Elementor uses the template file name as the "ID" of its indexing, rather than specifying an exact path to the template.
				// This extracts the "file name" part out of our exact source list and we treat that as an ID.
				$file_name_without_extension = str_replace( '.json', '', basename( $original_template['source'] ) );
				// Append our template to the global list:
				$settings['templates'][ $file_name_without_extension ] = $new_template;
			}
		}

		$root_directory = new Root( $this );

		$import_result = $root_directory->run_import( $settings );

		$this->remove_dir( $temp_dir );

		return $import_result;
	}

	final public function read_json_file( $name ) {
		$name = $this->get_temp_dir() . $this->get_archive_file_path( $name . '.json' );

		return json_decode( file_get_contents( $name ), true );
	}

	private function remove_dir( $dir ) {
		$dir_iterator = new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS );

		foreach ( new \RecursiveIteratorIterator( $dir_iterator, \RecursiveIteratorIterator::CHILD_FIRST ) as $name => $item ) {
			if ( is_dir( $name ) ) {
				rmdir( $name );
			} else {
				unlink( $name );
			}
		}

		return rmdir( $dir );
	}
}
