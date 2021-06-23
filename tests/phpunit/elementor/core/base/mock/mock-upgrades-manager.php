<?php
namespace Elementor\Testing\Core\Base\Mock;

use Elementor\Core\Base\DB_Upgrades_Manager;

require_once 'mock-upgrades.php';

class Mock_Upgrades_Manager extends DB_Upgrades_Manager {
	const OPTION_CURRENT_VERSION_NAME = 'mock_upgrades_manager';
	const OPTION_NEW_VERSION_NAME = 'mock_upgrades_manager_new_version';

	public function get_action() {
		return 'mock-upgrades-manager';
	}

	public function get_plugin_name() {
		return 'mock-upgrades-manager';
	}

	public function get_plugin_label() {
		return 'mock-upgrades-manager';
	}

	public function get_new_version() {
		return get_option( self::OPTION_NEW_VERSION_NAME );
	}

	public function get_version_option_name() {
		return self::OPTION_CURRENT_VERSION_NAME;
	}

	public function get_upgrades_class() {
		return Mock_Upgrades::class;
	}

	public function get_updater_label() {
		return 'mock-upgrades-manager';
	}

	public function get_name() {
		return 'mock-upgrades';
	}

	public function mock_continue_run() {
		$this->get_task_runner()->continue_run();
	}
}
