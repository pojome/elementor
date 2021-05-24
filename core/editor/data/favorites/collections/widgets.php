<?php
namespace Elementor\Core\Editor\Data\Favorites\Collections;

use Elementor\Core\Editor\Data\Favorites\Collection;
use Elementor\Plugin;

class Widgets extends Collection {

	/**
	 * @inheritDoc
	 */
	public static function get_name() {
		return 'widgets';
	}

	/**
	 * @inheritDoc
	 */
	public function prepare( $favorites ) {
		return array_intersect( parent::prepare( $favorites ), $this->get_available() );
	}

	/**
	 * Get all available widgets.
	 *
	 * @return string[]
	 */
	public function get_available() {
		return (array) array_keys(
			Plugin::instance()->widgets_manager->get_widget_types()
		);
	}
}
