<?php
namespace Elementor\Tests\Phpunit\Elementor\Core\Kits\Documents;

use Elementor\Core\Breakpoints\Breakpoint;
use Elementor\Modules\History\Revisions_Manager;
use Elementor\Plugin;
use Elementor\Testing\Elementor_Test_Base;
use Elementor\Core\Breakpoints\Manager as Breakpoints_Manager;

class Test_Kit extends Elementor_Test_Base {

	/**
	 * @var \Elementor\Core\Kits\Documents\Kit
	 */
	private $kit;

	public function setUp() {
		parent::setUp();

		wp_set_current_user( $this->factory()->get_administrator_user()->ID );

		$kit = Plugin::$instance->kits_manager->get_active_kit();
		$this->kit = Plugin::$instance->documents->get( $kit->get_id(), false );

		// In the production environment 'JS' sends empty array, do the same.
		add_post_meta( $kit->get_main_id(), '_elementor_data', '[]' );
	}

	public function test_save__kits_revision_ensure_changed() {
		// Arrange.
		$this->kit->set_settings( 'post_status', 'draft');

		$excepted_count = count( Revisions_Manager::get_revisions( $this->kit->get_main_id() ) );

		// Ensure new revision added.
		$excepted_count++;

		// Act.
		$this->kit->save( [ 'settings' => $this->kit->get_settings() ] );

		// Assert.
		$this->assertCount( $excepted_count, Revisions_Manager::get_revisions( $this->kit->get_main_id() ) );
	}

	public function test_save__kits_revision_ensure_same() {
		// Arrange.
		$this->kit->save( [ 'settings' => $this->kit->get_settings() ] );

		$excepted_count = count( Revisions_Manager::get_revisions( $this->kit->get_main_id() ) );

		// Act.
		$this->kit->save( [ 'settings' => $this->kit->get_settings() ] );

		// Assert.
		$this->assertCount( $excepted_count, Revisions_Manager::get_revisions( $this->kit->get_main_id() ) );
	}

	public function test_settings_layout_before_save() {
		$prefix = Breakpoints_Manager::BREAKPOINT_SETTING_PREFIX;
		$kit_id = $this->kit->get_id();
		$settings = $this->kit->get_settings();

		// Set custom values for the mobile and tablet settings.
		$settings['viewport_mobile'] = 599;
		$settings['viewport_tablet'] = 799;

		// Save the kit to trigger `before_save()`.
		$this->kit->save( [ 'settings' => $settings ] );
		// Refresh the kit and kit settings variables.
		$this->kit = Plugin::$instance->documents->get( $kit_id, false );
		$settings = $this->kit->get_settings();

		// Check that the legacy mobile and tablet values are equal to the newer mobile and tablet settings + 1px.
		$this->assertEquals( $settings['viewport_md'], $settings[ $prefix . Breakpoints_Manager::BREAKPOINT_KEY_MOBILE ] + 1 );
		$this->assertEquals( $settings['viewport_lg'], $settings[ $prefix . Breakpoints_Manager::BREAKPOINT_KEY_TABLET ] + 1 );
	}
}
