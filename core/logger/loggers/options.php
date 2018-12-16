<?php
namespace Elementor\Core\Logger\Loggers;

use Elementor\Core\Logger\Items\Log_Item_Interface as Log_Item;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Options extends Base {

	public function save_log( Log_Item $item ) {
		/** @var Log_Item[] $log */
		$log = $this->get_log();

		$id = $item->get_fingerprint();

		if ( empty( $log[ $id ] ) ) {
			$log[ $id ] = $item;
		}

		$log[ $id ]->increase_times( $item );

		update_option( self::LOG_NAME, $log, 'no' );
	}

	protected function get_log() {
		// Clear cache.
		wp_cache_delete( self::LOG_NAME, 'options' );

		return get_option( self::LOG_NAME, [] );
	}
}
