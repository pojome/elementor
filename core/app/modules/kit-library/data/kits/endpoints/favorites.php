<?php
namespace Elementor\Core\App\Modules\KitLibrary\Data\Kits\Endpoints;

use Elementor\Core\App\Modules\KitLibrary\Data\Kits\Controller;
use Elementor\Core\App\Modules\KitLibrary\Data\Exceptions\Wp_Error_Exception;
use Elementor\Data\Base\Endpoint;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * @property Controller $controller
 */
class Favorites extends Endpoint {
	public function get_name() {
		return 'favorites';
	}

	protected function register() {
		$this->register_route(
			'/(?P<id>[\w-]+)/',
			\WP_REST_Server::CREATABLE,
			function ( $request ) {
				return $this->base_callback( \WP_REST_Server::CREATABLE, $request, false );
			},
			[
				'id' => [
					'description' => 'Unique identifier for the object.',
					'type' => 'string',
					'required' => true,
				],
			]
		);

		$this->register_route(
			'/(?P<id>[\w-]+)/',
			\WP_REST_Server::DELETABLE,
			function ( $request ) {
				return $this->base_callback( \WP_REST_Server::DELETABLE, $request, false );
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

	/**
	 * @param string           $id
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function create_item( $id, $request ) {
		$repository = $this->controller->get_repository();

		try {
			$kit = $repository->add_to_favorites( $id );
		} catch ( Wp_Error_Exception $exception ) {
			return new \WP_Error( $exception->getCode(), $exception->getMessage(), [ 'status' => $exception->getCode() ] );
		} catch ( \Exception $exception ) {
			return new \WP_Error( 'server_error', __( 'Something went wrong.', 'elementor' ), [ 'status' => 500 ] );
		}

		return new \WP_REST_Response( [
			'data' => $kit,
		] );
	}

	/**
	 * @param string           $id
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_item( $id, $request ) {
		$repository = $this->controller->get_repository();

		try {
			$kit = $repository->remove_from_favorites( $id );
		} catch ( Wp_Error_Exception $exception ) {
			return new \WP_Error( $exception->getCode(), $exception->getMessage(), [ 'status' => $exception->getCode() ] );
		} catch ( \Exception $exception ) {
			return new \WP_Error( 'server_error', __( 'Something went wrong.', 'elementor' ), [ 'status' => 500 ] );
		}

		return new \WP_REST_Response( [
			'data' => $kit,
		] );
	}
}
