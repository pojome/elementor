<?php
namespace Elementor\Testing\Modules\History;

use Elementor\Core\Base\Document;
use Elementor\Modules\History\Revisions_Manager;
use Elementor\Testing\Elementor_Test_Base;

class Elementor_Test_Revisions_Manager extends Elementor_Test_Base {

	private $fake_post_id = 1234;

	public function test_should_return_false_from_handle_revision_hook() {
		Revisions_Manager::handle_revision();
		$this->assertFalse( apply_filters( 'wp_save_post_revision_check_for_changes', false ),
			'the filter "wp_save_post_revision_check_for_changes" should return false' );
	}

	public function test_should_avoid_delete_auto_save() {
		$post_content = 'post content';
		$post_id = $this->factory()->create_and_get_custom_post( [ 'post_content' => $post_content ] )->ID;

		$res = Revisions_Manager::avoid_delete_auto_save( $post_content, false );
		$this->assertEquals( $post_content, $res, 'post content should not change' );

		update_post_meta( $post_id, '_elementor_edit_mode', false );
		$res = Revisions_Manager::avoid_delete_auto_save( $post_content, $post_id );
		$this->assertEquals( $post_content, $res, 'post content should not change' );

		update_post_meta( $post_id, '_elementor_edit_mode', true );
		$res = Revisions_Manager::avoid_delete_auto_save( $post_content, $post_id );
		$this->assertEquals( $post_content . '<!-- Created with Elementor -->', $res,
			'post content should change, "<!-- Created with Elementor -->" should be added' );
	}

	public function test_should_remove_temp_post_content() {
		global $post;
		$temp_post = is_object( $post ) ? clone $post : $post;

		$elementor_addon = '<!-- Created with Elementor -->';
		$post_content = 'post content';
		$post = $this->factory()->create_and_get_custom_post( [ 'post_content' => $post_content . $elementor_addon ] );

		update_post_meta( $post->ID, '_elementor_edit_mode', false );
		Revisions_Manager::remove_temp_post_content();
		$this->assertEquals( $post->post_content, $post_content . $elementor_addon,
			'post content should contain "<!-- Created with Elementor -->"' );

		update_post_meta( $post->ID, '_elementor_edit_mode', true );
		Revisions_Manager::remove_temp_post_content();
		$this->assertEquals( $post->post_content, $post_content,
			'post content should not contain "<!-- Created with Elementor -->"' );

		$post = $temp_post;
	}

	public function test_should_get_revisions_id() {
		$parent_and_child_posts = $this->factory()->create_and_get_parent_and_child_posts();
		$parent_post_id = $parent_and_child_posts['parent_id'];
		$child_post_id = $parent_and_child_posts['child_id'];

		$ret = Revisions_Manager::get_revisions( $parent_post_id, [], false );
		self::assertEquals( 2, count( $ret ) );
		self::assertEquals( $ret[0], $parent_post_id );
		self::assertEquals( $ret[1], $child_post_id );

		$ret = Revisions_Manager::get_revisions( $child_post_id, [], false );
		$this->assertEquals( $ret[0], $child_post_id );

		$post_id = $this->factory()->create_and_get_default_post()->ID;

		$ret = Revisions_Manager::get_revisions( $post_id, [], false );
		$this->assertEquals( $ret[0], $post_id );
	}

	public function test_should_not_get_revisions() {
		$ret = Revisions_Manager::get_revisions();
		$this->assertEquals( $ret, [], 'get_revisions should return an empty array' );
	}

	public function test_should_get_revisions() {
		$parent_and_child_posts = $this->factory()->create_and_get_parent_and_child_posts();
		$parent_post_id = $parent_and_child_posts['parent_id'];
		$child_post_id = $parent_and_child_posts['child_id'];

		$ret = Revisions_Manager::get_revisions( $parent_post_id );
		self::assertEquals( 2, count( $ret ) );
		$this->assert_array_have_keys( [
			'id',
			'author',
			'timestamp',
			'date',
			'type',
			'gravatar',
		], $ret[0] );
		$this->assert_array_have_keys( [
			'id',
			'author',
			'timestamp',
			'date',
			'type',
			'gravatar',
		], $ret[1] );

		$ret = Revisions_Manager::get_revisions( $child_post_id );
		self::assertEquals( 1, count( $ret ) );
		$this->assert_array_have_keys( [
			'id',
			'author',
			'timestamp',
			'date',
			'type',
			'gravatar',
		], $ret[0] );
	}

	public function test_should_update_autosave() {
		$parent_and_child_posts = $this->setup_revision_check();

		Revisions_Manager::update_autosave( [
			'ID' => $parent_and_child_posts['child_id'],
		] );

		$this->assertTrue( $this->check_revisions( $parent_and_child_posts['parent_id'], $parent_and_child_posts['child_id'] ) );
	}

	public function test_should_save_revision() {
		$parent_and_child_posts = $this->setup_revision_check();

		Revisions_Manager::save_revision( $parent_and_child_posts['child_id'] );

		$this->assertTrue( $this->check_revisions( $parent_and_child_posts['parent_id'], $parent_and_child_posts['child_id'] ) );
	}

	public function test_should_return_null_from_restore_revision() {
		$res = $this->factory()->create_and_get_parent_and_child_posts();
		$post_id = $res['parent_id'];
		$autosave_post_id = $res['child_id'];

		update_post_meta( $post_id, '_elementor_meta_data', 'content' );

		Revisions_Manager::restore_revision( $post_id, $autosave_post_id );

		$this->assertFalse( $this->check_revisions( $post_id, $autosave_post_id ) );
	}

	public function test_should_restore_revision() {
		$res = $this->factory()->create_and_get_parent_and_child_posts();
		$post_id = $res['parent_id'];
		$autosave_post_id = $res['child_id'];

		update_metadata( 'post', $autosave_post_id, Document::BUILT_WITH_ELEMENTOR_META_KEY, 'builder' );
		update_metadata( 'post', $autosave_post_id, '_elementor_meta_data', 'content' );

		Revisions_Manager::restore_revision( $post_id, $autosave_post_id );

		$this->assertTrue( $this->check_revisions( $post_id, $autosave_post_id ) );
		$this->assertEquals( 'builder', get_post_meta( $post_id, Document::BUILT_WITH_ELEMENTOR_META_KEY, true ) );
	}

	public function test_should_add_revision_support_for_all_post_types() {
		Revisions_Manager::add_revision_support_for_all_post_types();

		$supported_types = [ 'page', 'post', 'elementor_library' ];

		foreach ( $supported_types as $supported_type ) {
			$this->assertTrue( post_type_supports( $supported_type, 'elementor' ) );
		}
	}

	public function test_should_return_false_from_db_before_save_hook() {
		Revisions_Manager::db_before_save( 'status', true );
		$this->assertFalse( apply_filters( 'wp_save_post_revision_check_for_changes', false ),
			'the filter "wp_save_post_revision_check_for_changes" should return false' );
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage You must set the revision ID.
	 */
	public function test_should_not_get_revision_data_on_request_because_of_unset_revision_ID() {
		Revisions_Manager::ajax_get_revision_data( [] );
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Invalid revision.
	 */
	public function test_should_not_get_revision_data_on_request_because_of_invalid_revision() {
		$args['id'] = $this->fake_post_id;

		Revisions_Manager::ajax_get_revision_data( $args );
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Access denied.
	 */
	public function test_should_not_get_revision_data_on_request_because_of_access_denied() {
		wp_set_current_user( $this->factory()->get_subscriber_user()->ID );
		$args['id'] = $this->factory()->create_and_get_default_post()->ID;

		Revisions_Manager::ajax_get_revision_data( $args );
	}

	public function test_should_get_revision_data_on_request() {
		wp_set_current_user( $this->factory()->create_and_get_administrator_user()->ID );
		$args['id'] = $this->factory()->create_and_get_default_post()->ID;

		$revision_data = Revisions_Manager::ajax_get_revision_data( $args );

		$this->assert_array_have_keys( [ 'settings', 'elements' ], $revision_data );
	}

	private function setup_revision_check() {
		$parent_and_child_posts = $this->factory()->create_and_get_parent_and_child_posts();
		$post_id = $parent_and_child_posts['parent_id'];

		update_post_meta( $post_id, '_elementor_edit_mode', true );
		update_post_meta( $post_id, '_elementor_meta_data', 'content' );

		return $parent_and_child_posts;
	}

	private function check_revisions( $post_id, $autosave_post_id ) {
		$parent_elementor_meta_data = get_post_meta( $post_id, '_elementor_meta_data', true );
		$child_elementor_meta_data = get_post_meta( $autosave_post_id, '_elementor_meta_data', true );

		return $child_elementor_meta_data === $parent_elementor_meta_data;
	}
}
