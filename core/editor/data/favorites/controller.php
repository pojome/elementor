<?php
namespace Elementor\Core\Editor\Data\Favorites;

use Elementor\Data\Base\Controller as Controller_Base;

class Controller extends Controller_Base {

	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return 'favorites';
	}

	/**
	 * @inheritDoc
	 */
	public function register_endpoints() {}

	/**
	 * @inheritDoc
	 */
	public function register_internal_endpoints() {
		$this->register_endpoint( Endpoints\Index::class );
	}
}
