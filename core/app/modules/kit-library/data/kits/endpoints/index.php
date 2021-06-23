<?php
namespace Elementor\Core\App\Modules\KitLibrary\Data\Kits\Endpoints;

use Elementor\Data\Base\Endpoint;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Index extends Endpoint {
	public function get_name() {
		return 'index';
	}

	protected function register() {
		$this->register_route(
			'',
			\WP_REST_Server::READABLE,
			function ( $request ) {
				return $this->base_callback( \WP_REST_Server::READABLE, $request, true );
			},
			[
				'force' => [
					'description' => 'Force an API request and skip the cache.',
					'required' => false,
					'default' => false,
					'type' => 'boolean',
				],
			]
		);

		$this->register_route(
			'(?P<id>[\w-]+)/',
			\WP_REST_Server::READABLE,
			function ( $request ) {
				return $this->base_callback( \WP_REST_Server::READABLE, $request, false );
			},
			[
				'id' => [
					'description' => 'Unique identifier for the object.',
					'type' => 'string',
					'required' => true,
				],
			]
		);
	}
}
