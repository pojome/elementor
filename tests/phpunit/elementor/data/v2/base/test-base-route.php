<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\V2\Base;

use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Controller as ControllerTemplate;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Endpoint as EndpointTemplate;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\WithEndpoint\Controller as ControllerWithEndpoint;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\WithSubEndpoint\Controller as ControllerWithSubEndpoint;

class Test_Base_Route extends Data_Test_Base {
	public function test_get_base_route() {
		// Arrange.
		$controller = new ControllerWithEndpoint();
		$this->manager->run_server();

		$endpoint = array_values( $controller->endpoints )[ 0 ];

		// Act.
		$actual = $endpoint->get_base_route();

		// Assert.
		$this->assertEquals( implode( '/', [
			'',
			$controller->get_name(),
			$endpoint->get_name(),
		] ),  $actual );
	}

	public function test_get_base_route__from_parent_index_endpoint() {
		// Arrange.
		$controller = new Mock\Template\Controller();

		// Trigger register.
		$this->manager->run_server();

		$index_endpoint = $controller->get_endpoint_index();

		$sub_endpoint = new Mock\Template\Endpoint( $index_endpoint );

		// Act.
		$actual = $sub_endpoint->get_base_route();

		// Assert.
		$this->assertEquals( implode( '/', [
			'',
			$controller->get_name(),
			$sub_endpoint->get_name(),
		] ),  $actual );
	}

	public function test_get_base_route__from_sub_endpoint() {
		// Arrange.
		$controller = new Mock\Template\Controller();
		$controller->bypass_original_register();

		$endpoint = new Mock\Template\Endpoint( $controller );
		$sub_endpoint = new Mock\Template\Endpoint(  $endpoint );

		// Act.
		$actual = $sub_endpoint->get_base_route();

		// Assert.
		$this->assertEquals( implode( '/', [
			'',
			$controller->get_name(),
			$endpoint->get_name(),
			$sub_endpoint->get_name(),
		] ), $actual );
	}

	public function test_get_base_route__from_grandchild_endpoint() {
		// Arrange.
		$controller = new Mock\Template\Controller();
		$controller->bypass_original_register();

		$endpoint = new Mock\Template\Endpoint( $controller );
		$sub_endpoint = new Mock\Template\Endpoint( $endpoint );
		$descendant_endpoint = new Mock\Template\Endpoint( $sub_endpoint );

		// Act.
		$actual = $descendant_endpoint->get_base_route();

		// Assert.
		$this->assertEquals( implode( '/', [
			'',
			$controller->get_name(),
			$endpoint->get_name(),
			$sub_endpoint->get_name(),
			$descendant_endpoint->get_name()
		] ), $actual );
	}

	// TODO: get_permission_callback

	public function test_get_items() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = new EndpointTemplate( $controller );
		$endpoint->set_test_data( 'get_items', 'valid' );

		// Act.
		$actual = $endpoint->get_items( null );

		// Assert.
		$this->assertEquals( 'valid', $actual );
	}

	public function test_get_items__simulated() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = $controller->do_register_endpoint( new EndpointTemplate( $controller ) );
		$endpoint->set_test_data( 'get_items', 'valid' );

		// Act.
		$actual = $this->manager->run_endpoint( trim( $endpoint->get_base_route(), '/' ) );

		// Assert.
		$this->assertEquals( 'valid', $actual );
	}

	public function test_get_item() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = new EndpointTemplate( $controller );
		$endpoint->set_test_data( 'get_item', 'valid' );

		// Act.
		$actual = $endpoint->get_item( null,null );

		// Assert.
		$this->assertEquals( 'valid', $actual );
	}

	public function test_get_item__simulated() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = $controller->do_register_endpoint( new EndpointTemplate( $controller ) );
		$endpoint->register_item_route( \WP_REST_Server::READABLE, [
			'id_arg_type_regex' => '[\w]+',
		] );

		$endpoint->set_test_data( 'get_item', 'valid' );
		$endpoint = trim( $endpoint->get_base_route(), '/' ) . '/fake_id';

		// Act.
		$actual = $this->manager->run_endpoint( $endpoint );

		// Assert.
		$this->assertEquals( 'valid', $actual );
	}

	public function test_create_items() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = new EndpointTemplate( $controller );
		$endpoint->set_test_data( 'create_items', 'valid' );

		// Act.
		$actual = $endpoint->create_items( null );

		// Assert.
		$this->assertEquals( 'valid', $actual );
	}

	public function test_create_items__simulated() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = $controller->do_register_endpoint( new EndpointTemplate( $controller ) );
		$endpoint->register_items_route( \WP_REST_Server::CREATABLE );

		$endpoint->set_test_data( 'create_items', 'valid' );
		$endpoint = trim( $endpoint->get_base_route(), '/' );

		// Act.
		$actual =  $this->manager->run_endpoint( $endpoint, [], 'POST' );

		// Assert.
		$this->assertEquals( 'valid', $actual );
	}

	public function test_create_item() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = new EndpointTemplate( $controller );
		$endpoint->set_test_data( 'create_item', 'valid' );

		// Act.
		$actual = $endpoint->create_item( null,null );

		// Assert.
		$this->assertEquals( 'valid', $actual );
	}

	public function test_create_item__simulated() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = $controller->do_register_endpoint( new EndpointTemplate( $controller ) );
		$endpoint->register_item_route(  \WP_REST_Server::CREATABLE, [
			'id_arg_type_regex' => '[\w]+',
		] );

		$endpoint->set_test_data( 'create_item', 'valid' );
		$endpoint = trim( $endpoint->get_base_route(), '/' ) . '/fake_id';

		// Actual.
		$actual = $this->manager->run_endpoint( $endpoint, [], \WP_REST_Server::CREATABLE );

		// Assert.
		$this->assertEquals( 'valid', $actual );
	}

	public function test_update_items() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = new EndpointTemplate( $controller );
		$endpoint->set_test_data( 'update_items', 'valid' );

		// Act.
		$actual = $endpoint->update_items( null );

		$this->assertEquals( 'valid', $actual );
	}

	public function test_update_items__simulated() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = $controller->do_register_endpoint( new EndpointTemplate( $controller ) );
		$endpoint->register_items_route( \WP_REST_Server::EDITABLE );

		$endpoint->set_test_data( 'update_items', 'valid' );
		$endpoint = trim( $endpoint->get_base_route(), '/' );

		// Act.
		$actual = $this->manager->run_endpoint( $endpoint, [], 'PUT' );

		// Assert.
		$this->assertEquals( 'valid', $actual );
	}

	public function test_update_item() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = new EndpointTemplate( $controller );
		$endpoint->set_test_data( 'update_item', 'valid' );

		// Actual.
		$actual = $endpoint->update_item( null,null );

		// Assert.
		$this->assertEquals( 'valid', $actual );
	}

	public function test_update_item__simulated() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = $controller->do_register_endpoint( new EndpointTemplate( $controller ) );
		$endpoint->register_item_route(  \WP_REST_Server::EDITABLE, [
			'id_arg_type_regex' => '[\w]+',
		] );

		$endpoint->set_test_data( 'update_item', 'valid' );
		$endpoint = trim( $endpoint->get_base_route(), '/' ) . '/fake_id';

		// Act.
		$actual = $this->manager->run_endpoint( $endpoint, [], 'PUT' );

		// Assert.
		$this->assertEquals( 'valid', $actual );
	}

	public function test_delete_items() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = new EndpointTemplate( $controller );
		$endpoint->set_test_data( 'delete_items', 'valid' );

		// Actual.
		$actual = $endpoint->delete_items( null );

		// Assert.
		$this->assertEquals( 'valid', $actual );
	}

	public function test_delete_items__simulated() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = $controller->do_register_endpoint( new EndpointTemplate( $controller ) );
		$endpoint->register_items_route( \WP_REST_Server::DELETABLE );

		$endpoint->set_test_data( 'delete_items', 'valid' );
		$endpoint = trim( $endpoint->get_base_route(), '/' );

		// Actual.
		$actual =  $this->manager->run_endpoint( $endpoint, [], 'DELETE' );

		// Assert.
		$this->assertEquals( 'valid', $actual );
	}

	public function test_delete_item() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = new EndpointTemplate( $controller );
		$endpoint->set_test_data( 'delete_item', 'valid' );

		// Actual.
		$actual = $endpoint->delete_item( null,null );

		// Assert.
		$this->assertEquals( 'valid', $actual );
	}

	public function test_delete_item__simulated() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = $controller->do_register_endpoint( new EndpointTemplate( $controller ) );
		$endpoint->register_item_route(  \WP_REST_Server::DELETABLE, [
			'id_arg_type_regex' => '[\w]+',
		] );

		$endpoint->set_test_data( 'delete_item', 'valid' );
		$endpoint = trim( $endpoint->get_base_route(), '/' ) . '/fake_id';

		// Actual.
		$actual = $this->manager->run_endpoint( $endpoint, [], 'DELETE' );

		// Assert.
		$this->assertEquals( 'valid', $actual );
	}

	public function test_register() {
		// Arrange.
		$test_controller = new ControllerWithEndpoint();

		// Act
		$this->manager->run_server();

		// Validate `$this->>register()`.
		$this->assertCount( 1, $test_controller->endpoints );
	}

	public function test_register__ensure_sub_endpoint() {
		// Arrange.
		$controller = new ControllerWithSubEndpoint();

		// Act
		$this->manager->run_server();

		// Assert - Validate `$this->>register()`.
		$this->assertCount( 1, $controller->get_test_endpoint()->get_sub_endpoints() );
	}

	public function test_register__simulated() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$endpoint = new EndpointTemplate( $controller );
		$endpoint->set_test_data( 'get_items', 'valid' );

		// Act.
		$data = $this->manager->run_endpoint( $controller->get_name() . '/' . $endpoint->get_name() );

		// Assert.
		$this->assertEquals( 'valid', $data );
	}

	public function test_register_route() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$controller->bypass_original_register();

		$endpoint = new EndpointTemplate( $controller );
		$endpoint->do_register_route( '/custom-route' );

		$except_route = '/' . $controller->get_controller_route() . '/' . $endpoint->get_name() . '/custom-route';

		// Act.
		$data = $controller->get_controller_index()->get_data();

		// Assert.
		$this->assertArrayHaveKeys( [ $except_route ], $data['routes'] );
	}

	public function test_register_items_route() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$controller->bypass_original_register();

		$endpoint = new EndpointTemplate( $controller );
		$endpoint->register_items_route();

		$except_route = '/' . $controller->get_controller_route() . '/' . $endpoint->get_name();

		// Act.
		$data = $controller->get_controller_index()->get_data();

		// Assert.
		$this->assertArrayHaveKeys( [ $except_route ], $data['routes'] );
	}

	public function test_register_item_route() {
		// Arrange.
		$this->manager->run_server();

		$controller = new ControllerTemplate();
		$controller->bypass_original_register();

		$endpoint = new EndpointTemplate( $controller );
		$endpoint->register_item_route();

		$except_route = '/' . $controller->get_controller_route() . '/' . $endpoint->get_name() . '/(?P<id>[\d]+)';

		// Act.
		$data = $controller->get_controller_index()->get_data();

		// Assert.
		$this->assertArrayHaveKeys( [ $except_route ], $data['routes'] );
	}

	public function test_base_callback() {
		// Arrange.
		$excepted_data = [ 'test' => true ];

		$controller = new ControllerTemplate();
		$controller->bypass_original_register();

		// Arrange - get_items.
		$endpoint = $controller->do_register_endpoint( new EndpointTemplate( $controller ) );
		$endpoint->set_test_data( 'get_items', $excepted_data );

		// Act - get_items.
		$result = $endpoint->base_callback( \WP_REST_Server::READABLE, new \WP_REST_Request(), true );

		// Assert - get_items.
		$this->assertEquals( $excepted_data, $result->get_data() );

		// Arrange - get_item.
		$request = new \WP_REST_Request( 'GET', [ 'id' => true ] );
		$endpoint->set_test_data( 'get_item', $excepted_data );

		// Act - get_item.
		$result = $endpoint->base_callback( \WP_REST_Server::READABLE, $request, false );

		// Assert - get_item.
		$this->assertEquals( $excepted_data, $result->get_data() );

		// Arrange - create_items.
		$endpoint->set_test_data( 'create_items', $excepted_data );

		// Act - create_items.
		$result = $endpoint->base_callback( \WP_REST_Server::CREATABLE, $request, true );

		// Assert - create_items.
		$this->assertEquals( $excepted_data, $result->get_data() );

		// Arrange - create item.
		$request = new \WP_REST_Request( 'CREATE', [ 'id' => true ] );
		$endpoint->set_test_data( 'create_item', $excepted_data );

		// Act - create_item.
		$result = $endpoint->base_callback( \WP_REST_Server::CREATABLE, $request, false );

		// Assert - create_items.
		$this->assertEquals( $excepted_data, $result->get_data() );

		// Arrange - update_items.
		$endpoint->set_test_data( 'update_items', $excepted_data );

		// Act - update_items.
		$result = $endpoint->base_callback( \WP_REST_Server::EDITABLE, new \WP_REST_Request(), true );

		// Assert - update_items.
		$this->assertEquals( $excepted_data, $result->get_data() );

		// Arrange - update_item.
		$request = new \WP_REST_Request( 'PUT', [ 'id' => true ] );
		$endpoint->set_test_data( 'update_item', $excepted_data );

		// Act - update_item.
		$result = $endpoint->base_callback( \WP_REST_Server::EDITABLE, $request, false );

		// Assert - update_item.
		$this->assertEquals( $excepted_data, $result->get_data() );

		// Arrange - delete_items.
		$endpoint->set_test_data( 'delete_items', $excepted_data );

		// Act - delete_items.
		$result = $endpoint->base_callback( \WP_REST_Server::DELETABLE, new \WP_REST_Request(), true );

		// Assert - delete_items.
		$this->assertEquals( $excepted_data, $result->get_data() );

		// Arrange - delete_item.
		$request = new \WP_REST_Request( 'DELETE', [ 'id' => true ] );
		$endpoint->set_test_data( 'delete_item', $excepted_data );

		// Act - delete_item.
		$result = $endpoint->base_callback( \WP_REST_Server::DELETABLE, $request, false );

		// Assert - delete_item.
		$this->assertEquals( $excepted_data, $result->get_data() );
	}
}
