<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Standards\OnlyController;

class Alpha_Controller extends \Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Controller {
	public function get_name() {
		return 'alpha';
	}

	public function get_items( $request ) {
		return 'alpha-items';
	}

	public function get_item( $request ) {
		return 'alpha-item';
	}
}
