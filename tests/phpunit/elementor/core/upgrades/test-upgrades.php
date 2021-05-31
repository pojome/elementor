<?php
namespace Elementor\Tests\Phpunit\Elementor\Core\Upgrades;

use Elementor\Core\Base\Document;
use Elementor\Core\Experiments\Manager as Experiments_Manager;
use Elementor\Core\Settings\Manager as Settings_Manager;
use Elementor\Core\Upgrade\Upgrades;
use Elementor\Modules\Usage\Module;
use Elementor\Plugin;
use Elementor\Testing\Elementor_Test_Base;
use Elementor\Tests\Phpunit\Test_Upgrades_Trait;

class Test_Upgrades extends Elementor_Test_Base {

	use Test_Upgrades_Trait;

	/**
	 * @param string $old_usage_option_name
	 * @param string $old_usage_meta_key
	 * @param callable $remove_old_usage_callback
	 */
	public function ensure_removed_old_usage_data( $old_usage_option_name, $old_usage_meta_key, $remove_old_usage_callback ) {
		add_option( $old_usage_option_name, 'test' );

		$document = $this->create_post();
		$document->update_main_meta( $old_usage_meta_key, 'test' );

		$this->assertEquals( 'test', get_option( $old_usage_option_name ) );
		$this->assertEquals( 'test', $document->get_main_meta( $old_usage_meta_key ) );

		// Run upgrade.
		$remove_old_usage_callback();

		$this->assertNotEquals( 'test', get_option( $old_usage_option_name ) );
		$this->assertNotEquals( 'test', $document->get_main_meta( $old_usage_meta_key ) );
	}

	public function test_v_2_7_0_rename_document_types_to_wp() {
		$this->markTestSkipped();
		// Create a post with post types (post, page).
		$document_post = $this->create_post( 'post' );
		$document_page = $this->create_post( 'page' );

		// Assert meta key elementor_template_type = (wp-post, wp-page).
		$post_meta = $document_post->get_meta( Document::TYPE_META_KEY );
		$page_meta = $document_page->get_meta( Document::TYPE_META_KEY );

		$this->assertEquals( 'wp-post', $post_meta );
		$this->assertEquals( 'wp-page', $page_meta );

		// Update meta without wp prefix.
		$document_post->update_meta( Document::TYPE_META_KEY, 'post' );
		$document_page->update_meta( Document::TYPE_META_KEY, 'page' );

		// Ensure the database has been updated.
		$post_meta = $document_post->get_meta( Document::TYPE_META_KEY );
		$page_meta = $document_page->get_meta( Document::TYPE_META_KEY );

		$this->assertEquals( 'post', $post_meta );
		$this->assertEquals( 'page', $page_meta );

		wp_cache_flush();

		// Run upgrade.
		$updater = $this->create_updater();
		Upgrades::_v_2_7_0_rename_document_types_to_wp( $updater );

		// Assert again (wp_post, wp_page).
		$post_meta = $document_post->get_meta( Document::TYPE_META_KEY );
		$page_meta = $document_page->get_meta( Document::TYPE_META_KEY );

		$this->assertEquals( 'wp-post', $post_meta );
		$this->assertEquals( 'wp-page', $page_meta );
	}

	public function test_v_2_7_1_remove_old_usage_data() {
		$this->ensure_removed_old_usage_data(
			'elementor_elements_usage',
			'_elementor_elements_usage',
			function () {
				Upgrades::_v_2_7_1_remove_old_usage_data();
			}
		);
	}

	public function test_v_2_7_1_recalc_usage_data() {
		$posts_count = 10;
		$expected_iterations = (int) ceil( $posts_count / $this->query_limit );
		$upgrade_iterations = 1;

		// Create posts.
		for ( $i = 0; $i < $posts_count; $i++ ) {
			$this->create_document_with_data();
		}

		$updater = $this->create_updater();
		$updater->set_limit( $this->query_limit );

		// Run upgrade.
		while ( Upgrades::_v_2_7_1_recalc_usage_data( $updater ) ) {
			$upgrade_iterations++;

			$updater->set_current_item( [
				'iterate_num' => $upgrade_iterations,
			] );

			// Avoid infinity loop.
			if ( $upgrade_iterations > $posts_count ) {
				break;
			}
		}

		$this->assertEquals( $expected_iterations, $upgrade_iterations );

		/** @var Module $module */
		$module = Plugin::$instance->modules_manager->get_modules( 'usage' );
		$usage = get_option( $module::ELEMENTS_OPTION_NAME, [] );

		// Check there usage.
		$this->assertEquals( $posts_count, $usage['wp-post']['button']['count'] );
	}

	public function test_v_3_0_0_move_general_settings_to_kit() {
		$updater = $this->create_updater();

		// Prepare.
		$generic_font = 'some-generic-font';
		$lightbox_color = '#e1e3ef';
		$container_width = '1000';
		$space_between_widgets = '0'; // Ensure that value 0 is also upgraded (#12298).
		$viewport_lg = '900';
		$viewport_md = '800';

		$general_settings = [
			'default_generic_fonts' => $generic_font,
			'lightbox_color' => $lightbox_color,
			'container_width' => $container_width,
		];

		update_option( '_elementor_general_settings', $general_settings );

		// Take the `space_between_widgets` from the option due to a bug on E < 3.0.0 that the value `0` is stored separated.
		update_option( 'elementor_space_between_widgets', $space_between_widgets );
		update_option( 'elementor_viewport_lg', $viewport_lg );
		update_option( 'elementor_viewport_md', $viewport_md );

		$user_id = $this->factory()->create_and_get_administrator_user()->ID;
		wp_set_current_user( $user_id );

		$kit_id = Plugin::$instance->kits_manager->get_active_id();
		$kit = Plugin::$instance->documents->get( $kit_id );

		// Create revisions.
		$expected_iterations = (int) ceil( $this->revisions_count / $this->query_limit );
		$upgrade_iterations = 1;

		for ( $i = 0; $i < $this->revisions_count; $i++ ) {
			$kit->save( [
				'elements' => [],
			] );
		}

		// Ensure the testable values are not default values of the kit.
		$kit_generic_font_before = $kit->get_settings( 'default_generic_fonts' );
		$kit_lightbox_color_before = $kit->get_settings( 'lightbox_color' );
		$kit_container_width_before = $kit->get_settings( 'container_width' );
		$kit_space_between_widgets_before = $kit->get_settings( 'space_between_widgets' );
		$kit_viewport_md_before = $kit->get_settings( 'viewport_md' );

		$this->assertNotEquals( $generic_font, $kit_generic_font_before );
		$this->assertNotEquals( $lightbox_color, $kit_lightbox_color_before );
		$this->assertNotEquals( $container_width, $kit_container_width_before );
		$this->assertNotEquals( $space_between_widgets, $kit_space_between_widgets_before );
		$this->assertNotEquals( $viewport_md, $kit_viewport_md_before );

		$updater->set_limit( $this->query_limit );

		// Run upgrade.
		while ( Upgrades::_v_3_0_0_move_general_settings_to_kit( $updater ) ) {
			$upgrade_iterations++;

			$updater->set_current_item( [
				'iterate_num' => $upgrade_iterations,
			] );

			// Avoid infinity loop.
			if ( $upgrade_iterations > $this->revisions_count ) {
				break;
			}
		}

		// Assert iterations.
		$this->assertEquals( $expected_iterations, $upgrade_iterations );

		// Refresh kit.
		$kit = Plugin::$instance->documents->get( $kit_id, false );

		// Assert kit upgraded.
		$kit_generic_font_after = $kit->get_settings( 'default_generic_fonts' );
		$kit_lightbox_color_after = $kit->get_settings( 'lightbox_color' );
		$kit_container_width_after = $kit->get_settings( 'container_width' );
		$kit_space_between_widgets_after = $kit->get_settings( 'space_between_widgets' );
		$kit_viewport_md_after = $kit->get_settings( 'viewport_md' );

		$this->assertEquals( $generic_font, $kit_generic_font_after );
		$this->assertEquals( $lightbox_color, $kit_lightbox_color_after );
		$this->assertEquals( $container_width, $kit_container_width_after['size'] );
		$this->assertEquals( $space_between_widgets, $kit_space_between_widgets_after['size'] );
		$this->assertEquals( $viewport_md, $kit_viewport_md_after );

		// Assert revisions upgraded.

		$revisions_ids = wp_get_post_revisions( $kit_id, [
			'fields' => 'ids',
		] );

		foreach ( $revisions_ids as $revision_id ) {
			$revision = Plugin::$instance->documents->get( $revision_id, false );

			$revision_generic_font_after = $revision->get_settings( 'default_generic_fonts' );
			$revision_lightbox_color_after = $revision->get_settings( 'lightbox_color' );

			$this->assertEquals( $generic_font, $revision_generic_font_after );
			$this->assertEquals( $lightbox_color, $revision_lightbox_color_after );
		}
	}

	public function test_v_3_0_0_move_saved_colors_to_kit() {
		$updater = $this->create_updater();

		// Prepare.
		$scheme_obj = Plugin::$instance->schemes_manager->get_scheme( 'color-picker' );

		$user_id = $this->factory()->create_and_get_administrator_user()->ID;
		wp_set_current_user( $user_id );

		$kit_id = Plugin::$instance->kits_manager->get_active_id();
		$kit = Plugin::$instance->documents->get( $kit_id );

		// Create revisions.
		$expected_iterations = (int) ceil( $this->revisions_count / $this->query_limit );
		$upgrade_iterations = 1;

		for ( $i = 0; $i < $this->revisions_count; $i++ ) {
			$kit->save( [
				'elements' => [],
			] );
		}

		$updater->set_limit( $this->query_limit );

		// Run upgrade.
		while ( Upgrades::_v_3_0_0_move_saved_colors_to_kit( $updater ) ) {
			$upgrade_iterations++;

			$updater->set_current_item( [
				'iterate_num' => $upgrade_iterations,
			] );

			// Avoid infinity loop.
			if ( $upgrade_iterations > $this->revisions_count ) {
				break;
			}
		}

		// Assert iterations.
		$this->assertEquals( $expected_iterations, $upgrade_iterations );

		// Refresh kit.
		$kit = Plugin::$instance->documents->get( $kit_id, false );

		// Assert kit upgraded.
		$saved_colors = $scheme_obj->get_scheme();
		$kit_custom_colors = $kit->get_settings( 'custom_colors' );

		// First 4 saved colors are actually the 4 system colors that shouldn't be saved as custom colors.
		$this->assertEquals( strtoupper( $saved_colors[5]['value'] ), $kit_custom_colors[0]['color'] );
		$this->assertEquals( strtoupper( $saved_colors[6]['value'] ), $kit_custom_colors[1]['color'] );
		$this->assertEquals( strtoupper( $saved_colors[7]['value'] ), $kit_custom_colors[2]['color'] );
		$this->assertEquals( strtoupper( $saved_colors[8]['value'] ), $kit_custom_colors[3]['color'] );

		// Assert revisions upgraded.
		$revisions_ids = wp_get_post_revisions( $kit_id, [
			'fields' => 'ids',
		] );

		foreach ( $revisions_ids as $revision_id ) {
			$revision = Plugin::$instance->documents->get( $revision_id, false );
			$revision_system_colors = $revision->get_settings( 'custom_colors' );

			// First 4 saved colors are actually the 4 system colors that shouldn't be saved as custom colors.
			$this->assertEquals( strtoupper( $saved_colors[5]['value'] ), $revision_system_colors[0]['color'] );
			$this->assertEquals( strtoupper( $saved_colors[6]['value'] ), $revision_system_colors[1]['color'] );
			$this->assertEquals( strtoupper( $saved_colors[7]['value'] ), $revision_system_colors[2]['color'] );
			$this->assertEquals( strtoupper( $saved_colors[8]['value'] ), $revision_system_colors[3]['color'] );
		}
	}

	public function test_v_3_0_0_move_default_colors_to_kit() {
		$updater = $this->create_updater();

		// Prepare.
		$scheme_obj = Plugin::$instance->schemes_manager->get_scheme( 'color' );

		$user_id = $this->factory()->create_and_get_administrator_user()->ID;
		wp_set_current_user( $user_id );

		$kit_id = Plugin::$instance->kits_manager->get_active_id();
		$kit = Plugin::$instance->documents->get( $kit_id );

		// Create revisions.
		$expected_iterations = (int) ceil( $this->revisions_count / $this->query_limit );
		$upgrade_iterations = 1;

		for ( $i = 0; $i < $this->revisions_count; $i++ ) {
			$kit->save( [
				'elements' => [],
			] );
		}

		$updater->set_limit( $this->query_limit );

		// Run upgrade.
		while ( Upgrades::_v_3_0_0_move_default_colors_to_kit( $updater ) ) {
			$upgrade_iterations++;

			$updater->set_current_item( [
				'iterate_num' => $upgrade_iterations,
			] );

			// Avoid infinity loop.
			if ( $upgrade_iterations > $this->revisions_count ) {
				break;
			}
		}

		// Assert iterations.
		$this->assertEquals( $expected_iterations, $upgrade_iterations );

		// Refresh kit.
		$kit = Plugin::$instance->documents->get( $kit_id, false );

		// Assert kit upgraded.
		$default_colors = $scheme_obj->get_scheme();
		$kit_system_colors = $kit->get_settings( 'system_colors' );

		$this->assertEquals( 'primary', $kit_system_colors[0]['_id'] );
		$this->assertEquals( strtoupper( $default_colors[1]['value'] ), $kit_system_colors[0]['color'] );
		$this->assertEquals( strtoupper( $default_colors[2]['value'] ), $kit_system_colors[1]['color'] );
		$this->assertEquals( strtoupper( $default_colors[3]['value'] ), $kit_system_colors[2]['color'] );
		$this->assertEquals( strtoupper( $default_colors[4]['value'] ), $kit_system_colors[3]['color'] );

		// Assert revisions upgraded.
		$revisions_ids = wp_get_post_revisions( $kit_id, [
			'fields' => 'ids',
		] );

		foreach ( $revisions_ids as $revision_id ) {
			$revision = Plugin::$instance->documents->get( $revision_id, false );
			$revision_system_colors = $revision->get_settings( 'system_colors' );

			$this->assertEquals( strtoupper( $default_colors[1]['value'] ), $revision_system_colors[0]['color'] );
			$this->assertEquals( strtoupper( $default_colors[2]['value'] ), $revision_system_colors[1]['color'] );
			$this->assertEquals( strtoupper( $default_colors[3]['value'] ), $revision_system_colors[2]['color'] );
			$this->assertEquals( strtoupper( $default_colors[4]['value'] ), $revision_system_colors[3]['color'] );
		}
	}

	public function test_v_3_0_0_move_default_typography_to_kit() {
		$updater = $this->create_updater();

		// Prepare.
		$scheme_obj = Plugin::$instance->schemes_manager->get_scheme( 'typography' );

		$user_id = $this->factory()->create_and_get_administrator_user()->ID;
		wp_set_current_user( $user_id );

		$kit_id = Plugin::$instance->kits_manager->get_active_id();
		$kit = Plugin::$instance->documents->get( $kit_id, false );

		// Create revisions.
		$expected_iterations = (int) ceil( $this->revisions_count / $this->query_limit );
		$upgrade_iterations = 1;

		for ( $i = 0; $i < $this->revisions_count; $i++ ) {
			$kit->save( [
				'elements' => [],
			] );
		}

		$updater->set_limit( $this->query_limit );

		// Run upgrade.
		while ( Upgrades::_v_3_0_0_move_default_typography_to_kit( $updater ) ) {
			$upgrade_iterations++;

			$updater->set_current_item( [
				'iterate_num' => $upgrade_iterations,
			] );

			// Avoid infinity loop.
			if ( $upgrade_iterations > $this->revisions_count ) {
				break;
			}
		}

		// Assert iterations.
		$this->assertEquals( $expected_iterations, $upgrade_iterations );

		// Refresh kit.
		$kit = Plugin::$instance->documents->get( $kit_id, false );

		// Assert kit upgraded.
		$saved_typography = $scheme_obj->get_scheme();
		$kit_system_typography = $kit->get_settings( 'system_typography' );

		$this->assertEquals( 'primary', $kit_system_typography[0]['_id'] );
		$this->assertEquals( $saved_typography[1]['value']['font_family'], $kit_system_typography[0]['typography_font_family'] );
		$this->assertEquals( $saved_typography[2]['value']['font_family'], $kit_system_typography[1]['typography_font_family'] );
		$this->assertEquals( $saved_typography[3]['value']['font_family'], $kit_system_typography[2]['typography_font_family'] );
		$this->assertEquals( $saved_typography[4]['value']['font_family'], $kit_system_typography[3]['typography_font_family'] );

		// Assert revisions upgraded.
		$revisions_ids = wp_get_post_revisions( $kit_id, [
			'fields' => 'ids',
		] );

		foreach ( $revisions_ids as $revision_id ) {
			$revision = Plugin::$instance->documents->get( $revision_id, false );
			$revision_saved_typography = $revision->get_settings( 'system_typography' );

			$this->assertEquals( $saved_typography[1]['value']['font_family'], $revision_saved_typography[0]['typography_font_family'] );
			$this->assertEquals( $saved_typography[2]['value']['font_family'], $revision_saved_typography[1]['typography_font_family'] );
			$this->assertEquals( $saved_typography[3]['value']['font_family'], $revision_saved_typography[2]['typography_font_family'] );
			$this->assertEquals( $saved_typography[4]['value']['font_family'], $revision_saved_typography[3]['typography_font_family'] );
		}
	}

	public function test_v_3_1_0_move_optimized_dom_output_to_experiments() {
		add_option( 'elementor_optimized_dom_output', 'enabled' );

		$is_old_feature_active = Plugin::$instance->experiments->is_feature_active( 'e_dom_optimization' );

		Upgrades::v_3_1_0_move_optimized_dom_output_to_experiments();

		$experiments = new Experiments_Manager();

		$this->assertFalse( $is_old_feature_active );

		$this->assertTrue( $experiments->is_feature_active( 'e_dom_optimization' ) );
	}

	public function test_v_3_2_0_migrate_breakpoints_to_new_system() {
		$updater = $this->create_updater();

		// Set admin user so the kit will be able to be edited.
		$user_id = $this->factory()->create_and_get_administrator_user()->ID;
		wp_set_current_user( $user_id );

		$kit_id = Plugin::$instance->kits_manager->get_active_id();
		$kit = Plugin::$instance->documents->get( $kit_id );

		// Set a custom value for 'viewport_md' before running the upgrade, to test the migration in case the user has
		// a saved custom breakpoint value.
		$kit_settings = $kit->get_settings();
		$kit_settings['viewport_md'] = 600;

		$page_settings_manager = Settings_Manager::get_settings_managers( 'page' );
		$page_settings_manager->save_settings( $kit_settings, $kit_id );

		// Refresh kit after update.
		$kit = Plugin::$instance->documents->get( $kit_id, false );

		// Create revisions.
		$expected_iterations = (int) ceil( $this->revisions_count / $this->query_limit );
		$upgrade_iterations = 1;

		for ( $i = 0; $i < $this->revisions_count; $i++ ) {
			$kit->save( [
				'elements' => [],
			] );
		}

		$updater->set_limit( $this->query_limit );

		// Run upgrade.
		while ( Upgrades::_v_3_2_0_migrate_breakpoints_to_new_system( $updater ) ) {
			$upgrade_iterations++;

			$updater->set_current_item( [
				'iterate_num' => $upgrade_iterations,
			] );

			// Avoid infinity loop.
			if ( $upgrade_iterations > $this->revisions_count ) {
				break;
			}
		}

		// Assert iterations.
		$this->assertEquals( $expected_iterations, $upgrade_iterations );

		// Refresh kit.
		$kit = Plugin::$instance->documents->get( $kit_id, false );
		$kit_settings = $kit->get_settings();

		$this->run_breakpoint_assertions( $kit_settings );

		// Assert revisions upgraded.
		$revisions_ids = wp_get_post_revisions( $kit_id, [
			'fields' => 'ids',
		] );

		foreach ( $revisions_ids as $revision_id ) {
			$revision = Plugin::$instance->documents->get( $revision_id, false );
			$revision_settings = $revision->get_settings();

			$this->run_breakpoint_assertions( $revision_settings );
		}
	}

	private function run_breakpoint_assertions( $settings ) {
		// Mobile.
		$this->assertEquals( $settings['viewport_md'] - 1, $settings['viewport_mobile'] );

		// Tablet - Check the case where there is no custom breakpoint saved, so the value is empty.
		$expected_value = '' !== $settings['viewport_lg'] ? $settings['viewport_lg'] - 1 : $settings['viewport_lg'];
		$actual_value = $settings['viewport_tablet'];

		$this->assertEquals( $expected_value, $actual_value );
	}

	public function test_v_3_3_0_remove_old_elements_usage() {
		$this->ensure_removed_old_usage_data(
			'elementor_controls_usage',
			'_elementor_controls_usage',
			function () {
				Upgrades::_v_3_3_0_remove_old_elements_usage();
			}
		);
	}
}
