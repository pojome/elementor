<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Endpoint;

use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Endpoint;
use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Traits\Mock_Force_Permission;

class Bypass_Permission extends Endpoint {
	use Mock_Force_Permission;
}
