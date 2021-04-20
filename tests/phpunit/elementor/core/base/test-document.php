<?php

namespace Elementor\Testing\Core\Base;

use Elementor\Core\Base\Document;
use Elementor\Core\DocumentTypes\Post;
use Elementor\Testing\Elementor_Test_Base;

use Elementor\Plugin;

class Test_Document extends Elementor_Test_Base {
	// TODO: Full test of get_editor_panel_config.

	public function test_get_editor_panel_config_ensure_default_route() {
		$before_user = wp_get_current_user();

		// Set editor user.
		wp_set_current_user( $this->factory()->get_editor_user()->ID );

		$default_route_excepted = 'panel/elements/categories';
		$document = Plugin::$instance->documents->get( $this->factory()->create_and_get_default_post()->ID );

		query_posts( [
			'p' => $document->get_main_id(),
		] );

		the_post();

		$config = $document->get_config();

		$this->assertEquals( $default_route_excepted, $config['panel']['default_route'],
			"Ensure user without design restriction have '$default_route_excepted' as default route");

		add_filter( 'elementor/editor/user/restrictions', function( $restrictions ) {
			$restrictions['editor'] = [ 'design' ];

			return $restrictions;
		} );

		$default_route_excepted = 'panel/page-settings/settings';
		$document = Plugin::$instance->documents->get( $this->factory()->create_and_get_default_post()->ID );
		$config = $document->get_config();

		$this->assertEquals( $default_route_excepted, $config['panel']['default_route'],
			"Ensure user with design restriction have '$default_route_excepted' as default route");

		wp_set_current_user( $before_user->ID );
	}

	public function test_get_elements_data__should_copy_elementor_data_to_parent_if_edit_mode_and_no_elements(  ) {
		// Arrange
		Plugin::$instance->editor->set_edit_mode( true );

		$document = $this->factory()->documents->create_and_get( [
			'meta_input' => [
				Document::ELEMENTOR_DATA_META_KEY => $data = json_encode( [ [ 'elType' => 'section' ] ] ),
			],
		] );
		$autosave = $this->factory()->revisions->create_for_parent( $document->get_id() );

		// Act
		$elements = $autosave->get_elements_data();

		// Assert
		$this->assertNotEmpty( $elements );
		$this->assertEquals( $data, get_metadata( 'post', $autosave->get_id(), Document::ELEMENTOR_DATA_META_KEY, true ) );
	}

	public function test_get_autosave__should_create_new_autosave() {
		// Arrange
		wp_set_current_user( $this->factory()->create_and_get_administrator_user()->ID );
		$document = $this->factory()->documents->create_elementor_document();

		// Act
		$autosave = $document->get_autosave( 0, true );

		// Assert
		$this->assertEquals( $autosave->get_post()->post_parent, $document->get_id() );

		$this->assertEquals(
			$autosave->get_meta( Document::ELEMENTOR_DATA_META_KEY ),
			$document->get_meta( Document::ELEMENTOR_DATA_META_KEY )
		);

		$this->assertEquals(
			$autosave->get_meta( Document::BUILT_WITH_ELEMENTOR_META_KEY ),
			$document->get_meta( Document::BUILT_WITH_ELEMENTOR_META_KEY )
		);
	}

	public function test_copy_elementor_meta_to() {
		// Arrange
		$valid_meta_inputs = [
			'_elementor_something' => 'test',
			'_wp_page_template' => 'test',
			'_thumbnail_id' => 'test',
		];

		$invalid_meta_inputs = [
			'not_related_to_elementor' => 'test',
			'something' => 'test',
		];

		$parent = $this->factory()->documents->create_and_get( [
			'meta_input' => array_merge( $valid_meta_inputs, $invalid_meta_inputs ),
		] );
		$child = $this->factory()->documents->create_and_get();

		// Act
		$parent->copy_elementor_meta_to( $child->get_id() );

		// Assert
		$child_meta = $child->get_meta();

		$this->assertArrayHaveKeys( array_keys( $valid_meta_inputs ), $child_meta );
		$this->assertArrayNotHaveKeys( array_keys( $invalid_meta_inputs ), $child_meta );
	}

	public function test_safe_copy_elementor_meta_to() {
		// Arrange
		$parent_post = $this->factory()->post->create_and_get( [
			'meta_input' => [
				'_elementor_edit_mode' => 'builder',
			],
		] );

		$parent_mock = $this->getMockBuilder( Post::class )
			->setConstructorArgs( [ 'data' => [ 'post_id' => $parent_post->ID ] ] )
			->setMethods( [ 'copy_elementor_meta_to' ] )
			->getMock();

		$child = $this->factory()->documents->create_and_get();

		// Assert (expect before the act)
		$parent_mock->expects( $this->once() )->method( 'copy_elementor_meta_to' )->with( $child->get_id() );

		// Act
		$parent_mock->safe_copy_elementor_meta_to( $child->get_id() );
	}

	public function test_safe_copy_elementor_meta_to__if_saved_from_elementor_it_always_copy_meta() {
		// Arrange
		$parent_post = $this->factory()->post->create_and_get();

		$parent_mock = $this->getMockBuilder( Post::class )
			->setConstructorArgs( [ 'data' => [ 'post_id' => $parent_post->ID ] ] )
			->setMethods( [ 'copy_elementor_meta_to' ] )
			->getMock();

		$child = $this->factory()->documents->create_and_get();

		// Assert (expect before the act)
		$parent_mock->expects( $this->once() )->method( 'copy_elementor_meta_to' )->with( $child->get_id() );

		do_action( 'elementor/db/before_save', 'draft', false );

		// Act
		$parent_mock->safe_copy_elementor_meta_to( $child->get_id() );
	}

	public function test_safe_copy_elementor_meta_to__if_not_built_with_elementor_it_should_failed() {
		// Arrange
		$parent_post = $this->factory()->post->create_and_get();

		$parent_mock = $this->getMockBuilder( Post::class )
			->setConstructorArgs( [ 'data' => [ 'post_id' => $parent_post->ID ] ] )
			->setMethods( [ 'copy_elementor_meta_to' ] )
			->getMock();

		$child = $this->factory()->documents->create_and_get();

		// Assert (expect before the act)
		$parent_mock->expects( $this->never() )->method( 'copy_elementor_meta_to' )->with( $child->get_id() );

		// Act
		$parent_mock->safe_copy_elementor_meta_to( $child->get_id() );
	}

	public function test_safe_copy_elementor_meta_to__if_the_target_post_has_elementor_data_it_should_failed() {
		// Arrange
		$parent_post = $this->factory()->post->create_and_get( [
			'meta_input' => [
				'_elementor_edit_mode' => 'builder',
			],
		] );

		$parent_mock = $this->getMockBuilder( Post::class )
			->setConstructorArgs( [ 'data' => [ 'post_id' => $parent_post->ID ] ] )
			->setMethods( [ 'copy_elementor_meta_to' ] )
			->getMock();

		$child = $this->factory()->documents->create_and_get( [
			'meta_input' => [ '_elementor_data' => [ [ 'elType' => 'section' ] ] ],
		] );

		// Assert (expect before the act)
		$parent_mock->expects( $this->never() )->method( 'copy_elementor_meta_to' )->with( $child->get_id() );

		// Act
		$parent_mock->safe_copy_elementor_meta_to( $child->get_id() );
	}
}
