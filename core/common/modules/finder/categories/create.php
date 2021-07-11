<?php
namespace Elementor\Core\Common\Modules\Finder\Categories;

use Elementor\Core\Common\Modules\Finder\Base_Category;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Create Category
 *
 * Provides items related to creation of new posts/pages/templates etc.
 */
class Create extends Base_Category {

	/**
	 * Get title.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Create', 'elementor' );
	}

	/**
	 * Get category items.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function get_category_items( array $options = [] ) {
		$result = [];

		$registered_document_types = Plugin::$instance->documents->get_document_types();
		$elementor_supported_post_types = array_flip( get_post_types_by_support( 'elementor' ) );

		foreach ( $registered_document_types as $document_name => $document_class ) {
			$document_properties = $document_class::get_properties();

			if ( empty( $document_properties['show_in_finder'] ) ) {
				continue;
			}

			// To Support backward compatibility.
			if ( empty( $document_properties['cpt'] ) ) {
				continue;
			}

			foreach ( $document_properties['cpt'] as $cpt ) {
				unset( $elementor_supported_post_types[ $cpt ] );
			}

			if ( in_array( $document_name, [ 'post', 'page', 'code_snippet' ], true ) ) {
				continue;
			}
			// End backward compatibility.

			$url = $this->create_item_url_by_document_class( $document_class );

			if ( ! $url ) {
				continue;
			}

			$result[ $document_name ] = $url;
		}

		// Handled by new mechanism.
		$ignore_list = [
			'elementor_library',
		];

		// Old mechanism.
		foreach ( $elementor_supported_post_types as $post_type => $val ) {
			if ( in_array( $post_type, $ignore_list, true ) ) {
				continue;
			}

			$url = $this->create_item_url_by_post_type( $post_type );

			if ( ! $url ) {
				continue;
			}

			$result[ $post_type ] = $url;
		}

		return $result;
	}

	private function create_item_url_by_post_type( $post_type ) {
		$post_type_object = get_post_type_object( $post_type );

		// If there is an old post type from inactive plugins
		if ( ! $post_type_object ) {
			return false;
		}

		return $this->get_create_new_template(
			$post_type_object->labels->singular_name,
			Plugin::$instance->documents->get_create_new_post_url( $post_type )
		);
	}

	private function create_item_url_by_document_class( $document_class ) {
		return $this->get_create_new_template(
			$document_class::get_title(),
			$document_class::get_create_url()
		);
	}

	private function get_create_new_template( $title, $url ) {
		return [
			/* translators: %s the title of the post type */
			'title' => sprintf( __( 'Add New %s', 'elementor' ), $title ),
			'icon' => 'plus-circle-o',
			'url' => $url,
			'keywords' => [ $title, 'post', 'page', 'template', 'new', 'create' ],
		];
	}
}
