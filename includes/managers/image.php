<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor images manager.
 *
 * Elementor images manager handler class is responsible for retrieving image
 * details.
 *
 * @since 1.0.0
 */
class Images_Manager {

	/**
	 * Get images details.
	 *
	 * Retrieve details for all the images.
	 *
	 * Fired by `wp_ajax_elementor_get_images_details` action.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function get_images_details() {
		$items = $_POST['items'];
		$urls  = [];

		foreach ( $items as $item ) {
			$urls[ $item['id'] ] = $this->get_details( $item['id'], $item['size'], $item['is_first_time'] );
		}

		wp_send_json_success( $urls );
	}

	/**
	 * Get image details.
	 *
	 * Retrieve single image details.
	 *
	 * Fired by `wp_ajax_elementor_get_image_details` action.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string       $id            Image attachment ID.
	 * @param string|array $size          Image size. Accepts any valid image
	 *                                    size, or an array of width and height
	 *                                    values in pixels (in that order).
	 * @param string       $is_first_time Set 'true' string to force reloading
	 *                                    all image sizes.
	 *
	 * @return array URLs with different image sizes.
	 */
	public function get_details( $id, $size, $is_first_time ) {
		if ( ! class_exists( 'Group_Control_Image_Size' ) ) {
			require_once ELEMENTOR_PATH . '/includes/controls/groups/image-size.php';
		}

		if ( 'true' === $is_first_time ) {
			$sizes = get_intermediate_image_sizes();
			$sizes[] = 'full';
		} else {
			$sizes = [];
		}

		$sizes[] = $size;
		$urls = [];
		foreach ( $sizes as $size ) {
			if ( 0 === strpos( $size, 'custom_' ) ) {
				preg_match( '/custom_(\d*)x(\d*)/', $size, $matches );

				$instance = [
					'image_size' => 'custom',
					'image_custom_dimension' => [
						'width' => $matches[1],
						'height' => $matches[2],
					],
				];

				$urls[ $size ] = Group_Control_Image_Size::get_attachment_image_src( $id, 'image', $instance );
			} else {
				$urls[ $size ] = wp_get_attachment_image_src( $id, $size )[0];
			}
		}

		return $urls;
	}

	/**
	 * Get Light-Box Image Attributes
	 *
	 * Used to retrieve an array of image attributes to be used for displaying an image in Elementor's Light Box module.
	 *
	 * @param int $id       The ID of the image
	 *
	 * @return array An array of image attributes including `title` and `description`.
	 * @since 2.9.0
	 * @access public
	 */

	public function get_lightbox_image_attributes( $id ) {
		$attributes = [];
		$kit = Plugin::$instance->kits_manager->get_active_kit();
		$lightbox_title_src = $kit->get_settings( 'lightbox_title_src' );
		$lightbox_description_src = $kit->get_settings( 'lightbox_description_src' );
		$attachment = get_post( $id );
		$image_data = [
			'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
			'caption' => $attachment->post_excerpt,
			'description' => $attachment->post_content,
			'title' => $attachment->post_title,
		];

		if ( $lightbox_title_src && $image_data[ $lightbox_title_src ] ) {
			$attributes['title'] = $image_data[ $lightbox_title_src ];
		}

		if ( $lightbox_description_src && $image_data[ $lightbox_description_src ] ) {
			$attributes['description'] = $image_data[ $lightbox_description_src ];
		}

		return $attributes;
	}

	private static function get_all_image_sizes() {
		return Group_Control_Image_Size::get_all_image_sizes();
	}

	public static function get_image_sizes( $size, $custom_dimension = [] ) {
		if ( 'custom' === $size ) {
			return $custom_dimension;
		} else {
			$image_sizes = self::get_all_image_sizes();

			if ( ! array_key_exists( $size, $image_sizes ) ) {
				$size = 'large';
			}

			return $image_sizes[ $size ];
		}
	}

	public static function is_svg_image( $image_id ) {
		return get_post_mime_type( $image_id ) === 'image/svg+xml';
	}

	public function set_svg_image_size( $image_data, $attachment_id, $size ) {
		if ( ! self::is_svg_image( $attachment_id ) ) {
			return $image_data;
		}

		$image_sizes = self::get_all_image_sizes();

		// In some cases that the image is set with custom sizes, the filter value of the $size might be an array.
		if ( is_array( $size ) ) {
			$size = 'custom';
		}

		if ( ! array_key_exists( $size, $image_sizes ) ) {
			$size = 'large';
		}

		$image_data['1'] = $image_sizes[ $size ]['width'];
		$image_data['2'] = $image_sizes[ $size ]['height'];

		return $image_data;
	}

	private static function handle_svg_image_filters( $action, $image_size, $image_custom_size_array ) {
		$is_custom_size = 'custom' === $image_size;
		$self = new self();

		if ( 'before_render' === $action ) {
			if ( $is_custom_size ) {
				add_image_size( 'custom', $image_custom_size_array['width'], $image_custom_size_array['height'] );
			}

			add_filter( 'wp_get_attachment_image_src', [ $self, 'set_svg_image_size' ], 10, 4 );
		} elseif ( 'after_render' === $action ) {
			remove_filter( 'wp_get_attachment_image_src', [ $self, 'set_svg_image_size' ] );

			if ( $is_custom_size ) {
				remove_image_size( 'custom' );
			}
		}
	}

	// Responsible for applying image size when the image type is svg.
	public static function handle_svg_image_size( $action, $image_id, $image_size = '', $image_custom_size_array = [] ) {
		if ( ! is_array( $image_id ) ) {
			$image_id = [ $image_id ];
		}

		// We need to add the SVG filters only once, therefore if we find at least one SVG image we don't need to proceed.
		foreach ( $image_id as $id ) {
			if ( self::is_svg_image( $id ) ) {
				self::handle_svg_image_filters( $action, $image_size, $image_custom_size_array );

				break;
			}
		}
	}

	/**
	 * Images manager constructor.
	 *
	 * Initializing Elementor images manager.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'wp_ajax_elementor_get_images_details', [ $this, 'get_images_details' ] );
	}
}
