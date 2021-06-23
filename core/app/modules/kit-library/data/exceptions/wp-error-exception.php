<?php
namespace Elementor\Core\App\Modules\KitLibrary\Data\Exceptions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// TODO: Maybe transfer into base/data
class Wp_Error_Exception extends \Exception {
	/**
	 * @var \WP_Error
	 */
	protected $error;

	public function __construct( \WP_Error $error ) {
		// If the code is not an http code it transfer it into 500 (Server Error).
		$code = (int) $error->get_error_code();
		$code = 0 === $code ? 500 : $code;

		parent::__construct( $error->get_error_message(), $code );

		$this->error = $error;
	}
}
