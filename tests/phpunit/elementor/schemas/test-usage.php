<?php
namespace Elementor\Tests\Phpunit\Schemas;

use Elementor\Plugin;
use Elementor\Testing\Base_Schema;
use Elementor\Testing\Factories\Documents;
use Elementor\Tests\Phpunit\Elementor\Modules\Usage\DynamicTags\Link;
use Elementor\Tests\Phpunit\Elementor\Modules\Usage\DynamicTags\Title;
use Elementor\Tracker;
use JsonSchema\Exception\ValidationException;

class Test_Usage extends Base_Schema {
	/**
	 * @var string
	 */
	private $schema_file = __DIR__  . '/../../schemas/usage.json';

	public function test__ensure_clean_is_valid() {
		// Arrange.
		$this->generate_plugins_mock();

		// Act + Assert.
		$this->assertTrue( $this->validate_current_tracking_data_against_schema( $this->schema_file ) );
	}

	public function test__ensure_invalid_exception() {
		// Arrange.
		$this->expectException( ValidationException::class );

		// Assert.
		$this->validate_against_schema( [], $this->schema_file );
	}

	public function test__ensure_all_objects_have_no_additional_properties() {
		// Assert.
		$this->assert_schema_has_no_additional_properties( $this->schema_file );
	}

	// The aim of the test is to fill all the possible tracking 'usage' data.
	public function test__ensure_tracking_data_with_usage_full_mock() {
		$this->generate_plugins_mock();

		// Arrange - Posts
		$this->factory()->create_post();

		// Library
		$this->factory()->documents->create_and_get_template( 'not-supported' );

		// Elements
		$this->factory()->documents->publish_and_get();

		// Elements with dynamic
		Plugin::$instance->dynamic_tags->register_tag( new Title() );
		Plugin::$instance->dynamic_tags->register_tag( new Link() );

		$this->factory()->documents->publish_and_get( [
			'meta_input' => [
				'_elementor_data' => Documents::DOCUMENT_DATA_MOCK_WITH_DYNAMIC_WIDGET,
			]
		] );

		// Act.
		$tracking_data = Tracker::get_tracking_data();
		$usage = $tracking_data[ 'usages' ];

		// Assert - Ensure tracking data have arranged data.
		$this->assertArrayHaveKeys( ['publish'], $usage['posts']['post'] );
		$this->assertArrayHaveKeys( ['not-supported'], $usage['library'] );
		$this->assertArrayHaveKeys( ['wp-post'], $usage['elements'] );
		$this->assertCount( 4, $usage['elements']['wp-post'] );
		$this->assertArrayHaveKeys( ['__dynamic__'], $usage['elements']['wp-post']['heading']['controls']['general'] );

		// Assert - Validate schema.
		$this->assertTrue( $this->validate_against_schema( $tracking_data, $this->schema_file ) );
	}
}
