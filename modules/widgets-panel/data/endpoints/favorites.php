<?php

namespace Elementor\Modules\WidgetsPanel\Data\Endpoints;

use Elementor\Data\Base\Endpoint;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Favorites
 * @package Elementor\Modules\WidgetsPanel\Data\Endpoints
 */
class Favorites extends Endpoint {

	/**
	 * Set meta key name
	 *
	 * Filed meta_key in database table wp_usermeta
	 *
	 * @since 3.0.16
	 */
	const META_KEY = '_elementor_favorites_widgets';

	/**
	 * The name of path - Rest Api
	 *
	 * @since 3.0.16
	 * @access public
	 *
	 * @return string
	 */
	public function get_name() {
		return 'favorites';
	}

	/**
	 * Register Rest
	 *
	 * @since 3.0.16
	 * @access protected
	 *
	 * @throws \Exception
	 */
	protected function register() {
		parent::register();

		/** @TODO: Check what the right way to stop request ( like middleware ) */
		$this->register_item_route( \WP_REST_Server::READABLE );
		$this->register_route( '/(?P<id>[\w\-]+)/', \WP_REST_Server::CREATABLE, function ( $request ) {
			return $this->base_callback( \WP_REST_Server::CREATABLE, $request );
		}, [
			'id' => [
				'description' => 'Unique identifier for the object.',
				'type' => 'string',
			]
		] );
		$this->register_route( '/(?P<id>[\w\-]+)/', \WP_REST_Server::DELETABLE, function ( $request ) {
			return $this->base_callback( \WP_REST_Server::DELETABLE, $request );
		}, [
			'id' => [
				'description' => 'Unique identifier for the object.',
				'type' => 'string',
			]
		] );
	}

	/**
	 * Retrieve favorites widgets from DB
	 *
	 * @since 3.0.16
	 * @access public
	 *
	 * @param string           $id
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|int|\WP_Error|\WP_REST_Response
	 */
	public function get_item( $id, $request ) {
		return $this->get_favorite_widget();
	}

	/**
	 * Add favorite widget to DB
	 *
	 * @since 3.0.16
	 * @access public
	 *
	 * @param string           $id
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|int|\WP_Error|\WP_REST_Response
	 */
	public function create_item( $id, $request ) {
		return $this->mark_as_favorite_widget( $id, true );
	}

	/**
	 * Delete favorite widget from DB
	 *
	 * @since 3.0.16
	 * @access public
	 *
	 * @param string           $id
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|int|\WP_Error|\WP_REST_Response
	 */
	public function delete_item( $id, $request ) {
		return $this->mark_as_favorite_widget( $id, false );
	}

	/**
	 * Retrieve favorites widgets from DB.
	 *
	 * @param int $user_id
	 *
	 * @return array|mixed
	 * @since  3.0.16
	 * @access public
	 *
	 */
	public static function get_favorite_widget( $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$favorites_widgets = get_user_meta( $user_id, self::META_KEY, true );
		if ( ! $favorites_widgets ) {
			$favorites_widgets = [];
		}
		return $favorites_widgets;
	}

	/**
	 * Mark or unmark widget as favorite.
	 *
	 * Update user meta containing his favorite widgets. For a given widget
	 * ID, add the widget to the favorite widgets or remove it from the
	 * favorites, based on the `favorite` parameter.
	 *
	 * @param string $widget_id The widget T.
	 * @param bool   $favorite  Optional. Whether the template is marked as
	 *                          favorite, or not. Default is true.
	 *
	 * @return int|bool User meta ID if the key didn't exist, true on successful
	 *                  update, false on failure.
	 * @since  3.0.16
	 * @access private
	 *
	 */
	private function mark_as_favorite_widget( string $widget_id, $favorite = true ) {
		/**
		 * Validate: widget is registered widget
		 * @var array $widgets - Retrieve all the registered widgets
		 */
		$widgets = Plugin::instance()->widgets_manager->get_widget_types_config();
		if ( in_array( $widget_id, $widgets ) ) {
			return false;
		}

		$favorites_widgets = $this->get_favorite_widget();

		/** @var  $widget_id */
		$widget_id = esc_html__( $widget_id, 'elementor' );

		if ( $favorite ) {
			if ( isset( $favorites_widgets[ $widget_id ] ) ) {
				return true;
			}
			$favorites_widgets[ $widget_id ] = $favorite;
		} else {
			unset( $favorites_widgets[ $widget_id ] );
		}

		return update_user_meta( get_current_user_id(), self::META_KEY, $favorites_widgets );
	}

	/**
	 * Update tracking data for favorites-widgets-panel
	 *
	 * @since  3.0.16
	 * @access public
	 */
	public static function add_tracking_data() {
		/**
		 * Add tracking data for favorites-widgets-panel
		 *
		 * Called on elementor/tracker/send_tracking_data_params.
		 *
		 * @param array $params
		 *
		 * @return array
		 */
		add_filter( 'elementor/tracker/send_tracking_data_params', function( $params ) {
			$args = [
				'orderby' => 'caps, last_name',
				'order'   => 'ASC'
			];
			// Get All WP Users
			$users = get_users( $args );

			$users_data = [];
			foreach ( $users as $user ) {
				if ( empty( $user->ID ) ) {
					continue;
				}

				$get_favorite_widgets_by_userId = self::get_favorite_widget( $user->ID );

				if ( ! empty( $get_favorite_widgets_by_userId ) ) {
					$users_data[ 'users' ][] = [
						'ID' => $user->ID,
						'first_name' => $user->first_name,
						'last_name' => $user->last_name,
						'display_name' => $user->display_name,
						'user_nicename' => $user->user_nicename,
						'user_registered' => $user->user_registered,
						'user_email' => $user->user_email,
						'user_url' => $user->user_url,
						'user_status' => $user->user_status,
						'caps' => $user->caps,
						'roles' => $user->roles,
						'favorites_widgets' => $get_favorite_widgets_by_userId,
					];
				}
			}

			if ( ! empty( $users_data ) ) {
				$params['usages']['favorites_widgets_panel'] = $users_data;
			}

			return $params;
		} );
	}
}
