<?php
/**
 * Inspired by Laravel Collection.
 * @link https://github.com/illuminate/collections
 */
namespace Elementor\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Collection implements \ArrayAccess, \Countable, \IteratorAggregate {
	/**
	 * The items contained in the collection.
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * Collection constructor.
	 *
	 * @param array $items
	 */
	public function __construct( array $items ) {
		$this->items = $items;
	}

	/**
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function filter( callable $callback = null ) {
		if ( ! $callback ) {
			return new static( array_filter( $this->items ) );
		}

		return new static( array_filter( $this->items, $callback, ARRAY_FILTER_USE_BOTH ) );
	}

	/**
	 * @param $items
	 *
	 * @return $this
	 */
	public function merge( $items ) {
		if ( $items instanceof Collection ) {
			$items = $items->all();
		}

		return new static( array_merge( $this->items, $items ) );
	}

	/**
	 * Union the collection with the given items.
	 *
	 * @param array $items
	 *
	 * @return $this
	 */
	public function union( array $items ) {
		return new static( $this->all() + $items );
	}

	/**
	 * Merge array recursively
	 *
	 * @param $items
	 *
	 * @return $this
	 */
	public function merge_recursive( $items ) {
		if ( $items instanceof Collection ) {
			$items = $items->all();
		}

		return new static( array_merge_recursive( $this->items, $items ) );
	}

	/**
	 * Replace array recursively
	 *
	 * @param $items
	 *
	 * @return $this
	 */
	public function replace_recursive( $items ) {
		if ( $items instanceof Collection ) {
			$items = $items->all();
		}

		return new static( array_replace_recursive( $this->items, $items ) );
	}

	/**
	 * Implode the items
	 *
	 * @param $glue
	 *
	 * @return string
	 */
	public function implode( $glue ) {
		return implode( $glue, $this->items );
	}

	/**
	 * Run a map over each of the items.
	 *
	 * @param  callable  $callback
	 * @return $this
	 */
	public function map( callable $callback ) {
		$keys = array_keys( $this->items );

		$items = array_map( $callback, $this->items, $keys );

		return new static( array_combine( $keys, $items ) );
	}

	/**
	 * @param callable $callback
	 * @param null     $initial
	 *
	 * @return mixed|null
	 */
	public function reduce( callable $callback, $initial = null ) {
		$result = $initial;

		foreach ( $this->all() as $key => $value ) {
			$result = $callback( $result, $value, $key );
		}

		return $result;
	}

	/**
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function map_with_keys( callable $callback ) {
		$result = [];

		foreach ( $this->items as $key => $value ) {
			$assoc = $callback( $value, $key );

			foreach ( $assoc as $map_key => $map_value ) {
				$result[ $map_key ] = $map_value;
			}
		}

		return new static( $result );
	}

	/**
	 * Get all items except for those with the specified keys.
	 *
	 * @param array $keys
	 *
	 * @return $this
	 */
	public function except( array $keys ) {
		return $this->filter( function ( $value, $key ) use ( $keys ) {
			return ! in_array( $key, $keys, true );
		} );
	}

	/**
	 * Get the items with the specified keys.
	 *
	 * @param array $keys
	 *
	 * @return $this
	 */
	public function only( array $keys ) {
		return $this->filter( function ( $value, $key ) use ( $keys ) {
			return in_array( $key, $keys, true );
		} );
	}

	/**
	 * Run over the collection to get specific prop from the collection item.
	 *
	 * @param $key
	 *
	 * @return $this
	 */
	public function pluck( $key ) {
		$result = [];

		foreach ( $this->items as $value ) {
			if ( is_object( $value ) && isset( $value->{$key} ) ) {
				$result[] = $value->{$key};
			} elseif ( is_array( $value ) && isset( $value[ $key ] ) ) {
				$result[] = $value[ $key ];
			}
		}

		return new static( $result );
	}

	/**
	 * Group the collection items by specific key in each collection item.
	 *
	 * @param $group_by
	 *
	 * @return $this
	 */
	public function group_by( $group_by ) {
		$result = [];

		foreach ( $this->items as $value ) {
			$group_key = 0;

			if ( is_object( $value ) && isset( $value->{$group_by} ) ) {
				$group_key = $value->{$group_by};
			} elseif ( is_array( $value ) && isset( $value[ $group_by ] ) ) {
				$group_key = $value[ $group_by ];
			}

			$result[ $group_key ][] = $value;
		}

		return new static( $result );
	}

	/**
	 * Sort keys
	 *
	 * @param false $descending
	 *
	 * @return $this
	 */
	public function sort_keys( $descending = false ) {
		$items = $this->items;

		if ( $descending ) {
			krsort( $items );
		} else {
			ksort( $items );
		}

		return new static( $items );
	}

	/**
	 * Get specific item from the collection.
	 *
	 * @param      $key
	 * @param null $default
	 *
	 * @return mixed|null
	 */
	public function get( $key, $default = null ) {
		if ( ! array_key_exists( $key, $this->items ) ) {
			return $default;
		}

		return $this->items[ $key ];
	}

	/**
	 * Get the first item.
	 *
	 * @param null $default
	 *
	 * @return mixed|null
	 */
	public function first( $default = null ) {
		if ( $this->is_empty() ) {
			return $default;
		}

		foreach ( $this->items as $item ) {
			return $item;
		}
	}

	/**
	 * Find an element from the items.
	 *
	 * @param callable $callback
	 * @param null     $default
	 *
	 * @return mixed|null
	 */
	public function find( callable $callback, $default = null ) {
		foreach ( $this->all() as $key => $item ) {
			if ( $callback( $item, $key ) ) {
				return $item;
			}
		}

		return $default;
	}

	/**
	 * Make sure all the values inside the array are uniques.
	 *
	 * @param null $key
	 *
	 * @return $this
	 */
	public function unique( $key = null ) {
		if ( ! $key ) {
			return new static(
				array_unique( $this->items )
			);
		}

		$exists = [];

		return $this->filter( function ( $item ) use ( $key, &$exists ) {
			$value = null;

			if ( is_object( $item ) && isset( $item->{$key} ) ) {
				$value = $item->{$key};
			} elseif ( is_array( $item ) && isset( $item[ $key ] ) ) {
				$value = $item[ $key ];
			}

			if ( null !== $value && ! in_array( $value, $exists, true ) ) {
				$exists[] = $value;

				return true;
			}

			return false;
		} );
	}

	/**
	 * @return array
	 */
	public function keys() {
		return array_keys( $this->items );
	}

	/**
	 * @return bool
	 */
	public function is_empty() {
		return empty( $this->items );
	}

	/**
	 * @return array
	 */
	public function all() {
		return $this->items;
	}

	/**
	 * @return array
	 */
	public function values() {
		return array_values( $this->all() );
	}

	/**
	 * @param mixed $key
	 *
	 * @return bool
	 */
	public function offsetExists( $key ) {
		return isset( $this->items[ $key ] );
	}

	/**
	 * @param mixed $key
	 *
	 * @return mixed
	 */
	public function offsetGet( $key ) {
		return $this->items[ $key ];
	}

	/**
	 * @param mixed $key
	 * @param mixed $value
	 */
	public function offsetSet( $key, $value ) {
		if ( is_null( $key ) ) {
			$this->items[] = $value;
		} else {
			$this->items[ $key ] = $value;
		}
	}

	/**
	 * @param mixed $key
	 */
	public function offsetUnset( $key ) {
		unset( $this->items[ $key ] );
	}

	/**
	 * @return \ArrayIterator|\Traversable
	 */
	public function getIterator() {
		return new \ArrayIterator( $this->items );
	}

	/**
	 * @return int|void
	 */
	public function count() {
		return count( $this->items );
	}
}
