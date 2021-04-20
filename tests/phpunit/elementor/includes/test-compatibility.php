<?php
namespace Elementor\Testing\Includes;

use Elementor\Compatibility;
use Elementor\Core\Base\Document;
use Elementor\Testing\Elementor_Test_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Test_Compatibility extends Elementor_Test_Base {
	public function test_save_polylang_meta__should_copy_elementor_meta_keys() {
		$factory = $this->factory()->documents;

		$from = $factory->create_and_get( [
			'meta_input' => [ Document::ELEMENTOR_DATA_META_KEY => $data = json_encode( [ [ 'elType' => 'section' ] ] ) ],
		] );
		$to = $factory->create_and_get();

		Compatibility::save_polylang_meta( [], false, $from->get_id(), $to->get_id() );

		$this->assertEquals( $data, $to->get_meta( Document::ELEMENTOR_DATA_META_KEY ) );
	}
}
