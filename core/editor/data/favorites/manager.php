<?php
namespace Elementor\Core\Editor\Data\Favorites;

use Elementor\Core\Editor\Data\Favorites\Collections\Widgets;
use WP_Error;

class Manager {

	/**
	 * List of registered collections.
	 *
	 * @var Collection[]
	 */
	protected $collections = [];

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
	 * Manager constructor.
	 */
	public function __construct() {
		// Register default collections
		$this->register( Widgets::class );
		// ...

		$this->populate();
	}

	/**
	 * Get user favorites collection.
	 *
	 * @param string[]|string $collections
	 *
	 * @return array
	 */
	public function get( $collections = null ) {
		if ( null === $collections ) {
			return $this->compile();
		}

		return $this->implementation( $collections )
			->all();
	}

	/**
	 * Merge new user favorites to collection.
	 *
	 * @param string $collection
	 * @param array  $favorites
	 * @param bool   $store
	 *
	 * @return array|bool
	 */
	public function merge( $collection, $favorites, $store = true ) {
		return $this->update( $collection, $favorites, static::ACTION_MERGE, $store );
	}

	/**
	 * Delete existing favorites from collection.
	 *
	 * @param string $collection
	 * @param array  $favorites
	 * @param bool   $store
	 *
	 * @return array|int
	 */
	public function delete( $collection, $favorites, $store = true ) {
		return $this->update( $collection, $favorites, static::ACTION_DELETE, $store );
	}

	/**
	 * Update favorites collection by merging or deleting from it.
	 *
	 * @param      $collection
	 * @param      $favorites
	 * @param      $action
	 * @param bool $store
	 *
	 * @return array|boolean
	 */
	public function update( $collection, $favorites, $action, $store = true ) {
		$implementation = $this->implementation( $collection );

		switch ( $action ) {
			case static::ACTION_MERGE:
				$implementation->merge( $favorites );
				break;
			case static::ACTION_DELETE:
				$implementation->filter(
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

		return $implementation->all();
	}

	/**
	 * Check whether favorites collection is registered.
	 *
	 * @param string $collection
	 *
	 * @return bool
	 */
	public function exists( $collection ) {
		return in_array( $collection, array_keys( $this->collections ), true );
	}

	/**
	 * Get registered favorites collection class.
	 *
	 * @param string $collection
	 *
	 * @return Collection
	 */
	public function implementation( $collection ) {
		if ( ! $this->exists( $collection ) ) {
			$this->collection_doesnt_exists( $collection );
		}

		return $this->collections[ $collection ];
	}

	/**
	 * Register a new collection class.
	 *
	 * @param string $class
	 */
	public function register( $class ) {
		if ( ! class_exists( $class ) ) {
			$this->class_doesnt_exists( $class );
		}

		$key = call_user_func( [ $class, 'get_name' ] );

		$this->collections[ $key ] = new $class();
	}

	/**
	 * Returns all available collections keys.
	 *
	 * @return string[]
	 */
	public function available() {
		return array_keys( $this->collections );
	}

	/**
	 * Compile all favorites collections into a single array.
	 *
	 * @return array
	 */
	protected function compile() {
		$compiled = [];

		foreach ( $this->collections as $collection ) {
			$favorites = $collection->all();

			if ( ! empty( $favorites ) ) {
				$compiled[ $collection->get_name() ] = $favorites;
			}
		}

		return $compiled;
	}

	/**
	 * Populate all collections with the stored data.
	 */
	protected function populate() {
		$compiled = $this->retrieve();

		foreach ( $this->collections as $key => $collection ) {
			if ( isset( $compiled[ $key ] ) ) {
				$collection->merge( $compiled[ $key ] );
			}
		}
	}

	/**
	 * Retrieve stored user favorites collection.
	 *
	 * @return mixed|false
	 */
	protected function retrieve() {
		return get_user_option( static::get_option_name() );
	}

	/**
	 * Update all changes to user favorites collections.
	 *
	 * @return int|bool
	 */
	protected function store() {
		return update_user_option( get_current_user_id(), static::get_option_name(), $this->compile() );
	}

	/**
	 * Get the favorites option name.
	 *
	 * @return string
	 */
	protected static function get_option_name() {
		return 'elementor_editor_user_favorites';
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
					__( "Action '%s' for favorites collections doesn't exists", 'elementor' ),
					$action
				)
			)
		);
	}

	/**
	 * Display collection doesn't exists error.
	 *
	 * @param string $collection
	 */
	public function collection_doesnt_exists( $collection ) {
		wp_die(
			new WP_Error(
				'collection_doesnt_exists',
				sprintf(
					__( "Favorites collection '%s' doesn't exists", 'elementor' ),
					$collection
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
					__( "Can't register collection because class '%s' doesn't exists", 'elementor' ),
					$class
				)
			)
		);
	}
}
