<?php
namespace Elementor\Testing;

use Elementor\Core\Kits\Manager;
use Elementor\Core\Wp_Api;
use Elementor\Plugin;
use Elementor\Testing\Traits\Auth_Helpers;
use Elementor\Testing\Traits\Base_Elementor;
use Elementor\Testing\Traits\Extra_Assertions;

class Elementor_Test_Base extends \WP_UnitTestCase {
	use Base_Elementor, Extra_Assertions, Auth_Helpers;

	public function setUp() {
		parent::setUp();

		$this->create_default_kit();

		set_current_screen( 'dashboard' );
	}

	public function tearDown() {
		parent::tearDown();

		Plugin::$instance->editor->set_edit_mode( false );
		Plugin::$instance->documents->restore_document();
		Plugin::$instance->editor->set_edit_mode( false );
		Plugin::$instance->wp = new Wp_Api();
	}

	protected function create_default_kit() {
		return Manager::create_default_kit();
	}

	protected function remove_default_kit() {
		// Make sure the the 'wp_delete_post' function will actually delete the kit.
		$_GET['force_delete_kit'] = '1';

		$active_kit_id = Plugin::$instance->kits_manager->get_active_id();

		wp_delete_post( $active_kit_id, true );
		delete_option( Manager::OPTION_ACTIVE );

		unset( $_GET['force_delete_kit'] );
	}
}
