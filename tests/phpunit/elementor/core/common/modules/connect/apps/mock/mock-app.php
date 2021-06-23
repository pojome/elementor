<?php
namespace Elementor\Tests\Phpunit\Elementor\Core\Common\Modules\Connect\Apps\Mock;

use Elementor\Core\Common\Modules\Connect\Apps\Common_App;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Mock_App extends Common_App {
	const BASE_URL = 'localhost';

	/**
	 * Mock_App constructor.
	 */
	public function __construct() {
		parent::__construct();

		// Make sure common_data is empty.
		// it should not be shared between tests.
		self::$common_data = null;
	}


	protected function get_slug() {
		return 'mock-app';
	}

	/**
	 * Proxy to http request to allow test it.
	 *
	 * @param       $method
	 * @param       $endpoint
	 * @param array $args
	 * @param array $options
	 *
	 * @return mixed|\WP_Error
	 */
	public function proxy_http_request( $method, $endpoint, $args = [], $options = [] ) {
		return $this->http_request( $method, $endpoint, $args, $options );
	}

	/**
	 * Proxy to request to allow test it.
	 *
	 * @param       $action
	 * @param array $request_body
	 * @param false $as_array
	 *
	 * @return mixed|\WP_Error
	 */
	public function proxy_request( $action, $request_body = [], $as_array = false ) {
		return $this->request( $action, $request_body, $as_array );
	}

	protected function get_api_url() {
		return static::BASE_URL;
	}


	/**
	 * Set WP_Http - pass a mock for testing.
	 *
	 * @param $http_service
	 */
	public function set_http( $http_service ) {
		$this->http = $http_service;
	}

	protected function update_settings() {
		//
	}
}
