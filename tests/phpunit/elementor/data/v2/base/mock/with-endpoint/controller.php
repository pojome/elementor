<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\WithEndpoint;

use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Endpoint as EndpointTemplate;

class Controller extends \Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Controller {
	public function register_endpoints() {
		$this->register_endpoint( new EndpointTemplate( $this ) );
	}
}
