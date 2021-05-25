<?php
namespace Elementor\Core\App\Modules\KitLibrary\Data\Taxonomies\Endpoints;

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
	}
}
