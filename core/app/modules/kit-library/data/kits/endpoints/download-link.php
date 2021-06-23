<?php
namespace Elementor\Core\App\Modules\KitLibrary\Data\Kits\Endpoints;

use Elementor\Data\Base\Endpoint;
use Elementor\Core\App\Modules\KitLibrary\Data\Kits\Controller;
use Elementor\Core\App\Modules\KitLibrary\Data\Exceptions\Wp_Error_Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * @property Controller $controller
 */
class Download_Link extends Endpoint {
	public function get_name() {
		return 'download-link';
	}

	protected function register() {
		$this->register_route(
			'/(?P<id>[\w-]+)/',
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

	public function get_item( $id, $request ) {
		$repository = $this->controller->get_repository();

		try {
			$data = $repository->get_download_link( $id );
		} catch ( Wp_Error_Exception $exception ) {
			return new \WP_Error( $exception->getCode(), $exception->getMessage(), [ 'status' => $exception->getCode() ] );
		} catch ( \Exception $exception ) {
			return new \WP_Error( 'server_error', __( 'Something went wrong.', 'elementor' ), [ 'status' => 500 ] );
		}

		return [
			'data' => $data,
			'meta' => [
				'nonce' => wp_create_nonce( 'kit-library-import' ),
			],
		];
	}
}
