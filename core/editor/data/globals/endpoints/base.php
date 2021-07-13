<?php
namespace Elementor\Core\Editor\Data\Globals\Endpoints;

use Elementor\Core\Utils\Exceptions;
use Elementor\Data\V2\Base\Endpoint;
use Elementor\Plugin;

abstract class Base extends Endpoint {
	protected function register() {
		parent::register();

		$args = [
			'id_arg_type_regex' => '[\w]+',
		];

		$this->register_item_route( \WP_REST_Server::READABLE, $args );
		$this->register_item_route( \WP_REST_Server::CREATABLE, $args );
		$this->register_item_route( \WP_REST_Server::DELETABLE, $args );
	}

	public function get_items( $request ) {
		return $this->get_kit_items();
	}

	public function get_item( $id, $request ) {
		$items = $this->get_kit_items();

		if ( ! isset( $items[ $id ] ) ) {
			return new \WP_Error(
				'global_not_found',
				__( 'The Global value you are trying to use is not available.', 'elementor' ),
				[ 'status' => Exceptions::NOT_FOUND ]
			);
		}

		return $items[ $id ];
	}

	public function create_item( $id, $request ) {
		$item = $request->get_body_params();

		if ( ! isset( $item['title'] ) ) {
			return new \WP_Error( 'invalid_title', 'Invalid title' );
		}

		$kit = Plugin::$instance->kits_manager->get_active_kit();

		$item['id'] = $id;

		$db_item = $this->convert_db_format( $item );

		$kit->add_repeater_row( 'custom_' . $this->get_name(), $db_item );

		return $item;
	}

	abstract protected function get_kit_items();

	/**
	 * @param array $item frontend format.
	 * @return array backend format.
	 */
	abstract protected function convert_db_format( $item );
}
