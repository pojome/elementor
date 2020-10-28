<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\Base;

use Elementor\Data\Manager;
use Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Processor\Controller as ControllerWithProcessor;
use Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\WithEndpoint\Controller as ControllerWithEndpoint;
use Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Template\Controller as ControllerTemplate;
use Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Template\Endpoint as EndpointTemplate;
use Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Template\Endpoint\Format as EndpointFormatTemplate;
use Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Template\Processor as ProcessorTemplate;

class Test_Controller extends Data_Test_Base {
	public function test_create_simple() {
		$controller = new ControllerWithEndpoint();
		$this->manager->run_server();

		$rest_index = $this->manager->run_endpoint( $controller->get_name() );
		$rest_routes = $rest_index['routes'];

		foreach ( $controller->endpoints as $endpoint ) {
			$this->assertArrayHaveKeys( [ '/' . $controller->get_controller_route() . '/' . $endpoint->get_name() ], $rest_routes, 'Validate `$this->register_endpoints();`.' );
		}

		$this->assertArrayHaveKeys( [ '/' . $controller->get_controller_route() ], $rest_routes, 'Validate `$this->register_index_endpoint();`.' );
		$this->assertNotEquals( null, $controller->get_endpoint_index() );
	}

	public function test_get_name() {
		$controller = new ControllerWithEndpoint();

		$name = $controller->get_name();

		$this->assertEquals( 'test-controller-' . $controller->random, $name );
	}

	public function test_get_full_name() {
		$controller = new ControllerWithEndpoint();

		$name = $controller->get_full_name();

		$this->assertEquals( 'test-controller-' . $controller->random, $name );
	}

	public function test_get_namespace() {
		$controller = new ControllerWithEndpoint();

		$this->assertEquals( Manager::ROOT_NAMESPACE . '/v' . Manager::VERSION, $controller->get_namespace() );
	}

	public function test_get_reset_base() {
		$controller = new ControllerWithEndpoint();

		$this->assertEquals( Manager::REST_BASE . $controller->get_name(), $controller->get_rest_base() );
	}

	public function test_get_controller_route() {
		$controller = new ControllerWithEndpoint();

		$this->assertEquals( $controller->get_namespace() . '/' . $controller->get_rest_base(), $controller->get_controller_route() );
	}

	public function test_get_controller_index() {
		$controller = new ControllerTemplate();
		$this->manager->run_server();

		$data = $controller->get_controller_index()->get_data();

		$controller_pure_name = str_replace( $data['namespace'] . '/', '', $data['controller'] );

		$this->assertEquals( $controller->get_name(), $controller_pure_name );
	}

	public function test_get_processors() {
		// Validate also `$register_processors();`.
		$controller = new ControllerWithProcessor();
		$this->manager->run_server();

		$processors = $controller->get_processors( $controller->get_name() );

		$this->assertCount( 1, $processors );
		$this->assertEquals( $controller->processors[ $controller->get_name() ][0], $processors[0] );
	}

	public function test_get_items() {
		$controller = new ControllerTemplate();
		$this->manager->run_server();

		$data = $controller->get_items( null )->get_data();

		$controller_pure_name = str_replace( $data['namespace'] . '/', '', $data['controller'] );

		$this->assertEquals( $controller->get_name(), $controller_pure_name );
	}

	public function test_register_index_endpoint() {
		$controller = new ControllerTemplate();
		$controller->do_register_index_endpoint();
		$this->manager->run_server();

		$data = $this->manager->run_endpoint( $controller->get_name() );

		$controller_pure_name = str_replace( $data['namespace'] . '/', '', $data['controller'] );

		$this->assertEquals( $controller->get_name(), $controller_pure_name );
	}

	public function test_register_endpoint() {
		$controller = new ControllerTemplate();
		$controller->bypass_original_register();

		$endpoint_instance = $controller->do_register_endpoint( new EndpointTemplate( $controller ) );

		$this->assertCount( 1, $controller->endpoints );
		$this->assertEquals( $endpoint_instance, array_values( $controller->endpoints )[0] );
	}

	public function test_register_processor() {
		$controller = new ControllerTemplate();
		$controller->bypass_original_register();

		$controller->do_register_endpoint( new EndpointTemplate( $controller ) );

		$processor_instance = $controller->do_register_processor( new ProcessorTemplate( $controller ) );

		$this->assertCount( 1, $controller->processors );
		$this->assertCount( 1, $controller->get_processors( $processor_instance->get_command() ) );
	}

	public function test_register_endpoint_with_format() {
		$controller = new ControllerTemplate();
		$controller->bypass_original_register();

		$endpoint_instance = $controller->do_register_endpoint( new EndpointFormatTemplate( $controller ) );

		$this->assertCount( 1, $this->manager->command_formats );

		$command_format = array_values( $this->manager->command_formats )[0];

		$this->assertEquals( $controller->get_name() . '/' . $endpoint_instance->get_name() . '/{arg_id}', $command_format );
	}

	public function test_get_permission_callback() {
		// Set admin.
		wp_set_current_user( $this->factory()->create_and_get_administrator_user()->ID );

		$controller = new ControllerWithEndpoint();
		$controller->bypass_original_permission( false );

		$methods = explode( ', ', \WP_REST_Server::ALLMETHODS );

		foreach( $methods  as $method ) {
			$request = new \WP_REST_Request( $method );
			$this->assertEquals( true, $controller->get_permission_callback( $request ) );
		}

		// Set editor.
		wp_set_current_user( $this->factory()->get_editor_user()->ID );

		foreach( $methods as $method ) {
			$request = new \WP_REST_Request( $method );
			$this->assertEquals( false, $controller->get_permission_callback( $request ) );
		}
	}
}
