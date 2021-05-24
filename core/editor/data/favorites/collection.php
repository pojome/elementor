<?php
namespace Elementor\Core\Editor\Data\Favorites;

use Elementor\Core\Utils\Collection as Base_Collection;

abstract class Collection {

	/**
	 * The Laravel inspired Collection.
	 *
	 * @var Base_Collection|null
	 */
	protected $collection = null;

	/**
	 * Collection constructor.
	 */
	public function __construct() {
		$this->collection = new Base_Collection( [] );
	}

	/**
	 * Get the name of favorites collection.
	 *
	 * @return mixed
	 */
	abstract public static function get_name();

	/**
	 * Prepare favorites before taking any action.
	 *
	 * @param array $favorites
	 *
	 * @return array
	 */
	public function prepare( $favorites ) {
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

		if ( null !== $call && $call instanceof Base_Collection ) {
			$this->collection = $call;
		}

		return $call;
	}
}
