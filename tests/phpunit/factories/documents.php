<?php

namespace Elementor\Testing\Factories;

use Elementor\Core\Base\Document;
use Elementor\Plugin;
use WP_UnitTest_Factory_For_Thing;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * @method int create( $args = array(), $generation_definitions = null )
 * @method Document create_and_get( $args = array(), $generation_definitions = null )
 * @method int[] create_many( $count, $args = array(), $generation_definitions = null )
 */
class Documents extends WP_UnitTest_Factory_For_Thing {
	const DEFAULT_WIDGET_DATA_MOCK = [
		'id' => 'mock-widget',
		'elType' => 'widget',
		'isInner' => false,
		'settings' => [ 'text' => 'I\'m just a mock', ],
		'elements' => [],
		'widgetType' => 'button',
	];

	const DEFAULT_DYNAMIC_WIDGET_DATA_MOCK = [
		'id' => 'mock-widget-dynamic',
		'elType' => 'widget',
		'settings' => [
			'title' => 'Add Your Heading Text Here',
			'link' => [
				'custom_attributes' => '',
				'is_external' => '',
				'nofollow' => '',
				'url' => 'http://just-a-mock.com',
			],
			'__dynamic__' => [
				'title' => '[elementor-tag id="2e7ade9" name="post-title" settings="%7B%7D"]',
				'link' => '[elementor-tag id="68a0003" name="post-url" settings="%7B%7D"]',
			],
		],
		'elements' => [],
		'widgetType' => 'heading',
	];

	const DEFAULT_DOCUMENT_DATA_MOCK = [
		[
			'id' => 'mock-section',
			'elType' => 'section',
			'isInner' => false,
			'settings' => [
				'post_status' => 'new',
			],
			'elements' => [
				[
					'id' => 'mock-column',
					'elType' => 'column',
					'isInner' => false,
					'settings' => [ '_column_size' => 100, ],
					'elements' => [ self::DEFAULT_WIDGET_DATA_MOCK ],
				],
			],
		],
	];

	const DOCUMENT_DATA_MOCK_WITHOUT_ELEMENTS = [
		'settings' => [
			'post_status' => 'publish',
		],
		'elements' => [],
	];

	const DOCUMENT_DATA_MOCK_WITH_DYNAMIC_WIDGET = [
		[
			'id' => 'mock-section',
			'elType' => 'section',
			'isInner' => false,
			'settings' => [
				'post_status' => 'new',
			],
			'elements' => [
				[
					'id' => 'mock-column',
					'elType' => 'column',
					'isInner' => false,
					'settings' => [ '_column_size' => 100, ],
					'elements' => [ self::DEFAULT_DYNAMIC_WIDGET_DATA_MOCK ],
				],
			],
		],
	];

	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = [
			'post_title' => new \WP_UnitTest_Generator_Sequence( 'Elementor post title %s' ),
			'post_content' => new \WP_UnitTest_Generator_Sequence( 'Elementor post content %s' ),
			'post_excerpt' => new \WP_UnitTest_Generator_Sequence( 'Elementor post excerpt %s' ),
			'post_type' => 'post',
		];
	}

	public function create_object( $args ) {
		$document_type = 'page';
		$meta = [];

		if ( isset( $args['document_type'] ) ) {
			$document_type = $args['document_type'];

			unset( $args['document_type'] );
		}

		if ( isset( $args['meta_input'] ) ) {
			$meta = $args['meta_input'];

			unset( $args['meta_input'] );
		}

		if ( empty( $meta['_elementor_data'] ) ) {
			$meta['_elementor_data'] = wp_json_encode( self::DEFAULT_DOCUMENT_DATA_MOCK );
		}

		return Plugin::$instance->documents->create( $document_type, $args, $meta )->get_id();
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
	 * publish_and_get.
	 *
	 * Why this method exists and called the way it do?
	 *
	 * Naming: called the way it do to follow PHPUnit convection.
	 * Why publish after create? It have to behave in the same way it do in real-world.
	 * elementor never create a document that already published ( Other system depends on this behavior).
	 *
	 * @param array $args
	 *
	 * @return \Elementor\Core\Base\Document|false
	 */
	public function publish_and_get( $args = [] ) {
		$document = $this->create_and_get( $args );

		return $this->update_object( $document->get_id(), [ 'post_status' => 'publish' ] );
	}
}
