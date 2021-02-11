<?php

namespace Elementor\Modules\WidgetsPanel\Data;

use Elementor\Data\Base\Controller as Controller_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Controller
 * @package Elementor\Modules\WidgetsPanel\Data
 */
class Controller extends Controller_Base {

	/**
	 * Get rout name.
	 *
	 * Retrieve the widgets-panel rout name.
	 *
	 * @since 3.0.16
	 * @access public
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'widgets-panel';
	}

	/**
	 * Register endpoints
	 *
	 * @since 3.0.16
	 * @access public
	 */
	public function register_endpoints() {
		$this->register_endpoint( Endpoints\Favorites::class );
	}

}
