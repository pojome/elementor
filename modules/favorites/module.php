<?php
namespace Elementor\Modules\Favorites;

use Elementor\Core\Base\Module as BaseModule;
use Elementor\Modules\Favorites\Types\Widgets;
use Elementor\Plugin;
use WP_Error;

class Module extends BaseModule {

	/**
	 * List of registered favorites type.
	 *
	 * @var Favorites_Type[]
	 */
	protected $types = [];

	const OPTION_NAME = 'elementor_editor_user_favorites';

	/**
	 * The name of the merge action.
	 *
	 * @var string
	 */
	const ACTION_MERGE = 'merge';

	/**
	 * The name of the delete action.
	 *
	 * @var string
	 */
	const ACTION_DELETE = 'delete';

	/**
	 * Favorites module constructor.
	 */
	protected function __construct() {
		// Register default types
		$this->register( Widgets::class );
		// ...

		$this->populate();

		Plugin::instance()->data_manager->register_controller( Controller::class );
	}

	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return 'favorites';
	}

	/**
	 * Get user favorites by type.
	 *
	 * @param string[]|string $type
	 *
	 * @return array
	 */
	public function get( $type = null ) {
		if ( null === $type || is_array( $type ) ) {
			return array_intersect_key(
				array_flip( (array) $type ),
				$this->combined()
			);
		}

		return $this->type_instance( $type )
			->values();
	}

	/**
	 * Merge new user favorites to a type.
	 *
	 * @param string $type
	 * @param array  $favorites
	 * @param bool   $store
	 *
	 * @return array|bool
	 */
	public function merge( $type, $favorites, $store = true ) {
		return $this->update( $type, $favorites, static::ACTION_MERGE, $store );
	}

	/**
	 * Delete existing favorites from a type.
	 *
	 * @param string $type
	 * @param array  $favorites
	 * @param bool   $store
	 *
	 * @return array|int
	 */
	public function delete( $type, $favorites, $store = true ) {
		return $this->update( $type, $favorites, static::ACTION_DELETE, $store );
	}

	/**
	 * Update favorites on a type by merging or deleting from it.
	 *
	 * @param      $type
	 * @param      $favorites
	 * @param      $action
	 * @param bool $store
	 *
	 * @return array|boolean
	 */
	public function update( $type, $favorites, $action, $store = true ) {
		$type_instance = $this->type_instance( $type );
		$favorites = $type_instance->prepare( $favorites );

		switch ( $action ) {
			case static::ACTION_MERGE:
				$type_instance->merge( $favorites );
				break;
			case static::ACTION_DELETE:
				$type_instance->filter(
					function( $value ) use ( $favorites ) {
						return ! in_array( $value, $favorites, true );
					}
				);
				break;
			default:
				$this->action_doesnt_exists( $action );
		}

		if ( $store && ! $this->store() ) {
			return false;
		}

		return $type_instance->values();
	}

	/**
	 * Check whether favorites type is registered.
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public function exists( $type ) {
		return in_array( $type, array_keys( $this->types ), true );
	}

	/**
	 * Get registered favorites type instance.
	 *
	 * @param string $type
	 *
	 * @return Favorites_Type
	 */
	public function type_instance( $type ) {
		if ( ! $this->exists( $type ) ) {
			$this->type_doesnt_exists( $type );
		}

		return $this->types[ $type ];
	}

	/**
	 * Register a new type class.
	 *
	 * @param string $class
	 */
	public function register( $class ) {
		if ( ! class_exists( $class ) ) {
			$this->class_doesnt_exists( $class );
		}

		$type_instance = new $class();

		$this->types[ $type_instance->get_name() ] = $type_instance;
	}

	/**
	 * Returns all available types keys.
	 *
	 * @return string[]
	 */
	public function available() {
		return array_keys( $this->types );
	}

	/**
	 * Compbine favorites from all types into a single array.
	 *
	 * @return array
	 */
	protected function combined() {
		$all = [];

		foreach ( $this->types as $type ) {
			$favorites = $type->values();

			if ( ! empty( $favorites ) ) {
				$all[ $type->get_name() ] = $favorites;
			}
		}

		return $all;
	}

	/**
	 * Populate all type classes with the stored data.
	 */
	protected function populate() {
		$combined = $this->retrieve();

		foreach ( $this->types as $key => $type ) {
			if ( isset( $combined[ $key ] ) ) {
				$type->merge( $combined[ $key ] );
			}
		}
	}

	/**
	 * Retrieve stored user favorites types.
	 *
	 * @return mixed|false
	 */
	protected function retrieve() {
		return get_user_option( static::OPTION_NAME );
	}

	/**
	 * Update all changes to user favorites type.
	 *
	 * @return int|bool
	 */
	protected function store() {
		return update_user_option( get_current_user_id(), static::OPTION_NAME, $this->combined() );
	}

	/**
	 * Display action doesn't exists error.
	 *
	 * @param string $action
	 */
	public function action_doesnt_exists( $action ) {
		wp_die(
			new WP_Error(
				'action_doesnt_exists',
				sprintf(
					__( "Action '%s' to apply on favorites doesn't exists", 'elementor' ),
					$action
				)
			)
		);
	}

	/**
	 * Display type doesn't exists error.
	 *
	 * @param string $type
	 */
	public function type_doesnt_exists( $type ) {
		wp_die(
			new WP_Error(
				'type_doesnt_exists',
				sprintf(
					__( "Favorites type '%s' doesn't exists", 'elementor' ),
					$type
				)
			)
		);
	}

	/**
	 * Display class doesn't exists error.
	 *
	 * @param string $class
	 */
	public function class_doesnt_exists( $class ) {
		wp_die(
			new WP_Error(
				'class_doesnt_exists',
				sprintf(
					__( "Can't register type because class '%s' doesn't exists", 'elementor' ),
					$class
				)
			)
		);
	}
}
