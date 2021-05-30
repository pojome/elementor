<?php
namespace Elementor\Testing\Modules\LandingPages;

use Elementor\Plugin;
use Elementor\Testing\Elementor_Test_Base;
use Elementor\Modules\LandingPages\Module;
use Elementor\Testing\Traits\Elementor_Library;

class Elementor_Test_Landing_Pages_Module extends Elementor_Test_Base {

	use Elementor_Library;

	public function test__construct() {
		$this->assert_document_type_registered( Module::DOCUMENT_TYPE );
	}

	public function test_get_name() {
		$this->assertEquals( 'landing-pages', Plugin::$instance->modules_manager->get_modules( 'landing-pages' )->get_name() );
	}
}
