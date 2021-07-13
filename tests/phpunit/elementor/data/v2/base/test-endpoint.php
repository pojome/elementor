<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\V2\Base;

use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Endpoint;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\WithEndpoint\Controller as ControllerWithEndpoint;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Controller as ControllerTemplate;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Endpoint as EndpointTemplate;

class Test_Endpoint extends Data_Test_Base {
	public function test_get_controller() {
		// Arrange.
		$controller = new Mock\Template\Controller();
		$controller->bypass_original_register();

		$endpoint = new Mock\Template\Endpoint( $controller );
		$sub_endpoint = new Mock\Template\Endpoint( $endpoint );

		// Act + Assert.
		$this->assertEquals( $controller, $endpoint->get_controller() );
		$this->assertEquals( $controller, $sub_endpoint->get_controller() );
	}

	public function test_get_parent() {
		// Arrange.
		$controller = new Mock\Template\Controller();
		$controller->bypass_original_register();

		$endpoint = new Mock\Template\Endpoint( $controller );
		$sub_endpoint = new Mock\Template\Endpoint( $endpoint );

		// Act.
		$expected = $sub_endpoint->get_parent();

		// Assert.
		$this->assertEquals( $expected, $endpoint );
	}

	public function test_get_public_name() {
		// Arrange.
		$controller = new ControllerWithEndpoint();
		$this->manager->run_server();

		$endpoint = reset( $controller->endpoints );
		$index_endpoint = $controller->get_endpoint_index();

		// Act.
		$index_endpoint_name = $index_endpoint->get_public_name();
		$endpoint_name = $endpoint->get_public_name();

		// Assert.
		$this->assertEquals( '', $index_endpoint_name );
		$this->assertEquals( $endpoint->get_name(), $endpoint_name );
	}

	public function test_get_full_command() {
		// Arrange.
		$controller = new ControllerWithEndpoint();
		$this->manager->run_server();

		$endpoint = reset( $controller->endpoints );
		$excepted = $controller->get_name() . '/' . $endpoint->get_name();

		// Act.
		$actual = $endpoint->get_full_command();

		$this->assertEquals( $excepted, $actual );
	}

	public function test_get_full_command__from_parent_sub_endpoint() {
		// Arrange.
		$controller = new Mock\Template\Controller();
		$controller->bypass_original_register();

		$endpoint = new Mock\Template\Endpoint( $controller );
		$sub_endpoint = new Mock\Template\Endpoint(  $endpoint );

		// Act.
		$actual = $sub_endpoint->get_full_command();

		// Assert.
		$this->assertEquals( implode( '/', [
			$controller->get_name(),
			$endpoint->get_name(),
			$sub_endpoint->get_name(),
		] ), $actual );
	}

	public function test_get_full_command__from_grandchild_endpoint() {
		// Arrange.
		$controller = new Mock\Template\Controller();
		$controller->bypass_original_register();

		$endpoint = new Mock\Template\Endpoint( $controller );
		$sub_endpoint = new Mock\Template\Endpoint( $endpoint, 'first-sub-route' );
		$descendant_endpoint = new Mock\Template\Endpoint( $sub_endpoint, 'second-sub-route' );

		// Act.
		$actual = $descendant_endpoint->get_full_command();

		// Assert.
		$this->assertEquals( implode( '/', [
			$controller->get_name(),
			$endpoint->get_name(),
			$sub_endpoint->get_name(),
			$descendant_endpoint->get_name()
		] ), $actual );
	}

	public function test_get_name_ancestry() {
		// Arrange.
		$controller = new Mock\Template\Controller();
		$controller->bypass_original_register();

		$endpoint = new Mock\Template\Endpoint( $controller );
		$sub_endpoint = new Mock\Template\Endpoint( $endpoint );

		// Act.
		$actual = $sub_endpoint->get_name_ancestry();

		// Assert.
		$this->assertEquals( implode( '/', [
			$controller->get_name(),
			$endpoint->get_name(),
			$sub_endpoint->get_name(),
		] ), $actual );
	}

	public function test_register_sub_endpoint() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = new EndpointTemplate( $controller );

		/**
		 * @var $sub_endpoint \Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Endpoint
		 */
		$sub_endpoint = $endpoint->register_sub_endpoint(
			new Endpoint( $endpoint, 'test-route' )
		);
		$sub_endpoint->set_test_data( 'get_items', 'valid' );

		$endpoint = $controller->get_name()  . '/' .
		            $endpoint->get_name() .
		            '/test-route/' .
		            $sub_endpoint->get_name();

		// Act.
		$data = $this->manager->run_endpoint( $endpoint  );

		// Assert.
		$this->assertEquals( $data, 'valid' );
	}

	public function test_register_sub_endpoint__run_as_command_from_sub_endpoint() {
		// Arrange.
		$controller = $this->manager->register_controller( new Mock\Template\Controller );

		$this->manager->run_server();

		$endpoint = new Mock\Template\Endpoint( $controller );

		$sub_endpoint = new Mock\Template\Endpoint( $endpoint, 'test-route' );
		$sub_endpoint->set_test_data( 'get_items', 'valid' );

		$endpoint->register_sub_endpoint( $sub_endpoint );

		$command = $controller->get_name() . '/' .
		            $endpoint->get_name() . '/' .
		            'test-route/' .
		            $sub_endpoint->get_name();
		// Act.
		$data = $this->manager->run( $command );

		// Assert.
		$this->assertEquals( 'valid', $data );
	}

	public function test_register_sub_endpoint___run_as_endpoint_from_grandchild_endpoint() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = new EndpointTemplate( $controller );

		$this->manager->register_controller( $controller );

		$sub_endpoint = $endpoint->register_sub_endpoint(
			new Endpoint( $endpoint, '/first-sub-route' )
		);

		/**
		 * @var $descendant_endpoint \Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Endpoint
		 */
		$descendant_endpoint = $sub_endpoint->register_sub_endpoint(
			new Endpoint( $sub_endpoint, '/second-sub-route' )
		);
		$descendant_endpoint->set_test_data( 'get_items', 'valid' );

		$endpoint = $controller->get_name() . '/' .
		            $endpoint->get_name() . '/' .
		            'first-sub-route/' .
		            $sub_endpoint->get_name() . '/' .
		            'second-sub-route/' .
		            $descendant_endpoint->get_name();

		// Act.
		$data = $this->manager->run_endpoint( $endpoint  );

		// Assert.
		$this->assertEquals( $data, 'valid' );
	}

	public function test_register_sub_endpoint___run_as_command_from_grandchild_endpoint() {
		// Arrange.
		$controller = $this->manager->register_controller( new Mock\Template\Controller );

		$this->manager->run_server();

		$endpoint = new Mock\Template\Endpoint( $controller );
		$sub_endpoint = $endpoint->register_sub_endpoint( new Mock\Template\Endpoint( $endpoint ) );

		/**
		 * @var $descendant_endpoint \Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Endpoint
		 */
		$descendant_endpoint = $sub_endpoint->register_sub_endpoint(
			new Mock\Template\Endpoint( $sub_endpoint, 'first-sub-route/second-sub-route' )
		);
		$descendant_endpoint->set_test_data( 'get_items', 'valid' );

		$endpoint = $controller->get_name() . '/' .
		            $endpoint->get_name() . '/' .
		            $sub_endpoint->get_name() . '/' .
		            'first-sub-route/' .
		            'second-sub-route/' .
		            $descendant_endpoint->get_name();

		// Act.
		$data = $this->manager->run_endpoint( $endpoint );

		// Assert.
		$this->assertEquals( 'valid', $data );
	}

	public function test_register_sub_endpoint___run_as_command_from_sub_endpoint_of_index_endpoint() {
		// Arrange.
		$controller = $this->manager->register_controller( new Mock\Template\Controller );

		$this->manager->run_server();

		$index_endpoint = $controller->index_endpoint;

		/**
		 * @var $sub_endpoint \Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Endpoint
		 */
		$sub_endpoint = $index_endpoint->register_sub_endpoint( new Mock\Template\Endpoint( $index_endpoint ) );
		$sub_endpoint->set_test_data( 'get_items', 'valid' );

		$endpoint = $controller->get_name() . '/' .
		            $sub_endpoint->get_name();

		// Act.
		$data = $this->manager->run_endpoint( $endpoint );

		// Assert.
		$this->assertEquals( 'valid', $data );
	}
}
