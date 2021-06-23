<?php
namespace Elementor\Tests\Phpunit\Elementor\Core\Upgrade;

use Elementor\Plugin;
use Elementor\Testing\Elementor_Test_Base;

class Test_Manager extends Elementor_Test_Base {

	public function test_start_run() {
		$manager = new Manager();

		// Prepare the upgrades manager
		$manager->public_start_run();

		// Trigger the run.
		$manager->get_task_runner()->handle_cron_healthcheck();

		// Assert via extract the created log entries.
		$raw_log_rows = Plugin::$instance->logger->get_logger()->get_log();
		$messages = [];

		foreach ( $raw_log_rows as $row ) {
			$messages[] = $row->message;
		}

		$this->assertContains( 'Elementor data updater process has been queued.', $messages );
		$this->assertContains( 'Elementor/Upgrades - upgrade_callback Start ', $messages );
		$this->assertContains( 'Elementor/Upgrades - upgrade_callback Finished', $messages );
		$this->assertContains( 'Elementor data updater process has been completed.', $messages );
	}
}
