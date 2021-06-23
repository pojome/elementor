<?php
namespace Elementor\Modules\Library;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class User_Favorites {
	const USER_META_KEY = 'elementor_library_favorites';

	/**
	 * @var int
	 */
	private $user_id;

	/**
	 * @var array|null
	 */
	private $cache;

	/**
	 * User_Favorites constructor.
	 *
	 * @param $user_id
	 */
	public function __construct( $user_id ) {
		$this->user_id = $user_id;
	}

	/**
	 * @param null  $vendor
	 * @param null  $resource
	 * @param false $ignore_cache
	 *
	 * @return array
	 */
	public function get( $vendor = null, $resource = null, $ignore_cache = false ) {
		if ( $ignore_cache || empty( $this->cache ) ) {
			$this->cache = get_user_meta( $this->user_id, self::USER_META_KEY, true );
		}

		if ( ! $this->cache || ! is_array( $this->cache ) ) {
			return [];
		}

		if ( $vendor && $resource ) {
			$key = $this->get_key( $vendor, $resource );

			return isset( $this->cache[ $key ] ) ? $this->cache[ $key ] : [];
		}

		return $this->cache;
	}

	/**
	 * @param $vendor
	 * @param $resource
	 * @param $id
	 *
	 * @return bool
	 */
	public function exists( $vendor, $resource, $id ) {
		return in_array( $id, $this->get( $vendor, $resource ), true );
	}

	/**
	 * @param       $vendor
	 * @param       $resource
	 * @param array $value
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function save( $vendor, $resource, $value = [] ) {
		$all_favorites = $this->get();

		$all_favorites[ $this->get_key( $vendor, $resource ) ] = $value;

		$result = update_user_meta( $this->user_id, self::USER_META_KEY, $all_favorites );

		if ( false === $result ) {
			throw new \Exception( __( 'Failed to save user favorites', 'elementor' ) );
		}

		$this->cache = $all_favorites;

		return $this;
	}

	/**
	 * @param $vendor
	 * @param $resource
	 * @param $id
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function add( $vendor, $resource, $id ) {
		$favorites = $this->get( $vendor, $resource );

		if ( in_array( $id, $favorites, true ) ) {
			return $this;
		}

		$favorites[] = $id;

		$this->save( $vendor, $resource, $favorites );

		return $this;
	}

	/**
	 * @param $vendor
	 * @param $resource
	 * @param $id
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function remove( $vendor, $resource, $id ) {
		$favorites = $this->get( $vendor, $resource );

		if ( ! in_array( $id, $favorites, true ) ) {
			return $this;
		}

		$favorites = array_filter( $favorites, function ( $item ) use ( $id ) {
			return $item !== $id;
		} );

		$this->save( $vendor, $resource, $favorites );

		return $this;
	}

	/**
	 * @param $vendor
	 * @param $resource
	 *
	 * @return string
	 */
	private function get_key( $vendor, $resource ) {
		return "{$vendor}/{$resource}";
	}
}
