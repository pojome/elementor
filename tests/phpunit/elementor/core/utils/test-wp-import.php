<?php
namespace Elementor\Tests\Phpunit\Elementor\Core\Utils;

use Elementor\Core\Utils\ImportExport\WP_Import;
use Elementor\Testing\Elementor_Test_Base;

class Test_WP_Import extends Elementor_Test_Base {
	public function setUp() {
		parent::setUp();

		// Should remove the default kit because it is actually a post and it affect
		// the number of posts that exists.
		$this->remove_default_kit();
	}

	public function test_run() {
		// Arrange.
		$importer = new WP_Import( __DIR__ . '/mock/fresh-wordpress-database.xml' );

		// Act.
		$result = $importer->run();
		$posts = get_posts( [
			'numberposts' => -1,
			'post_status' => 'any',
			'post_type' => get_post_types('', 'names'),
		] );

		// Assert.
		$this->assertEquals( [
			'status' => 'success',
			'errors' => [],
			'summary' => [
				'categories' => 0,
				'tags' => 0,
				'terms' => 0,
				'posts' => [
					'failed' => [],
					'succeed' => [
						1 => 1,
						2 => 2,
						3 => 3,
					],
				],
			],
		], $result );
		$this->assertCount( 3, $posts );
	}
}
