<?php
namespace Elementor\Core\Files;

use Elementor\Core\Base\Base_Object;
use Elementor\Core\Files\File_Types\Base as File_Type_Base;
use Elementor\Core\Files\File_Types\Json;
use Elementor\Core\Files\File_Types\Zip;
use Elementor\Core\Utils\Exceptions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor uploads manager.
 *
 * Elementor uploads manager handler class is responsible for handling file uploads that are not done with WP Media.
 *
 * @since 3.3.0
 */
class Uploads_Manager extends Base_Object {

	const UNFILTERED_FILE_UPLOADS_KEY = 'elementor_unfiltered_files_upload';
	const INVALID_FILE_CONTENT = 'Invalid Content In File';

	/**
	 * @var File_Type_Base[]
	 */
	private $file_type_handlers = [];

	private $allowed_file_extensions;

	private $are_unfiltered_files_enabled;

	/**
	 * @var string
	 */
	private $temp_dir;

	/**
	 * Register File Types
	 *
	 * To Add a new file type to Elementor, with its own handling logic, you need to add it to the $file_types array here.
	 *
	 * @since 3.3.0
	 */
	public function register_file_types() {
		// All file types that have handlers should be included here.
		$file_types = [
			'json' => new Json(),
			'zip' => new Zip(),
		];

		foreach ( $file_types as $file_type => $file_handler ) {
			$this->file_type_handlers[ $file_type ] = $file_handler;
		}
	}

	/**
	 * Extract and Validate Zip
	 *
	 * This method accepts a $file array (which minimally should include a 'tmp_name')
	 *
	 * @param string $file_path
	 * @param array $allowed_file_types
	 * @return array|\WP_Error
	 */
	public function extract_and_validate_zip( $file_path, $allowed_file_types = null ) {
		$result = [];

		/** @var Zip $zip_handler - File Type */
		$zip_handler = $this->file_type_handlers['zip'];

		// Returns an array of file paths.
		$extracted = $zip_handler->extract( $file_path, $allowed_file_types );

		// If there are no extracted file names, no files passed the extraction validation.
		if ( empty( $extracted['files'] ) ) {
			// TODO: Decide what to do if no files passed the extraction validation
			return new \WP_Error( 'file_error', self::INVALID_FILE_CONTENT );
		}

		$result['extraction_directory'] = $extracted['extraction_directory'];

		foreach ( $extracted['files'] as $extracted_file_path ) {
			// Each file is an array with a 'name' (file path) property.
			if ( ! is_wp_error( $this->validate_file( $extracted_file_path ) ) ) {
				$result['files'][] = $extracted_file_path;
			}
		}

		return $result;
	}

	/**
	 * Handle Elementor Upload
	 *
	 * This method receives a $file array. If the received file is a Base64 string, the $file array should include a
	 * 'fileData' property containing the string, which is decoded and has its contents stored in a temporary file.
	 * If the $file parameter passed is a standard $file array, the 'name' and 'tmp_name' properties are used for
	 * validation.
	 *
	 * The file goes through validation; if it passes validation, the file is returned. Otherwise, an error is returned.
	 *
	 * @param array $file
	 * @param array $allowed_file_extensions Optional. an array of file types that are allowed to pass validation for each
	 * upload.
	 * @return array|\WP_Error
	 */
	public function handle_elementor_upload( array $file, $allowed_file_extensions = null ) {
		// If $file['fileData'] is set, it signals that the passed file is a Base64 string that needs to be decoded and
		// saved to a temporary file.
		if ( isset( $file['fileData'] ) ) {
			$file = $this->save_base64_to_tmp_file( $file );
		}

		$validation_result = $this->validate_file( $file['tmp_name'], $allowed_file_extensions );

		if ( is_wp_error( $validation_result ) ) {
			return $validation_result;
		}

		return $file;
	}

	/**
	 * Runs on the 'wp_handle_upload_prefilter' filter.
	 *
	 * @param $file
	 * @return mixed
	 */
	public function handle_elementor_wp_media_upload( $file ) {
		// If it isn't a file uploaded by Elementor, we do not intervene.
		if ( ! $this->is_elementor_wp_media_upload() ) {
			return $file;
		}

		$result = $this->validate_file( $file['tmp_name'] );

		if ( is_wp_error( $result ) ) {
			$file['error'] = $result->get_error_message();
		}

		return $file;
	}

	/**
	 * Get File Type Handler
	 *
	 * Initialize the proper file type handler according to the file extension
	 * and assign it to the file type handlers array.
	 *
	 * @since 3.3.0
	 *
	 * @param string|null $file_extension - file extension
	 * @return File_Type_Base[]|File_Type_Base
	 */
	public function get_file_type_handlers( $file_extension = null ) {
		return self::get_items( $this->file_type_handlers, $file_extension );
	}

	/**
	 * Create Temp File
	 *
	 * Create a random temporary file.
	 *
	 * @since 3.3.0
	 *
	 * @param string $file_content
	 * @param string $file_name
	 * @return string|\WP_Error
	 */
	public function create_temp_file( $file_content, $file_name ) {
		$temp_filename = $this->create_unique_dir() . $file_name;

		file_put_contents( $temp_filename, $file_content ); // phpcs:ignore

		return $temp_filename;
	}

	/**
	 * Get Temp Directory
	 *
	 * Get the temporary files directory path. If the directory does not exist, this method creates it.
	 *
	 * @since 3.3.0
	 *
	 * @return string $temp_dir
	 */
	public function get_temp_dir() {
		if ( ! $this->temp_dir ) {
			$wp_upload_dir = wp_upload_dir();

			$this->temp_dir = implode( DIRECTORY_SEPARATOR, [ $wp_upload_dir['basedir'], 'elementor', 'tmp' ] ) . DIRECTORY_SEPARATOR;

			if ( ! is_dir( $this->temp_dir ) ) {
				wp_mkdir_p( $this->temp_dir );
			}
		}

		return $this->temp_dir;
	}

	/**
	 * Create Unique Temp Dir
	 *
	 * Create a unique temporary directory
	 *
	 * @since 3.3.0
	 *
	 * @return string the new directory path
	 */
	public function create_unique_dir() {
		$unique_dir_path = $this->get_temp_dir() . uniqid() . DIRECTORY_SEPARATOR;

		wp_mkdir_p( $unique_dir_path );

		return $unique_dir_path;
	}

	/**
	 * Are Unfiltered Uploads Enabled
	 *
	 * Checks if the user allowed uploading unfiltered files.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	private function are_unfiltered_uploads_enabled() {
		if ( ! $this->are_unfiltered_files_enabled ) {
			$this->are_unfiltered_files_enabled = ! ! get_option( self::UNFILTERED_FILE_UPLOADS_KEY );
		}

		return $this->are_unfiltered_files_enabled;
	}

	/**
	 * Add File Extension To Allowed Extensions List
	 *
	 * @since 3.3.0
	 *
	 * @param string $file_type
	 */
	private function add_file_extension_to_allowed_extensions_list( $file_type ) {
		$file_handler = $this->file_type_handlers[ $file_type ];

		$file_extension = $file_handler->get_file_extension();

		// Only add the file extension to the list if it doesn't already exist in it.
		if ( ! in_array( $file_extension, $this->allowed_file_extensions, true ) ) {
			$this->allowed_file_extensions[] = $file_extension;
		}
	}

	/**
	 * Save Base64 as File
	 *
	 * Saves a Base64 string as a .tmp file in Elementor's temporary files directory.
	 *
	 * @since 3.3.0
	 *
	 * @param $file
	 * @return array|\WP_Error
	 */
	private function save_base64_to_tmp_file( $file ) {
		$file_content = base64_decode( $file['fileData'] ); // phpcs:ignore

		// If the decode fails
		if ( ! $file_content ) {
			return new \WP_Error( 'file_error', self::INVALID_FILE_CONTENT );
		}

		$temp_filename = $this->create_temp_file( $file_content, $file['fileName'] );

		if ( is_wp_error( $temp_filename ) ) {
			return $temp_filename;
		}

		$new_file_array = [
			// the original uploaded file name
			'name' => $file['fileName'],
			// The path to the temporary file
			'tmp_name' => $temp_filename,
		];

		return $new_file_array;
	}

	/**
	 * is_elementor_wp_media_upload
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	private function is_elementor_wp_media_upload() {
		return isset( $_POST['elementor_wp_media_upload'] ); // phpcs:ignore
	}

	/**
	 * Validate File
	 *
	 * @since 3.3.0
	 *
	 * @param string $file_path
	 * @param array $file_extensions Optional
	 * @return bool|\WP_Error
	 *
	 */
	private function validate_file( $file_path, $file_extensions = [] ) {
		$file_extension = pathinfo( $file_path, PATHINFO_EXTENSION );

		$allowed_file_extensions = $this->get_allowed_file_extensions();

		if ( $file_extensions ) {
			$allowed_file_extensions = array_intersect( $allowed_file_extensions, $file_extensions );
		}

		// Check if the file type (extension) is in the allowed extensions list. If it is a non-standard file type (not
		// enabled by default in WordPress) and unfiltered file uploads are not enabled, it will not be in the allowed
		// file extensions list.
		if ( ! in_array( $file_extension, $allowed_file_extensions, true ) ) {
			return new \WP_Error( Exceptions::FORBIDDEN, 'Uploading this file type is not allowed.' );
		}

		$file_type_handler = $this->get_file_type_handlers( $file_extension );

		// If Elementor does not have a handler for this file type, don't block it.
		if ( ! $file_type_handler ) {
			return true;
		}

		// Here is each file type handler's chance to run its own specific validations
		return $file_type_handler->validate_file( $file_path );
	}

	/**
	 * Remove File Or Directory
	 *
	 * Directory is deleted recursively with all of its contents (subdirectories and files).
	 *
	 * @since 3.3.0
	 *
	 * @param string $path
	 */
	public function remove_file_or_dir( $path ) {
		if ( is_dir( $path ) ) {
			$this->remove_directory_with_files( $path );
		} else {
			unlink( $path );
		}
	}

	/**
	 * Remove Directory with Files
	 *
	 * @since 3.3.0
	 *
	 * @param string $dir
	 * @return bool
	 */
	private function remove_directory_with_files( $dir ) {
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

	/**
	 * Get Allowed File Extensions
	 *
	 * Retrieve an array containing the list of file extensions allowed for upload.
	 *
	 * @since 3.3.0
	 *
	 * @return array file extension/s
	 */
	private function get_allowed_file_extensions() {
		if ( ! $this->allowed_file_extensions ) {
			$this->allowed_file_extensions = array_keys( get_allowed_mime_types() );

			foreach ( $this->get_file_type_handlers() as $file_type => $handler ) {
				if ( $handler->is_upload_allowed() ) {
					// Add the file extension to the allowed extensions list only if unfiltered files upload is enabled.
					$this->add_file_extension_to_allowed_extensions_list( $file_type );
				}
			}
		}

		return $this->allowed_file_extensions;
	}

	public function __construct() {
		$this->register_file_types();

		add_filter( 'wp_handle_upload_prefilter', [ $this, 'handle_elementor_wp_media_upload' ] );
	}
}
