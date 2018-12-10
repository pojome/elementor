<?php

namespace Elementor\Core\Upgrade;

use Elementor\Core\Base\Background_Task;
use Elementor\Plugin;

/**
 * Background Updater
 * Based on https://github.com/woocommerce/woocommerce/blob/master/includes/class-wc-background-updater.php
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_Background_Updater Class.
 */
class Updater extends Background_Task {

	/**
	 * @var Manager
	 */
	protected $manager;

	public function __construct( $manager ) {
		$this->manager = $manager;

		parent::__construct();
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		$this->manager->on_upgrade_complete();

		parent::complete();
	}

	public function continue_upgrades() {
		// Used to fire an action added in WP_Background_Process::_construct() that calls WP_Background_Process::handle_cron_healthcheck().
		// This method will make sure the database updates are executed even if cron is disabled. Nothing will happen if the updates are already running.
		do_action( $this->cron_hook_identifier );
	}
}
