<?php
namespace Elementor\Core\DocumentTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Page extends PageBase {

	/**
	 * Get Properties
	 *
	 * Return Document Configuration Properties.
	 *
	 * @return array $properties
	 */
	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['cpt'] = [ 'page' ];
		$properties['support_kit'] = true;

		return $properties;
	}

	public static function get_type() {
		return 'wp-page';
	}

	/**
	 * Get Title
	 *
	 * @access public
	 * @static
	 */
	public static function get_title() {
		return __( 'Page', 'elementor' );
	}

	public static function get_plural_title() {
		return __( 'Pages', 'elementor' );
	}
}
