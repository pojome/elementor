<?php
namespace Elementor\Data\V2\Base;

use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class purpose is to separate routing logic into one file.
 */
abstract class Base_Route {
	const AVAILABLE_METHODS = [
		WP_REST_Server::READABLE,
		WP_REST_Server::CREATABLE,
		WP_REST_Server::EDITABLE,
		WP_REST_Server::DELETABLE,
		WP_REST_Server::ALLMETHODS,
	];

	/**
	 * Controller of current endpoint.
	 *
	 * @var \Elementor\Data\V2\Base\Controller
	 */
	protected $controller;

	/**
	 * Current route, effect only in case the endpoint behave like sub-endpoint.
	 *
	 * @var string
	 */
	protected $route;

	/**
	 * All register routes.
	 *
	 * @var array
	 */
	protected $routes = [];

	/**
	 * Registered item route.
	 *
	 * @var array|null
	 */
	protected $item_route = null;

	protected $id_arg_name = 'id';
	protected $id_arg_type_regex = '[\d]+';

	/**
	 * Constructor.
	 *
	 * run `$this->register()`.
	 *
	 * @param \Elementor\Data\V2\Base\Controller $controller
	 * @param string $route
	 */
	protected function __construct( Controller $controller, $route ) {
		$this->controller = $controller;
		$this->route = $this->ensure_slashes( $route );

		$this->register();
	}

	/**
	 * Ensure start-with and end-with slashes.
	 *
	 * '/' => '/'
	 * 'abc' => '/abc/'
	 * '/abc' => '/abc/'
	 * 'abc/' => '/abc/'
	 * '/abc/' => '/abc/'
	 *
	 * @param string $route
	 *
	 * @return string
	 */
	private function ensure_slashes( $route ) {
		if ( '/' !== $route[0] ) {
			$route = '/' . $route;
		}

		return trailingslashit( $route );
	}

	/**
	 * Get base route.
	 * This method should always return the base route starts with '/' and ends without '/'.
	 *
	 * @return string
	 */
	public function get_base_route() {
		$name = $this->get_public_name();
		$parent = $this->get_parent();
		$parent_base = $parent->get_base_route();
		$route = '/';

		if ( ! ( $parent instanceof Controller ) ) {
			$route = $parent->item_route ? $parent->item_route['route'] . '/' : $this->route;
		}

		return untrailingslashit( '/' . trim( $parent_base . $route . $name, '/' ) );
	}

	/**
	 * Get permission callback.
	 *
	 * By default get permission callback from the controller.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return boolean
	 */
	public function get_permission_callback( $request ) {
		return $this->controller->get_permission_callback( $request );
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	protected function get_items( $request ) {
		return $this->controller->get_items( $request );
	}

	/**
	 * Retrieves one item from the collection.
	 *
	 * @param string $id
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	protected function get_item( $id, $request ) {
		return $this->controller->get_item( $request );
	}

	/**
	 * Creates multiple items.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	protected function create_items( $request ) {
		return new \WP_Error( 'invalid-method', sprintf( "Method '%s' not implemented. Must be overridden in subclass.", __METHOD__ ), [ 'status' => 405 ] );
	}

	/**
	 * Creates one item.
	 *
	 * @param string $id id of request item.
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	protected function create_item( $id, $request ) {
		return $this->controller->create_item( $request );
	}

	/**
	 * Updates multiple items.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	protected function update_items( $request ) {
		return new \WP_Error( 'invalid-method', sprintf( "Method '%s' not implemented. Must be overridden in subclass.", __METHOD__ ), [ 'status' => 405 ] );
	}

	/**
	 * Updates one item.
	 *
	 * @param string $id id of request item.
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	protected function update_item( $id, $request ) {
		return $this->controller->update_item( $request );
	}

	/**
	 * Delete multiple items.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	protected function delete_items( $request ) {
		return new \WP_Error( 'invalid-method', sprintf( "Method '%s' not implemented. Must be overridden in subclass.", __METHOD__ ), [ 'status' => 405 ] );
	}

	/**
	 * Delete one item.
	 *
	 * @param string $id id of request item.
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|\WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	protected function delete_item( $id, $request ) {
		return $this->controller->delete_item( $request );
	}

	/**
	 * Register the endpoint.
	 *
	 * By default: register get items route.
	 */
	protected function register() {
		$this->register_items_route();
	}

	protected function register_route( $route = '', $methods = WP_REST_Server::READABLE, $callback = null, $args = [] ) {
		if ( ! in_array( $methods, self::AVAILABLE_METHODS, true ) ) {
			trigger_error( "Invalid method: '$methods'.", E_USER_ERROR ); // phpcs:ignore
		}

		$route = $this->get_base_route() . $route;

		$this->routes [] = [
			'args' => $args,
			'route' => $route,
		];

		return register_rest_route( $this->controller->get_namespace(), $route, [
			[
				'args' => $args,
				'methods' => $methods,
				'callback' => $callback,
				'permission_callback' => function ( $request ) {
					return $this->get_permission_callback( $request );
				},
			],
		] );
	}

	/**
	 * Register items route.
	 *
	 * @param string $methods
	 * @param array $args
	 */
	public function register_items_route( $methods = WP_REST_Server::READABLE, $args = [] ) {
		$this->register_route( '', $methods, function ( $request ) use ( $methods ) {
			return $this->base_callback( $methods, $request, true );
		}, $args );
	}

	/**
	 * Register item route.
	 *
	 * @param string $route
	 * @param array $args
	 * @param string $methods
	 */
	public function register_item_route( $methods = WP_REST_Server::READABLE, $args = [], $route = '/' ) {
		if ( ! empty( $args['id_arg_name'] ) ) {
			$this->id_arg_name = $args['id_arg_name'];

			unset( $args['id_arg_name'] );
		}

		if ( ! empty( $args['id_arg_type_regex'] ) ) {
			$this->id_arg_type_regex = $args['id_arg_type_regex'];

			unset( $args['id_arg_type_regex'] );
		}

		$args = array_merge( [
			$this->id_arg_name => [
				'description' => 'Unique identifier for the object.',
				'type' => 'string',
			],
		], $args );

		$route .= '(?P<' . $this->id_arg_name . '>' . $this->id_arg_type_regex . ')';

		$this->item_route = [
			'args' => $args,
			'route' => $route,
		];

		$this->register_route( $route, $methods, function ( $request ) use ( $methods ) {
			return $this->base_callback( $methods, $request );
		}, $args );
	}

	/**
	 * Base callback.
	 * All reset requests from the client should pass this function.
	 *
	 * @param string $methods
	 * @param \WP_REST_Request $request
	 * @param bool $is_multi
	 *
	 * @return mixed|\WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	public function base_callback( $methods, $request, $is_multi = false ) {
		if ( $request ) {
			$json_params = $request->get_json_params();

			if ( $json_params ) {
				$request->set_body_params( $json_params );
			}
		}

		$result = [];

		if ( $this->get_permission_callback( $request ) ) {
			switch ( $methods ) {
				case WP_REST_Server::READABLE:
					$result = $is_multi ? $this->get_items( $request ) : $this->get_item( $request->get_param( 'id' ), $request );
					break;

				case WP_REST_Server::CREATABLE:
					$result = $is_multi ? $this->create_items( $request ) : $this->create_item( $request->get_param( 'id' ), $request );
					break;

				case WP_REST_Server::EDITABLE:
					$result = $is_multi ? $this->update_items( $request ) : $this->update_item( $request->get_param( 'id' ), $request );
					break;

				case WP_REST_Server::DELETABLE:
					$result = $is_multi ? $this->delete_items( $request ) : $this->delete_item( $request->get_param( 'id' ), $request );
					break;
			}
		}

		return rest_ensure_response( $result );
	}
}
