<?php
namespace Elementor\Testing\Core\Base;

use Elementor\Testing\Core\Base\Mock\Mock_Upgrades_Manager;
use Elementor\Testing\Elementor_Test_Base;

require_once 'mock/mock-upgrades-manager.php';

class Test_DB_Upgrades_Manager extends Elementor_Test_Base {
	public function test_get_upgrade_callbacks__ensure_callback_on_each_version() {
		// Arrange.
		$upgrade_manager = new Mock_Upgrades_Manager();

		// Act.
		$callbacks = $upgrade_manager->get_upgrade_callbacks();

		// Assert.
		$this->assertCount( 1, $callbacks );
		$this->assertEquals( '_on_each_version', reset( $callbacks )[1] );
	}
}
