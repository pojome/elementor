<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\Base;

use Elementor\Data\Manager;
use Elementor\Testing\Elementor_Test_Base;
use \Elementor\Data\Base\Controller as ControllerBase;
use Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Processor\Controller as ControllerWithProcessor;
use Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Simple\Controller as ControllerSimple;
use Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Template\Controller as ControllerTemplate;
use Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Template\Endpoint as EndpointTemplate;
use Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Template\Endpoint\Format as EndpointFormatTemplate;
use Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Template\Processor as ProcessorTemplate;

class Test_Controller extends Elementor_Test_Base {

	/**
	 * @var \Elementor\Data\Manager
	 */
	protected $manager;

	public function setUp() {
		parent::setUp();

		$this->manager = Manager::instance();
	}

	public function tearDown() {
		parent::tearDown();

		$this->manager->kill_server();
	}

	public function test_create_simple() {
		$controller = new ControllerSimple();
		$this->manager->run_server();

		$rest_index = $this->manager->run_endpoint( $controller->get_name() );
		$rest_routes = $rest_index['routes'];

		$this->assert_array_have_keys( [ '/' . $controller->get_controller_route() ], $rest_routes, 'Validate `$this->register_internal_endpoints();`.' );

		foreach ( $controller->endpoints as $endpoint ) {
			$this->assert_array_have_keys( [ '/' . $controller->get_controller_route() . '/' . $endpoint->get_name() ], $rest_routes, 'Validate `$this->register_endpoints();`.' );
		}
	}

	public function test_get_name() {
		$controller = new ControllerSimple();

		$name = $controller->get_name();

		$this->assertEquals( 'test-controller-' . $controller->random, $name );
	}

	public function test_get_namespace() {
		$controller = new ControllerSimple();

		$this->assertEquals( Manager::ROOT_NAMESPACE . '/v' . Manager::VERSION, $controller->get_namespace() );
	}

	public function test_get_reset_base() {
		$controller = new ControllerSimple();

		$this->assertEquals( Manager::REST_BASE . $controller->get_name(), $controller->get_rest_base() );
	}

	public function test_get_controller_route() {
		$controller = new ControllerSimple();

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

	public function test_register_internal_endpoints() {
		$controller = new ControllerTemplate();
		$controller->do_register_internal_endpoints();
		$this->manager->run_server();

		$data = $this->manager->run_endpoint( $controller->get_name() );

		$controller_pure_name = str_replace( $data['namespace'] . '/', '', $data['controller'] );

		$this->assertEquals( $controller->get_name(), $controller_pure_name );
	}

	public function test_register_endpoint() {
		$controller = new ControllerTemplate();
		$controller->bypass_original_register();

		$endpoint_instance = $controller->do_register_endpoint( EndpointTemplate::class );

		$this->assertCount( 1, $controller->endpoints );
		$this->assertEquals( $endpoint_instance, array_values( $controller->endpoints )[0] );
	}

	public function test_register_processor() {
		$controller = new ControllerTemplate();
		$controller->bypass_original_register();

		$controller->do_register_endpoint( EndpointTemplate::class );

		$processor_instance = $controller->do_register_processor( ProcessorTemplate::class );

		$this->assertCount( 1, $controller->processors );
		$this->assertCount( 1, $controller->get_processors( $processor_instance->get_command() ) );
	}

	public function test_register_endpoint_with_format() {
		$controller = new ControllerTemplate();
		$controller->bypass_original_register();

		$endpoint_instance = $controller->do_register_endpoint( EndpointFormatTemplate::class );

		$this->assertCount( 1, $this->manager->command_formats );

		$command_format = array_values( $this->manager->command_formats )[0];

		$this->assertEquals( $controller->get_name() . '/' . $endpoint_instance->get_name() . '/{arg_id}', $command_format );
	}

	public function test_get_items_recursive() {
		$this->manager->run_server();

		$controller_instance = new \Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Template\Controller();
		$endpoint_instance0 = $controller_instance->do_register_endpoint( EndpointTemplate::class );
		$endpoint_instance1 = $controller_instance->do_register_endpoint( EndpointTemplate::class );
		$endpoint_index_instance = $controller_instance->do_register_endpoint( \Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Template\Endpoint\Index::class );

		$endpoint_instance0->set_test_data( 'get_items', 'endpoint0_result');
		$endpoint_instance1->set_test_data( 'get_items', 'endpoint1_result');

		// Result should include both endpoints result.
		$results = $controller_instance->get_items_recursive( [ $endpoint_index_instance ] );
		$count = 0;

		foreach ( $results as $result ) {
			$this->assertEquals( 'endpoint' . $count . '_result', $result );
			$count++;
		}
	}

	public function test_get_items_recursive_simulated() {
		/**
		 * TODO: Create Base Endpoint\Internal
		 * TODO: Create Base Endpoint\Internal\Index
		 */

		$controller = $this->manager->register_controller_instance( new Mock\Recursive\Controller );
		$this->manager->run_server(); // Ensure controller loaded.

		// Run internal index endpoint. Run endpoint 'test-controller'.
		$endpoints_results = $this->manager->run_endpoint( $controller->get_name() );

		foreach ( $endpoints_results as $endpoint_name => $endpoints_result ) {
			// Run endpoint like `test-controller/test-endpoint-{random}`.
			$endpoint = $controller->get_name() . '/' . $endpoint_name;
			$result = $this->manager->run_endpoint( $endpoint );

			// Each manual run of the endpoint equals to part of $endpoints_results which is recursive result.
			$this->assertEquals( $endpoints_result, $result );
		}
	}

	public function test_get_permission_callback() {
		// Set admin.
		wp_set_current_user( $this->factory()->create_and_get_administrator_user()->ID );

		$controller = new ControllerSimple();
		$controller->bypass_original_permission( false );

		$methods = explode( ', ', \WP_REST_Server::ALLMETHODS );

		foreach( $methods  as $method ) {
			$request = new \WP_REST_Request( $method );
			$this->assertEquals( $controller->get_permission_callback( $request ), true );
		}

		// Set editor.
		wp_set_current_user( $this->factory()->get_editor_user()->ID );

		foreach( $methods as $method ) {
			$request = new \WP_REST_Request( $method );
			$this->assertEquals( $controller->get_permission_callback( $request ), false );
		}
	}
}
