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
		parent::__construct( $error->get_error_message(), $error->get_error_code() );

		$this->error = $error;
	}
}
