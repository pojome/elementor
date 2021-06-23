<?php
namespace Elementor\Testing\Traits;

use Elementor\Data\Manager;
use Elementor\Testing\Elementor_Test_Base;

/**
 * @mixin Elementor_Test_Base
 */
Trait Rest_Trait {
	/**
	 * @var Manager
	 */
	protected $data_manager;

	public function setUp() {
		parent::setUp();

		$this->data_manager = Manager::instance();
		$this->data_manager->kill_server();
	}

	protected function http_get( $command, $args = [] ) {
		return $this->data_manager->run( $command, $args, 'GET' );
	}

	protected function http_post( $command, $args = [] ) {
		return $this->data_manager->run( $command, $args, 'POST' );
	}

	protected function http_put( $command, $args = [] ) {
		return $this->data_manager->run( $command, $args, 'PUT' );
	}

	protected function http_delete( $command, $args = [] ) {
		return $this->data_manager->run( $command, $args, 'DELETE' );
	}
}
