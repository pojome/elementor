<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Standards\OnlySubController;

class Beta_Controller extends \Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Template\Sub_Controller {
	public function get_name() {
		return 'beta';
	}

	public function get_parent_name() {
		return 'alpha';
	}

	public function get_route() {
		return '/(?P<sub_id>[\w]+)/';
	}

	public function get_items( $request ) {
		return 'beta-items';
	}

	public function get_item( $request ) {
		return 'beta-item';
	}
}
