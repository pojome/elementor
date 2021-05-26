<?php
namespace Elementor\Core\Editor\Data\Favorites\Endpoints;

use Elementor\Core\Editor\Data\Favorites\Manager;
use Elementor\Data\Base\Endpoint;
use Elementor\Plugin;
use WP_REST_Server;

class Index extends Endpoint {

	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return 'index';
	}

	protected function register() {
		$this->register_item_route( WP_REST_Server::READABLE, [], '' );
		$this->register_item_route( WP_REST_Server::EDITABLE, [], '' );
		$this->register_item_route( WP_REST_Server::DELETABLE, [], '' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_item( $key, $request ) {
		return $this->get_manager()->get( $key );
	}

	/**
	 * @inheritDoc
	 */
	public function update_item( $key, $request ) {
		$manager = $this->get_manager();
		$data = explode( ',', $request->get_query_params()['data'] );

		$manager->update( $key, $data, $manager::ACTION_MERGE );

		return $manager->get( $key );
	}

	/**
	 * @inheritDoc
	 */
	public function delete_item( $key, $request ) {
		$manager = $this->get_manager();
		$data = explode( ',', $request->get_query_params()['data'] );

		$manager->update( $key, $data, $manager::ACTION_DELETE );

		return $manager->get( $key );
	}

	/**
	 * Gets the favorites manager instance.
	 *
	 * @return Manager
	 */
	protected function get_manager() {
		return Plugin::instance()->editor->favorites;
	}
}
