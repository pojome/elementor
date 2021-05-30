<?php
namespace Elementor\Testing\Includes;

use Elementor\Testing\Elementor_Test_Base;
use Elementor\Tracker;

class Test_Tracker extends Elementor_Test_Base {
	public function test_get_library_usage_extend() {
		// Arrange.
		$post_types = [ 'section', 'page' ];
		$post_statuses = [ 'draft', 'private', 'publish' ];
		$posts_per_status = 2;

		foreach ( $post_types as $post_type ) {
			foreach ( $post_statuses as $post_status ) {
				for ( $i = 0; $i < $posts_per_status; ++$i ) {
					$template = $this->factory()->documents->create_and_get_template( $post_type );

					$this->factory()->documents->update_object( $template->get_id(), [ 'post_status' => $post_status ] );
				}
			}
		}

		// Act.
		$library_usage = Tracker::get_library_usage_extend();

		// Assert.
		foreach ( $post_types as $post_type ) {
			foreach ( $post_statuses as $post_status ) {
				$this->assertEquals( $posts_per_status, $library_usage[ $post_type ][ $post_status ] );
			}
		}
	}
}
