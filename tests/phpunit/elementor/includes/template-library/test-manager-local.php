<?php
namespace Elementor\Testing\Includes\TemplateLibrary;

use Elementor\Plugin;
use Elementor\TemplateLibrary\Manager;
use Elementor\Testing\Elementor_Test_Base;

class Elementor_Test_Manager_Local extends Elementor_Test_Base {

	/**
	 * @var Manager
	 */
	protected static $manager;
	private $fake_post_id = '123';

	public static function setUpBeforeClass() {
		self::$manager = self::elementor()->templates_manager;
	}

	public function setUp() {
		parent::setUp();
		wp_set_current_user( $this->factory()->get_administrator_user()->ID );
	}

	public function test_should_return_registered_sources() {
		$this->assertEquals( self::$manager->get_registered_sources()['local'], new \Elementor\TemplateLibrary\Source_Local() );
	}

	public function test_should_return_source() {
		$this->assertEquals( self::$manager->get_source( 'local' ), new \Elementor\TemplateLibrary\Source_Local() );
	}

	public function test_should_return_wp_error_save_error_from_save_template() {
		wp_set_current_user( $this->factory()->get_subscriber_user()->ID );
		$this->assertWPError(
			self::$manager->save_template(
				[
					'post_id' => $this->fake_post_id,
					'source' => 'local',
					'content' => 'content',
					'type' => 'comment',
				]
			), 'save_error'
		);
	}

	public function test_should_return_template_data_from_save_template() {
		wp_set_current_user( $this->factory()->create_and_get_administrator_user()->ID );
		$template_data = [
			'post_id' => $this->factory()->get_default_post()->ID,
			'source' => 'local',
			'content' => 'content',
			'type' => 'page',
		];

		$remote_remote = [
			'template_id',
			'source',
			'type',
			'title',
			'thumbnail',
			'hasPageSettings',
			'tags',
			'url',
		];
		$saved_template = self::$manager->save_template( $template_data );

		$this->assert_array_have_keys( $remote_remote, $saved_template );
	}


	public function test_should_return_wp_error_arguments_not_specified_from_update_template() {
		$this->assertWPError( self::$manager->update_template( [ 'post_id' => $this->fake_post_id ] ), 'arguments_not_specified' );
	}


	public function test_should_return_wp_error_template_error_from_update_template() {
		$this->assertWPError(
			self::$manager->update_template(
				[
					'source' => 'content',
					'content' => 'content',
					'type' => 'page',
				]
			), 'template_error'
		);
	}

	public function test_should_return_wp_error_save_error_from_update_template() {
		wp_set_current_user( $this->factory()->get_subscriber_user()->ID );
		$this->assertWPError(
			self::$manager->update_template(
				[
					'source' => 'local',
					'content' => 'content',
					'type' => 'comment',
					'id' => $this->fake_post_id,
				]
			), 'save_error'
		);
	}

	/**
	 *
	 */
	public function test_should_return_template_data_from_update_template() {
		wp_set_current_user( $this->factory()->create_and_get_administrator_user()->ID );
		$post_id = $this->factory()->create_and_get_default_post()->ID;
		$template_data = [
			'source' => 'local',
			'content' => 'content',
			'type' => 'post',
			'id' => $post_id,
		];

		$remote_remote = [
			'template_id',
			'source',
			'type',
			'title',
			'thumbnail',
			'author',
			'hasPageSettings',
			'tags',
			'url',
		];
		$updated_template = self::$manager->update_template( $template_data );

		$this->assert_array_have_keys( $remote_remote, $updated_template );
	}

	/**
	 * @covers \Elementor\TemplateLibrary\Manager::get_template_data()
	 */
	public function test_should_return_data_from_get_template_data() {
		$ret = self::$manager->get_template_data(
			[
				'source' => 'local',
				'template_id' => $this->fake_post_id,
			]
		);

		$this->assertEquals( $ret, [ 'content' => [] ] );
	}

	public function test_get_data__document_without_data() {
		// Arrange
		$is_edit_mode = Plugin::$instance->editor->is_edit_mode();

		Plugin::$instance->editor->set_edit_mode( false );

		$post = $this->factory()->create_and_get_custom_post( [
			'meta_input' => [
				'_elementor_data' => [],
			],
		] );

		// Act
		$result = Plugin::$instance->templates_manager->get_source( 'local' )->get_data( [
			'template_id' => $post->ID,
		] );

		// Assert
		$this->assertArrayHasKey( 'content', $result );
		$this->assertTrue( is_array( $result['content'] ) );
		$this->assertEmpty( $result['content'] );

		Plugin::$instance->editor->set_edit_mode( $is_edit_mode );
	}

	public function test_get_data() {
		// Arrange
		$post = $this->factory()->create_and_get_custom_post( [
			'meta_input' => [
				'_elementor_data' => [
					[
						'id' => 'test',
						'elType' => 'section',
					],
				],
			],
		] );

		// Act
		$result = Plugin::$instance->templates_manager->get_source( 'local' )->get_data( [
			'template_id' => $post->ID,
		] );

		// Assert
		$this->assertArrayHasKey( 'content', $result );
		$this->assert_array_have_keys( [ 'id', 'elType' ], $result['content'][0] );
		$this->assert_array_not_have_keys(
			[ 'settings', 'elements', 'isInner' ],
			$result['content'][0],
			'should NOT have those keys if display is not exists in the args'
		);
		$this->assertEquals( 'section', $result['content'][0]['elType'] );
		$this->assertNotEquals( 'test', $result['content'][0]['id'], 'The id should be regenerate in get_data method' );

		// Act
		$result = Plugin::$instance->templates_manager->get_source( 'local' )->get_data( [
			'template_id' => $post->ID,
			'display' => true,
		] );

		// Assert
		$this->assertArrayHasKey( 'content', $result );
		$this->assert_array_have_keys( [ 'id', 'elType' ], $result['content'][0] );
		$this->assert_array_have_keys(
			[ 'settings', 'elements', 'isInner' ],
			$result['content'][0],
			'SHOULD have those keys if display is exists in the args'
		);
		$this->assertEquals( 'section', $result['content'][0]['elType'] );
		$this->assertNotEquals( 'test', $result['content'][0]['id'], 'The id should be regenerate in get_data method' );
	}
}
