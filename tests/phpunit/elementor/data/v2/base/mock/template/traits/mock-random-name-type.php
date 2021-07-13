<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Traits;

trait Mock_Random_Name_Type {
	public $random = null;

	public function get_name() {
		if ( ! $this->random ) {
			$this->random = rand_long_str( 5 );
		}

		return 'test-' . $this->get_type() . '-' . $this->random;
	}

	abstract function get_type();
}
