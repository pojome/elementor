<?php
namespace Elementor\Data\Base\Processor;

use Elementor\Data\Base\Processor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Before extends Processor {

	/**
	 * Get conditions for running processor.
	 * @param array $args
	 *
	 * @return bool
	 */
	public function get_conditions( $args ) {
		return true;
	}

	/**
	 * Apply processor.
	 *
	 * @param array $args
	 *
	 * @return mixed
	 */
	abstract public function apply( $args );
}
