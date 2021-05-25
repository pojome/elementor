<?php
namespace Elementor\Tests\Phpunit\Elementor\Core\Data;

use Elementor\Data\Base\Processor;
use Elementor\Data\Manager;
use Elementor\Testing\Elementor_Test_Base;
use Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Template\Controller as ControllerTemplate;
use Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Simple\Controller as ControllerSimple;
use Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Processor\Controller as ControllerWithProcessor;


class Test_Manager extends Elementor_Test_Base {

	/**
	 * @var \Elementor\Data\Manager
	 */
	protected $manager;

	public function setUp() {
		parent::setUp();

		$this->manager = Manager::instance();
		$this->manager->kill_server();
	}

	public function tearDown() {
		parent::tearDown();

		$this->manager->kill_server();
	}

	public function test_get_controllers() {
		$controller = $this->manager->register_controller( ControllerTemplate::class );
		$controllers = $this->manager->get_controllers();

		$this->assertCount( 1, $controllers );
		$this->assertArrayHasKey( $controller->get_name(), $controllers );
	}

	public function test_register_controller() {
		$controller = $this->manager->register_controller( ControllerTemplate::class );

		$this->assertArrayHasKey( $controller->get_name(), $this->manager->controllers );
	}

	public function test_register_controller_instance() {
		$controller = new ControllerTemplate();
		$controller = $this->manager->register_controller_instance( $controller );

		$this->assertArrayHasKey( $controller->get_name(), $this->manager->controllers );
	}

	public function test_register_endpoint_format() {
		$controller = new ControllerTemplate();

		$this->manager->register_endpoint_format( 'test-command', 'test-command/{test-format}' );

		$this->assertEquals( 'test-command/{test-format}', $this->manager->command_formats['test-command'] );
	}

	public function test_find_controller_instance() {
		$controller = new ControllerTemplate();
		$controller = $this->manager->register_controller_instance( $controller );

		// Case controller name.
		$this->assertEquals( $controller, $this->manager->find_controller_instance( $controller->get_name() ));
	}

	public function test_find_controller_instance_advance() {
		$controller = new ControllerSimple(); // controller with endpoint.
		$controller = $this->manager->register_controller_instance( $controller );

		$this->manager->run_server();

		$endpoint_instance = array_values( $controller->endpoints )[ 0 ];

		// Case controller + endpoint name.
		$this->assertEquals( $controller, $this->manager->find_controller_instance( $controller->get_name() . '/' . $endpoint_instance->get_name() ));
	}

	public function test_command_extract_args() {
		$args = [];
		$command = 'controller/endpoint/?id=test&test=true';
		$command_extracted = $this->manager->command_extract_args( $command, $args );

		$this->assertEquals( 'controller/endpoint', $command_extracted->command );
		$this->assertEquals( [ 'id' => 'test', 'test' => 'true' ], $command_extracted->args );
	}

	public function test_command_extract_args_merged() {
		$args = [ 'merge' => 'true'];
		$command = 'controller/endpoint/?id=test&test=true';
		$command_extracted = $this->manager->command_extract_args( $command, $args );

		$this->assertEquals( 'controller/endpoint', $command_extracted->command );
		$this->assertEquals( [ 'id' => 'test', 'test' => 'true', 'merge' => 'true' ], $command_extracted->args );
	}

	public function test_command_to_endpoint() {
		$one_parameter = $this->manager->command_to_endpoint( 'controller/endpoint', 'controller/endpoint/{paramA}', [
			'paramA' => 'valueA',
		] );
		$this->assertEquals( 'controller/endpoint/valueA', $one_parameter );

		$one_parameter = $this->manager->command_to_endpoint( 'controller/endpoint', 'controller/{paramA}/endpoint', [
			'paramA' => 'valueA',
		] );
		$this->assertEquals( 'controller/valueA/endpoint', $one_parameter );

		$one_parameter = $this->manager->command_to_endpoint( 'controller/endpoint', 'controller/{paramA}/endpoint/whatever', [
			'paramA' => 'valueA',
		] );
		$this->assertEquals( 'controller/valueA/endpoint/whatever', $one_parameter );

		$one_parameter = $this->manager->command_to_endpoint( 'controller/endpoint', 'controller/whatever/{paramA}/endpoint', [
			'paramA' => 'valueA',
		] );
		$this->assertEquals( 'controller/whatever/valueA/endpoint', $one_parameter );

		$one_parameter = $this->manager->command_to_endpoint( 'controller/endpoint', 'controller/{paramA}/whatever/endpoint', [
			'paramA' => 'valueA',
		] );
		$this->assertEquals( 'controller/valueA/whatever/endpoint', $one_parameter );

		$two_parameter = $this->manager->command_to_endpoint( 'controller/endpoint', 'controller/{paramA}/endpoint/{paramB}', [
			'paramA' => 'valueA',
			'paramB' => 'valueB',
		] );
		$this->assertEquals( 'controller/valueA/endpoint/valueB', $two_parameter );

		$two_parameter = $this->manager->command_to_endpoint( 'controller/endpoint', 'controller/{paramA}/{paramB}/endpoint', [
			'paramA' => 'valueA',
			'paramB' => 'valueB',
		] );
		$this->assertEquals( 'controller/valueA/valueB/endpoint', $two_parameter );

		$two_parameter = $this->manager->command_to_endpoint( 'controller/endpoint', 'controller/endpoint/{paramA}/{paramB}', [
			'paramA' => 'valueA',
			'paramB' => 'valueB',
		] );
		$this->assertEquals( 'controller/endpoint/valueA/valueB', $two_parameter );

		$two_parameter = $this->manager->command_to_endpoint( 'controller/endpoint', 'controller/endpoint/{paramA}/try-hard/{paramB}', [
			'paramA' => 'valueA',
			'paramB' => 'valueB',
		] );
		$this->assertEquals( 'controller/endpoint/valueA/try-hard/valueB', $two_parameter );

		$two_parameter_second_missing = $this->manager->command_to_endpoint( 'controller/endpoint', 'controller/{paramA}/endpoint/{paramB}', [
			'paramA' => 'valueA',
		] );
		$this->assertEquals( 'controller/valueA/endpoint', $two_parameter_second_missing );

		$two_parameter_second_missing = $this->manager->command_to_endpoint( 'controller/endpoint', 'controller/{paramA}/{paramB}/endpoint', [
			'paramA' => 'valueA',
		] );
		$this->assertEquals( 'controller/valueA', $two_parameter_second_missing );

		$two_parameter_second_missing = $this->manager->command_to_endpoint( 'controller/endpoint', 'controller/endpoint/{paramA}/{paramB}', [
			'paramA' => 'valueA',
		] );
		$this->assertEquals( 'controller/endpoint/valueA', $two_parameter_second_missing );

		$two_parameter_second_missing = $this->manager->command_to_endpoint( 'controller/endpoint', 'controller/endpoint/{paramA}/try-hard/{paramB}', [
			'paramA' => 'valueA',
		] );
		$this->assertEquals( 'controller/endpoint/valueA/try-hard', $two_parameter_second_missing );

		$two_parameters_first_missing = $this->manager->command_to_endpoint( 'controller/endpoint', 'controller/{paramA}/endpoint/{paramB}', [
			'paramB' => 'valueB',
		] );
		$this->assertEquals( 'controller', $two_parameters_first_missing );

		$two_parameters_first_missing = $this->manager->command_to_endpoint( 'controller/endpoint', 'controller/{paramA}/{paramB}/endpoint', [
			'paramB' => 'valueB',
		] );
		$this->assertEquals( 'controller', $two_parameters_first_missing );

		$two_parameters_first_missing = $this->manager->command_to_endpoint( 'controller/endpoint', 'controller/endpoint/{paramA}/{paramB}', [
			'paramB' => 'valueB',
		] );
		$this->assertEquals( 'controller/endpoint', $two_parameters_first_missing );

		$two_parameters_first_missing = $this->manager->command_to_endpoint( 'controller/endpoint', 'controller/endpoint/{paramA}/try-hard/{paramB}', [
			'paramB' => 'valueB',
		] );
		$this->assertEquals( 'controller/endpoint', $two_parameters_first_missing );

		$advance = $this->manager->command_to_endpoint( 'example/documents', 'example/documents/{document_id}/elements/{element_id}', [
			'document_id' => '1618',
		] );
		$this->assertEquals( 'example/documents/1618/elements', $advance );
	}

	public function test_run_server() {
		global $wp_rest_server;

		$this->manager->run_server();

		$this->assertEquals( true, !! $wp_rest_server );
	}

	public function test_kill_server() {
		global $wp_rest_server;

		$this->manager->kill_server();

		$this->assertEquals( false, !! $wp_rest_server );
	}

	public function test_run_processor() {
		$controller = $this->manager->register_controller( ControllerWithProcessor::class );
		$this->manager->run_server();

		$processor = array_values( $controller->processors )[ 0 ][ 0 ];

		$result = $this->manager->run_processor( $processor, [ 'args' => [], 'result' => [ 'test' => true ] ] );

		$this->assertCount( 2, $result );
		$this->assertEquals( true, $result['test'] );
		$this->assertEquals( true, $result['from_processor'] );
	}

	public function test_run_processors() {
		$controller = $this->manager->register_controller( ControllerWithProcessor::class );
		$this->manager->run_server();

		$processor = array_values( $controller->processors )[ 0 ][ 0 ];
		$processors = [ $processor ];

		$result = $this->manager->run_processors( $processors, Processor\After::class,  [ 'args' => [], 'result' => [ 'test' => true ] ] );

		$this->assertCount( 2, $result );
		$this->assertEquals( true, $result['test'] );
		$this->assertEquals( true, $result['from_processor'] );

		$result = $this->manager->run_processors( $processors, Processor\Before::class,  [ 'args' => [], 'result' => [ 'test' => true ] ] );

		$this->assertEquals( false, $result );
	}

	public function test_run_endpoint() {
		$controller = new ControllerSimple(); // controller with endpoint.
		$controller = $this->manager->register_controller_instance( $controller );

		$this->manager->run_server();
		$endpoint_instance = array_values( $controller->endpoints )[ 0 ];

		$endpoint = $this->manager->command_to_endpoint( $controller->get_name() . '/' . $endpoint_instance->get_name(), false, [] );

		$data = $this->manager->run_endpoint( $endpoint );
		$data_controller_name = str_replace( $data['namespace'] . '/', '', $data['controller'] );

		$this->assertEquals( $controller->get_name(), $data_controller_name );
	}

	public function test_run() {
		$controller = new ControllerSimple(); // controller with endpoint.
		$controller = $this->manager->register_controller_instance( $controller );

		$this->manager->run_server();
		$endpoint_instance = array_values( $controller->endpoints )[ 0 ];

		$data = $this->manager->run( $controller->get_name() . '/' . $endpoint_instance->get_name() );
		$data_controller_name = str_replace( $data['namespace'] . '/', '', $data['controller'] );

		$this->assertEquals( $controller->get_name(), $data_controller_name );
	}

	public function test_commands_formats() {
		$this->manager->run_server();

		$this->assertEquals( [
			'globals/index' => 'globals/index',
			'globals/colors' => 'globals/colors/{id}',
			'globals/typography' => 'globals/typography/{id}',
			'kits/index' => 'kits/index',
			'kits/download-link' => 'kits/download-link',
			'kit-taxonomies/index' => 'kit-taxonomies/index',
		], $this->manager->command_formats );
	}
}
