<?php
namespace Elementor\Tests\Phpunit\Includes\Elements;

use Elementor\Repeater;
use Elementor\Controls_Manager;
use Elementor\Testing\Elementor_Test_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Test_Repeater extends Elementor_Test_Base {
	public function test_get_controls__when_new_repeater_created_between_the_initiated_and_the_call_for_get_controls() {
		$repeater = new Repeater();
		$repeater->add_control( 'test_1', [
			[
				'label' => __( 'Placeholder', 'elementor-pro' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
			],
		] );

		$repeater2 = new Repeater();
		$repeater2->add_control( 'test_2', [
			[
				'label' => __( 'Placeholder', 'elementor-pro' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
			],
		] );

		$this->assertTrue( (bool) $repeater->get_controls('test_1') );
		$this->assertFalse( (bool) $repeater->get_controls('test_2') );

		$this->assertFalse( (bool) $repeater2->get_controls('test_1') );
		$this->assertTrue( (bool) $repeater2->get_controls('test_2') );

	}
}
