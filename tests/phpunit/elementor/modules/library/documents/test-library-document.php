<?php
namespace Elementor\Testing\Modules\Library\Documents;

use Elementor\Modules\Library\Documents\Library_Document;
use Elementor\Testing\Elementor_Test_Base;

class Elementor_Test_Library_Document extends Elementor_Test_Base {

	public function test_should_return_properties() {
		$properties = Library_Document::get_properties();

		$this->assertTrue( $properties['show_in_library'] );
		$this->assertTrue( $properties['register_type'] );
		$this->assertSame( $properties['group'], 'blocks' );
		$this->assertSame( $properties['library_view'], 'grid' );
	}

	public function test_should_save_type() {
		$library_document = $this->getMockForAbstractClass( 'Elementor\Modules\Library\Documents\Library_Document',
			[ [ 'post_id' => $this->factory()->create_and_get_default_post()->ID ] ] );
		$library_document->method( 'get_name' )->willReturn( 'libraryTypeExample' );
		$library_document->save_type();

		$ret = wp_get_object_terms( $library_document->get_post()->ID, Library_Document::TAXONOMY_TYPE_SLUG );
		$this->assertEquals( $ret[0]->name, 'libraryTypeExample' );
	}
}
