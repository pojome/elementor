<?php

namespace Elementor\Tests\Phpunit\Elementor\Core\Editor\Data\Globals\Endpoints;

use Elementor\Plugin;
use Elementor\Core\Editor\Data;

class Test_Typography extends Base {
	public function get_command() {
		return 'globals/typography';
	}

	public function get_controller_class() {
		return Data\Globals\Controller::class;
	}

	public function test_create() {
		$id = (string) rand();
		$args = [
			'id' => $id,
			'title' => 'whatever',
			'value' => [
				'whatever' => true,
			],
		];

		// Create
		$result = $this->manager->run_endpoint( $this->get_endpoint( $id ), $args, \WP_REST_Server::CREATABLE );

		// Bug: kit does not updated after save.
		$kit = Plugin::$instance->documents->get( Plugin::$instance->kits_manager->get_active_id(), false );

		$typography = $kit->get_settings( 'custom_typography' );

		$this->assertEquals( $id, $typography[0]['_id'] );
		$this->assert_array_have_keys( [ 'whatever' ], $typography[0] );

		return $typography;
	}

	public function test_get() {
		$typography = $this->test_create();

		$rest_result = $this->manager->run_endpoint( $this->get_endpoint( $typography[0]['_id'] ) );

		$this->assertEquals( $rest_result['id'], $typography[0]['_id'] );
	}

	public function test_get_item_that_does_not_exists() {
		$rest_result = $this->manager->run_endpoint( $this->get_endpoint( 'fake_id' ) );

		$this->assertEquals( 'global_not_found', $rest_result['code'] );
		$this->assertEquals( 404, $rest_result['data']['status'] );
	}
}
