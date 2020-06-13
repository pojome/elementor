<?php
namespace Elementor;

use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor URL control.
 *
 * A base control for creating url control. Displays a URL input with the
 * ability to set the target of the link to `_blank` to open in a new tab.
 *
 * @since 1.0.0
 */
class Control_URL extends Base_Data_Control {

	/**
	 * Get url control type.
	 *
	 * Retrieve the control type, in this case `url`.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Control type.
	 */
	public function get_type() {
		return 'url';
	}

	/**
	 * Get Value Type
	 *
	 * Check whether the default value of the control is single (such as string, int) or multiple (array).
	 * Default value is 'single', can also be 'multiple'.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @return string Value Type
	 */
	public function get_value_type() {
		return 'multiple';
	}

	/**
	 * Get url control default values.
	 *
	 * Retrieve the default value of the url control. Used to return the default
	 * values while initializing the url control.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Control default value.
	 */
	public function get_default_value() {
		return [
			'url' => '',
			'is_external' => '',
			'nofollow' => '',
			'custom_attributes' => '',
		];
	}

	/**
	 * Get url control default settings.
	 *
	 * Retrieve the default settings of the url control. Used to return the default
	 * settings while initializing the url control.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Control default settings.
	 */
	protected function get_default_settings() {
		return [
			'label_block' => true,
			'placeholder' => __( 'Paste URL or type', 'elementor' ),
			'autocomplete' => true,
			'options' => [ 'is_external', 'nofollow', 'custom_attributes' ],
			'dynamic' => [
				'categories' => [ TagsModule::URL_CATEGORY ],
				'property' => 'url',
			],
			'custom_attributes_description' => __( 'Set custom attributes for the link element. Separate attribute keys from values using the | (pipe) character. Separate key-value pairs with a comma.', 'elementor' )
			. ' <a href="https://go.elementor.com/panel-link-custom-attributes/" target="_blank">' . __( 'Learn More', 'elementor' ) . '</a>',
		];
	}

	/**
	 * Render url control output in the editor.
	 *
	 * Used to generate the control HTML in the editor using Underscore JS
	 * template. The variables for the class are available using `data` JS
	 * object.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function content_template() {
		$control_uid = $this->get_control_uid();
		$is_external_control_uid = $this->get_control_uid( 'is_external' );
		$nofollow_control_uid = $this->get_control_uid( 'nofollow' );
		$custom_attributes_uid = $this->get_control_uid( 'custom_attributes' );
		?>
		<div class="elementor-control-field elementor-control-url-external-{{{ ( data.options.length || data.show_external ) ? 'show' : 'hide' }}}">
			<label for="<?php echo $control_uid; ?>" class="elementor-control-title">{{{ data.label }}}</label>
			<div class="elementor-control-input-wrapper elementor-control-dynamic-switcher-wrapper">
				<i class="elementor-control-url-autocomplete-spinner eicon-loading eicon-animation-spin" aria-hidden="true"></i>
				<input id="<?php echo $control_uid; ?>" class="elementor-control-tag-area elementor-input" data-setting="url" placeholder="{{ data.placeholder }}" />
				<input id="_ajax_linking_nonce" type="hidden" value="<?php echo wp_create_nonce( 'internal-linking' ); ?>" />
				<div class="elementor-control-url-more tooltip-target elementor-control-unit-1" data-tooltip="<?php echo __( 'Link Options', 'elementor' ); ?>">
					<i class="eicon-cog" aria-hidden="true"></i>
				</div>
			</div>
			<div class="elementor-control-url-more-options">
				<div class="elementor-control-url-option">
					<input id="<?php echo $is_external_control_uid; ?>" type="checkbox" class="elementor-control-url-option-input" data-setting="is_external">
					<label for="<?php echo $is_external_control_uid; ?>"><?php echo __( 'Open in new window', 'elementor' ); ?></label>
				</div>
				<div class="elementor-control-url-option">
					<input id="<?php echo $nofollow_control_uid; ?>" type="checkbox" class="elementor-control-url-option-input" data-setting="nofollow">
					<label for="<?php echo $nofollow_control_uid; ?>"><?php echo __( 'Add nofollow', 'elementor' ); ?></label>
				</div>
				<div class="elementor-control-url__custom-attributes">
					<label for="<?php echo $custom_attributes_uid; ?>" class="elementor-control-url__custom-attributes-label"><?php echo __( 'Custom Attributes', 'elementor' ); ?></label>
					<input type="text" id="<?php echo $custom_attributes_uid; ?>" class="elementor-control-unit-5" placeholder="key|value,key|value..." data-setting="custom_attributes">
				</div>
				<# if ( ( data.options && -1 !== data.options.indexOf( 'custom_attributes' ) ) && data.custom_attributes_description ) { #>
				<div class="elementor-control-field-description">{{{ data.custom_attributes_description }}}</div>
				<# } #>
			</div>
		</div>
		<# if ( data.description ) { #>
		<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}
}
