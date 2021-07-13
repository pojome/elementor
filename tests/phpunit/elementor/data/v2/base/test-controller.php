<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\V2\Base;

use Elementor\Data\V2\Base\Endpoint\Index;
use Elementor\Data\V2\Manager;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Processor\Controller as ControllerWithProcessor;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\WithEndpoint\Controller as ControllerWithEndpoint;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Controller as ControllerTemplate;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Endpoint as EndpointTemplate;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Endpoint\Format as EndpointFormatTemplate;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Processor as ProcessorTemplate;

class Test_Controller extends Data_Test_Base {
	public function test_get_full_name() {
		// Arrange.
		$controller = new ControllerWithEndpoint();

		// Act.
		$name = $controller->get_full_name();

		// Assert.
		$this->assertEquals( 'test-controller-' . $controller->random, $name );
	}

	public function test_get_name() {
		// Arrange.
		$controller = new ControllerWithEndpoint();

		// Act.
		$name = $controller->get_name();

		// Assert.
		$this->assertEquals( 'test-controller-' . $controller->random, $name );
	}

	public function test_get_namespace() {
		// Arrange.
		$controller = new ControllerWithEndpoint();

		// Act.
		$actual = $controller->get_namespace();

		// Assert.
		$this->assertEquals( Manager::ROOT_NAMESPACE . '/v' . Manager::VERSION, $actual );
	}

	public function test_get_base_route() {
		// Arrange.
		$controller = new ControllerWithEndpoint();

		// Act.
		$actual = $controller->get_base_route() ;

		// Assert.
		$this->assertEquals( Manager::REST_BASE . $controller->get_name(), $actual );
	}

	public function test_get_controller_route() {
		// Arrange.
		$controller = new ControllerWithEndpoint();

		// Act.
		$actual = $controller->get_controller_route();

		// Assert.
		$this->assertEquals( $controller->get_namespace() . '/' . $controller->get_base_route(), $actual );
	}

	public function test_get_controller_index() {
		// Arrange.
		$controller = new ControllerTemplate();
		$this->manager->run_server();

		// Act.
		$data = $controller->get_controller_index()->get_data();
		$actual = str_replace( $data['namespace'] . '/', '', $data['controller'] );

		// Assert.
		$this->assertEquals( $controller->get_name(), $actual );
	}

	public function test_get_permission_callback__ensure_admin_have_privileges() {
		// Arrange - Set administrator.
		wp_set_current_user( $this->factory()->create_and_get_administrator_user()->ID );

		$controller = new ControllerWithEndpoint();
		$controller->bypass_original_permission( false );

		$methods = explode( ', ', \WP_REST_Server::ALLMETHODS );

		foreach( $methods  as $method ) {
			// Act.
			$actual = $controller->get_permission_callback( new \WP_REST_Request( $method ) );

			// Assert.
			$this->assertEquals( true, $actual );
		}
	}

	public function test_get_permission_callback__ensure_editor_have_no_privileges() {
		// Arrange - Set editor.
		wp_set_current_user( $this->factory()->get_editor_user()->ID );

		$controller = new ControllerWithEndpoint();
		$controller->bypass_original_permission( false );

		$methods = explode( ', ', \WP_REST_Server::ALLMETHODS );

		foreach( $methods as $method ) {
			// Act.
			$actual = $controller->get_permission_callback( new \WP_REST_Request( $method ) );

			// Assert.
			$this->assertEquals( false, $actual );
		}
	}

	public function test_get_items() {
		// Arrange.
		$controller = new ControllerTemplate();
		$this->manager->run_server();

		// Act.
		$data = $controller->get_items( null )->get_data();
		$actual = str_replace( $data['namespace'] . '/', '', $data['controller'] );

		// Assert.
		$this->assertEquals( $controller->get_name(), $actual );
	}

	public function test_get_processors() {
		// Arrange.
		$controller = new ControllerWithProcessor();
		$this->manager->run_server();

		// Act.
		$processors = $controller->get_processors( $controller->get_name() );

		// Assert.
		$this->assertCount( 1, $processors );
		$this->assertEquals( $controller->processors[ $controller->get_name() ][0], $processors[0] );
	}

	// test_register_processors() - Does not require since `test_get_processors` already test it.

	public function test_register_index_endpoint() {
		// Arrange.
		$controller = new ControllerTemplate();
		$controller->do_register_index_endpoint();
		$this->manager->run_server();

		// Act.
		$data = $this->manager->run_endpoint( $controller->get_name() );
		$actual = str_replace( $data['namespace'] . '/', '', $data['controller'] );

		// Assert.
		$this->assertEquals( $controller->get_name(), $actual );
	}

	public function test_register_endpoint() {
		// Arrange.
		$controller = new ControllerTemplate();
		$controller->bypass_original_register();

		// Act.
		$endpoint = $controller->do_register_endpoint( new EndpointTemplate( $controller ) );

		// Assert.
		$this->assertCount( 1, $controller->endpoints );
		$this->assertEquals( $endpoint, array_values( $controller->endpoints )[0] );
	}

	public function test_register_processor() {
		// Arrange.
		$controller = new ControllerTemplate();
		$controller->bypass_original_register();

		$controller->do_register_endpoint( new EndpointTemplate( $controller ) );

		// Act.
		$processor_instance = $controller->do_register_processor( new ProcessorTemplate( $controller ) );

		// Assert.
		$this->assertCount( 1, $controller->processors );
		$this->assertCount( 1, $controller->get_processors( $processor_instance->get_command() ) );
	}

	public function test_register() {
		// Arrange.
		$controller = new ControllerWithEndpoint();
		$this->manager->run_server();

		// Act.
		$rest_index = $this->manager->run_endpoint( $controller->get_name() );
		$rest_routes = $rest_index['routes'];

		// Assert.
		foreach ( $controller->endpoints as $endpoint ) {
			$this->assertArrayHaveKeys( [ '/' . $controller->get_controller_route() . '/' . $endpoint->get_name() ], $rest_routes, 'Validate `$this->register_endpoints();`.' );
		}

		$this->assertArrayHaveKeys( [ '/' . $controller->get_controller_route() ], $rest_routes, 'Validate `$this->register_index_endpoint();`.' );
		$this->assertInstanceOf( Index::class, $controller->get_endpoint_index() );
	}

	public function test_register__endpoint_with_format() {
		// Arrange.
		$controller = new ControllerTemplate();
		$controller->bypass_original_register();

		// Act.
		$endpoint = $controller->do_register_endpoint( new EndpointFormatTemplate( $controller ) );
		$actual = reset( $this->manager->command_formats );

		// Assert.
		$this->assertCount( 1, $this->manager->command_formats );
		$this->assertEquals( $controller->get_name() . '/' . $endpoint->get_name() . '/{arg_id}', $actual );
	}
}
