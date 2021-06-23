<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor hover animation control.
 *
 * A base control for creating hover animation control. Displays a select box
 * with the available hover animation effects @see Control_Hover_Animation::get_animations()
 *
 * @since 1.0.0
 */
class Control_Hover_Animation extends Base_Data_Control {

	/**
	 * Animations.
	 *
	 * Holds all the available hover animation effects of the control.
	 *
	 * @access private
	 * @static
	 *
	 * @var array
	 */
	private static $_animations;

	/**
	 * Get hover animation control type.
	 *
	 * Retrieve the control type, in this case `hover_animation`.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Control type.
	 */
	public function get_type() {
		return 'hover_animation';
	}

	/**
	 * Get animations.
	 *
	 * Retrieve the available hover animation effects.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return array Available hover animation.
	 */
	public static function get_animations() {
		if ( is_null( self::$_animations ) ) {
			self::$_animations = [
				'grow' => 'Grow',
				'shrink' => 'Shrink',
				'pulse' => 'Pulse',
				'pulse-grow' => 'Pulse Grow',
				'pulse-shrink' => 'Pulse Shrink',
				'push' => 'Push',
				'pop' => 'Pop',
				'bounce-in' => 'Bounce In',
				'bounce-out' => 'Bounce Out',
				'rotate' => 'Rotate',
				'grow-rotate' => 'Grow Rotate',
				'float' => 'Float',
				'sink' => 'Sink',
				'bob' => 'Bob',
				'hang' => 'Hang',
				'skew' => 'Skew',
				'skew-forward' => 'Skew Forward',
				'skew-backward' => 'Skew Backward',
				'wobble-vertical' => 'Wobble Vertical',
				'wobble-horizontal' => 'Wobble Horizontal',
				'wobble-to-bottom-right' => 'Wobble To Bottom Right',
				'wobble-to-top-right' => 'Wobble To Top Right',
				'wobble-top' => 'Wobble Top',
				'wobble-bottom' => 'Wobble Bottom',
				'wobble-skew' => 'Wobble Skew',
				'buzz' => 'Buzz',
				'buzz-out' => 'Buzz Out',
			];

			$additional_animations = [];
			/**
			 * Element hover animations list.
			 *
			 * @since 2.4.0
			 *
			 * @param array $additional_animations Additional Animations array.
			 */
			$additional_animations = apply_filters( 'elementor/controls/hover_animations/additional_animations', $additional_animations );
			self::$_animations = array_merge( self::$_animations, $additional_animations );
		}

		return self::$_animations;
	}

	/**
	 * Render hover animation control output in the editor.
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
		?>
		<div class="elementor-control-field">
			<label for="<?php echo $control_uid; ?>" class="elementor-control-title">{{{ data.label }}}</label>
			<div class="elementor-control-input-wrapper">
				<select id="<?php echo $control_uid; ?>" data-setting="{{ data.name }}">
					<option value=""><?php echo __( 'None', 'elementor' ); ?></option>
					<?php foreach ( self::get_animations() as $animation_name => $animation_title ) : ?>
						<option value="<?php echo $animation_name; ?>"><?php echo $animation_title; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<# if ( data.description ) { #>
		<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}

	/**
	 * Get hover animation control default settings.
	 *
	 * Retrieve the default settings of the hover animation control. Used to return
	 * the default settings while initializing the hover animation control.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Control default settings.
	 */
	protected function get_default_settings() {
		return [
			'label_block' => true,
		];
	}

	public static function get_assets( $setting ) {
		if ( ! $setting || 'none' === $setting ) {
			return [];
		}

		return [
			'styles' => [ 'e-animations' ],
		];
	}
}
