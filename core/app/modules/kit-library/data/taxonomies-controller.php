<?php
namespace Elementor\Core\App\Modules\KitLibrary\Data;

use Elementor\Data\Base\Controller as Controller_Base;
use Elementor\Core\App\Modules\KitLibrary\Data\Exceptions\Api_Response_Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Taxonomies_Controller extends Controller_Base {
	/**
	 * @var Repository
	 */
	private $repository;

	public function get_name() {
		return 'kit-taxonomies';
	}

	public function register_endpoints() {
		//
	}

	/**
	 * Register internal endpoint.
	 */
	protected function register_internal_endpoints() {
		// Register as internal to remove the default endpoint generated by the base controller.
		$this->register_endpoint( Endpoints\Taxonomies_Index::class );
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		try {
			$data = $this->repository->get_taxonomies( $request->get_param( 'force' ) );
		} catch ( Api_Response_Exception $exception ) {
			return new \WP_Error( 'http_response_error', __( 'Connection error.', 'elementor' ) );
		} catch ( \Exception $exception ) {
			return new \WP_Error( 'server_error', __( 'Something went wrong.', 'elementor' ) );
		}

		return new \WP_REST_Response( [
			'data' => $data->all(),
		] );
	}

	/**
	 * Taxonomies_Controller constructor.
	 *
	 * @param Repository $repository
	 */
	public function __construct( Repository $repository ) {
		parent::__construct();

		$this->repository = $repository;
	}
}
