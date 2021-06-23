<?php
namespace Elementor\Core\Upgrade;

use Elementor\Core\Common\Modules\Ajax\Module as Ajax;
use Elementor\Core\Settings\Manager as SettingsManager;
use Elementor\Core\Settings\Page\Manager as SettingsPageManager;
use Elementor\Plugin;
use Elementor\Tools;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Elementor_3_Re_Migrate_Globals {

	public function register_ajax_actions( Ajax $ajax ) {
		$ajax->register_ajax_action( 're_migrate_globals', [ $this, 'ajax_re_migrate_globals' ] );
	}

	public function ajax_re_migrate_globals() {
		$this->run();

		return true;
	}

	/**
	 * @deprecated 3.1.0
	 */
	public function admin_localize_settings() {
		Plugin::$instance->modules_manager->get_modules( 'dev-tools' )->deprecation->deprecated_function( __METHOD__, '3.1.0' );

		return [];
	}

	public function register_admin_tools_fields( Tools $tools ) {
		// Add the re-migrate button only if needed.
		if ( ! $this->has_typography() && ! $this->has_colors() && ! $this->has_saved_colors() ) {
			return;
		}

		// Add the button to the rollback screen.
		$tools->add_fields( 'versions', 'rollback', [
			're_migrate_globals_separator' => [
				'field_args' => [
					'type' => 'raw_html',
					'html' => '<hr>',
				],
			],
			're_migrate_globals' => [
				'label' => __( 'Rerun Update Script', 'elementor' ),
				'field_args' => [
					'type' => 'raw_html',
					'html' => sprintf(
						'<a href="#" data-nonce="%s" class="button elementor-button-spinner elementor-re-migrate-globals-button">%s</a>',
						wp_create_nonce( 'elementor_re_migrate_globals' ),
						__( 'Migrate to v3.0', 'elementor' )
					),
					'desc' => '<span style="color: red;">' . __( 'Warning: This will reset your current Global Fonts and Colors, and will migrate your previous settings from v2.x versions.', 'elementor' ) . '</span>',
				],
			],
		] );
	}

	private function run() {
		$this->notice( 'Re-migrate 3.0.0 globals: Start' );

		$callbacks = [];

		$kit = Plugin::$instance->kits_manager->get_active_kit();
		// Already exist. use raw settings that doesn't have default values.
		$meta_key = SettingsPageManager::META_KEY;
		$kit_raw_settings = $kit->get_meta( $meta_key );

		if ( $this->has_typography() ) {
			$callbacks[] = [ Upgrades::class, '_v_3_0_0_move_default_typography_to_kit' ];
			unset( $kit_raw_settings['system_typography'] );
		} else {
			$this->notice( 'Typography not found' );
		}

		if ( $this->has_colors() ) {
			$callbacks[] = [ Upgrades::class, '_v_3_0_0_move_default_colors_to_kit' ];
			unset( $kit_raw_settings['system_colors'] );
		} else {
			$this->notice( 'Colors not found' );
		}

		if ( $this->has_saved_colors() ) {
			$callbacks[] = [ Upgrades::class, '_v_3_0_0_move_saved_colors_to_kit' ];
			unset( $kit_raw_settings['custom_colors'] );
		} else {
			$this->notice( 'Saved Colors not found' );
		}

		if ( ! empty( $callbacks ) ) {
			$page_settings_manager = SettingsManager::get_settings_managers( 'page' );
			$page_settings_manager->save_settings( $kit_raw_settings, $active_kit_id );

			$this->run_callbacks( $callbacks );
		}

		Plugin::$instance->files_manager->clear_cache();

		$this->notice( 'Re-migrate 3.0.0 globals: End' );
	}

	private function run_callbacks( $callbacks ) {
		foreach ( $callbacks as $callback ) {
			$updater = $this->create_updater();

			$this->notice( 'Re-migrate 3.0.0 globals: ' . $callback[1] );

			// Run upgrade.
			call_user_func( $callback, $updater, false );
		}
	}

	/**
	 * @return Updater
	 */
	private function create_updater() {
		$upgrades_manager = new Manager();

		/** @var Updater $updater */
		$updater = $upgrades_manager->get_task_runner();

		$updater->set_current_item( [
			'iterate_num' => 1,
		] );

		return $updater;
	}

	private function has_typography() {
		return ! ! get_option( 'elementor_scheme_typography' );
	}

	private function has_colors() {
		return ! ! get_option( 'elementor_scheme_color' );
	}

	private function has_saved_colors() {
		return ! ! get_option( 'elementor_scheme_color-picker' );
	}

	private function notice( $message ) {
		$logger = Plugin::$instance->logger->get_logger();
		$logger->notice( $message );
	}

	public function __construct() {
		add_action( 'elementor/admin/after_create_settings/' . Tools::PAGE_ID, [ $this, 'register_admin_tools_fields' ], 60 /* After plugins rollback */ );
		add_action( 'elementor/ajax/register_actions', [ $this, 'register_ajax_actions' ] );

	}
}
