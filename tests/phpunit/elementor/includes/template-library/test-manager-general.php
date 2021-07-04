<?php
namespace Elementor\Testing\Includes\TemplateLibrary;

use Elementor\Api;
use Elementor\Core\Base\Document;
use Elementor\TemplateLibrary\Manager;
use Elementor\Testing\Elementor_Test_Base;
use Elementor\TemplateLibrary\Source_Local;

class Elementor_Test_Manager_General extends Elementor_Test_Base {
	/**
	 * @var Manager
	 */
	protected static $manager;
	private $fake_post_id = '123';

	public static function setUpBeforeClass() {
		self::$manager = self::elementor()->templates_manager;
	}

	public function test_should_return_import_images_instance() {
		$this->assertEquals( self::$manager->get_import_images_instance(), new \Elementor\TemplateLibrary\Classes\Import_Images() );
	}

	public function test_should_return_wp_error_source_class_name_not_exists_from_register_source() {
		$this->assertWPError( self::$manager->register_source( 'invalid class' ), 'source_class_name_not_exists' );
	}


	public function test_should_return_wp_error_wrong_instance_source_from_register_source() {
		$this->assertWPError( self::$manager->register_source( 'Elementor\Core\Common\Modules\Ajax\Module' ), 'wrong_instance_source' );
	}

	public function test_should_fail_to_return_source() {
		$this->assertFalse( self::$manager->get_source( 'invalid source' ) );
	}

	public function test_should_return_wp_error_arguments_not_specified_from_save_template() {
		$this->assertWPError(
			self::$manager->save_template( [ 'post_id' => $this->fake_post_id ] ), 'arguments_not_specified'
		);
	}

	public function test_should_return_wp_error_template_error_from_save_template() {
		$this->assertWPError(
			self::$manager->save_template(
				[
					'post_id' => $this->fake_post_id,
					'source' => 'invalid source',
					'content' => 'content',
					'type' => 'page',
				]
			),
			'template_error'
		);
	}


	public function test_should_return_wp_error_arguments_not_specified_from_update_template() {
		$this->assertWPError(
			self::$manager->update_template( [ 'post_id' => $this->fake_post_id ] ), 'arguments_not_specified'
		);
	}


	public function test_should_return_wp_error_template_error_from_update_template() {
		$this->assertWPError(
			self::$manager->update_template(
				[
					'source' => 'invalid source',
					'content' => 'content',
					'type' => 'page',
				]
			),
			'template_error'
		);
	}

	public function test_should_return_wp_error_update_templates() {
		$templates = [
			'templates' => [
				[
					'source' => 'invalid content 1 ',
					'content' => 'content',
					'type' => 'comment',
					'id' => $this->fake_post_id,
				],
				[
					'source' => 'invalid content 2',
					'content' => 'content',
					'type' => 'comment',
					'id' => $this->fake_post_id,
				],
			],
		];

		$this->assertWPError( self::$manager->update_templates( $templates ) );
	}

	public function test_should_return_true_from_update_templates() {
		wp_set_current_user( $this->factory()->create_and_get_administrator_user()->ID );
		$templates = [
			'templates' => [
				[
					'source' => 'local',
					'content' => 'content',
					'type' => 'page',
					'id' => $this->factory()->create_and_get_default_post()->ID,
				],
				[
					'source' => 'local',
					'content' => 'content',
					'type' => 'comment',
					'id' => $this->factory()->create_and_get_default_post()->ID,
				],
			],
		];

		$this->assertTrue( self::$manager->update_templates( $templates ) );
	}


	public function test_should_return_wp_error_massage_arguments_not_specified_from_get_template_data() {
		$this->assertWPError( self::$manager->get_template_data( [] ), 'arguments_not_specified' );
	}

	public function test_should_return_wp_error_massage_template_error_from_get_template_data() {
		$this->assertWPError(
			self::$manager->get_template_data(
				[
					'source' => 'invalid source',
					'template_id' => $this->fake_post_id,
					'edit_mode' => true,
				]
			), 'template_error'
		);
	}

	public function test_should_return_wp_error_arguments_not_specified_from_delete_template() {
		$this->assertWPError( self::$manager->delete_template( [] ), 'arguments_not_specified' );
	}

	public function test_should_return_wp_error_template_error_from_delete_template() {
		$this->assertWPError(
			self::$manager->delete_template(
				[
					'source' => 'invalid source',
					'template_id' => $this->fake_post_id,
				]
			), 'template_error'
		);
	}

	public function test_should_return_wp_error_arguments_not_specified_from_export_template() {
		$this->assertWPError( self::$manager->export_template( [] ), 'arguments_not_specified' );
	}

	public function test_should_return_wp_error_template_error_from_export_template() {
		$this->assertWPError(
			self::$manager->export_template(
				[
					'source' => 'invalid source',
					'template_id' => $this->fake_post_id,
				]
			), 'template_error'
		);
	}

	public function test_get_templates() {
		// Arrange
		$admin = $this->act_as_admin();

		$document_ids = $this->create_mock_templates( $admin );

		// Act
		$templates = self::$manager->get_templates();

		// Assert
		$ids = array_map( function ( $item ) {
			return $item['template_id'];
		}, $templates );

		$this->assertCount( 3, $templates );
		$this->assertEqualSets( $document_ids, $ids );
	}

	public function test_get_templates__only_local() {
		// Arrange
		$admin = $this->act_as_admin();

		$document_ids = $this->create_mock_templates( $admin );

		// Act
		$templates = self::$manager->get_templates( [ 'local' ] );

		// Assert
		$ids = array_map( function ( $item ) {
			return $item['template_id'];
		}, $templates );

		$this->assertCount( 1, $templates );
		$this->assertEqualSets( [ $document_ids[0] ], $ids );
	}

	public function test_get_templates__only_remote() {
		// Arrange
		$admin = $this->act_as_admin();

		$document_ids = $this->create_mock_templates( $admin );

		// Act
		$templates = self::$manager->get_templates( [ 'remote' ] );

		// Assert
		$ids = array_map( function ( $item ) {
			return $item['template_id'];
		}, $templates );

		$this->assertCount( 2, $templates );
		$this->assertEqualSets( [ $document_ids[1], $document_ids[2] ], $ids );
	}

	private function create_mock_templates( $user ) {
		// This is the indication to not go to fetch from server and go to the option.
		set_transient('elementor_remote_info_api_data_' . ELEMENTOR_VERSION, ['test' => 'test'] );

		update_option( Api::LIBRARY_OPTION_KEY, [
			'types_data' => [],
			'categories' => [],
			'templates' => [
				[
					'id' => 100,
					'title' => 'A',
					'thumbnail' => 'https://localhost/test.png',
					'author' => 'Elementor',
					'url' => 'https://localhost/url',
					'type' => 'popup',
					'subtype' => 'classic',
					'tags' => '[]',
					'menu_order' => 0,
					'popularity_index' => 100,
					'trend_index' => 100,
					'has_page_settings' => 1,
					'is_pro' => 1,
					'access_level' => 1,
					'tmpl_created' => '2020-10-10',
				],
				[
					'id' => 200,
					'title' => 'B',
					'thumbnail' => 'https://localhost/test.png',
					'author' => 'Elementor',
					'url' => 'https://localhost/url',
					'type' => 'popup',
					'subtype' => 'classic',
					'tags' => '[]',
					'menu_order' => 0,
					'popularity_index' => 100,
					'trend_index' => 100,
					'has_page_settings' => 1,
					'is_pro' => 1,
					'access_level' => 1,
					'tmpl_created' => '2020-10-10',
				]
			]
		] );

		$document = $this->factory()->documents->create_and_get([
			'document_type' => 'page',
			'post_author' => $user->ID,
			'post_status' => 'publish',
			'post_type' => Source_Local::CPT,
			'meta_input' => [
				Document::TYPE_META_KEY => 'page'
			]
		]);

		return [ $document->get_id(), 100, 200 ];
	}
}
