<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template;

use Elementor\Data\V2\Base\Processor\After;

class Processor extends After {

	private $test_access_controller;

	public function __construct( $controller ) {
		parent::__construct( $controller );

		$this->test_access_controller = $controller;
	}

	public function get_command() {
		return $this->test_access_controller->get_name();
	}

	public function get_conditions( $args, $result ) {
		return true;
	}

	public function apply( $args, $result ) {
		if ( is_array( $result ) ) {
			$result[ 'from_processor'] = true;
		}
		return $result;
	}
}
