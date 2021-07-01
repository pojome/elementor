<?php
namespace Elementor\Modules\DefaultValues\Data;

use Elementor\Plugin;
use Elementor\Modules\DefaultValues\Module;
use Elementor\Data\Base\Controller as Base_Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Controller extends Base_Controller {
	public function get_name() {
		return 'default-values';
	}

	protected function register_internal_endpoints() {
		$this->register_endpoint( Endpoints\Index::class );
	}

	public function register_endpoints() {
		//
	}

	/**
	 * Get all the default values.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error
	 */
	public function get_items( $request ) {
		$kit = Plugin::$instance->kits_manager->get_active_kit();

		if ( ! $kit->get_id() ) {
			return new \WP_Error( 'kit_not_exists', __( 'Kit not exists.', 'elementor' ), [ 'status' => 500 ] );
		}

		return [ 'data' => $kit->get_json_meta( Module::DEFAULT_VALUES_META_KEY ) ];
	}

	/**
	 * Create new default value.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error
	 */
	public function create_item( $request ) {
		$kit = Plugin::$instance->kits_manager->get_active_kit();

		if ( ! $kit->get_id() ) {
			return new \WP_Error( 'kit_not_exists', __( 'Kit not exists.', 'elementor' ), [ 'status' => 500 ] );
		}

		$type = $request->get_param( 'type' );

		$types = array_merge(
			array_keys( Plugin::$instance->elements_manager->get_element_types() ),
			array_keys( Plugin::$instance->widgets_manager->get_widget_types() )
		);

		if ( ! in_array( $type, $types, true ) ) {
			return new \WP_Error( 'type_not_exists', __( 'Type not exists.', 'elementor' ), [ 'status' => 422 ] );
		}

		$default_values = $kit->get_json_meta( Module::DEFAULT_VALUES_META_KEY );

		$default_values[ $type ] = $request->get_param( 'settings' );

		$kit->update_meta( Module::DEFAULT_VALUES_META_KEY, wp_json_encode( $default_values ) );

		return [ 'data' => $default_values[ $type ] ];
	}
}