<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\Base\Mock\Template\Traits;

trait MockTestData {
	public $test_data = [];

	public function set_test_data( $key, $value ) {
		$this->test_data[ $key ] = $value;
	}

	public function get_test_data( $key ) {
		if ( $key ) {
			if ( isset( $this->test_data[ $key ] ) ) {
				return $this->test_data[ $key ];
			}

			return false;
		}

		return $this->test_data;
	}
}
