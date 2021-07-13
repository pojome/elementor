<?php
namespace Elementor\Testing\Traits;

use Elementor\Plugin;

Trait Breakpoints_Trait {

	function set_admin_user() {
		if ( ! current_user_can( 'administrator' ) ) {
			wp_set_current_user( $this->factory()->get_administrator_user()->ID );
		}

		$kit = Plugin::$instance->kits_manager->get_active_kit();

		// In the production environment 'JS' sends empty array, do the same.
		add_post_meta( $kit->get_main_id(), '_elementor_data', '[]' );
	}

	function set_custom_breakpoint_and_refresh_kit_and_breakpoints( $value ) {
		$this->set_admin_user();

		$kit = Plugin::$instance->kits_manager->get_active_kit();

		// Set a custom value for the tablet breakpoint.
		$kit->set_settings( 'viewport_tablet', $value );

		// Save kit settings.
		$kit->save( [ 'settings' => $kit->get_settings() ] );

		// Refresh kit.
		$kit = Plugin::$instance->documents->get( $kit->get_id(), false );

		Plugin::$instance->breakpoints->refresh();
	}

	function reset_breakpoints_to_default() {
		$kit = Plugin::$instance->kits_manager->get_active_kit();

		$kit->set_settings( 'active_breakpoints', [ 'viewport_mobile', 'viewport_tablet' ] );
		$kit->set_settings( 'viewport_mobile', '' );
		$kit->set_settings( 'viewport_tablet', '' );

		// Save kit settings.
		$kit->save( [ 'settings' => $kit->get_settings() ] );

		// Refresh kit.
		$kit = Plugin::$instance->documents->get( $kit->get_id(), false );

		Plugin::$instance->breakpoints->refresh();
	}
}
