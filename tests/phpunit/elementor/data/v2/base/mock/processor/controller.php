<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Processor;

use Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Processor;

class Controller extends \Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Controller {

	public function register_processors() {
		$this->register_processor( new Processor( $this ) );
	}
}
