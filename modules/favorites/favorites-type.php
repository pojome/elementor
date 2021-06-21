<?php
namespace Elementor\Modules\Favorites;

use Elementor\Core\Utils\Collection;

abstract class Favorites_Type {

	/**
	 * The Laravel inspired Collection.
	 *
	 * @var Collection|null
	 */
	protected $collection = null;

	/**
	 * Favorites Type constructor.
	 */
	public function __construct() {
		$this->collection = new Collection( [] );
	}

	/**
	 * Get the name of the type.
	 *
	 * @return mixed
	 */
	abstract public function get_name();

	/**
	 * Prepare favorites before taking any action.
	 *
	 * @param array $favorites
	 *
	 * @return array
	 */
	public function prepare( $favorites ) {
		if ( ! is_array( $favorites ) && ! $favorites instanceof Collection ) {
			return [ $favorites ];
		}

		return $favorites;
	}

	/**
	 * Since this class is a wrapper, every call which has no declaration here,
	 * will be forwarded to wrapped class. Most of the collection methods returns
	 * a new collection instance, and therefore it will be assigned as the current
	 * collection instance after executing any method.
	 *
	 * @param string $name
	 * @param array $arguments
	 */
	public function __call( $name, $arguments ) {
		$call = call_user_func_array( [ $this->collection, $name ], $arguments );

		if ( null !== $call && $call instanceof Collection ) {
			$this->collection = $call->unique();
		}

		return $call;
	}
}
