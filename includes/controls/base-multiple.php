<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor control base multiple.
 *
 * An abstract class for creating new controls in the panel that return
 * more than a single value. Each value of the multi-value control will
 * be returned as an item in a `key => value` array.
 *
 * @since 1.0.0
 * @abstract
 */
abstract class Control_Base_Multiple extends Base_Data_Control {

	/**
	 * Get Value Type
	 *
	 * Check whether the default value of the control is single (such as string, int) or multiple (array).
	 * Default value is 'single', can also be 'multiple'.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @return string Value Type
	 */
	public function get_value_type() {
		return 'multiple';
	}
}
