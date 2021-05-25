<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor element base.
 *
 * An abstract class to register new Elementor elements. It extended the
 * `Controls_Stack` class to inherit its properties.
 *
 * This abstract class must be extended in order to register new elements.
 *
 * @since 1.0.0
 * @abstract
 */
abstract class Element_Base extends Controls_Stack {

	/**
	 * Child elements.
	 *
	 * Holds all the child elements of the element.
	 *
	 * @access private
	 *
	 * @var Element_Base[]
	 */
	private $children;

	/**
	 * Element render attributes.
	 *
	 * Holds all the render attributes of the element. Used to store data like
	 * the HTML class name and the class value, or HTML element ID name and value.
	 *
	 * @access private
	 *
	 * @var array
	 */
	private $render_attributes = [];

	/**
	 * Element default arguments.
	 *
	 * Holds all the default arguments of the element. Used to store additional
	 * data. For example WordPress widgets use this to store widget names.
	 *
	 * @access private
	 *
	 * @var array
	 */
	private $default_args = [];

	/**
	 * Is type instance.
	 *
	 * Whether the element is an instance of that type or not.
	 *
	 * @access private
	 *
	 * @var bool
	 */
	private $is_type_instance = true;

	/**
	 * Depended scripts.
	 *
	 * Holds all the element depended scripts to enqueue.
	 *
	 * @since 1.9.0
	 * @access private
	 *
	 * @var array
	 */
	private $depended_scripts = [];

	/**
	 * Depended styles.
	 *
	 * Holds all the element depended styles to enqueue.
	 *
	 * @since 1.9.0
	 * @access private
	 *
	 * @var array
	 */
	private $depended_styles = [];

	/**
	 * Add script depends.
	 *
	 * Register new script to enqueue by the handler.
	 *
	 * @since 1.9.0
	 * @access public
	 *
	 * @param string $handler Depend script handler.
	 */
	public function add_script_depends( $handler ) {
		$this->depended_scripts[] = $handler;
	}

	/**
	 * Add style depends.
	 *
	 * Register new style to enqueue by the handler.
	 *
	 * @since 1.9.0
	 * @access public
	 *
	 * @param string $handler Depend style handler.
	 */
	public function add_style_depends( $handler ) {
		$this->depended_styles[] = $handler;
	}

	/**
	 * Get script dependencies.
	 *
	 * Retrieve the list of script dependencies the element requires.
	 *
	 * @since 1.3.0
	 * @access public
	 *
	 * @return array Element scripts dependencies.
	 */
	public function get_script_depends() {
		return $this->depended_scripts;
	}

	/**
	 * Enqueue scripts.
	 *
	 * Registers all the scripts defined as element dependencies and enqueues
	 * them. Use `get_script_depends()` method to add custom script dependencies.
	 *
	 * @since 1.3.0
	 * @access public
	 */
	final public function enqueue_scripts() {
		$deprecated_scripts = [
			'jquery-slick' => [
				'version' => '2.7.0',
				'replacement' => 'Swiper',
			],
		];

		foreach ( $this->get_script_depends() as $script ) {
			if ( isset( $deprecated_scripts[ $script ] ) ) {
				Utils::handle_deprecation( $script, $deprecated_scripts[ $script ]['version'], $deprecated_scripts[ $script ]['replacement'] );
			}

			wp_enqueue_script( $script );
		}
	}

	/**
	 * Get style dependencies.
	 *
	 * Retrieve the list of style dependencies the element requires.
	 *
	 * @since 1.9.0
	 * @access public
	 *
	 * @return array Element styles dependencies.
	 */
	public function get_style_depends() {
		return $this->depended_styles;
	}

	/**
	 * Enqueue styles.
	 *
	 * Registers all the styles defined as element dependencies and enqueues
	 * them. Use `get_style_depends()` method to add custom style dependencies.
	 *
	 * @since 1.9.0
	 * @access public
	 */
	final public function enqueue_styles() {
		foreach ( $this->get_style_depends() as $style ) {
			wp_enqueue_style( $style );
		}
	}

	/**
	 * @since 1.0.0
	 * @deprecated 2.6.0
	 * @access public
	 * @static
	 */
	final public static function add_edit_tool() {}

	/**
	 * @since 2.2.0
	 * @deprecated 2.6.0
	 * @access public
	 * @static
	 */
	final public static function is_edit_buttons_enabled() {
		return get_option( 'elementor_edit_buttons' );
	}

	/**
	 * Get default child type.
	 *
	 * Retrieve the default child type based on element data.
	 *
	 * Note that not all elements support children.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @abstract
	 *
	 * @param array $element_data Element data.
	 *
	 * @return Element_Base
	 */
	abstract protected function _get_default_child_type( array $element_data );

	/**
	 * Before element rendering.
	 *
	 * Used to add stuff before the element.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function before_render() {}

	/**
	 * After element rendering.
	 *
	 * Used to add stuff after the element.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function after_render() {}

	/**
	 * Get element title.
	 *
	 * Retrieve the element title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Element title.
	 */
	public function get_title() {
		return '';
	}

	/**
	 * Get element icon.
	 *
	 * Retrieve the element icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Element icon.
	 */
	public function get_icon() {
		return 'eicon-columns';
	}

	public function get_help_url() {
		return 'https://go.elementor.com/widget-' . $this->get_name();
	}

	public function get_custom_help_url() {
		return '';
	}

	/**
	 * Whether the reload preview is required.
	 *
	 * Used to determine whether the reload preview is required or not.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool Whether the reload preview is required.
	 */
	public function is_reload_preview_required() {
		return false;
	}

	/**
	 * @since 2.3.1
	 * @access protected
	 */
	protected function should_print_empty() {
		return true;
	}

	/**
	 * Get child elements.
	 *
	 * Retrieve all the child elements of this element.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Element_Base[] Child elements.
	 */
	public function get_children() {
		if ( null === $this->children ) {
			$this->init_children();
		}

		return $this->children;
	}

	/**
	 * Get default arguments.
	 *
	 * Retrieve the element default arguments. Used to return all the default
	 * arguments or a specific default argument, if one is set.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $item Optional. Default is null.
	 *
	 * @return array Default argument(s).
	 */
	public function get_default_args( $item = null ) {
		return self::get_items( $this->default_args, $item );
	}

	/**
	 * Add new child element.
	 *
	 * Register new child element to allow hierarchy.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $child_data Child element data.
	 * @param array $child_args Child element arguments.
	 *
	 * @return Element_Base|false Child element instance, or false if failed.
	 */
	public function add_child( array $child_data, array $child_args = [] ) {
		if ( null === $this->children ) {
			$this->init_children();
		}

		$child_type = $this->get_child_type( $child_data );

		if ( ! $child_type ) {
			return false;
		}

		$child = Plugin::$instance->elements_manager->create_element_instance( $child_data, $child_args, $child_type );

		if ( $child ) {
			$this->children[] = $child;
		}

		return $child;
	}

	/**
	 * Add render attribute.
	 *
	 * Used to add attributes to a specific HTML element.
	 *
	 * The HTML tag is represented by the element parameter, then you need to
	 * define the attribute key and the attribute key. The final result will be:
	 * `<element attribute_key="attribute_value">`.
	 *
	 * Example usage:
	 *
	 * `$this->add_render_attribute( 'wrapper', 'class', 'custom-widget-wrapper-class' );`
	 * `$this->add_render_attribute( 'widget', 'id', 'custom-widget-id' );`
	 * `$this->add_render_attribute( 'button', [ 'class' => 'custom-button-class', 'id' => 'custom-button-id' ] );`
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array|string $element   The HTML element.
	 * @param array|string $key       Optional. Attribute key. Default is null.
	 * @param array|string $value     Optional. Attribute value. Default is null.
	 * @param bool         $overwrite Optional. Whether to overwrite existing
	 *                                attribute. Default is false, not to overwrite.
	 *
	 * @return Element_Base Current instance of the element.
	 */
	public function add_render_attribute( $element, $key = null, $value = null, $overwrite = false ) {
		if ( is_array( $element ) ) {
			foreach ( $element as $element_key => $attributes ) {
				$this->add_render_attribute( $element_key, $attributes, null, $overwrite );
			}

			return $this;
		}

		if ( is_array( $key ) ) {
			foreach ( $key as $attribute_key => $attributes ) {
				$this->add_render_attribute( $element, $attribute_key, $attributes, $overwrite );
			}

			return $this;
		}

		if ( empty( $this->render_attributes[ $element ][ $key ] ) ) {
			$this->render_attributes[ $element ][ $key ] = [];
		}

		settype( $value, 'array' );

		if ( $overwrite ) {
			$this->render_attributes[ $element ][ $key ] = $value;
		} else {
			$this->render_attributes[ $element ][ $key ] = array_merge( $this->render_attributes[ $element ][ $key ], $value );
		}

		return $this;
	}

	/**
	 * Add link render attributes.
	 *
	 * Used to add link tag attributes to a specific HTML element.
	 *
	 * The HTML link tag is represented by the element parameter. The `url_control` parameter
	 * needs to be an array of link settings in the same format they are set by Elementor's URL control.
	 *
	 * Example usage:
	 *
	 * `$this->add_link_attributes( 'button', $settings['link'] );`
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array|string $element   The HTML element.
	 * @param array $url_control      Array of link settings.
	 * @param bool $overwrite         Optional. Whether to overwrite existing
	 *                                attribute. Default is false, not to overwrite.
	 *
	 * @return Element_Base Current instance of the element.
	 */

	public function add_link_attributes( $element, array $url_control, $overwrite = false ) {
		$attributes = [];

		if ( ! empty( $url_control['url'] ) ) {
			$allowed_protocols = array_merge( wp_allowed_protocols(), [ 'skype', 'viber' ] );

			$attributes['href'] = esc_url( $url_control['url'], $allowed_protocols );
		}

		if ( ! empty( $url_control['is_external'] ) ) {
			$attributes['target'] = '_blank';
		}

		if ( ! empty( $url_control['nofollow'] ) ) {
			$attributes['rel'] = 'nofollow';
		}

		if ( ! empty( $url_control['custom_attributes'] ) ) {
			// Custom URL attributes should come as a string of comma-delimited key|value pairs
			$attributes = array_merge( $attributes, Utils::parse_custom_attributes( $url_control['custom_attributes'] ) );
		}

		if ( $attributes ) {
			$this->add_render_attribute( $element, $attributes, $overwrite );
		}

		return $this;
	}

	/**
	 * Get Render Attributes
	 *
	 * Used to retrieve render attribute.
	 *
	 * The returned array is either all elements and their attributes if no `$element` is specified, an array of all
	 * attributes of a specific element or a specific attribute properties if `$key` is specified.
	 *
	 * Returns null if one of the requested parameters isn't set.
	 *
	 * @since 2.2.6
	 * @access public
	 * @param string $element
	 * @param string $key
	 *
	 * @return array
	 */
	public function get_render_attributes( $element = '', $key = '' ) {
		$attributes = $this->render_attributes;

		if ( $element ) {
			if ( ! isset( $attributes[ $element ] ) ) {
				return null;
			}

			$attributes = $attributes[ $element ];

			if ( $key ) {
				if ( ! isset( $attributes[ $key ] ) ) {
					return null;
				}

				$attributes = $attributes[ $key ];
			}
		}

		return $attributes;
	}

	/**
	 * Set render attribute.
	 *
	 * Used to set the value of the HTML element render attribute or to update
	 * an existing render attribute.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array|string $element The HTML element.
	 * @param array|string $key     Optional. Attribute key. Default is null.
	 * @param array|string $value   Optional. Attribute value. Default is null.
	 *
	 * @return Element_Base Current instance of the element.
	 */
	public function set_render_attribute( $element, $key = null, $value = null ) {
		return $this->add_render_attribute( $element, $key, $value, true );
	}

	/**
	 * Remove render attribute.
	 *
	 * Used to remove an element (with its keys and their values), key (with its values),
	 * or value/s from an HTML element's render attribute.
	 *
	 * @since 2.7.0
	 * @access public
	 *
	 * @param string $element       The HTML element.
	 * @param string $key           Optional. Attribute key. Default is null.
	 * @param array|string $values   Optional. Attribute value/s. Default is null.
	 */
	public function remove_render_attribute( $element, $key = null, $values = null ) {
		if ( $key && ! isset( $this->render_attributes[ $element ][ $key ] ) ) {
			return;
		}

		if ( $values ) {
			$values = (array) $values;

			$this->render_attributes[ $element ][ $key ] = array_diff( $this->render_attributes[ $element ][ $key ], $values );

			return;
		}

		if ( $key ) {
			unset( $this->render_attributes[ $element ][ $key ] );

			return;
		}

		if ( isset( $this->render_attributes[ $element ] ) ) {
			unset( $this->render_attributes[ $element ] );
		}
	}

	/**
	 * Get render attribute string.
	 *
	 * Used to retrieve the value of the render attribute.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $element The element.
	 *
	 * @return string Render attribute string, or an empty string if the attribute
	 *                is empty or not exist.
	 */
	public function get_render_attribute_string( $element ) {
		if ( empty( $this->render_attributes[ $element ] ) ) {
			return '';
		}

		return Utils::render_html_attributes( $this->render_attributes[ $element ] );
	}

	/**
	 * Print render attribute string.
	 *
	 * Used to output the rendered attribute.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param array|string $element The element.
	 */
	public function print_render_attribute_string( $element ) {
		echo $this->get_render_attribute_string( $element ); // XSS ok.
	}

	/**
	 * Print element.
	 *
	 * Used to generate the element final HTML on the frontend and the editor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function print_element() {
		$element_type = $this->get_type();

		/**
		 * Before frontend element render.
		 *
		 * Fires before Elementor element is rendered in the frontend.
		 *
		 * @since 2.2.0
		 *
		 * @param Element_Base $this The element.
		 */
		do_action( 'elementor/frontend/before_render', $this );

		/**
		 * Before frontend element render.
		 *
		 * Fires before Elementor element is rendered in the frontend.
		 *
		 * The dynamic portion of the hook name, `$element_type`, refers to the element type.
		 *
		 * @since 1.0.0
		 *
		 * @param Element_Base $this The element.
		 */
		do_action( "elementor/frontend/{$element_type}/before_render", $this );

		ob_start();

		if ( $this->has_own_method( '_print_content', self::class ) ) {
			Plugin::$instance->modules_manager->get_modules( 'dev-tools' )->deprecation->deprecated_function( '_print_content', '3.1.0', __CLASS__ . '::print_content()' );

			$this->_print_content();
		} else {
			$this->print_content();
		}

		$content = ob_get_clean();

		$should_render = ( ! empty( $content ) || $this->should_print_empty() );

		/**
		 * Should the element be rendered for frontend
		 *
		 * Filters if the element should be rendered on frontend.
		 *
		 * @since 2.3.3
		 *
		 * @param bool true The element.
		 * @param Element_Base $this The element.
		 */
		$should_render = apply_filters( "elementor/frontend/{$element_type}/should_render", $should_render, $this );

		if ( $should_render ) {
			if ( $this->has_own_method( '_add_render_attributes', self::class ) ) {
				Plugin::$instance->modules_manager->get_modules( 'dev-tools' )->deprecation->deprecated_function( '_add_render_attributes', '3.1.0', __CLASS__ . '::add_render_attributes()' );

				$this->_add_render_attributes();
			} else {
				$this->add_render_attributes();
			}

			$this->before_render();
			echo $content;
			$this->after_render();

			$this->enqueue_scripts();
			$this->enqueue_styles();
		}

		/**
		 * After frontend element render.
		 *
		 * Fires after Elementor element is rendered in the frontend.
		 *
		 * The dynamic portion of the hook name, `$element_type`, refers to the element type.
		 *
		 * @since 1.0.0
		 *
		 * @param Element_Base $this The element.
		 */
		do_action( "elementor/frontend/{$element_type}/after_render", $this );

		/**
		 * After frontend element render.
		 *
		 * Fires after Elementor element is rendered in the frontend.
		 *
		 * @since 2.3.0
		 *
		 * @param Element_Base $this The element.
		 */
		do_action( 'elementor/frontend/after_render', $this );
	}

	/**
	 * Get the element raw data.
	 *
	 * Retrieve the raw element data, including the id, type, settings, child
	 * elements and whether it is an inner element.
	 *
	 * The data with the HTML used always to display the data, but the Elementor
	 * editor uses the raw data without the HTML in order not to render the data
	 * again.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool $with_html_content Optional. Whether to return the data with
	 *                                HTML content or without. Used for caching.
	 *                                Default is false, without HTML.
	 *
	 * @return array Element raw data.
	 */
	public function get_raw_data( $with_html_content = false ) {
		$data = $this->get_data();

		$elements = [];

		foreach ( $this->get_children() as $child ) {
			$elements[] = $child->get_raw_data( $with_html_content );
		}

		return [
			'id' => $this->get_id(),
			'elType' => $data['elType'],
			'settings' => $data['settings'],
			'elements' => $elements,
			'isInner' => $data['isInner'],
		];
	}

	/**
	 * Get unique selector.
	 *
	 * Retrieve the unique selector of the element. Used to set a unique HTML
	 * class for each HTML element. This way Elementor can set custom styles for
	 * each element.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Unique selector.
	 */
	public function get_unique_selector() {
		return '.elementor-element-' . $this->get_id();
	}

	/**
	 * Is type instance.
	 *
	 * Used to determine whether the element is an instance of that type or not.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool Whether the element is an instance of that type.
	 */
	public function is_type_instance() {
		return $this->is_type_instance;
	}

	/**
	 * Add render attributes.
	 *
	 * Used to add attributes to the current element wrapper HTML tag.
	 *
	 * @since 1.3.0
	 * @access protected
	 * @deprecated 3.1.0
	 */
	protected function _add_render_attributes() {
		Plugin::$instance->modules_manager->get_modules( 'dev-tools' )->deprecation->deprecated_function( __METHOD__, '3.1.0', __CLASS__ . '::add_render_attributes()' );

		return $this->add_render_attributes();
	}

	/**
	 * Add render attributes.
	 *
	 * Used to add attributes to the current element wrapper HTML tag.
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	protected function add_render_attributes() {
		$id = $this->get_id();

		$settings = $this->get_settings_for_display();
		$frontend_settings = $this->get_frontend_settings();
		$parsed_element_data_from_db = Plugin::$instance->documents->get_current()->get_elements_parsed_data( $this->get_id() );

		$this->add_render_attribute( '_wrapper', [
			'class' => [
				'elementor-element',
				'elementor-element-' . $id,
			],
			'data-id' => $id,
			'data-element_type' => $this->get_type(),
		] );

		$class_settings = [];

		foreach ( $settings as $setting_key => $setting ) {
			if ( isset( $parsed_element_data_from_db[ $setting_key ]['prefix_class'] ) ) {
				$class_settings[ $setting_key ] = $setting;
			}
		}

		foreach ( $class_settings as $setting_key => $setting ) {
			if ( empty( $setting ) && '0' !== $setting ) {
				continue;
			}

			$this->add_render_attribute( '_wrapper', 'class', $parsed_element_data_from_db[ $setting_key ]['prefix_class'] . $setting );
		}

		$_animation = ! empty( $settings['_animation'] );
		$animation = ! empty( $settings['animation'] );
		$has_animation = $_animation && 'none' !== $settings['_animation'] || $animation && 'none' !== $settings['animation'];

		if ( $has_animation ) {
			$is_static_render_mode = Plugin::$instance->frontend->is_static_render_mode();

			if ( ! $is_static_render_mode ) {
				// Hide the element until the animation begins
				$this->add_render_attribute( '_wrapper', 'class', 'elementor-invisible' );
			}
		}

		if ( ! empty( $settings['_element_id'] ) ) {
			$this->add_render_attribute( '_wrapper', 'id', trim( $settings['_element_id'] ) );
		}

		if ( $frontend_settings ) {
			$this->add_render_attribute( '_wrapper', 'data-settings', wp_json_encode( $frontend_settings ) );
		}

		/**
		 * After element attribute rendered.
		 *
		 * Fires after the attributes of the element HTML tag are rendered.
		 *
		 * @since 2.3.0
		 *
		 * @param Element_Base $this The element.
		 */
		do_action( 'elementor/element/after_add_attributes', $this );
	}

	/**
	 * Get default data.
	 *
	 * Retrieve the default element data. Used to reset the data on initialization.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Default data.
	 */
	protected function get_default_data() {
		$data = parent::get_default_data();

		return array_merge(
			$data, [
				'elements' => [],
				'isInner' => false,
			]
		);
	}

	/**
	 * Print element content.
	 *
	 * Output the element final HTML on the frontend.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _print_content() {
		Plugin::$instance->modules_manager->get_modules( 'dev-tools' )->deprecation->deprecated_function( __METHOD__, '3.1.0', __CLASS__ . '::print_content()' );

		$this->print_content();
	}

	/**
	 * Print element content.
	 *
	 * Output the element final HTML on the frontend.
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	protected function print_content() {
		foreach ( $this->get_children() as $child ) {
			$child->print_element();
		}
	}

	/**
	 * Get initial config.
	 *
	 * Retrieve the current element initial configuration.
	 *
	 * Adds more configuration on top of the controls list and the tabs assigned
	 * to the control. This method also adds element name, type, icon and more.
	 *
	 * @since 2.9.0
	 * @access protected
	 *
	 * @return array The initial config.
	 */
	protected function get_initial_config() {
		$config = [
			'name' => $this->get_name(),
			'elType' => $this->get_type(),
			'title' => $this->get_title(),
			'icon' => $this->get_icon(),
			'reload_preview' => $this->is_reload_preview_required(),
		];

		if ( preg_match( '/^' . __NAMESPACE__ . '(Pro)?\\\\/', get_called_class() ) ) {
			$config['help_url'] = $this->get_help_url();
		} else {
			$config['help_url'] = $this->get_custom_help_url();
		}

		if ( ! $this->is_editable() ) {
			$config['editable'] = false;
		}

		return $config;
	}

	/**
	 * Get child type.
	 *
	 * Retrieve the element child type based on element data.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param array $element_data Element ID.
	 *
	 * @return Element_Base|false Child type or false if type not found.
	 */
	private function get_child_type( $element_data ) {
		$child_type = $this->_get_default_child_type( $element_data );

		// If it's not a valid widget ( like a deactivated plugin )
		if ( ! $child_type ) {
			return false;
		}

		/**
		 * Element child type.
		 *
		 * Filters the child type of the element.
		 *
		 * @since 1.0.0
		 *
		 * @param Element_Base $child_type   The child element.
		 * @param array        $element_data The original element ID.
		 * @param Element_Base $this         The original element.
		 */
		$child_type = apply_filters( 'elementor/element/get_child_type', $child_type, $element_data, $this );

		return $child_type;
	}

	/**
	 * Initialize children.
	 *
	 * Initializing the element child elements.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private function init_children() {
		$this->children = [];

		$children_data = $this->get_data( 'elements' );

		if ( ! $children_data ) {
			return;
		}

		foreach ( $children_data as $child_data ) {
			if ( ! $child_data ) {
				continue;
			}

			$this->add_child( $child_data );
		}
	}

	/**
	 * Element base constructor.
	 *
	 * Initializing the element base class using `$data` and `$args`.
	 *
	 * The `$data` parameter is required for a normal instance because of the
	 * way Elementor renders data when initializing elements.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array      $data Optional. Element data. Default is an empty array.
	 * @param array|null $args Optional. Element default arguments. Default is null.
	 **/
	public function __construct( array $data = [], array $args = null ) {
		if ( $data ) {
			$this->is_type_instance = false;
		} elseif ( $args ) {
			$this->default_args = $args;
		}

		parent::__construct( $data );
	}
}
