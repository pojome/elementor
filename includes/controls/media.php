<?php
namespace Elementor;

use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor media control.
 *
 * A base control for creating a media chooser control. Based on the WordPress
 * media library. Used to select an image from the WordPress media library.
 *
 * @since 1.0.0
 */
class Control_Media extends Control_Base_Multiple {

	/**
	 * Get media control type.
	 *
	 * Retrieve the control type, in this case `media`.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Control type.
	 */
	public function get_type() {
		return 'media';
	}

	/**
	 * Get media control default values.
	 *
	 * Retrieve the default value of the media control. Used to return the default
	 * values while initializing the media control.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Control default value.
	 */
	public function get_default_value() {
		return [
			'url' => '',
			'id' => '',
		];
	}

	/**
	 * Import media images.
	 *
	 * Used to import media control files from external sites while importing
	 * Elementor template JSON file, and replacing the old data.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $settings Control settings
	 *
	 * @return array Control settings.
	 */
	public function on_import( $settings ) {
		if ( empty( $settings['url'] ) ) {
			return $settings;
		}

		$settings = Plugin::$instance->templates_manager->get_import_images_instance()->import( $settings );

		if ( ! $settings ) {
			$settings = [
				'id' => '',
				'url' => Utils::get_placeholder_image_src(),
			];
		}

		return $settings;
	}

	/**
	 * Enqueue media control scripts and styles.
	 *
	 * Used to register and enqueue custom scripts and styles used by the media
	 * control.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue() {
		global $wp_version;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_media();

		wp_enqueue_style(
			'media',
			admin_url( '/css/media' . $suffix . '.css' ),
			[],
			$wp_version
		);

		wp_register_script(
			'image-edit',
			'/wp-admin/js/image-edit' . $suffix . '.js',
			[
				'jquery',
				'json2',
				'imgareaselect',
			],
			$wp_version,
			true
		);

		wp_enqueue_script( 'image-edit' );
	}

	/**
	 * Render media control output in the editor.
	 *
	 * Used to generate the control HTML in the editor using Underscore JS
	 * template. The variables for the class are available using `data` JS
	 * object.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function content_template() {
		?>
		<#
			// For BC.
			if ( data.media_type ) {
				data.media_types = [ data.media_type ];
			}

			if ( data.should_include_svg_inline_option ) {
				data.media_types.push( 'svg' );
			}

			// Determine if the current media type is viewable.
			const isViewable = () => {
				const viewable = [
					'image',
					'video',
					'svg',
				];

				// Make sure that all media types are viewable.
				return data.media_types.every( ( type ) => viewable.includes( type ) );
			};

			// Get the preview type for the current media type.
			const getPreviewType = () => {
				if ( data.media_types.includes( 'video' ) ) {
					return 'video';
				}

				if ( data.media_types.includes( 'image' ) || data.media_types.includes( 'svg' ) ) {
					return 'image';
				}

				return 'none';
			}

			// Retrieve a button label by media type.
			const getButtonLabel = ( mediaType ) => {
				switch( mediaType ) {
					case 'image':
						return '<?php esc_html_e( 'Choose Image', 'elementor' ); ?>';

					case 'video':
						return '<?php esc_html_e( 'Choose Video', 'elementor' ); ?>';

					case 'svg':
						return '<?php esc_html_e( 'Choose SVG', 'elementor' ); ?>';

					default:
						return '<?php esc_html_e( 'Choose File', 'elementor' ); ?>';
				}
			}
		#>
		<div class="elementor-control-field elementor-control-media">
			<label class="elementor-control-title">{{{ data.label }}}</label>
			<#
			if ( isViewable() ) {
				let inputWrapperClasses = 'elementor-control-input-wrapper elementor-aspect-ratio-219';

				if ( ! data.label_block ) {
					inputWrapperClasses += ' elementor-control-unit-5';
				}
			#>
				<div class="{{{ inputWrapperClasses }}}">
					<div class="elementor-control-media__content elementor-control-tag-area elementor-control-preview-area elementor-fit-aspect-ratio">
						<div class="elementor-control-media-upload-button elementor-control-media__content__upload-button elementor-fit-aspect-ratio">
							<i class="eicon-plus-circle" aria-hidden="true"></i>
						</div>
						<div class="elementor-control-media-area elementor-fit-aspect-ratio">
							<div class="elementor-control-media__remove elementor-control-media__content__remove" title="<?php echo __( 'Remove', 'elementor' ); ?>">
								<i class="eicon-trash-o"></i>
							</div>
							<#
								switch( getPreviewType() ) {
									case 'image':
										#>
										<div class="elementor-control-media__preview elementor-fit-aspect-ratio"></div>
										<#
										break;

									case 'video':
										#>
										<video class="elementor-control-media-video" preload="metadata"></video>
										<i class="eicon-video-camera"></i>
										<#
										break;
								}
							#>
						</div>
						<div class="elementor-control-media__tools elementor-control-dynamic-switcher-wrapper">
							<#
								data.media_types.forEach( ( type ) => {
									#>
									<div class="elementor-control-media__tool elementor-control-media__replace" data-media-type="{{{ type }}}">{{{ getButtonLabel( type ) }}}</div>
									<#
								} );
							#>
						</div>
					</div>
				</div>
			<# } /* endif isViewable() */ else { #>
				<div class="elementor-control-media__file elementor-control-preview-area">
					<div class="elementor-control-media__file__content">
						<div class="elementor-control-media__file__content__label"><?php echo __( 'Click the media icon to upload file', 'elementor' ); ?></div>
						<div class="elementor-control-media__file__content__info">
							<div class="elementor-control-media__file__content__info__icon">
								<i class="eicon-document-file"></i>
							</div>
							<div class="elementor-control-media__file__content__info__name"></div>
						</div>
					</div>
					<div class="elementor-control-media__file__controls">
						<div class="elementor-control-media__remove elementor-control-media__file__controls__remove" title="<?php echo __( 'Remove', 'elementor' ); ?>">
							<i class="eicon-trash-o"></i>
						</div>
						<div class="elementor-control-media__file__controls__upload-button elementor-control-media-upload-button" title="<?php echo __( 'Upload', 'elementor' ); ?>">
							<i class="eicon-upload"></i>
						</div>
					</div>
				</div>
			<# } #>
			<# if ( data.description ) { #>
				<div class="elementor-control-field-description">{{{ data.description }}}</div>
			<# } #>
			<input type="hidden" data-setting="{{ data.name }}"/>
		</div>
		<?php
	}

	/**
	 * Get media control default settings.
	 *
	 * Retrieve the default settings of the media control. Used to return the default
	 * settings while initializing the media control.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Control default settings.
	 */
	protected function get_default_settings() {
		return [
			'label_block' => true,
			'media_types' => [
				'image',
			],
			'dynamic' => [
				'categories' => [ TagsModule::IMAGE_CATEGORY ],
				'returnType' => 'object',
			],
		];
	}

	/**
	 * Get media control image title.
	 *
	 * Retrieve the `title` of the image selected by the media control.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param array $attachment Media attachment.
	 *
	 * @return string Image title.
	 */
	public static function get_image_title( $attachment ) {
		if ( empty( $attachment['id'] ) ) {
			return '';
		}

		return get_the_title( $attachment['id'] );
	}

	/**
	 * Get media control image alt.
	 *
	 * Retrieve the `alt` value of the image selected by the media control.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param array $instance Media attachment.
	 *
	 * @return string Image alt.
	 */
	public static function get_image_alt( $instance ) {
		if ( empty( $instance['id'] ) ) {
			// For `Insert From URL` images.
			return isset( $instance['alt'] ) ? trim( strip_tags( $instance['alt'] ) ) : '';
		}

		$attachment_id = $instance['id'];
		if ( ! $attachment_id ) {
			return '';
		}

		$attachment = get_post( $attachment_id );
		if ( ! $attachment ) {
			return '';
		}

		$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		if ( ! $alt ) {
			$alt = $attachment->post_excerpt;
			if ( ! $alt ) {
				$alt = $attachment->post_title;
			}
		}
		return trim( strip_tags( $alt ) );
	}
}
