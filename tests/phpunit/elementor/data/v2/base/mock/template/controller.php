<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template;

use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Traits;

class Controller extends \Elementor\Data\V2\Base\Controller {

	use Traits\Mock_Random_Name_Type, Traits\Mock_Force_Permission, Traits\Mock_Bypass_Register;

	public function __construct() {
		parent::__construct();

		$this->bypass_original_permission();
	}

	public function get_type() {
		return 'controller';
	}

	public function register_endpoints() {
		// TODO: Implement register_endpoints() method.
	}

	public function do_register_index_endpoint() {
		$this->bypass_original_register();

		add_action( 'elementor_rest_api_before_init', function () {
			add_action( 'rest_api_init', function() {
				$this->register_index_endpoint();
			} );
		} );
	}

	/**
	 * @param $endpoint
	 * @return \Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Endpoint
	 */
	public function do_register_endpoint( $endpoint ) {
		return $this->register_endpoint( $endpoint ); // Protected.
	}

	public function do_register_processor( $processor ) {
		return $this->register_processor( $processor ); // Protected.
	}

	public function get_endpoint_index() {
		return $this->index_endpoint;
	}
}
