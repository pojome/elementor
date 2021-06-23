<?php

namespace Elementor\Core\App\Modules\ImportExport;

use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Wp_Cli extends \WP_CLI_Command {

	/**
	 * Export a Kit
	 *
	 * [--include]
	 *      Which type of content to include. Possible values are 'content', 'templates', 'site-settings'.
	 *      if this parameter won't be specified, All data types will be included.
	 *
	 * ## EXAMPLES
	 *
	 * 1. wp elementor kit export path/to/export-file-name.zip
	 *      - This will export all site data to the specified file name.
	 *
	 * 2. wp elementor kit export path/to/export-file-name.zip --include=kit-settings,content
	 *      - This will export only site settings and content.
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function export( $args, $assoc_args ) {
		if ( empty( $args[0] ) ) {
			\WP_CLI::error( 'Please specify a file name' );
		}

		\WP_CLI::line( 'Kit export started.' );

		$export_settings = [
			'include' => [ 'content', 'templates', 'settings' ],
		];

		foreach ( $assoc_args as $key => $value ) {
			$import_settings[ $key ] = explode( ',', $value );
		}

		$export_settings = array_merge( $export_settings, $assoc_args );

		try {
			$exporter = new Export( $export_settings );

			$result = $exporter->run();

			rename( $result['file_name'], $args[0] );
		} catch ( \Error $error ) {
			\WP_CLI::error( $error->getMessage() );
		}

		\WP_CLI::success( 'Kit exported successfully.' );
	}

	/**
	 * Import a Kit
	 *
	 * [--include]
	 *      Which type of content to include. Possible values are 'content', 'templates', 'site-settings'.
	 *      if this parameter won't be specified, All data types will be included.
	 *
	 * [--overrideConditions]
	 *      Templates ids to override conditions for.
	 *
	 * ## EXAMPLES
	 *
	 * 1. wp elementor kit import path/to/elementor-kit.zip
	 *      - This will import the whole kit file content.
	 *
	 * 2. wp elementor kit import path/to/elementor-kit.zip --include=site-settings,content
	 *      - This will import only site settings and content.
	 *
	 * 3. wp elementor kit import path/to/elementor-kit.zip --overrideConditions=3478,4520
	 *      - This will import all content and will override conditions for the given template ids.
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function import( array $args, array $assoc_args ) {
		if ( ! current_user_can( 'administrator' ) ) {
			\WP_CLI::error( 'You must run this command as an admin user' );
		}

		if ( empty( $args[0] ) ) {
			\WP_CLI::error( 'Please specify a file to import' );
		}

		\WP_CLI::line( 'Kit export started' );

		\WP_CLI::line( 'Extracting zip archive...' );

		$extraction_result = Plugin::$instance->uploads_manager->extract_and_validate_zip( $args[0], [ 'json', 'xml' ] );

		if ( is_wp_error( $extraction_result ) ) {
			\WP_CLI::error( $extraction_result->get_error_message() );
		}

		$import_settings = [
			'include' => [ 'templates', 'content', 'site-settings' ],
			'directory' => $extraction_result['extraction_directory'],
		];

		foreach ( $assoc_args as $key => $value ) {
			$import_settings[ $key ] = explode( ',', $value );
		}

		try {
			\WP_CLI::line( 'Importing data...' );

			$import = new Import( $import_settings );

			Plugin::$instance->app->get_component( 'import-export' )->import = $import;

			$import->run();

			\WP_CLI::line( 'Removing temp files...' );

			Plugin::$instance->uploads_manager->remove_file_or_dir( $import_settings['directory'] );

			\WP_CLI::success( 'Kit imported successfully' );
		} catch ( \Error $error ) {
			Plugin::$instance->uploads_manager->remove_file_or_dir( $import_settings['directory'] );

			\WP_CLI::error( $error->getMessage() );
		}
	}
}
