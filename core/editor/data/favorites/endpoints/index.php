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
		$body = $request->get_body_params();

		$manager->update( $key, $body['data'], $body['action'] );

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
