<?php

namespace Elementor\Modules\WidgetsPanel;

use Elementor\Core\Base\Module as BaseModule;
use Elementor\Plugin;
use Elementor\Modules\WidgetsPanel\Data\Controller;
use Elementor\Modules\WidgetsPanel\Data\Endpoints\Favorites;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor widgets panel module.
 *
 * Elementor widgets panel module handler class is responsible for registering
 * and managing Elementor widgets panel modules.
 *
 * @since 3.0.16
 */
class Module extends BaseModule {

	/**
	 * Set meta key name
	 *
	 * Filed meta_key in database table wp_usermeta
	 *
	 * @since 3.0.16
	 */
	const META_KEY = '_elementor_favorites_widgets';

	/**
	 * Module constructor.
	 *
	 * @since 3.0.16
	 * @access public
	 */
	public function __construct() {
		$this->add_hooks();
	}

	/**
	 * Get module name.
	 *
	 * Retrieve the widgets-panel module name.
	 *
	 * @since 3.0.16
	 * @access public
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'widgets-panel';
	}

	/**
	 * Add hook type filter
	 *
	 * This hook help to add category "favorites" to default widget categories, when loading the editor panel.
	 *
	 * @since 3.0.16
	 * @access public
	 */
	public function add_hooks() {
		Plugin::$instance->data_manager->register_controller( Controller::class );

		add_filter( 'elementor/document/config', function( $config, $document_id ) {
			$config['panel'] = [ 'elements_categories' => [
				'favorites' => [
					'title' => __( 'Favorites', 'elementor' ),
					'icon' => 'eicon-font',
				],
			] ];

			$config['widgets'] = [ 'inner-section' => [
				'elType' => 'section',
				'title' => __( 'Inner Section', 'elementor' ),
				'icon' => 'eicon-columns',
				'reload_preview' => false,
				'widget_type' => 'inner-section',
				'keywords' => [ 'row', 'columns', 'nested' ],
				'categories' => [ 'basic' ],
				'show_in_panel' => true,
			] ];

			$document = Plugin::$instance->documents->get( $document_id );
			if ( $document::get_property( 'has_elements' ) ) {
				if ( ! isset( $config['widgets'] ) ) {
					$config['widgets'] = [];
				}
				$favorites_widgets = Favorites::get_favorite_widget();
				if ( ! empty( $favorites_widgets ) ) {
					foreach ( $favorites_widgets as $widget_name => $value ) {
						$config['widgets'][ $widget_name ]['categories'][] = 'favorites';
					}
				}
			}

			return $config;

		}, 10, 2 );

		Favorites::add_tracking_data();
	}

}
