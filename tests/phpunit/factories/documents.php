<?php
namespace Elementor\Testing\Factories;

use Elementor\Plugin;
use Elementor\Core\Base\Document;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Documents extends \WP_UnitTest_Factory_For_Post {
	public function __construct( $factory = null ) {
		parent::__construct( $factory );
		$this->default_generation_definitions = array(
			'post_status' => 'publish',
			'post_title' => new \WP_UnitTest_Generator_Sequence( 'Elementor post title %s' ),
			'post_content' => new \WP_UnitTest_Generator_Sequence( 'Elementor post content %s' ),
			'post_excerpt' => new \WP_UnitTest_Generator_Sequence( 'Elementor post excerpt %s' ),
			'post_type' => 'page',
		);
	}

	public function create_object( $args ) {
		$type = 'page';
		$meta = [];

		if ( isset( $args['type'] ) ) {
			$type = $args['type'];

			unset( $args['type'] );
		}

		if ( isset( $args['meta_input'] ) ) {
			$meta = $args['meta_input'];
		}

		return Plugin::$instance->documents->create( $type, $args, $meta )->get_id();
	}

	public function update_object( $document_id, $fields ) {
		$fields['ID'] = $document_id;

		wp_update_post( $fields );

		return Plugin::$instance->documents->get( $document_id, false );
	}

	public function get_object_by_id( $document_id ) {
		return Plugin::$instance->documents->get( $document_id, false );
	}

	/**
	 * @param array $arg
	 *
	 * @return Document
	 */
	public function create_elementor_document( $arg = [] ) {
		return $this->create_and_get( array_replace_recursive( [
			'meta_input' => [
				Document::BUILT_WITH_ELEMENTOR_META_KEY => 'builder',
				Document::ELEMENTOR_DATA_META_KEY => wp_json_encode( [ [ 'elType' => 'section' ] ] ),
			],
		], $arg ) );
	}

	/**
	 * Create and get document.
	 *
	 * Overwrite mostly for typehint.
	 *
	 * @param array $args
	 * @param null $generation_definitions
	 *
	 * @return Document
	 */
	public function create_and_get( $args = [], $generation_definitions = null ) {
		return parent::create_and_get( $args, $generation_definitions );
	}
}
