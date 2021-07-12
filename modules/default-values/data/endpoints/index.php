<?php
namespace Elementor\Modules\DefaultValues\Data\Endpoints;

use Elementor\Plugin;
use Elementor\Data\Base\Endpoint;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Index extends Endpoint {
	public function get_name() {
		return 'index';
	}

	protected function register() {
		$type_validate_callback = function ( $param ) {
			$types = array_merge(
				array_keys( Plugin::$instance->elements_manager->get_element_types() ),
				array_keys( Plugin::$instance->widgets_manager->get_widget_types() )
			);

			return in_array( $param, $types, true );
		};

		$this->register_route(
			'',
			\WP_REST_Server::READABLE,
			function ( $request ) {
				return $this->base_callback( \WP_REST_Server::READABLE, $request, true );
			},
			[]
		);

		$this->register_route(
			'(?P<type>[\w]+)/',
			\WP_REST_Server::CREATABLE,
			function ( $request ) {
				return $this->base_callback( \WP_REST_Server::CREATABLE, $request, false );
			},
			[
				'type' => [
					'description' => 'Unique identifier for the object.',
					'required' => true,
					'type' => 'string',
					'validate_callback' => $type_validate_callback,
				],
			]
		);

		$this->register_route(
			'(?P<type>[\w]+)/',
			\WP_REST_Server::DELETABLE,
			function ( $request ) {
				return $this->base_callback( \WP_REST_Server::DELETABLE, $request, false );
			},
			[
				'type' => [
					'description' => 'Unique identifier for the object.',
					'required' => true,
					'type' => 'string',
					'validate_callback' => $type_validate_callback,
				],
			]
		);
	}
}
