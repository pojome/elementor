<?php
namespace Elementor\Tests\Phpunit\Elementor\Data\V2\Base\Mock\Template\Traits;

/**
 * Its created to avoid default registration process and gain the ability to manually register the owners of this trait.
 */
trait Mock_Bypass_Register {
	public $bypass_register_status = false;

	public function bypass_original_register( $status = true ) {
		$this->bypass_register_status = $status;
	}

	protected function register() {
		if ( isset( $this->controller ) ) {
			if ( ! $this->controller->bypass_register_status ) {
				return parent::register();
			}
		} else if ( ! $this->bypass_register_status ) {
			parent::register();
		}
	}

	public function do_register() {
		parent::register();
	}
}
