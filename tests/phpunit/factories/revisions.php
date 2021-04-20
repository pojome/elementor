<?php
namespace Elementor\Testing\Factories;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Revisions extends Documents {
	public function __construct( $factory = null ) {
		parent::__construct( $factory );
		$this->default_generation_definitions = array(
			'post_status' => 'draft',
			'post_title' => new \WP_UnitTest_Generator_Sequence( 'Elementor post title %s' ),
			'post_name' => new \WP_UnitTest_Generator_Sequence( '0-autosave' ),
			'post_content' => new \WP_UnitTest_Generator_Sequence( 'Elementor post content %s' ),
			'post_excerpt' => new \WP_UnitTest_Generator_Sequence( 'Elementor post excerpt %s' ),
			'post_type' => 'revision',
		);
	}

	/**
	 * @param $parent_id
	 * @param array $args
	 *
	 * @return \Elementor\Core\Base\Document
	 */
	public function create_for_parent( $parent_id, $args = [] ) {
		return $this->create_and_get(
			array_merge( [
				'post_parent' => $parent_id,
				'post_name' => $parent_id . '-autosave',
			], $args )
		);
	}
}
