<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor box shadow control.
 *
 * A base control for creating box shadows control. Displays input fields for
 * horizontal shadow, vertical shadow, shadow blur, shadow spread and shadow
 * color.
 *
 * @since 1.0.0
 */
class Control_Box_Shadow extends Base_Data_Control {

	/**
	 * Get box shadow control type.
	 *
	 * Retrieve the control type, in this case `box_shadow`.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Control type.
	 */
	public function get_type() {
		return 'box_shadow';
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
	 * Get box shadow control default value.
	 *
	 * Retrieve the default value of the box shadow control. Used to return the
	 * default values while initializing the box shadow control.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Control default value.
	 */
	public function get_default_value() {
		return [
			'horizontal' => 0,
			'vertical' => 0,
			'blur' => 10,
			'spread' => 0,
			'color' => 'rgba(0,0,0,0.5)',
		];
	}

	/**
	 * Get box shadow control sliders.
	 *
	 * Retrieve the sliders of the box shadow control. Sliders are used while
	 * rendering the control output in the editor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Control sliders.
	 */
	public function get_sliders() {
		return [
			'horizontal' => [
				'label' => __( 'Horizontal', 'elementor' ),
				'min' => -100,
				'max' => 100,
			],
			'vertical' => [
				'label' => __( 'Vertical', 'elementor' ),
				'min' => -100,
				'max' => 100,
			],
			'blur' => [
				'label' => __( 'Blur', 'elementor' ),
				'min' => 0,
				'max' => 100,
			],
			'spread' => [
				'label' => __( 'Spread', 'elementor' ),
				'min' => -100,
				'max' => 100,
			],
		];
	}

	/**
	 * Render box shadow control output in the editor.
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
		<div class="elementor-shadow-box">
			<div class="elementor-control-field elementor-color-picker-wrapper">
				<label class="elementor-control-title"><?php echo __( 'Color', 'elementor' ); ?></label>
				<div class="elementor-control-input-wrapper elementor-control-unit-1">
					<div class="elementor-color-picker-placeholder"></div>
				</div>
			</div>
			<?php
			foreach ( $this->get_sliders() as $slider_name => $slider ) :
				$control_uid = $this->get_control_uid( $slider_name );
				?>
				<div class="elementor-shadow-slider elementor-control-type-slider">
					<label for="<?php echo esc_attr( $control_uid ); ?>" class="elementor-control-title"><?php echo $slider['label']; ?></label>
					<div class="elementor-control-input-wrapper">
						<div class="elementor-slider" data-input="<?php echo esc_attr( $slider_name ); ?>"></div>
						<div class="elementor-slider-input elementor-control-unit-2">
							<input id="<?php echo esc_attr( $control_uid ); ?>" type="number" min="<?php echo esc_attr( $slider['min'] ); ?>" max="<?php echo esc_attr( $slider['max'] ); ?>" data-setting="<?php echo esc_attr( $slider_name ); ?>"/>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
