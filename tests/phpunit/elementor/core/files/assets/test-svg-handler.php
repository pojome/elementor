<?php

namespace Elementor\Tests\Phpunit\Elementor\Core\Files\Assets;

use Elementor\Core\Files\Assets\Files_Upload_Handler;
use Elementor\Testing\Elementor_Test_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Test_Svg_Handler extends Elementor_Test_Base {

	public function setUp() {
		parent::setUp();

		$_POST['uploadTypeCaller'] = 'elementor-wp-media-upload';
	}

	/**
	 * Assert that the uploader accepts SVG.
	 */
	public function test_support_unfiltered_files_upload__accepts_svg() {
		// Arrange.
		update_option( Files_Upload_Handler::OPTION_KEY, '1' );

		$key = 'svg';
		$value = 'image/svg+xml';

		// Act.
		$mimes = apply_filters( 'upload_mimes', [] );

		// Assert.
		$this->assertArrayHasKey( $key, $mimes );
		$this->assertEquals( $mimes[ $key ], $value );
	}

	/**
	 * Assert that the uploader doesn't accept SVG.
	 */
	public function test_support_unfiltered_files_upload__doesnt_accept_svg() {
		// Arrange.
		update_option( Files_Upload_Handler::OPTION_KEY, '0' );

		// Act.
		$mimes = apply_filters( 'upload_mimes', [] );

		// Assert.
		$this->assertEmpty( $mimes );
	}
}
