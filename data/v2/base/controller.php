<?php
namespace Elementor\Data\V2\Base;

use Elementor\Data\V2\Manager;
use WP_REST_Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * TODO: Utilize 'WP_REST_Controller' as much as possible.
 */
abstract class Controller extends WP_REST_Controller {
	/**
	 * Loaded endpoint(s).
	 *
	 * @var \Elementor\Data\V2\Base\Endpoint[]
	 */
	public $endpoints = [];

	/**
	 * Index endpoint.
	 *
	 * @var \Elementor\Data\V2\Base\Endpoint\Index
	 */
	public $index_endpoint = null;

	/**
	 * Loaded processor(s).
	 *
	 * @var \Elementor\Data\V2\Base\Processor[][]
	 */
	public $processors = [];

	/**
	 * @var \Elementor\Data\V2\Base\Controller|null
	 */
	private $parent = null;

	/**
	 * @var \Elementor\Data\V2\Base\Controller[]
	 */
	private $sub_controllers = [];

	/**
	 * Controller constructor.
	 *
	 * Register endpoints on 'rest_api_init'.
	 *
	 */
	public function __construct() {
		$this->namespace = Manager::ROOT_NAMESPACE . '/v' . Manager::VERSION;
		$this->rest_base = Manager::REST_BASE . $this->get_name();

		add_action( 'rest_api_init', function () {
			$this->register(); // Because 'register' is protected.
		} );

		/**
		 * Since all actions were removed for custom internal REST server.
		 * Re-add the actions.
		 */
		add_action( 'elementor_rest_api_before_init', function () {
			add_action( 'rest_api_init', function() {
				$this->register();
			} );
		} );

		$parent_name = $this->get_parent_name();
		if ( $parent_name ) {
			$this->act_as_sub_controller( $parent_name );
		}
	}

	/**
	 * Get controller name.
	 *
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * Register endpoints.
	 */
	abstract public function register_endpoints();

	/**
	 * Get parent controller name.
	 *
	 * @note: If `get_parent_name()` provided, controller will work as sub-controller.
	 *
	 * @returns null|string
	 */
	public function get_parent_name() {
		return null;
	}

	/**
	 * Get full controller name.
	 *
	 * The method exist to handle situations when parent exist, to include the parent name.
	 *
	 * @return string
	 */
	public function get_full_name() {
		if ( ! $this->parent ) {
			return $this->get_name();
		}

		return $this->parent->get_name() . '/' . $this->get_name();
	}

	/**
	 * Get controller namespace.
	 *
	 * @return string
	 */
	public function get_namespace() {
		return $this->namespace;
	}

	/**
	 * Get controller reset base.
	 *
	 * @return string
	 */
	public function get_base_route() {
		if ( ! $this->parent ) {
			return $this->rest_base;
		}

		return $this->parent->get_base_route() . '/' . $this->get_name();
	}

	/**
	 * Get controller route.
	 *
	 * @return string
	 */
	public function get_controller_route() {
		return $this->get_namespace() . '/' . $this->get_base_route();
	}

	/**
	 * Retrieves rest route(s) index for current controller.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_controller_index() {
		$server = rest_get_server();
		$routes = $server->get_routes();

		$endpoints = array_intersect_key( $server->get_routes(), $routes );

		$controller_route = $this->get_controller_route();

		array_walk( $endpoints, function ( &$item, $endpoint ) use ( &$endpoints, $controller_route ) {
			if ( ! strstr( $endpoint, $controller_route ) ) {
				unset( $endpoints[ $endpoint ] );
			}
		} );

		$data = [
			'namespace' => $this->get_namespace(),
			'controller' => $controller_route,
			'routes' => $server->get_data_for_routes( $endpoints ),
		];

		$response = rest_ensure_response( $data );

		// Link to the root index.
		$response->add_link( 'up', rest_url( '/' ) );

		return $response;
	}

	/**
	 * Get items args for specific endpoint.
	 *
	 * @param string $methods
	 *
	 * @return array
	 */
	public function get_items_args( $methods ) {
		if ( \WP_REST_Server::READABLE === $methods ) {
			return $this->get_collection_params();
		}

		return [];
	}

	/**
	 * Get permission callback.
	 *
	 * Default controller permission callback.
	 * By default endpoint will inherit the permission callback from the controller.
	 * By default permission is `current_user_can( 'administrator' );`.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return bool
	 */
	public function get_permission_callback( $request ) {
		// The function is public since endpoint need to access it.
		switch ( $request->get_method() ) {
			case 'GET':
			case 'POST':
			case 'UPDATE':
			case 'PUT':
			case 'DELETE':
			case 'PATCH':
				return current_user_can( 'administrator' );
		}

		return false;
	}

	public function get_items( $request ) {
		return $this->get_controller_index();
	}

	/**
	 * Creates multiple items.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function create_items( $request ) {
		return new \WP_Error( 'invalid-method', sprintf( "Method '%s' not implemented. Must be overridden in subclass.", __METHOD__ ), [ 'status' => 405 ] );
	}

	/**
	 * Updates multiple items.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function update_items( $request ) {
		return new \WP_Error( 'invalid-method', sprintf( "Method '%s' not implemented. Must be overridden in subclass.", __METHOD__ ), [ 'status' => 405 ] );
	}

	/**
	 * Delete multiple items.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function delete_items( $request ) {
		return new \WP_Error( 'invalid-method', sprintf( "Method '%s' not implemented. Must be overridden in subclass.", __METHOD__ ), [ 'status' => 405 ] );
	}

	/**
	 * Get the parent controller.
	 *
	 * @return \Elementor\Data\V2\Base\Controller|null
	 */
	public function get_parent() {
		return $this->parent;
	}

	/**
	 * Get sub controller(s).
	 *
	 * @return \Elementor\Data\V2\Base\Controller[]
	 */
	public function get_sub_controllers() {
		return $this->sub_controllers;
	}

	/**
	 * Get processors.
	 *
	 * @param string $command
	 *
	 * @return \Elementor\Data\V2\Base\Processor[]
	 */
	public function get_processors( $command ) {
		$result = [];

		if ( isset( $this->processors[ $command ] ) ) {
			$result = $this->processors[ $command ];
		}

		return $result;
	}

	/**
	 * Register processors.
	 */
	public function register_processors() {
	}

	/**
	 * Register index endpoint.
	 */
	protected function register_index_endpoint() {
		if ( ! $this->parent ) {
			return $this->register_endpoint( new Endpoint\Index( $this ) );
		}

		$this->register_endpoint( new Endpoint\Index\Sub_Index_Endpoint( $this ) );
	}

	/**
	 * Register endpoint.
	 *
	 * @param \Elementor\Data\V2\Base\Endpoint $endpoint
	 *
	 * @return \Elementor\Data\V2\Base\Endpoint
	 */
	protected function register_endpoint( Endpoint $endpoint ) {
		$command = $endpoint->get_full_command();

		if ( $endpoint instanceof Endpoint\Index ) {
			$this->index_endpoint = $endpoint;
		} else {
			$this->endpoints[ $command ] = $endpoint;
		}

		$format = $endpoint->get_format();

		// `$e.data.registerFormat()`.
		Manager::instance()->register_endpoint_format( $command, $format );

		return $endpoint;
	}

	/**
	 * Register a processor.
	 *
	 * That will be later attached to the endpoint class.
	 *
	 * @param Processor $processor
	 *
	 * @return \Elementor\Data\V2\Base\Processor $processor_instance
	 */
	protected function register_processor( Processor $processor ) {
		$command = $processor->get_command();

		if ( ! isset( $this->processors[ $command ] ) ) {
			$this->processors[ $command ] = [];
		}

		$this->processors[ $command ] [] = $processor;

		return $processor;
	}

	/**
	 * Register.
	 *
	 * Endpoints & processors.
	 */
	protected function register() {
		$this->register_index_endpoint();
		$this->register_endpoints();

		// Aka hooks.
		$this->register_processors();
	}

	/**
	 * Get collection params by 'additionalProperties' context.
	 *
	 * @param string $context
	 *
	 * @return array
	 */
	protected function get_collection_params_by_additional_props_context( $context ) {
		$result = [];

		$collection_params = $this->get_collection_params();

		foreach ( $collection_params as $collection_param_key => $collection_param ) {
			if ( isset( $collection_param['additionalProperties']['context'] ) && $context === $collection_param['additionalProperties']['context'] ) {
				$result[ $collection_param_key ] = $collection_param;
			}
		}

		return $result;
	}

	/**
	 * When `$this->get_parent_name` is extended, the controller will have a parent, and will know to behave like a sub-controller.
	 *
	 * @param string $parent_name
	 */
	private function act_as_sub_controller( $parent_name ) {
		$this->parent = Manager::instance()->get_controller( $parent_name );

		if ( ! $this->parent ) {
			trigger_error( "Cannot find parent controller: '$parent_name'", E_USER_ERROR ); // phpcs:ignore
		}

		$this->parent->sub_controllers [] = $this;
	}
}
