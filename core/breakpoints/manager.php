<?php
namespace Elementor\Core\Breakpoints;

use Elementor\Core\Base\Module;
use Elementor\Core\Kits\Documents\Tabs\Settings_Layout;
use Elementor\Core\Responsive\Files\Frontend;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Manager extends Module {

	const BREAKPOINT_SETTING_PREFIX = 'viewport_';
	const BREAKPOINT_KEY_MOBILE = 'mobile';
	const BREAKPOINT_KEY_MOBILE_EXTRA = 'mobile_extra';
	const BREAKPOINT_KEY_TABLET = 'tablet';
	const BREAKPOINT_KEY_TABLET_EXTRA = 'tablet_extra';
	const BREAKPOINT_KEY_LAPTOP = 'laptop';
	const BREAKPOINT_KEY_WIDESCREEN = 'widescreen';

	private $breakpoints;
	private $active_breakpoints;

	public function get_name() {
		return 'breakpoints';
	}

	/**
	 * Get Breakpoints
	 *
	 * Retrieve the array containing instances of all breakpoints existing in the system, or a single breakpoint if a
	 * name is passed.
	 *
	 * @since 3.2.0
	 *
	 * @param $breakpoint_name
	 * @return Breakpoint[]|Breakpoint
	 */
	public function get_breakpoints( $breakpoint_name = null ) {
		if ( ! $this->breakpoints ) {
			$this->init_breakpoints();
		}
		return self::get_items( $this->breakpoints, $breakpoint_name );
	}

	/**
	 * Get Active Breakpoints
	 *
	 * Retrieve the array of --enabled-- breakpoints, or a single breakpoint if a name is passed.
	 *
	 * @since 3.2.0
	 *
	 * @param string|null $breakpoint_name
	 * @return Breakpoint[]|Breakpoint
	 */
	public function get_active_breakpoints( $breakpoint_name = null ) {
		if ( ! $this->active_breakpoints ) {
			$this->init_active_breakpoints();
		}

		return self::get_items( $this->active_breakpoints, $breakpoint_name );
	}

	/** Has Custom Breakpoints
	 *
	 * Checks whether there are currently custom breakpoints saved in the database.
	 * Returns true if there are breakpoint values saved in the active kit.
	 *
	 * @since 3.2.0
	 *
	 * @return boolean
	 */
	public function has_custom_breakpoints() {
		$breakpoints = $this->get_breakpoints();

		foreach ( $breakpoints as $breakpoint ) {
			/** @var Breakpoint $breakpoint */
			if ( $breakpoint->is_custom() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get Device Min Breakpoint
	 *
	 * For a given device, return the minimum possible breakpoint. Except for the cases of mobile and widescreen
	 * devices, A device's min breakpoint is determined by the previous device's max breakpoint + 1px.
	 *
	 * @param string $device_name
	 * @return int the min breakpoint of the passed device
	 *@since 3.2.0
	 */
	public function get_device_min_breakpoint( $device_name ) {
		if ( 'desktop' === $device_name ) {
			return $this->get_desktop_min_point();
		}

		$active_breakpoints = $this->get_active_breakpoints();
		$current_device_breakpoint = $active_breakpoints[ $device_name ];

		// Since this method is called multiple times, usage of class variables is to memory and processing time.
		// Get only the keys for active breakpoints.
		$breakpoint_keys = array_keys( $active_breakpoints );

		if ( $breakpoint_keys[0] === $device_name ) {
			// For the lowest breakpoint, the min point is always 320.
			$min_breakpoint = 320;
		} elseif ( 'min' === $current_device_breakpoint->get_direction() ) {
			// 'min-width' breakpoints only have a minimum point. The breakpoint value itself the device min point.
			$min_breakpoint = $current_device_breakpoint->get_value();
		} else {
			// This block handles all other devices.
			$device_name_index = array_search( $device_name, $breakpoint_keys, true );

			$previous_index = $device_name_index - 1;
			$previous_breakpoint_key = $breakpoint_keys[ $previous_index ];
			/** @var Breakpoint $previous_breakpoint */
			$previous_breakpoint = $active_breakpoints[ $previous_breakpoint_key ];

			$min_breakpoint = $previous_breakpoint->get_value() + 1;
		}

		return $min_breakpoint;
	}

	public function get_desktop_min_point() {
		$active_breakpoints = $this->get_active_breakpoints();
		$desktop_previous_device = $this->get_desktop_previous_device_key();

		return $active_breakpoints[ $desktop_previous_device ]->get_value() + 1;
	}

	public function refresh() {
		$this->init_breakpoints();
		$this->init_active_breakpoints();
	}

	/**
	 * Get Default Config
	 *
	 * Retrieve the default breakpoints config array. The 'selector' property is used for CSS generation (the
	 * Stylesheet::add_device() method).
	 *
	 * @return array
	 */
	public static function get_default_config() {
		return [
			self::BREAKPOINT_KEY_MOBILE => [
				'label' => __( 'Mobile', 'elementor' ),
				'default_value' => 767,
				'direction' => 'max',
			],
			self::BREAKPOINT_KEY_MOBILE_EXTRA => [
				'label' => __( 'Mobile Extra', 'elementor' ),
				'default_value' => 880,
				'direction' => 'max',
			],
			self::BREAKPOINT_KEY_TABLET => [
				'label' => __( 'Tablet', 'elementor' ),
				'default_value' => 1024,
				'direction' => 'max',
			],
			self::BREAKPOINT_KEY_TABLET_EXTRA => [
				'label' => __( 'Tablet Extra', 'elementor' ),
				'default_value' => 1365,
				'direction' => 'max',
			],
			self::BREAKPOINT_KEY_LAPTOP => [
				'label' => __( 'Laptop', 'elementor' ),
				'default_value' => 1620,
				'direction' => 'max',
			],
			self::BREAKPOINT_KEY_WIDESCREEN => [
				'label' => __( 'Widescreen', 'elementor' ),
				'default_value' => 2400,
				'direction' => 'min',
			],
		];
	}

	/**
	 * Get Stylesheet Templates Path
	 *
	 * @since 3.2.0
	 * @access public
	 * @static
	 */
	public static function get_stylesheet_templates_path() {
		return ELEMENTOR_ASSETS_PATH . 'css/templates/';
	}

	/**
	 * Compile Stylesheet Templates
	 *
	 * @since 3.2.0
	 * @access public
	 * @static
	 */
	public static function compile_stylesheet_templates() {
		foreach ( self::get_stylesheet_templates() as $file_name => $template_path ) {
			$file = new Frontend( $file_name, $template_path );

			$file->update();
		}
	}

	/**
	 * Init Breakpoints
	 *
	 * Creates the breakpoints array, containing instances of each breakpoint. Returns an array of ALL breakpoints,
	 * both enabled and disabled.
	 *
	 * @since 3.2.0
	 */
	private function init_breakpoints() {
		$breakpoints = [];
		$default_config = self::get_default_config();

		foreach ( $default_config as $breakpoint_name => $breakpoint_config ) {
			$args = [ 'name' => $breakpoint_name ] + $breakpoint_config;

			// Make sure the default Mobile and Tablet breakpoints are always enabled.
			$args['is_enabled'] = self::BREAKPOINT_KEY_MOBILE === $breakpoint_name || self::BREAKPOINT_KEY_TABLET === $breakpoint_name;

			$breakpoints[ $breakpoint_name ] = new Breakpoint( $args );
		}

		$this->breakpoints = $breakpoints;
	}

	/**
	 * Init Active Breakpoints
	 *
	 * Create/Refresh the array of --enabled-- breakpoints.
	 *
	 * @since 3.2.0
	 */
	private function init_active_breakpoints() {
		$this->active_breakpoints = array_filter( $this->get_breakpoints(), function( $breakpoint ) {
			/** @var Breakpoint $breakpoint */
			return $breakpoint->is_enabled();
		} );
	}

	private function get_desktop_previous_device_key() {
		$config_array_keys = array_keys( $this->get_active_breakpoints() );
		$num_of_devices = count( $config_array_keys );

		// If the widescreen breakpoint is active, the device that's previous to desktop is the last one before
		// widescreen.
		if ( self::BREAKPOINT_KEY_WIDESCREEN === $config_array_keys[ $num_of_devices - 1 ] ) {
			$desktop_previous_device = $config_array_keys[ $num_of_devices - 2 ];
		} else {
			// If the widescreen breakpoint isn't active, we just take the last device returned by the config.
			$desktop_previous_device = $config_array_keys[ $num_of_devices - 1 ];
		}

		return $desktop_previous_device;
	}

	/**
	 * Get Stylesheet Templates
	 *
	 * @since 3.2.0
	 * @access private
	 * @static
	 */
	private static function get_stylesheet_templates() {
		$templates_paths = glob( self::get_stylesheet_templates_path() . '*.css' );

		$templates = [];

		foreach ( $templates_paths as $template_path ) {
			$file_name = 'custom-' . basename( $template_path );

			$templates[ $file_name ] = $template_path;
		}

		$deprecated_hook = 'elementor/core/responsive/get_stylesheet_templates';
		$replacement_hook = 'elementor/core/breakpoints/get_stylesheet_template';

		Plugin::$instance->modules_manager->get_modules( 'dev-tools' )->deprecation->deprecated_hook( $deprecated_hook, '3.2.0', $replacement_hook );

		// TODO: REMOVE THIS DEPRECATED HOOK IN ELEMENTOR v3.10.0/v4.0.0
		$templates = apply_filters( $deprecated_hook, $templates );

		return apply_filters( $replacement_hook, $templates );
	}
}
