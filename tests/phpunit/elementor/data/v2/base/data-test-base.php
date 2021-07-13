<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\V2\Base;

use Elementor\Data\V2\Manager;
use Elementor\Testing\Elementor_Test_Base;

abstract class Data_Test_Base extends Elementor_Test_Base {
	/**
	 * @var \Elementor\Data\V2\Manager
	 */
	protected $manager;

	public function setUp() {
		parent::setUp();

		$this->set_manager();
	}

	public function tearDown() {
		parent::tearDown();

		$this->manager->kill_server();
	}

	public function set_manager( $manager = null ) {
		if ( ! $manager ) {
			$manager = Manager::instance();
		}

		$this->manager = $manager;
	}

	public function get_manager() {
		if ( ! $this->manager ) {
			$this->manager = Manager::instance();
		}

		return $this->manager;
	}
}
