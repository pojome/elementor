<?php
namespace Elementor\Tests\Phpunit\Elementor\Core\Upgrade;

use Elementor\Core\Upgrade\Manager as UpgradeManager;
use Elementor\Core\Upgrade\Updater;

class Manager extends UpgradeManager {

	/**
	 * A callback for upgrade callbacks list.
	 *
	 * @param $updater Updater
	 *
	 * @return false - didn't should run again.
	 */
	static public function upgrade_callback( $updater ) {
		return false;
	}

	/**
	 * Make the protected method public.
	 */
	public function public_start_run() {
		$this->start_run();
	}


	/***
	 * Upgrade callbacks.
	 *
	 * @return array|array[]
	 */
	public function get_upgrade_callbacks() {
		return [
			[ __CLASS__, 'upgrade_callback' ],
		];
	}
}
