<?php
namespace Elementor\Testing\Modules\History;

use Elementor\Modules\History\Revisions_Manager;
use Elementor\Testing\Elementor_Test_AJAX;

class Elementor_Test_Revisions_Manager_Ajax extends Elementor_Test_AJAX {

	private $revisions_manager;

	public function setUp() {
		parent::setUp();
		if ( ! $this->revisions_manager ) {
			$this->define_doing_ajax();
			$this->revisions_manager = new Revisions_Manager();
		}
	}

	public function test_should_return_revisions_data() {
		$parent_and_child_posts = $this->factory()->create_and_get_parent_and_child_posts();
		$parent_id = $parent_and_child_posts['parent_id'];
		$child_id = $parent_and_child_posts['child_id'];
		$document = $this->elementor()->documents->get( $parent_id );

		$ret = apply_filters( 'elementor/documents/ajax_save/return_data', [], $document );

		$this->assert_array_have_keys( [
			'config',
			'latest_revisions',
			'revisions_ids',
		], $ret );
		$this->assertEquals( $child_id, $ret['config']['document']['revisions']['current_id'] );
		$this->assertEquals( 2, count( $ret['latest_revisions'] ) );
		$this->assertEquals( [ $parent_id, $child_id ], $ret['revisions_ids'] );
	}
}
