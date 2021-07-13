<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Endpoint;

use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Endpoint;

class Format extends Endpoint {
	public function get_type() {
		return 'endpoint-format';
	}

	public function get_format() {
		return trim( $this->get_base_route(), '/' ) . '/{arg_id}';
	}
}
