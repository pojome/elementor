<?php
namespace Elementor;

use Elementor\Core\Page_Assets\Data_Managers\Widgets_Css as Widgets_Css_Data_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor widget base.
 *
 * An abstract class to register new Elementor widgets. It extended the
 * `Element_Base` class to inherit its properties.
 *
 * This abstract class must be extended in order to register new widgets.
 *
 * @since 1.0.0
 * @abstract
 */
abstract class Widget_Base extends Element_Base {
	/**
	 * Whether the widget has content.
	 *
	 * Used in cases where the widget has no content. When widgets uses only
	 * skins to display dynamic content generated on the server. For example the
	 * posts widget in Elementor Pro. Default is true, the widget has content
	 * template.
	 *
	 * @access protected
	 *
	 * @var bool
	 */
	protected $_has_template_content = true;

	/**
	 * Registered Runtime Widgets.
	 *
	 * Registering in runtime all widgets that are being used on the page.
	 *
	 * @since 3.3.0
	 * @access public
	 * @static
	 *
	 * @var array
	 */
	public static $registered_runtime_widgets = [];

	private static $widgets_css_data_manager;

	/**
	 * Get element type.
	 *
	 * Retrieve the element type, in this case `widget`.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return string The type.
	 */
	public static function get_type() {
		return 'widget';
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve the widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-apps';
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the widget keywords.
	 *
	 * @since 1.0.10
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [];
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the widget categories.
	 *
	 * @since 1.0.10
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'general' ];
	}

	/**
	 * Widget base constructor.
	 *
	 * Initializing the widget base class.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @throws \Exception If arguments are missing when initializing a full widget
	 *                   instance.
	 *
	 * @param array      $data Widget data. Default is an empty array.
	 * @param array|null $args Optional. Widget default arguments. Default is null.
	 */
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		$is_type_instance = $this->is_type_instance();

		if ( ! $is_type_instance && null === $args ) {
			throw new \Exception( '`$args` argument is required when initializing a full widget instance.' );
		}

		if ( $is_type_instance ) {
			if ( $this->has_own_method( '_register_skins', self::class ) ) {
				Plugin::$instance->modules_manager->get_modules( 'dev-tools' )->deprecation->deprecated_function( '_register_skins', '3.1.0', __CLASS__ . '::register_skins()' );

				$this->_register_skins();
			} else {
				$this->register_skins();
			}

			$widget_name = $this->get_name();

			/**
			 * Widget skin init.
			 *
			 * Fires when Elementor widget is being initialized.
			 *
			 * The dynamic portion of the hook name, `$widget_name`, refers to the widget name.
			 *
			 * @since 1.0.0
			 *
			 * @param Widget_Base $this The current widget.
			 */
			do_action( "elementor/widget/{$widget_name}/skins_init", $this );
		}
	}

	/**
	 * Get stack.
	 *
	 * Retrieve the widget stack of controls.
	 *
	 * @since 1.9.2
	 * @access public
	 *
	 * @param bool $with_common_controls Optional. Whether to include the common controls. Default is true.
	 *
	 * @return array Widget stack of controls.
	 */
	public function get_stack( $with_common_controls = true ) {
		$stack = parent::get_stack();

		if ( $with_common_controls && 'common' !== $this->get_unique_name() ) {
			/** @var Widget_Common $common_widget */
			$common_widget = Plugin::$instance->widgets_manager->get_widget_types( 'common' );

			$stack['controls'] = array_merge( $stack['controls'], $common_widget->get_controls() );

			$stack['tabs'] = array_merge( $stack['tabs'], $common_widget->get_tabs_controls() );
		}

		return $stack;
	}

	/**
	 * Get widget controls pointer index.
	 *
	 * Retrieve widget pointer index where the next control should be added.
	 *
	 * While using injection point, it will return the injection point index. Otherwise index of the last control of the
	 * current widget itself without the common controls, plus one.
	 *
	 * @since 1.9.2
	 * @access public
	 *
	 * @return int Widget controls pointer index.
	 */
	public function get_pointer_index() {
		$injection_point = $this->get_injection_point();

		if ( null !== $injection_point ) {
			return $injection_point['index'];
		}

		return count( $this->get_stack( false )['controls'] );
	}

	/**
	 * Show in panel.
	 *
	 * Whether to show the widget in the panel or not. By default returns true.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool Whether to show the widget in the panel or not.
	 */
	public function show_in_panel() {
		return true;
	}

	/**
	 * Start widget controls section.
	 *
	 * Used to add a new section of controls to the widget. Regular controls and
	 * skin controls.
	 *
	 * Note that when you add new controls to widgets they must be wrapped by
	 * `start_controls_section()` and `end_controls_section()`.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $section_id Section ID.
	 * @param array  $args       Section arguments Optional.
	 */
	public function start_controls_section( $section_id, array $args = [] ) {
		parent::start_controls_section( $section_id, $args );

		static $is_first_section = true;

		if ( $is_first_section ) {
			$this->register_skin_control();

			$is_first_section = false;
		}
	}

	/**
	 * Register the Skin Control if the widget has skins.
	 *
	 * An internal method that is used to add a skin control to the widget.
	 * Added at the top of the controls section.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private function register_skin_control() {
		$skins = $this->get_skins();
		if ( ! empty( $skins ) ) {
			$skin_options = [];

			if ( $this->_has_template_content ) {
				$skin_options[''] = __( 'Default', 'elementor' );
			}

			foreach ( $skins as $skin_id => $skin ) {
				$skin_options[ $skin_id ] = $skin->get_title();
			}

			// Get the first item for default value
			$default_value = array_keys( $skin_options );
			$default_value = array_shift( $default_value );

			if ( 1 >= count( $skin_options ) ) {
				$this->add_control(
					'_skin',
					[
						'label' => __( 'Skin', 'elementor' ),
						'type' => Controls_Manager::HIDDEN,
						'default' => $default_value,
					]
				);
			} else {
				$this->add_control(
					'_skin',
					[
						'label' => __( 'Skin', 'elementor' ),
						'type' => Controls_Manager::SELECT,
						'default' => $default_value,
						'options' => $skin_options,
					]
				);
			}
		}
	}

	/**
	 * Register widget skins - deprecated prefixed method
	 *
	 * @since 1.7.12
	 * @access protected
	 * @deprecated 3.1.0
	 */
	protected function _register_skins() {
		Plugin::$instance->modules_manager->get_modules( 'dev-tools' )->deprecation->deprecated_function( __METHOD__, '3.1.0', __CLASS__ . '::register_skins()' );

		$this->register_skins();
	}

	/**
	 * Register widget skins.
	 *
	 * This method is activated while initializing the widget base class. It is
	 * used to assign skins to widgets with `add_skin()` method.
	 *
	 * Usage:
	 *
	 *    protected function register_skins() {
	 *        $this->add_skin( new Skin_Classic( $this ) );
	 *    }
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	protected function register_skins() {}

	/**
	 * Get initial config.
	 *
	 * Retrieve the current widget initial configuration.
	 *
	 * Adds more configuration on top of the controls list, the tabs assigned to
	 * the control, element name, type, icon and more. This method also adds
	 * widget type, keywords and categories.
	 *
	 * @since 2.9.0
	 * @access protected
	 *
	 * @return array The initial widget config.
	 */
	protected function get_initial_config() {
		$config = [
			'widget_type' => $this->get_name(),
			'keywords' => $this->get_keywords(),
			'categories' => $this->get_categories(),
			'html_wrapper_class' => $this->get_html_wrapper_class(),
			'show_in_panel' => $this->show_in_panel(),
		];

		$stack = Plugin::$instance->controls_manager->get_element_stack( $this );

		if ( $stack ) {
			$config['controls'] = $this->get_stack( false )['controls'];
			$config['tabs_controls'] = $this->get_tabs_controls();
		}

		return array_merge( parent::get_initial_config(), $config );
	}

	/**
	 * @since 2.3.1
	 * @access protected
	 */
	protected function should_print_empty() {
		return false;
	}

	/**
	 * Print widget content template.
	 *
	 * Used to generate the widget content template on the editor, using a
	 * Backbone JavaScript template.
	 *
	 * @since 2.0.0
	 * @access protected
	 *
	 * @param string $template_content Template content.
	 */
	protected function print_template_content( $template_content ) {
		?>
		<div class="elementor-widget-container">
			<?php
			echo $template_content; // XSS ok.
			?>
		</div>
		<?php
	}

	/**
	 * Parse text editor.
	 *
	 * Parses the content from rich text editor with shortcodes, oEmbed and
	 * filtered data.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $content Text editor content.
	 *
	 * @return string Parsed content.
	 */
	protected function parse_text_editor( $content ) {
		/** This filter is documented in wp-includes/widgets/class-wp-widget-text.php */
		$content = apply_filters( 'widget_text', $content, $this->get_settings() );

		$content = shortcode_unautop( $content );
		$content = do_shortcode( $content );
		$content = wptexturize( $content );

		if ( $GLOBALS['wp_embed'] instanceof \WP_Embed ) {
			$content = $GLOBALS['wp_embed']->autoembed( $content );
		}

		return $content;
	}

	/**
	 * Get HTML wrapper class.
	 *
	 * Retrieve the widget container class. Can be used to override the
	 * container class for specific widgets.
	 *
	 * @since 2.0.9
	 * @access protected
	 */
	protected function get_html_wrapper_class() {
		return 'elementor-widget-' . $this->get_name();
	}

	/**
	 * Add widget render attributes.
	 *
	 * Used to add attributes to the current widget wrapper HTML tag.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function add_render_attributes() {
		parent::add_render_attributes();

		$this->add_render_attribute(
			'_wrapper', 'class', [
				'elementor-widget',
				$this->get_html_wrapper_class(),
			]
		);

		$settings = $this->get_settings();

		$this->add_render_attribute( '_wrapper', 'data-widget_type', $this->get_name() . '.' . ( ! empty( $settings['_skin'] ) ? $settings['_skin'] : 'default' ) );
	}

	/**
	 * Add lightbox data to image link.
	 *
	 * Used to add lightbox data attributes to image link HTML.
	 *
	 * @since 2.9.1
	 * @access public
	 *
	 * @param string $link_html Image link HTML.
	 * @param string $id Attachment id.
	 *
	 * @return string Image link HTML with lightbox data attributes.
	 */
	public function add_lightbox_data_to_image_link( $link_html, $id ) {
		$settings = $this->get_settings_for_display();
		$open_lightbox = isset( $settings['open_lightbox'] ) ? $settings['open_lightbox'] : null;

		if ( Plugin::$instance->editor->is_edit_mode() ) {
			$this->add_render_attribute( 'link', 'class', 'elementor-clickable', true );
		}

		$this->add_lightbox_data_attributes( 'link', $id, $open_lightbox, $this->get_id(), true );
		return preg_replace( '/^<a/', '<a ' . $this->get_render_attribute_string( 'link' ), $link_html );
	}

	/**
	 * Add Light-Box attributes.
	 *
	 * Used to add Light-Box-related data attributes to links that open media files.
	 *
	 * @param array|string $element         The link HTML element.
	 * @param int $id                       The ID of the image
	 * @param string $lightbox_setting_key  The setting key that dictates weather to open the image in a lightbox
	 * @param string $group_id              Unique ID for a group of lightbox images
	 * @param bool $overwrite               Optional. Whether to overwrite existing
	 *                                      attribute. Default is false, not to overwrite.
	 *
	 * @return Widget_Base Current instance of the widget.
	 * @since 2.9.0
	 * @access public
	 *
	 */
	public function add_lightbox_data_attributes( $element, $id = null, $lightbox_setting_key = null, $group_id = null, $overwrite = false ) {
		$kit = Plugin::$instance->kits_manager->get_active_kit();

		$is_global_image_lightbox_enabled = 'yes' === $kit->get_settings( 'global_image_lightbox' );

		if ( 'no' === $lightbox_setting_key ) {
			if ( $is_global_image_lightbox_enabled ) {
				$this->add_render_attribute( $element, 'data-elementor-open-lightbox', 'no', $overwrite );
			}

			return $this;
		}

		if ( 'yes' !== $lightbox_setting_key && ! $is_global_image_lightbox_enabled ) {
			return $this;
		}

		$attributes['data-elementor-open-lightbox'] = 'yes';

		if ( $group_id ) {
			$attributes['data-elementor-lightbox-slideshow'] = $group_id;
		}

		if ( $id ) {
			$lightbox_image_attributes = Plugin::$instance->images_manager->get_lightbox_image_attributes( $id );

			if ( isset( $lightbox_image_attributes['title'] ) ) {
				$attributes['data-elementor-lightbox-title'] = $lightbox_image_attributes['title'];
			}

			if ( isset( $lightbox_image_attributes['description'] ) ) {
				$attributes['data-elementor-lightbox-description'] = $lightbox_image_attributes['description'];
			}
		}

		$this->add_render_attribute( $element, $attributes, null, $overwrite );

		return $this;
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Used to generate the final HTML displayed on the frontend.
	 *
	 * Note that if skin is selected, it will be rendered by the skin itself,
	 * not the widget.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function render_content() {
		/**
		 * Before widget render content.
		 *
		 * Fires before Elementor widget is being rendered.
		 *
		 * @since 1.0.0
		 *
		 * @param Widget_Base $this The current widget.
		 */
		do_action( 'elementor/widget/before_render_content', $this );

		ob_start();

		$skin = $this->get_current_skin();
		if ( $skin ) {
			$skin->set_parent( $this );
			$skin->render_by_mode();
		} else {
			$this->render_by_mode();
		}

		$widget_content = ob_get_clean();

		if ( empty( $widget_content ) ) {
			return;
		}
		?>
		<div class="elementor-widget-container">
			<?php
			if ( $this->is_widget_first_render() ) {

				$this->register_runtime_widget( $this->get_group_name() );

				$this->print_widget_css();
			}

			// get_name

			/**
			 * Render widget content.
			 *
			 * Filters the widget content before it's rendered.
			 *
			 * @since 1.0.0
			 *
			 * @param string      $widget_content The content of the widget.
			 * @param Widget_Base $this           The widget.
			 */
			$widget_content = apply_filters( 'elementor/widget/render_content', $widget_content, $this );

			echo $widget_content; // XSS ok.
			?>
		</div>
		<?php
	}

	protected function is_widget_first_render() {
		return ! in_array( $this->get_group_name(), self::$registered_runtime_widgets, true );
	}

	/**
	 * Render widget plain content.
	 *
	 * Elementor saves the page content in a unique way, but it's not the way
	 * WordPress saves data. This method is used to save generated HTML to the
	 * database as plain content the WordPress way.
	 *
	 * When rendering plain content, it allows other WordPress plugins to
	 * interact with the content - to search, check SEO and other purposes. It
	 * also allows the site to keep working even if Elementor is deactivated.
	 *
	 * Note that if the widget uses shortcodes to display the data, the best
	 * practice is to return the shortcode itself.
	 *
	 * Also note that if the widget don't display any content it should return
	 * an empty string. For example Elementor Pro Form Widget uses this method
	 * to return an empty string because there is no content to return. This way
	 * if Elementor Pro will be deactivated there won't be any form to display.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function render_plain_content() {
		$this->render_content();
	}

	/**
	 * Before widget rendering.
	 *
	 * Used to add stuff before the widget `_wrapper` element.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function before_render() {
		?>
		<div <?php $this->print_render_attribute_string( '_wrapper' ); ?>>
		<?php
	}

	/**
	 * After widget rendering.
	 *
	 * Used to add stuff after the widget `_wrapper` element.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function after_render() {
		?>
		</div>
		<?php
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
		$data = parent::get_raw_data( $with_html_content );

		unset( $data['isInner'] );

		$data['widgetType'] = $this->get_data( 'widgetType' );

		if ( $with_html_content ) {
			ob_start();

			$this->render_content();

			$data['htmlCache'] = ob_get_clean();
		}

		return $data;
	}

	/**
	 * Print widget content.
	 *
	 * Output the widget final HTML on the frontend.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function print_content() {
		$this->render_content();
	}

	/**
	 * Print a setting content without escaping.
	 *
	 * Script tags are allowed on frontend according to the WP theme securing policy.
	 *
	 * @param string $setting
	 * @param null $repeater_name
	 * @param null $index
	 */
	final protected function print_unescaped_setting( $setting, $repeater_name = null, $index = null ) {
		if ( $repeater_name ) {
			$repeater = $this->get_settings_for_display( $repeater_name );
			$output = $repeater[ $index ][ $setting ];
		} else {
			$output = $this->get_settings_for_display( $setting );
		}

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get default data.
	 *
	 * Retrieve the default widget data. Used to reset the data on initialization.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Default data.
	 */
	protected function get_default_data() {
		$data = parent::get_default_data();

		$data['widgetType'] = '';

		return $data;
	}

	/**
	 * Get default child type.
	 *
	 * Retrieve the widget child type based on element data.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $element_data Widget ID.
	 *
	 * @return array|false Child type or false if it's not a valid widget.
	 */
	protected function _get_default_child_type( array $element_data ) {
		return Plugin::$instance->elements_manager->get_element_types( 'section' );
	}

	/**
	 * Get repeater setting key.
	 *
	 * Retrieve the unique setting key for the current repeater item. Used to connect the current element in the
	 * repeater to it's settings model and it's control in the panel.
	 *
	 * PHP usage (inside `Widget_Base::render()` method):
	 *
	 *    $tabs = $this->get_settings( 'tabs' );
	 *    foreach ( $tabs as $index => $item ) {
	 *        $tab_title_setting_key = $this->get_repeater_setting_key( 'tab_title', 'tabs', $index );
	 *        $this->add_inline_editing_attributes( $tab_title_setting_key, 'none' );
	 *        echo '<div ' . $this->get_render_attribute_string( $tab_title_setting_key ) . '>' . $item['tab_title'] . '</div>';
	 *    }
	 *
	 * @since 1.8.0
	 * @access protected
	 *
	 * @param string $setting_key      The current setting key inside the repeater item (e.g. `tab_title`).
	 * @param string $repeater_key     The repeater key containing the array of all the items in the repeater (e.g. `tabs`).
	 * @param int $repeater_item_index The current item index in the repeater array (e.g. `3`).
	 *
	 * @return string The repeater setting key (e.g. `tabs.3.tab_title`).
	 */
	protected function get_repeater_setting_key( $setting_key, $repeater_key, $repeater_item_index ) {
		return implode( '.', [ $repeater_key, $repeater_item_index, $setting_key ] );
	}

	/**
	 * Add inline editing attributes.
	 *
	 * Define specific area in the element to be editable inline. The element can have several areas, with this method
	 * you can set the area inside the element that can be edited inline. You can also define the type of toolbar the
	 * user will see, whether it will be a basic toolbar or an advanced one.
	 *
	 * Note: When you use wysiwyg control use the advanced toolbar, with textarea control use the basic toolbar. Text
	 * control should not have toolbar.
	 *
	 * PHP usage (inside `Widget_Base::render()` method):
	 *
	 *    $this->add_inline_editing_attributes( 'text', 'advanced' );
	 *    echo '<div ' . $this->get_render_attribute_string( 'text' ) . '>' . $this->get_settings( 'text' ) . '</div>';
	 *
	 * @since 1.8.0
	 * @access protected
	 *
	 * @param string $key     Element key.
	 * @param string $toolbar Optional. Toolbar type. Accepted values are `advanced`, `basic` or `none`. Default is
	 *                        `basic`.
	 */
	protected function add_inline_editing_attributes( $key, $toolbar = 'basic' ) {
		if ( ! Plugin::$instance->editor->is_edit_mode() ) {
			return;
		}

		$this->add_render_attribute( $key, [
			'class' => 'elementor-inline-editing',
			'data-elementor-setting-key' => $key,
		] );

		if ( 'basic' !== $toolbar ) {
			$this->add_render_attribute( $key, [
				'data-elementor-inline-editing-toolbar' => $toolbar,
			] );
		}
	}

	/**
	 * Add new skin.
	 *
	 * Register new widget skin to allow the user to set custom designs. Must be
	 * called inside the `register_skins()` method.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Skin_Base $skin Skin instance.
	 */
	public function add_skin( Skin_Base $skin ) {
		Plugin::$instance->skins_manager->add_skin( $this, $skin );
	}

	/**
	 * Get single skin.
	 *
	 * Retrieve a single skin based on skin ID, from all the skin assigned to
	 * the widget. If the skin does not exist or not assigned to the widget,
	 * return false.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $skin_id Skin ID.
	 *
	 * @return string|false Single skin, or false.
	 */
	public function get_skin( $skin_id ) {
		$skins = $this->get_skins();
		if ( isset( $skins[ $skin_id ] ) ) {
			return $skins[ $skin_id ];
		}

		return false;
	}

	/**
	 * Get current skin ID.
	 *
	 * Retrieve the ID of the current skin.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Current skin.
	 */
	public function get_current_skin_id() {
		return $this->get_settings( '_skin' );
	}

	/**
	 * Get current skin.
	 *
	 * Retrieve the current skin, or if non exist return false.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Skin_Base|false Current skin or false.
	 */
	public function get_current_skin() {
		return $this->get_skin( $this->get_current_skin_id() );
	}

	/**
	 * Remove widget skin.
	 *
	 * Unregister an existing skin and remove it from the widget.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $skin_id Skin ID.
	 *
	 * @return \WP_Error|true Whether the skin was removed successfully from the widget.
	 */
	public function remove_skin( $skin_id ) {
		return Plugin::$instance->skins_manager->remove_skin( $this, $skin_id );
	}

	/**
	 * Get widget skins.
	 *
	 * Retrieve all the skin assigned to the widget.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Skin_Base[]
	 */
	public function get_skins() {
		return Plugin::$instance->skins_manager->get_skins( $this );
	}

	/**
	 * Get group name.
	 *
	 * Some widgets need to use group names, this method allows you to create them.
	 * By default it retrieves the regular name.
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @return string Unique name.
	 */
	public function get_group_name() {
		return $this->get_name();
	}

	/**
	 * @param string $plugin_title  Plugin's title
	 * @param string $since         Plugin version widget was deprecated
	 * @param string $last          Plugin version in which the widget will be removed
	 * @param string $replacement   Widget replacement
	 */
	protected function deprecated_notice( $plugin_title, $since, $last = '', $replacement = '' ) {
		$this->start_controls_section( 'Deprecated',
			[
				'label' => __( 'Deprecated', 'elementor' ),
			]
		);

		$this->add_control(
			'deprecated_notice',
			[
				'type' => Controls_Manager::DEPRECATED_NOTICE,
				'widget' => $this->get_title(),
				'since' => $since,
				'last' => $last,
				'plugin' => $plugin_title,
				'replacement' => $replacement,
			]
		);

		$this->end_controls_section();

	}

	public function register_runtime_widget( $widget_name ) {
		self::$registered_runtime_widgets[] = $widget_name;
	}

	public function get_css_config() {
		$widget_name = $this->get_group_name();

		$direction = is_rtl() ? '-rtl' : '';

		$css_file_path = 'css/widget-' . $widget_name . $direction . '.min.css';

		return [
			'key' => $widget_name,
			'version' => ELEMENTOR_VERSION,
			'file_path' => ELEMENTOR_ASSETS_PATH . $css_file_path,
			'data' => [
				'file_url' => ELEMENTOR_ASSETS_URL . $css_file_path,
			],
		];
	}

	private function get_widget_css() {
		$widgets_css_data_manager = $this->get_widgets_css_data_manager();

		$widget_css = $widgets_css_data_manager->get_asset_data( $this->get_css_config() );

		return $widget_css;

	}

	private function print_widget_css() {
		$is_edit_mode = Plugin::$instance->editor->is_edit_mode();
		$is_preview_mode = Plugin::$instance->preview->is_preview_mode();
		$is_optimized_mode = Plugin::$instance->experiments->is_feature_active( 'e_optimized_css_loading' );

		if ( Utils::is_script_debug() || $is_edit_mode || $is_preview_mode || ! $is_optimized_mode ) {
			return;
		}

		echo $this->get_widget_css();
	}

	private function get_widgets_css_data_manager() {
		if ( ! self::$widgets_css_data_manager ) {
			self::$widgets_css_data_manager = new Widgets_Css_Data_Manager();
		}

		return self::$widgets_css_data_manager;
	}
}
