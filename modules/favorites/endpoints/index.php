<?php
namespace Elementor\Modules\Favorites\Endpoints;

use Elementor\Modules\Favorites\Module;
use Elementor\Data\Base\Endpoint;
use Elementor\Plugin;

class Index extends Endpoint {

	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return 'index';
	}

	/**
	 * @inheritDoc
	 */
	protected function register() {
		$this->register_route(
			'(?P<id>[\w]+)',
			\WP_REST_Server::CREATABLE,
			function ( $request ) {
				return $this->base_callback( \WP_REST_Server::CREATABLE, $request );
			},
			[
				'id' => [
					'description' => 'Type of favorites.',
					'type'        => 'string',
					'required'    => true,
				],
				'favorite' => [
					'description' => 'The favorite slug to create.',
					'type'        => 'string',
					'required'    => true,
				],
			]
		);

		$this->register_route(
			'(?P<id>[\w]+)',
			\WP_REST_Server::DELETABLE,
			function ( $request ) {
				return $this->base_callback( \WP_REST_Server::DELETABLE, $request );
			},
			[
				'id' => [
					'description' => 'Type of favorites.',
					'type'        => 'string',
					'required'    => true,
				],
				'favorite' => [
					'description' => 'The favorite slug to delete.',
					'type'        => 'string',
					'required'    => true,
				],
			]
		);
	}

	/**
	 * @inheritDoc
	 */
	public function create_item( $type, $request ) {
		$module = $this->get_module();
		$favorite = $request->get_query_params()[ 'favorite' ];

		$module->update( $type, $favorite, $module::ACTION_MERGE );

		return $module->get( $type );
	}

	/**
	 * @inheritDoc
	 */
	public function delete_item( $type, $request ) {
		$module = $this->get_module();
		$favorite = $request->get_query_params()[ 'favorite' ];

		$module->update( $type, $favorite, $module::ACTION_DELETE );

		return $module->get( $type );
	}

	/**
	 * Get the favorites module instance.
	 *
	 * @return Module
	 */
	protected function get_module() {
		return Plugin::instance()->modules_manager->get_modules( 'favorites' );
	}
}
