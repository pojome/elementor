<?php
namespace Elementor\Tests\Phpunit\Elementor\Core\Breakpoints;

use Elementor\Core\Breakpoints\Breakpoint;
use Elementor\Core\Breakpoints\Manager as Breakpoints_Manager;
use Elementor\Plugin;
use Elementor\Testing\Elementor_Test_Base;
use Elementor\Testing\Traits\Breakpoints_Trait;

class Test_Manager extends Elementor_Test_Base {

	use Breakpoints_Trait;

	/**
	 * Test Get Name
	 *
	 * Test the Breakpoints_Manager::get_name() method.
	 *
	 * @since 3.2.0
	 */
	public function test_get_name() {
		$this->assertEquals( 'breakpoints', Plugin::$instance->breakpoints->get_name() );
	}

	/**
	 * Test Get Breakpoints
	 *
	 * Test the Breakpoints_Manager::get_breakpoints() method.
	 *
	 * @since 3.2.0
	 */
	public function test_get_breakpoints() {
		$breakpoints = Plugin::$instance->breakpoints->get_breakpoints();

		// Check that the breakpoints array contains exactly the same amount of keys as in the default config.
		$this->assertEmpty( array_diff_key( Breakpoints_Manager::get_default_config(), $breakpoints ) );
	}

	/**
	 * Test Get Active Breakpoints
	 *
	 * Test the Breakpoints_Manager::get_breakpoints() method.
	 *
	 * @since 3.2.0
	 */
	public function test_get_active_breakpoints() {
		$breakpoints = Plugin::$instance->breakpoints->get_breakpoints();
		$active_breakpoints = Plugin::$instance->breakpoints->get_active_breakpoints();

		$test_array = [];

		foreach ( $breakpoints as $breakpoint_name => $breakpoint_instance ) {
			/** @var Breakpoint $breakpoint_instance */
			if ( $breakpoint_instance->is_enabled() ) {
				$test_array[ $breakpoint_name ] = $breakpoint_instance;
			}
		}

		$this->assertEquals( $active_breakpoints, $test_array );
	}

	/**
	 * Test Has Custom Breakpoints
	 *
	 * Test the Breakpoints_Manager::has_custom_breakpoints() method.
	 *
	 * @since 3.2.0
	 */
	public function test_has_custom_breakpoints() {
		// If this has already been called once with a value, we call it again to set it to its initial empty value.
		$this->set_custom_breakpoint_and_refresh_kit_and_breakpoints( '' );

		// We know the breakpoint settings are empty and have no custom values. Assert it.
		$this->assertFalse( Plugin::$instance->breakpoints->has_custom_breakpoints() );

		$this->set_custom_breakpoint_and_refresh_kit_and_breakpoints( 900 );

		$this->assertTrue( Plugin::$instance->breakpoints->has_custom_breakpoints() );

		// Revert breakpoint change to not affect other tests.
		$this->set_custom_breakpoint_and_refresh_kit_and_breakpoints( '' );
	}

	/**
	 * Test Get Device Min Breakpoint
	 *
	 * Test the Breakpoints_Manager::get_device_min_breakpoint() method.
	 * This test only tests the default breakpoints (mobile, tablet).
	 *
	 * @since 3.2.0
	 */
	public function test_get_device_min_breakpoint() {
		$active_breakpoints = Plugin::$instance->breakpoints->get_active_breakpoints();

		$this->assertEquals( $active_breakpoints[ Breakpoints_Manager::BREAKPOINT_KEY_MOBILE ]->get_value() + 1, Plugin::$instance->breakpoints->get_device_min_breakpoint( Breakpoints_Manager::BREAKPOINT_KEY_TABLET ) );
	}

	/**
	 * Test Get Device Min Breakpoint - Mobile
	 *
	 * Test the Breakpoints_Manager::get_device_min_breakpoint() method for the mobile device case, which is a special
	 * case.
	 *
	 * @since 3.2.0
	 */
	public function test_get_device_min_breakpoint_mobile() {
		$active_breakpoints = Plugin::$instance->breakpoints->get_active_breakpoints();

		// Test for mobile specifically, which always has a min point of 320.
		$this->assertEquals( 320, Plugin::$instance->breakpoints->get_device_min_breakpoint( Breakpoints_Manager::BREAKPOINT_KEY_MOBILE ) );
	}

	/**
	 * Test Get Desktop Min Breakpoint
	 *
	 * Test the Breakpoints_Manager::get_desktop_min_point() method with default active breakpoints.
	 *
	 * @since 3.2.0
	 */
	public function test_get_desktop_min_point() {
		$tablet_breakpoint = Plugin::$instance->breakpoints->get_breakpoints( 'tablet' );

		$this->assertEquals( $tablet_breakpoint->get_value() + 1, Plugin::$instance->breakpoints->get_desktop_min_point() );
	}
}
