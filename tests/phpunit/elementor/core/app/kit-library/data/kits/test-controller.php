<?php
namespace Elementor\Tests\Phpunit\Elementor\Core\App\KitLibrary\Data\Kits;

use Elementor\Plugin;
use Elementor\Testing\Traits\Rest_Trait;
use Elementor\Testing\Elementor_Test_Base;
use Elementor\Modules\Library\User_Favorites;
use Elementor\Core\Common\Modules\Connect\Module;
use Elementor\Core\App\Modules\KitLibrary\Connect\Kit_Library;
use Elementor\Core\App\Modules\KitLibrary\Data\Kits\Controller;

class Test_Controller extends Elementor_Test_Base {
	use Rest_Trait {
		setUp as traitSetUP;
	}

	/**
	 * @var Kit_Library
	 */
	private $app_mock;

	public function setUp(  ) {
		$this->traitSetUP();

		$this->app_mock = $this->getMockBuilder( Kit_Library::class )
			->setMethods( [ 'get_all', 'get_manifest' ] )
			->getMock();

		$module_mock = $this->getMockBuilder( Module::class )
			->setMethods( [ 'get_app' ] )
			->getMock();

		$module_mock->method( 'get_app' )->willReturn( $this->app_mock );

		Plugin::$instance->common->add_component( 'connect', $module_mock );
	}

	public function test_get_items() {
		// Arrange
		$user = $this->act_as_admin();
		$this->app_mock->method( 'get_all' )->willReturn( $this->get_kits_api_mock() );

		$favorites = new User_Favorites( $user->ID );
		$favorites->add( 'elementor', 'kits', 'id_2' );

		$this->data_manager->register_controller( Controller::class );

		// Act
		$result = $this->http_get( 'kits', [ 'force' => true ] );

		// Assert
		$this->assertArrayHasKey( 'data', $result );
		$this->assertEqualSets( [
			[
				'id' => 'id_1',
				'title' => 'kit_1',
				'thumbnail_url' => 'https://localhost/image.png',
				'access_level' => 0,
				'keywords' => [ 'word', 'word2' ],
				'taxonomies' => ['a', 'b', 'c', 'd', 'e'],
				'is_favorite' => false,
				'trend_index' => 20,
				'featured_index' => 30,
				'popularity_index' => 40,
				'created_at' => '2021-05-26T22:30:39.397Z',
				'updated_at' => '2021-05-26T22:30:39.397Z',
			],
			[
				'id' => 'id_2',
				'title' => 'kit_2',
				'thumbnail_url' => 'https://localhost/image2.png',
				'access_level' => 1,
				'keywords' => [],
				'taxonomies' => ['1', '2', '3', '4', '5'],
				'is_favorite' => true,
				'trend_index' => 20,
				'featured_index' => 30,
				'popularity_index' => 40,
				'created_at' => '2021-05-26T22:30:39.397Z',
				'updated_at' => '2021-05-26T22:30:39.397Z',
			]
		], $result['data'] );
	}

	public function test_get_item() {
		// Arrange
		$this->act_as_admin();
		$this->app_mock->method( 'get_all' )->willReturn( $this->get_kits_api_mock() );
		$this->app_mock->method( 'get_manifest' )->willReturn( $this->get_manifest_api_mock() );

		$this->data_manager->register_controller( Controller::class );

		// Act
		$result = $this->http_get( "kits/id_1", [ 'force' => true ] );

		// Assert
		$this->assertArrayHasKey( 'data', $result );
		$this->assertEqualSets( [
			'id' => 'id_1',
			'title' => 'kit_1',
			'thumbnail_url' => 'https://localhost/image.png',
			'access_level' => 0,
			'keywords' => [ 'word', 'word2' ],
			'taxonomies' => ['a', 'b', 'c', 'd', 'e'],
			'is_favorite' => false,
			'trend_index' => 20,
			'featured_index' => 30,
			'popularity_index' => 40,
			'created_at' => '2021-05-26T22:30:39.397Z',
			'updated_at' => '2021-05-26T22:30:39.397Z',
			'description' => 'this is description',
			'preview_url' => 'https://localhost/site',
			'documents' => [
				[
					'id' => 10,
					'title' => 'template_1',
					'doc_type' => 'header',
					'thumbnail_url' => 'https://localhost/thumbnail_1.png',
					'preview_url' => null,
				],
				[
					'id' => 15,
					'title' => 'template_2',
					'doc_type' => 'footer',
					'thumbnail_url' => 'https://localhost/thumbnail_2.png',
					'preview_url' => null,
				],
				[
					'id' => 2,
					'title' => 'page_1',
					'doc_type' => 'wp-page',
					'thumbnail_url' => 'https://localhost/thumbnail_3.png',
					'preview_url' => 'https://localhost/page_1',
				],
				[
					'id' => 3,
					'title' => 'page_2',
					'doc_type' => 'wp-page',
					'thumbnail_url' => 'https://localhost/thumbnail_4.png',
					'preview_url' => 'https://localhost/page_2',
				],
				[
					'id' => 5,
					'title' => 'post_1',
					'doc_type' => 'wp-post',
					'thumbnail_url' => 'https://localhost/thumbnail_5.png',
					'preview_url' => 'https://localhost/post_1',
				],
			]
		], $result['data'] );
	}

	private function get_kits_api_mock() {
		return [
			(object) [
				'tags' => [
					(object) [ 'name' => 'a' ],
					(object) [ 'name' => 'b' ],
				],
				'categories' => [
					(object) [ 'name' => 'c' ],
				],
				'features' => [
					(object) [ 'name' => 'd' ],
				],
				'types' => [
					(object) [ 'name' => 'e' ],
				],
				'keywords' => [ 'word', 'word2' ],
				'_id' => 'id_1',
				'title' => 'kit_1',
				'slug' => 'kit_1',
				'thumbnail' => 'https://localhost/image.png',
				'access_level' => 0,
				'trend_index' => 20,
				'featured_index' => 30,
				'popularity_index' => 40,
				'created_at' => '2021-05-26T22:30:39.397Z',
				'updated_at' => '2021-05-26T22:30:39.397Z',
			],
			(object) [
				'tags' => [
					(object) [ 'name' => '1' ],
					(object) [ 'name' => '2' ],
				],
				'categories' => [
					(object) [ 'name' => '3' ],
				],
				'features' => [
					(object) [ 'name' => '4' ],
				],
				'types' => [
					(object) [ 'name' => '5' ],
				],
				'keywords' => [],
				'_id' => 'id_2',
				'title' => 'kit_2',
				'slug' => 'kit_2',
				'thumbnail' => 'https://localhost/image2.png',
				'access_level' => 1,
				'trend_index' => 20,
				'featured_index' => 30,
				'popularity_index' => 40,
				'created_at' => '2021-05-26T22:30:39.397Z',
				'updated_at' => '2021-05-26T22:30:39.397Z',
			],
		];
	}

	private function get_manifest_api_mock() {
		return (object) [
			'description' => 'this is description',
			'site' => 'https://localhost/site',
			'templates' => (object) [
				10 => (object) [
					'title' => 'template_1',
					'doc_type' => 'header',
					'thumbnail' => 'https://localhost/thumbnail_1.png'
				],
				15 => (object) [
					'title' => 'template_2',
					'doc_type' => 'footer',
					'thumbnail' => 'https://localhost/thumbnail_2.png'
				],
			],
			'content' => (object) [
				'page' => (object) [
					2 => (object) [
						'title' => 'page_1',
						'doc_type' => 'wp-post', // This represent a bug in the manifest.json should be replace to 'wp-page' when the bug fixed.
						'thumbnail' => 'https://localhost/thumbnail_3.png',
						'url' => 'https://localhost/page_1',
					],
					3 => (object) [
						'title' => 'page_2',
						'doc_type' => 'wp-page',
						'thumbnail' => 'https://localhost/thumbnail_4.png',
						'url' => 'https://localhost/page_2',
					],
				],
				'post' => (object) [
					5 => (object) [
						'title' => 'post_1',
						'doc_type' => 'wp-post',
						'thumbnail' => 'https://localhost/thumbnail_5.png',
						'url' => 'https://localhost/post_1',
					],
				],
			]
		];
	}
}
