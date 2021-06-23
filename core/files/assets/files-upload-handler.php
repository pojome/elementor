<?php

namespace Elementor\Core\Files\Assets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

abstract class Files_Upload_Handler {
	const OPTION_KEY = 'elementor_unfiltered_files_upload';

	public function __construct() {
		add_filter( 'upload_mimes', [ $this, 'support_unfiltered_files_upload' ] );
		add_filter( 'wp_handle_upload_prefilter', [ $this, 'handle_upload_prefilter' ] );
		add_filter( 'wp_check_filetype_and_ext', [ $this, 'check_filetype_and_ext' ], 10, 4 );
	}

	abstract public function get_mime_type();

	abstract public function get_file_type();

	/**
	 * is_elementor_media_upload
	 * @return bool
	 */
	private function is_elementor_media_upload() {
		return isset( $_POST['uploadTypeCaller'] ) && 'elementor-wp-media-upload' === $_POST['uploadTypeCaller']; // phpcs:ignore
	}

	/**
	 * @return bool
	 */
	final public static function is_enabled() {
		$enabled = ! ! get_option( self::OPTION_KEY ) && self::file_sanitizer_can_run();

		/**
		 * @deprecated 3.0.0 Use `elementor/document/urls/edit` filter instead.
		 */
		$enabled = apply_filters( 'elementor/files/svg/enabled', $enabled );

		/**
		 * Allow Unfiltered Files Upload.
		 *
		 * Determines whether to enable unfiltered file uploads.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $enabled Whether upload is enabled or not.
		 */
		$enabled = apply_filters( 'elementor/files/allow_unfiltered_upload', $enabled );

		return $enabled;
	}

	final public function support_unfiltered_files_upload( $existing_mimes ) {
		if ( $this->is_elementor_media_upload() && $this->is_enabled() ) {
			$existing_mimes[ $this->get_file_type() ] = $this->get_mime_type();
		}

		return $existing_mimes;
	}

	/**
	 * handle_upload_prefilter
	 * @param $file
	 *
	 * @return mixed
	 */
	public function handle_upload_prefilter( $file ) {
		if ( ! $this->is_file_should_handled( $file ) ) {
			return $file;
		}

		$ext = pathinfo( $file['name'], PATHINFO_EXTENSION );
		$file_type = $this->get_file_type();
		$display_type = strtoupper( $file_type );

		if ( $file_type !== $ext ) {
			$file['error'] = sprintf( __( 'The uploaded %1$s file is not supported. Please upload a valid %2$s file', 'elementor' ), $ext, $display_type );
			return $file;
		}

		if ( ! self::is_enabled() ) {
			$file['error'] = sprintf( __( '%1$s file is not allowed for security reasons', 'elementor' ), $display_type );
			return $file;
		}

		return $file;
	}

	protected function is_file_should_handled( $file ) {
		$ext = pathinfo( $file['name'], PATHINFO_EXTENSION );

		return $this->is_elementor_media_upload() && $this->get_file_type() === $ext;
	}

	/**
	 * file_sanitizer_can_run
	 * @return bool
	 */
	public static function file_sanitizer_can_run() {
		return class_exists( 'DOMDocument' ) && class_exists( 'SimpleXMLElement' );
	}

	/**
	 * Check filetype and ext
	 *
	 * A workaround for upload validation which relies on a PHP extension (fileinfo)
	 * with inconsistent reporting behaviour.
	 * ref: https://core.trac.wordpress.org/ticket/39550
	 * ref: https://core.trac.wordpress.org/ticket/40175
	 *
	 * @param $data
	 * @param $file
	 * @param $filename
	 * @param $mimes
	 *
	 * @return mixed
	 */
	public function check_filetype_and_ext( $data, $file, $filename, $mimes ) {
		if ( ! empty( $data['ext'] ) && ! empty( $data['type'] ) ) {
			return $data;
		}

		$wp_file_type = wp_check_filetype( $filename, $mimes );
		$file_type = strtolower( $this->get_file_type() );

		if ( $file_type === $wp_file_type['ext'] ) {
			$data['ext'] = $file_type;
			$data['type'] = $this->get_mime_type();
		}

		return $data;
	}
}
