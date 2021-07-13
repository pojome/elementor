<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template;

use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Traits\Mock_Force_Permission;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Traits\Mock_Bypass_Register;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Traits\Mock_Random_Name_Type;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Traits\Mock_Test_Data;

class Sub_Controller extends \Elementor\Data\V2\Base\Controller {
	use Mock_Random_Name_Type, Mock_Force_Permission, Mock_Test_Data, Mock_Bypass_Register;

	/**
	 * @var string
	 */
	private $test_route;

	/**
	 * @var \Elementor\Data\V2\Base\Controller
	 */
	private $test_parent_controller = null;

	public function __construct( $parent_controller = null ) {
		$this->test_parent_controller = $parent_controller;
		parent::__construct( $parent_controller );
		$this->bypass_original_permission();
	}

	function get_type() {
		return 'sub-controller';
	}

	public function register_endpoints() {
		// TODO: Implement register_endpoints() method.
	}

	public function get_items( $request ) {
		$test_data = $this->get_test_data( __FUNCTION__ );

		if ( $test_data ) {
			return $test_data;
		}

		return parent::get_items( $request );
	}

	public function get_item( $request ) {
		$test_data = $this->get_test_data( __FUNCTION__ );

		if ( $test_data ) {
			return $test_data;
		}

		return parent::get_item( $request );
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
		return $this->register_endpoint( $endpoint );
	}

	public function get_endpoint_index() {
		return $this->index_endpoint;
	}

	public function get_parent_name() {
		return $this->test_parent_controller->get_name();
	}
}
