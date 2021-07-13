<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\SubEndpoint;

use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Endpoint;

class Format extends Endpoint {
	public function get_format() {
		return '{sub_arg_id}';
	}

	public function get_type() {
		return 'sub-endpoint-format';
	}
}
