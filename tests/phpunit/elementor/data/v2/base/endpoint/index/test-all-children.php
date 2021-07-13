<?php
namespace Elementor\Tests\Phpunit\Data\V2\Base\Endpoint\Index;

use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Data_Test_Base;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\AllChildren\Controller as ControllerAllChildren;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Endpoint\Bypass_Permission as EndpointBypassPermission;

class Test_All_Children extends Data_Test_Base {

	public function test_get_items() {
		// Arrange.
		$controller = new ControllerAllChildren();

		$this->manager->run_server();

		$count = 0;

		foreach ( $controller->endpoints as $endpoint ) {
			$endpoint->set_test_data( 'get_items', "endpoint{$count}_result" );
			++$count;
		}

		// Act - Result should include both endpoints result.
		$results = $controller->get_endpoint_index()->get_items( null );

		$count = 0;

		// Assert.
		foreach ( $results as $result ) {
			$this->assertEquals( 'endpoint' . $count . '_result', $result );
			$count++;
		}
	}

	public function test_get_items__simulated() {
		// Arrange.
		$controller = $this->manager->register_controller( new ControllerAllChildren );
		$this->manager->run_server(); // Ensure controller loaded.

		// Act - Run index endpoint.
		$endpoints_results = $this->manager->run_endpoint( $controller->get_name() );

		// Assert.
		foreach ( $endpoints_results as $endpoint_name => $endpoints_result ) {
			// Run endpoint like `test-controller/test-endpoint-{random}`.
			$endpoint = $controller->get_name() . '/' . $endpoint_name;
			$result = $this->manager->run_endpoint( $endpoint );

			// Each manual run of the endpoint equals to part of $endpoints_results.
			$this->assertEquals( $endpoints_result, $result );
		}
	}

	public function test_get_items__one_endpoint_have_no_permission() {
		// Arrange.
		$controller = new ControllerAllChildren();

		$this->manager->run_server();

		$endpoint_bypass_permission = new EndpointBypassPermission( $controller );
		$endpoint_bypass_permission->bypass_original_permission( true );
		$endpoint_bypass_permission->bypass_set_value( false );
		$controller->do_register_endpoint( $endpoint_bypass_permission );

		// Act - Run index endpoint.
		$endpoints_results = $this->manager->run_endpoint( $controller->get_name() );

		// Assert.
		$this->assertCount( 2, $endpoints_results );
	}
}
