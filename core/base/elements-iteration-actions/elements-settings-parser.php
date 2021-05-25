<?php
namespace Elementor\Core\Base\Elements_Iteration_Actions;

use Elementor\Element_Base;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Elements_Settings_Parser extends Base {

	const PARSED_ELEMENTS_META_KEY = '_elementor_parsed_elements';

	/**
	 * @var array $parsed_elements_data
	 */
	private $parsed_elements_data = [];

	/**
	 * Is Action Needed
	 *
	 * This method is called during runtime only (as opposed to during a save process). If true is returned, the
	 * action will run on elements during runtime. If false is returned, the action will not run during runtime.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	public function is_action_needed() {
		if ( ! metadata_exists( 'post', $this->document->get_id(), '_elementor_parsed_elements' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Element Action
	 *
	 * This method is called on each element in the document after a document has been saved.
	 * The data for each document is saved in the database as an associative array of element IDs.
	 * Each Element ID property contains its own associative array of element setting keys.
	 * Each setting key contains an associative array with 3 properties:
	 * 'value' - Contains the parsed setting's value
	 * 'prefix_class' - If the control has a 'prefix_class' property, this will contain the prefix class value
	 * 'frontend_available' - If the control is available in the frontend, the setting array will contain this property
	 *  with a boolean value of `true`. If the control is not available in the frontend, this key will not exist as a
	 *  property in that setting's array.
	 *
	 * @since 3.3.0
	 *
	 * @param Element_Base $element
	 */
	public function element_action( Element_Base $element ) {
		$element_id = $element->get_id();
		$controls = $element->get_controls();
		$active_settings = $element->get_active_settings();

		foreach ( $controls as $control ) {
			// If the control is not active, it does not need to be parsed and saved.
			if ( ! isset( $active_settings[ $control['name'] ] ) ) {
				continue;
			}

			// Create an associative array for each setting, with a 'value', 'prefix_class', and 'frontend_available'
			// properties when they exist.
			$setting_data = [
				'value' => $active_settings[ $control['name'] ],
				'control_type' => $control['type'],
			];

			if ( isset( $control['prefix_class'] ) ) {
				$setting_data['prefix_class'] = $control['prefix_class'];
			}

			if ( ! empty( $control['frontend_available'] ) ) {
				$setting_data['frontend_available'] = true;
			}

			$this->parsed_elements_data[ $element_id ][ $control['name'] ] = $setting_data;
		}
	}

	/**
	 * After Elements Iteration
	 *
	 * This method is called after the iteration on the document elements finishes.
	 *
	 * @since 3.3.0
	 */
	public function after_elements_iteration() {
		// Saving the parsed elements data.
		$this->document->update_meta( self::PARSED_ELEMENTS_META_KEY, $this->parsed_elements_data );
	}
}
