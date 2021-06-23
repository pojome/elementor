<?php

namespace Elementor\Core\App\Modules\ImportExport\Directories;

use Elementor\Core\Base\Document;
use Elementor\Plugin;
use Elementor\TemplateLibrary\Source_Local;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Templates extends Base {

	protected function get_name() {
		return 'templates';
	}

	protected function export() {
		$template_types = array_values( Source_Local::get_template_types() );

		$query_args = [
			'post_type' => Source_Local::CPT,
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_query' => [
				[
					'key' => Document::TYPE_META_KEY,
					'value' => $template_types,
				],
			],
		];

		$templates_query = new \WP_Query( $query_args );

		$manifest_data = [];

		foreach ( $templates_query->posts as $template_post ) {
			$template_id = $template_post->ID;

			$template_document = Plugin::$instance->documents->get( $template_id );

			$template_export_data = $template_document->get_export_data();

			$this->exporter->add_json_file( $template_id, $template_export_data );

			$manifest_data[ $template_id ] = $template_document->get_export_summary();
		}

		return $manifest_data;
	}

	protected function import( array $import_settings ) {
		$result = [
			'succeed' => [],
			'failed' => [],
		];

		foreach ( $import_settings as $id => $template_settings ) {
			try {
				$import = $this->import_template( $id, $template_settings );

				if ( is_wp_error( $import ) ) {
					$result['failed'][ $id ] = $import->get_error_message();

					continue;
				}

				$result['succeed'][ $id ] = $import;
			} catch ( \Error $error ) {
				$result['failed'][ $id ] = $error->getMessage();
			}
		}

		return $result;
	}

	private function import_template( $id, array $template_settings ) {
		$template_data = $this->importer->read_json_file( $id );

		$doc_type = $template_settings['doc_type'];

		$new_document = Plugin::$instance->documents->create(
			$doc_type,
			[
				'post_title' => $template_settings['title'],
				'post_type' => Source_Local::CPT,
				'post_status' => 'publish',
			]
		);

		if ( is_wp_error( $new_document ) ) {
			return $new_document;
		}

		$template_data['import_settings'] = $template_settings;
		$template_data['id'] = $id;

		foreach ( $this->importer->get_adapters() as $adapter ) {
			$template_data = $adapter->get_template_data( $template_data, $template_settings );
		}

		$new_document->import( $template_data );

		return $new_document->get_main_id();
	}
}
