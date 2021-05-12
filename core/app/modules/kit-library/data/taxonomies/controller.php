<?php
namespace Elementor\Core\App\Modules\KitLibrary\Data\Taxonomies;

use Elementor\Data\Base\Controller as Controller_Base;
use Elementor\Core\App\Modules\KitLibrary\Data\Repository;
use Elementor\Core\App\Modules\KitLibrary\Data\Exceptions\Wp_Error_Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Controller extends Controller_Base {
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
		$this->register_endpoint( Endpoints\Index::class );
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|array
	 */
	public function get_items( $request ) {
		try {
			$data = $this->repository->get_taxonomies( $request->get_param( 'force' ) );
		} catch ( Wp_Error_Exception $exception ) {
			return new \WP_Error( $exception->getCode(), $exception->getMessage(), [ 'status' => $exception->getCode() ] );
		} catch ( \Exception $exception ) {
			return new \WP_Error( 'server_error', __( 'Something went wrong.', 'elementor' ), [ 'status' => 500 ] );
		}

		return [
			'data' => $data->all(),
		];
	}

	/**
	 * Taxonomies_Controller constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'rest_api_init', function () {
			$this->repository = new Repository();
		} );
	}
}
