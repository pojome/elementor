<?php
namespace Elementor\Core\Files\File_Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Json extends Base {

	/**
	 * Get File Extension
	 *
	 * Returns the file type's file extension
	 *
	 * @since 3.3.0
	 *
	 * @return string - file extension
	 */
	public function get_file_extension() {
		return 'json';
	}
}
