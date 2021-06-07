<?php
namespace Elementor\Modules\Favorites\Types;

use Elementor\Modules\Favorites\Favorites_Type;
use Elementor\Plugin;

class Widgets extends Favorites_Type {
	const CATEGORY_SLUG = 'favorites';

	/**
	 * Widgets favorites type constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'elementor/widget/categories', [ $this, 'update_widget_categories' ], 10, 2 );
	}

	/**
	 * @inheritDoc
	 */
	public function get_name() {
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

	/**
	 * Updat
	 *
	 * @param $categories
	 *
	 * @return string[]
	 */
	public function update_widget_categories( $categories, $widget ) {
		if ( false !== array_search( $widget->get_name(), $this->values() ) ) {
			$categories[] = static::CATEGORY_SLUG;

			return $categories;
		}

		return $categories;
	}
}
