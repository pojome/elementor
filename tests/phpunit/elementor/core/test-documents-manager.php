<?php
namespace Elementor\Tests\Phpunit\Elementor\Core;

use Elementor\Core\Base\Document;
use Elementor\Plugin;
use Elementor\Testing\Elementor_Test_Base;

class Test_Documents_Manager extends Elementor_Test_Base {
	public function test_ajax_get_document_config__set_id_build_with_elementor() {
		// Arrange
		$this->act_as_admin();

		Plugin::$instance->editor->set_edit_mode( true );

		$post = $this->factory()->create_and_get_custom_post( [ 'type' => 'post' ] );

		// Act
		Plugin::$instance->documents->ajax_get_document_config( [ 'id' => $post->ID ] );

		// Assert
		$this->assertEquals(
			'builder',
			get_post_meta( $post->ID, Document::BUILT_WITH_ELEMENTOR_META_KEY, true )
		);
	}

	public function test_get_new_post_url() {
		$new_post_url =  Plugin::$instance->documents->get_create_new_post_url();

		$this->assertContains( 'edit.php?action=elementor_new_post&post_type=', $new_post_url );
		$this->assertContains( '_wpnonce=', $new_post_url );
	}
}
