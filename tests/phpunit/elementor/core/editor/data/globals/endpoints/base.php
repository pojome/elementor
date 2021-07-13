<?php

namespace Elementor\Tests\Phpunit\Elementor\Core\Editor\Data\Globals\Endpoints;

use Elementor\Core\Editor\Data;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Data_Test_Base;

abstract class Base extends Data_Test_Base {
	public function setUp() {
		parent::setUp();

		$this->manager->kill_server();

		$controller_class = $this->get_controller_class();

		$this->manager->register_controller( new $controller_class );

		wp_set_current_user( $this->factory()->get_administrator_user()->ID);
	}

	public function tearDown() {
		parent::tearDown();

		$this->manager->kill_server();
	}

	public function get_endpoint( $id = null ) {
		$endpoint = $this->get_command();

		if ( $id ) {
			$endpoint .= '/' . $id;
		}

		return $endpoint;
	}

	/**
	 * @return string
	 */
	abstract public function get_command();

	/**
	 * @return string
	 */
	abstract public function get_controller_class();
}
