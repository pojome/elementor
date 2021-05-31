<?php
namespace Elementor\Modules\Usage\Collection;

use Elementor\Core\Base\Document;
use Elementor\Core\Kits\Documents\Kit;
use Elementor\Core\Utils\Collection;
use Elementor\Modules\Usage\Module;
use Elementor\Plugin;

class DocumentSettingsUsage extends Collection {
	/**
	 * Create new collection from 'get_option' of global document settings usage.
	 *
	 * Cannot be called 'instance' since its create new instance and not singleton.
	 *
	 * @return $this
	 */
	public static function create() {
		return new static( get_option( Module::DOCUMENT_OPTION_NAME, [] ) );
	}

	/**
	 * Validate if the value is the default value of the control.
	 *
	 * In situations where the validation is for repeater the `$control_default` and `$setting_value` are arrays.
	 *
	 * @param boolean $is_repeater
	 * @param string|array $control_default
	 * @param string|array $setting_value
	 *
	 * @return bool
	 */
	private function is_control_default_value( $is_repeater, $control_default, $setting_value ) {
		$is_default = $control_default === $setting_value;

		// Some repeater settings have empty controls, which are not exist in the default, it should be analyzed.
		if ( $is_repeater && ! $is_default && ! empty( $control_default ) ) {
			$clear_repeater_settings = [];

			// Using the control defaults, clean unused keys from $setting_value ( repeater values ).
			foreach ( $control_default as $key => $control_repeater_item_default ) {
				$clear_repeater_settings[ $key ] = array_intersect_key(
					$setting_value[ $key ],
					array_flip( /* allowed keys*/ array_keys( $control_repeater_item_default ) )
				);
			}

			$is_default = $clear_repeater_settings === $control_default;
		}

		return $is_default;
	}

	/**
	 * Get document settings usage.
	 *
	 * @param Document $document
	 *
	 * @return array
	 */
	private function get_usage( $document ) {
		$usage  = [];

		// Ensure not from cache.
		$document = Plugin::$instance->documents->get( $document->get_id(), false );
		$controls = $document->get_controls();

		foreach ( $document->get_settings() as $setting_name => $setting_value ) {
			if ( isset( $controls[ $setting_name ] ) ) {
				$control = $controls[ $setting_name ];
				$is_repeater = is_array( $setting_value ) && isset( $control['fields'] );

				if ( ! $this->is_control_default_value( $is_repeater, $controls[ $setting_name ]['default'], $setting_value ) ) {
					if ( $is_repeater ) {
						$usage[ $setting_name ] = count( $setting_value );
					} else {
						$usage[ $setting_name ] = 1;
					}
				}
			}
		}

		return $usage;
	}

	/**
	 * Add, adds the document to collection.
	 *
	 * @param Document $document
	 *
	 * @return $this
	 */
	public function add( $document ) {
		$current_usage = $this->get_usage( $document );
		$document_name = $document->get_name();

		$document->update_meta( Module::DOCUMENT_META_KEY, $current_usage );

		$global_usage = &$this->items;

		foreach ( $current_usage as $control_name => $control_count ) {
			$global_document_usage_ref = &$global_usage[ $document_name ];

			if ( ! is_array( $global_document_usage_ref ) ) {
				$global_document_usage_ref = [];
			}

			// Kit is unique and only exist one time.
			if ( Kit::NAME !== $document_name && isset( $global_document_usage_ref[ $control_name ] ) ) {
				$global_document_usage_ref[ $control_name ] += $control_count;
			} else {
				$global_document_usage_ref[ $control_name ] = $control_count;
			}
		}

		return new static( $this->all() );
	}

	/**
	 * Remove, remove's the document from the collection.
	 *
	 * @param Document $document
	 *
	 * @return $this
	 */
	public function remove( $document ) {
		$current_usage = $document->get_meta( Module::DOCUMENT_META_KEY );

		if ( ! empty( $current_usage ) ) {
			$document_name = $document->get_name();

			foreach ( $current_usage as $control_name => $control_count ) {
				if ( ! empty( $this->items[ $document_name ][ $control_name ] ) ) {
					$this->items[ $document_name ][ $control_name ] -= $control_count;
				}
			}

			$this->clear_empty_recursive();

			$document->delete_meta( Module::DOCUMENT_META_KEY );
		}

		return new static( $this->all() );
	}

	/**
	 * Save, saves the current collection using `update_option`.
	 *
	 * @return $this
	 */
	public function save() {
		update_option( Module::DOCUMENT_OPTION_NAME, $this->all(), false );

		return new static( $this->all() );
	}
}
