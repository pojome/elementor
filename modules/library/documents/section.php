<?php
namespace Elementor\Modules\Library\Documents;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Elementor section library document.
 *
 * Elementor section library document handler class is responsible for
 * handling a document of a section type.
 *
 * @since 2.0.0
 */
class Section extends Library_Document {

	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['support_kit'] = true;

		return $properties;
	}

	public static function get_type() {
		return 'section';
	}

	/**
	 * Get document title.
	 *
	 * Retrieve the document title.
	 *
	 * @since 2.0.0
	 * @access public
	 * @static
	 *
	 * @return string Document title.
	 */
	public static function get_title() {
		return __( 'Section', 'elementor' );
	}

	public static function get_plural_title() {
		return __( 'Sections', 'elementor' );
	}
}
