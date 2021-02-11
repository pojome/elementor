<?php
namespace Elementor\Tests\Phpunit\Elementor\Modules\WidgetsPanel;

use Elementor\Plugin;
use Elementor\Modules\WidgetsPanel\Module;
use Elementor\Testing\Elementor_Test_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elementor_Test_Module extends Elementor_Test_Base {

	/** @var Module */
    private static $module;

    public function setUp() {
        parent::setUp();

        wp_set_current_user( self::factory()->get_administrator_user()->ID );

        $this->module = new Module();
    }

    /** @TODO: add tests */
}
