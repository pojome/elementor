<?php

namespace Elementor\Tests\Phpunit\Elementor\Core\Editor\Data\Globals;

use Elementor\Core\Editor\Data\Globals;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Data_Test_Base;

class Test_Controller extends Data_Test_Base  {
	public function test_get_permission_callback() {
		$controller = new Globals\Controller();
		$methods = explode( ', ', \WP_REST_Server::ALLMETHODS );

		// Set Editor.
		wp_set_current_user( $this->factory()->get_editor_user()->ID );

		foreach ( $methods as $method ) {
			$request = new \WP_REST_Request( $method );
			$this->assertEquals( true, $controller->get_permission_callback( $request ) );
		}

		// Set subscriber.
		wp_set_current_user( $this->factory()->get_subscriber_user()->ID );

		foreach ( $methods as $method ) {
			$request = new \WP_REST_Request( $method );

			$this->assertEquals( false, $controller->get_permission_callback( $request ) );
		}
	}
}
