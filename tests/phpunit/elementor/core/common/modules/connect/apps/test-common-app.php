<?php
namespace Elementor\Tests\Phpunit\Elementor\Core\Common\Modules\Connect\Apps;

use Elementor\Core\Utils\Http;
use Elementor\Testing\Elementor_Test_Base;
use Elementor\Core\Common\Modules\Connect\Apps\Base_App;
use Elementor\Tests\Phpunit\Elementor\Core\Common\Modules\Connect\Apps\Mock\Mock_App;
use Elementor\Tests\Phpunit\Elementor\Core\Common\Modules\Connect\Apps\Mock\Mock_App_Multiple_Urls;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Test_Common_App extends Elementor_Test_Base {
	private $http_stub;

	/**
	 * @var Mock_App
	 */
	private $app_stub;

	/**
	 * @var Mock_App_Multiple_Urls
	 */
	private $app_stub_multiple_urls;

	public function setUp() {
		parent::setUp();

		require_once __DIR__ . '/mock/mock-app.php';
		require_once __DIR__ . '/mock/mock-app-multiple-urls.php';

		$this->http_stub = $this->getMockBuilder( Http::class )
			->setMethods( [ 'request' ] )
			->getMock();

		$this->app_stub = new Mock_App();
		$this->app_stub->set_http( $this->http_stub );

		$this->app_stub_multiple_urls = new Mock_App_Multiple_Urls();
		$this->app_stub_multiple_urls->set_http( $this->http_stub );
	}

	public function test_http_request() {
		// Arrange
		$endpoint = 'test/1';

		$this->http_stub
			->expects( $this->once() )
			->method( 'request' )
			->with(
				// Assert that those params sent to the api (similar to `expect`)
				$this->equalTo( Mock_App::BASE_URL . '/' . $endpoint ),
				$this->equalTo( [
					'headers' => [],
					'method' => 'POST',
					'timeout' => 10,
					'body' => [
						'a' => 1,
					],
				] )
			)
			->willReturn( [
				'response' => [
					'code' => 200,
				],
				'body' => '{"status": "success"}',
			] );

		// Act
		$result = $this->app_stub->proxy_http_request( 'POST', $endpoint, [
			'body' => [
				'a' => 1,
			]
		] );

		// Assert
		$this->assertEquals( (object) [ 'status' => 'success' ], $result );
	}

	public function test_http_request__return_error_when_not_response_code() {
		// Arrange
		$this->http_stub
			->expects( $this->once() )
			->method( 'request' )
			->willReturn( [
			    'response' => [
			        'code' => 0,
			    ],
			    'body' => '{"status": "success"}',
			] );

		// Act
		$result = $this->app_stub->proxy_http_request( 'POST', 'test' );

		// Assert
		$this->assertInstanceOf( \WP_Error::class, $result );
	}

	public function test_http_request__server_sent_success_without_body() {
		// Arrange
		$this->http_stub
			->expects( $this->once() )
			->method( 'request' )
			->willReturn( [
			    'response' => [
			        'code' => 200,
			    ],
			    'body' => 'null',
			] );

		// Act
		$result = $this->app_stub->proxy_http_request( 'POST', 'test' );

		// Assert
		// When body of the response is string with 'null' it convert it to `true` and after json_encode it became `1`
		$this->assertEquals( 1, $result );
	}

	public function test_http_request__wrong_server_response() {
		// Arrange
		$this->http_stub
			->expects( $this->once() )
			->method( 'request' )
			->willReturn( [
			    'response' => [
			        'code' => 200,
			    ],
			    'body' => 'false',
			] );

		// Act
		$result = $this->app_stub->proxy_http_request( 'POST', 'test' );

		// Assert
		$this->assertInstanceOf( \WP_Error::class, $result );
	}

	public function test_http_request__error_from_server() {
		// Arrange
		$this->http_stub
			->expects( $this->once() )
			->method( 'request' )
			->willReturn( [
			    'response' => [
			        'code' => 422,
			    ],
			    'body' => '{"message": "my test error"}',
			] );

		// Act
		$result = $this->app_stub->proxy_http_request( 'POST', 'test' );

		// Assert
		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEqualSets( [
			'422' => ['my test error']
		], $result->errors );
	}

	public function test_http_request__as_array() {
		// Arrange
		$this->http_stub
			->expects( $this->once() )
			->method( 'request' )
			->willReturn( [
			    'response' => [
			        'code' => 200,
			    ],
			    'body' => '{"message": "as array"}',
			] );

		// Act
		$result = $this->app_stub->proxy_http_request( 'POST', 'test', [], [ 'return_type' => Base_App::HTTP_RETURN_TYPE_ARRAY ] );

		// Assert
		$this->assertTrue( is_array( $result ) );
		$this->assertEqualSets( [ 'message' => 'as array' ], $result );
	}

	public function test_http_request__with_connect() {
		// Arrange
		$endpoint = 'test/1';
		$user = $this->get_connected_user();

		$this->http_stub
			->expects( $this->once() )
			->method( 'request' )
			->with(
				// Assert that those params sent to the api (similar to `expect`)
			    $this->equalTo( Mock_App::BASE_URL . '/' . $endpoint ),
			    $this->equalTo( [
					'headers' => [
						'access-token' => 'access_token_test',
						'app' => 'mock-app',
						'client-id' => 'client_id_test',
						'endpoint' => $endpoint,
						'home-url' => 'http://example.org/',
						'local-id' => $user->ID,
						'site-key' => 'site_key_test',
						'X-Elementor-Signature' => '2f5dccc6fd53fa38f80d18fbdd9e02a5c0e1dde77fe012e5e11679a9ff0a6faa',
						'a' => '1',
					],
					'method' => 'PUT',
					'timeout' => 30,
					'body' => [
					    'b' => '2',
					],
			    ] )
			)
			->willReturn( [
			    'response' => [
			        'code' => 200,
			    ],
			    'body' => '{"status": "success"}',
			] );

		// Act
		$result = $this->app_stub->proxy_http_request( 'PUT', $endpoint, [
			'timeout' => 30,
			'headers' => [
				'a' => '1',
			],
			'body' => [
				'b' => '2',
			]
		] );

		// Assert
		$this->assertEquals( (object) [ 'status' => 'success' ], $result );
	}

	public function test_http_request__multi_urls_2_failed_last_success() {
		// Arrange
		$endpoint = 'test/1';

		$this->http_stub
			->expects( $this->exactly( 3 ) )
			->method( 'request' )
			->withConsecutive(
				[
					$this->equalTo( Mock_App_Multiple_Urls::BASE_URL . '/' . $endpoint ),
					$this->equalTo( [
						'headers' => [],
						'method' => 'POST',
						'timeout' => 10,
						'body' => [
							'a' => 1,
						],
					] )
				],
				[
					$this->equalTo( Mock_App_Multiple_Urls::FAllBACK_URL . '/' . $endpoint ),
					$this->equalTo( [
						'headers' => [],
						'method' => 'POST',
						'timeout' => 10,
						'body' => [
							'a' => 1,
						],
					] )
				],
				[
					$this->equalTo( Mock_App_Multiple_Urls::FAllBACK2_URL . '/' . $endpoint ),
					$this->equalTo( [
						'headers' => [],
						'method' => 'POST',
						'timeout' => 10,
						'body' => [
							'a' => 1,
						],
					] )
				]
			)
			->willReturnOnConsecutiveCalls(
				[ 'response' => [ 'code' => 500 ] ],
				new \WP_Error( 'Some error' ),
				[ 'response' => [ 'code' => 200 ],  'body' => '{"status": "success"}' ]
			);

		// Act
		$result = $this->app_stub_multiple_urls->proxy_http_request( 'POST', $endpoint, [
			'body' => [
				'a' => 1,
			]
		] );

		// Assert
		$this->assertEquals( (object) [ 'status' => 'success' ], $result );
	}

	public function test_http_request__multi_urls_first_succeed() {
		// Arrange
		$endpoint = 'test/1';

		$this->http_stub
			->expects( $this->exactly( 1 ) )
			->method( 'request' )
			->withConsecutive(
				[
					$this->equalTo( Mock_App_Multiple_Urls::BASE_URL . '/' . $endpoint ),
					$this->equalTo( [
						'headers' => [],
						'method' => 'POST',
						'timeout' => 10,
						'body' => [
							'a' => 1,
						],
					] )
				]
			)
			->willReturnOnConsecutiveCalls(
				[ 'response' => [ 'code' => 200 ],  'body' => '{"status": "success"}' ]
			);

		// Act
		$result = $this->app_stub_multiple_urls->proxy_http_request( 'POST', $endpoint, [
			'body' => [
				'a' => 1,
			]
		] );

		// Assert
		$this->assertEquals( (object) [ 'status' => 'success' ], $result );
	}

	public function test_http_request__all_failed() {
		// Arrange
		$endpoint = 'test/1';

		$this->http_stub
			->expects( $this->exactly( 3 ) )
			->method( 'request' )
			->withConsecutive(
				[
					$this->equalTo( Mock_App_Multiple_Urls::BASE_URL . '/' . $endpoint ),
					$this->equalTo( [
						'headers' => [],
						'method' => 'POST',
						'timeout' => 10,
						'body' => [
							'a' => 1,
						],
					] )
				],
				[
					$this->equalTo( Mock_App_Multiple_Urls::FAllBACK_URL . '/' . $endpoint ),
					$this->equalTo( [
						'headers' => [],
						'method' => 'POST',
						'timeout' => 10,
						'body' => [
							'a' => 1,
						],
					] )
				],
				[
					$this->equalTo( Mock_App_Multiple_Urls::FAllBACK2_URL . '/' . $endpoint ),
					$this->equalTo( [
						'headers' => [],
						'method' => 'POST',
						'timeout' => 10,
						'body' => [
							'a' => 1,
						],
					] )
				]
			)
			->willReturnOnConsecutiveCalls(
				[ 'response' => [ 'code' => 500 ] ],
				new \WP_Error( 'Some error' ),
				[ 'response' => [ 'code' => 500 ],  'body' => '{"status": "failed", "message":"error message"}' ]
			);

		// Act
		$result = $this->app_stub_multiple_urls->proxy_http_request( 'POST', $endpoint, [
			'body' => [
				'a' => 1,
			]
		] );

		// Assert
		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEqualSets( [
			'500' => ['error message']
		], $result->errors );
	}

	public function test_request() {
		// Arrange
		$endpoint = 'test';
		$user = $this->get_connected_user();

		$this->http_stub
			->expects( $this->once() )
			->method( 'request' )
			->with(
				// Assert that those params sent to the api (similar to `expect`)
			    $this->equalTo( Mock_App::BASE_URL . '/' . $endpoint ),
			    $this->equalTo( [
					'headers' => [
						'access-token' => 'access_token_test',
						'app' => 'mock-app',
						'client-id' => 'client_id_test',
						'endpoint' => $endpoint,
						'home-url' => 'http://example.org/',
						'local-id' => $user->ID,
						'site-key' => 'site_key_test',
						'X-Elementor-Signature' => '6af1f7228a62f4efbbc6f4382dff33bcad4367328c86036dc69f40b175189220',
					],
					'method' => 'POST',
					'timeout' => 25,
					'body' => [
						'app' => 'mock-app',
						'access_token' => 'access_token_test',
						'client_id' => 'client_id_test',
						'local_id' => $user->ID,
						'site_key' => 'site_key_test',
						'home_url' => 'http://example.org/',
						'a' => '1',
						'b' => '2',
					],
			    ] )
			)
			->willReturn( [
			    'response' => [
			        'code' => 200,
			    ],
			    'body' => '{"status": "success"}',
			] );

		// Act
		$result = $this->app_stub->proxy_request( $endpoint, [
			'a' => '1',
			'b' => '2',
		] );

		// Assert
		$this->assertEquals( (object) [ 'status' => 'success' ], $result );
	}

	private function get_connected_user() {
		global $wpdb;

		$user = $this->act_as_admin();
		$user_test_id = 9999999;

		// An hack to make sure the id of the user is known to make sure the generated signature is the always the same for the tests.
		$wpdb->update( $wpdb->users, [ 'id' => $user_test_id ], [ 'id' => $user->ID ] );

		$user = get_user_by( 'id', $user_test_id );
		wp_set_current_user( $user_test_id );

		update_user_option( $user_test_id, 'elementor_connect_common_data', [
			'client_id' => 'client_id_test',
			'auth_secret' => 'auth_secret_test',
			'access_token' => 'access_token_test',
			'access_token_secret' => 'access_token_secret_test',
			'token_type' => 'bearer',
			'user' => [
				'email' => 'test@local.test',
			]
		] );

		update_option( 'elementor_connect_site_key', 'site_key_test' );

		return $user;
	}
}
