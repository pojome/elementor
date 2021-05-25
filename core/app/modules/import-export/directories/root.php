<?php

namespace Elementor\Core\App\Modules\ImportExport\Directories;

use Elementor\Core\App\Modules\ImportExport\Module;
use Elementor\Core\Settings\Page\Manager as PageManager;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Root extends Base {

	protected function get_name() {
		return '';
	}

	protected function export() {
		$kit = Plugin::$instance->kits_manager->get_active_kit();

		$exporter_settings = $this->exporter->get_settings();

		$include = $exporter_settings['include'];

		$include_site_settings = in_array( 'settings', $include, true );

		if ( $include_site_settings ) {
			$this->exporter->add_json_file( 'site-settings', $kit->get_export_data() );
		}

		$kit_post = $kit->get_post();

		$manifest_data = [
			'name' => sanitize_title( $kit_post->post_title ),
			'title' => $kit_post->post_title,
			'author' => get_the_author_meta( 'display_name', $kit_post->post_author ),
			'version' => Module::FORMAT_VERSION,
			'elementor_version' => ELEMENTOR_VERSION,
			'created' => gmdate( 'Y-m-d H:i:s' ),
			'description' => $kit_post->post_excerpt,
			'thumbnail' => get_the_post_thumbnail_url( $kit_post ),
			'site' => get_site_url(),
		];

		if ( $include_site_settings ) {
			$manifest_data['site-settings'] = array_keys( $kit->get_tabs() );
		}

		return $manifest_data;
	}

	protected function import( array $import_settings ) {
		if ( isset( $import_settings['site-settings'] ) ) {
			$kit = Plugin::$instance->kits_manager->get_active_kit();

			$old_settings = $kit->get_meta( PageManager::META_KEY );

			if ( ! $old_settings ) {
				$old_settings = [];
			}

			$new_settings = $this->importer->read_json_file( 'site-settings' );

			$new_settings = $new_settings['settings'];

			if ( ! empty( $old_settings[ 'custom_colors' ] ) ) {
				$new_settings['custom_colors'] = array_merge( $old_settings['custom_colors'], $new_settings['custom_colors'] );
			}

			if ( ! empty( $old_settings[ 'custom_typography' ] ) ) {
				$new_settings['custom_typography'] = array_merge( $old_settings['custom_typography'], $new_settings['custom_typography'] );
			}

			$new_settings = array_replace_recursive( $old_settings, $new_settings );

			$kit->save( [ 'settings' => $new_settings ] );
		}
	}

	protected function get_default_sub_directories() {
		$sub_directories = [];

		$include = $this->iterator->get_settings( 'include' );

		if ( in_array( 'templates', $include, true ) ) {
			$sub_directories[] = new Templates( $this->iterator, $this );
		}

		if ( in_array( 'content', $include, true ) ) {
			$sub_directories[] = new Content( $this->iterator, $this );

			$sub_directories[] = new WP_Content( $this->iterator, $this );
		}

		return $sub_directories;
	}
}
