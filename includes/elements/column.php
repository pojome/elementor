<?php
namespace Elementor;

use Elementor\Core\Breakpoints\Manager as Breakpoints_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor column element.
 *
 * Elementor column handler class is responsible for initializing the column
 * element.
 *
 * @since 1.0.0
 */
class Element_Column extends Element_Base {

	/**
	 * Get column name.
	 *
	 * Retrieve the column name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Column name.
	 */
	public function get_name() {
		return 'column';
	}

	/**
	 * Get element type.
	 *
	 * Retrieve the element type, in this case `column`.
	 *
	 * @since 2.1.0
	 * @access public
	 * @static
	 *
	 * @return string The type.
	 */
	public static function get_type() {
		return 'column';
	}

	/**
	 * Get column title.
	 *
	 * Retrieve the column title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Column title.
	 */
	public function get_title() {
		return __( 'Column', 'elementor' );
	}

	/**
	 * Get column icon.
	 *
	 * Retrieve the column icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Column icon.
	 */
	public function get_icon() {
		return 'eicon-column';
	}

	/**
	 * Get initial config.
	 *
	 * Retrieve the current section initial configuration.
	 *
	 * Adds more configuration on top of the controls list, the tabs assigned to
	 * the control, element name, type, icon and more. This method also adds
	 * section presets.
	 *
	 * @since 2.9.0
	 * @access protected
	 *
	 * @return array The initial config.
	 */
	protected function get_initial_config() {
		$config = parent::get_initial_config();

		$config['controls'] = $this->get_controls();
		$config['tabs_controls'] = $this->get_tabs_controls();

		return $config;
	}

	/**
	 * Register column controls.
	 *
	 * Used to add new controls to the column element.
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	protected function register_controls() {
		// Section Layout.
		$this->start_controls_section(
			'layout',
			[
				'label' => __( 'Layout', 'elementor' ),
				'tab' => Controls_Manager::TAB_LAYOUT,
			]
		);

		// Element Name for the Navigator
		$this->add_control(
			'_title',
			[
				'label' => __( 'Title', 'elementor' ),
				'type' => Controls_Manager::HIDDEN,
				'render_type' => 'none',
			]
		);

		$this->add_responsive_control(
			'_inline_size',
			[
				'label' => __( 'Column Width', 'elementor' ) . ' (%)',
				'type' => Controls_Manager::NUMBER,
				'min' => 2,
				'max' => 98,
				'required' => true,
				'device_args' => [
					Breakpoints_Manager::BREAKPOINT_KEY_TABLET => [
						'max' => 100,
						'required' => false,
					],
					Breakpoints_Manager::BREAKPOINT_KEY_MOBILE => [
						'max' => 100,
						'required' => false,
					],
				],
				'min_affected_device' => [
					Breakpoints_Manager::BREAKPOINT_KEY_DESKTOP => Breakpoints_Manager::BREAKPOINT_KEY_TABLET,
					Breakpoints_Manager::BREAKPOINT_KEY_TABLET => Breakpoints_Manager::BREAKPOINT_KEY_TABLET,
				],
				'selectors' => [
					'{{WRAPPER}}' => 'width: {{VALUE}}%',
				],
			]
		);

		$is_dome_optimization_active = Plugin::$instance->experiments->is_feature_active( 'e_dom_optimization' );
		$main_selector_element = $is_dome_optimization_active ? 'widget' : 'column';
		$widget_wrap_child = $is_dome_optimization_active ? '' : ' > .elementor-widget-wrap';
		$column_wrap_child = $is_dome_optimization_active ? '' : ' > .elementor-column-wrap';

		$this->add_responsive_control(
			'content_position',
			[
				'label' => __( 'Vertical Align', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'Default', 'elementor' ),
					'top' => __( 'Top', 'elementor' ),
					'center' => __( 'Middle', 'elementor' ),
					'bottom' => __( 'Bottom', 'elementor' ),
					'space-between' => __( 'Space Between', 'elementor' ),
					'space-around' => __( 'Space Around', 'elementor' ),
					'space-evenly' => __( 'Space Evenly', 'elementor' ),
				],
				'selectors_dictionary' => [
					'top' => 'flex-start',
					'bottom' => 'flex-end',
				],
				'selectors' => [
					// TODO: The following line is for BC since 2.7.0
					'.elementor-bc-flex-widget {{WRAPPER}}.elementor-column .elementor-' . $main_selector_element . '-wrap' => 'align-items: {{VALUE}}',
					// This specificity is intended to make sure column css overwrites section css on vertical alignment (content_position)
					'{{WRAPPER}}.elementor-column.elementor-element[data-element_type="column"] > .elementor-' . $main_selector_element . '-wrap.elementor-element-populated' . $widget_wrap_child => 'align-content: {{VALUE}}; align-items: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Horizontal Align', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'Default', 'elementor' ),
					'flex-start' => __( 'Start', 'elementor' ),
					'center' => __( 'Center', 'elementor' ),
					'flex-end' => __( 'End', 'elementor' ),
					'space-between' => __( 'Space Between', 'elementor' ),
					'space-around' => __( 'Space Around', 'elementor' ),
					'space-evenly' => __( 'Space Evenly', 'elementor' ),
				],
				'selectors' => [
					'{{WRAPPER}}.elementor-column' . $column_wrap_child . ' > .elementor-widget-wrap' => 'justify-content: {{VALUE}}',
				],
			]
		);

		$space_between_widgets_selector = $is_dome_optimization_active ? '' : '> .elementor-column-wrap ';

		$this->add_responsive_control(
			'space_between_widgets',
			[
				'label' => __( 'Widgets Space', 'elementor' ) . ' (px)',
				'type' => Controls_Manager::NUMBER,
				'placeholder' => 20,
				'selectors' => [
					'{{WRAPPER}} ' . $space_between_widgets_selector . '> .elementor-widget-wrap > .elementor-widget:not(.elementor-widget__width-auto):not(.elementor-widget__width-initial):not(:last-child):not(.elementor-absolute)' => 'margin-bottom: {{VALUE}}px', //Need the full path for exclude the inner section
				],
			]
		);

		$possible_tags = [
			'div',
			'header',
			'footer',
			'main',
			'article',
			'section',
			'aside',
			'nav',
		];

		$options = [
			'' => __( 'Default', 'elementor' ),
		] + array_combine( $possible_tags, $possible_tags );

		$this->add_control(
			'html_tag',
			[
				'label' => __( 'HTML Tag', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => $options,
				'render_type' => 'none',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Background', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'tabs_background' );

		$this->start_controls_tab(
			'tab_background_normal',
			[
				'label' => __( 'Normal', 'elementor' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'background',
				'types' => [ 'classic', 'gradient', 'slideshow' ],
				'selector' => '{{WRAPPER}}:not(.elementor-motion-effects-element-type-background) > .elementor-' . $main_selector_element . '-wrap, {{WRAPPER}} > .elementor-' . $main_selector_element . '-wrap > .elementor-motion-effects-container > .elementor-motion-effects-layer',
				'fields_options' => [
					'background' => [
						'frontend_available' => true,
					],
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_background_hover',
			[
				'label' => __( 'Hover', 'elementor' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'background_hover',
				'selector' => '{{WRAPPER}}:hover > .elementor-element-populated',
			]
		);

		$this->add_control(
			'background_hover_transition',
			[
				'label' => __( 'Transition Duration', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 0.3,
				],
				'range' => [
					'px' => [
						'max' => 3,
						'step' => 0.1,
					],
				],
				'render_type' => 'ui',
				'separator' => 'before',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// Section Column Background Overlay.
		$this->start_controls_section(
			'section_background_overlay',
			[
				'label' => __( 'Background Overlay', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'background_background' => [ 'classic', 'gradient' ],
				],
			]
		);

		$this->start_controls_tabs( 'tabs_background_overlay' );

		$this->start_controls_tab(
			'tab_background_overlay_normal',
			[
				'label' => __( 'Normal', 'elementor' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'background_overlay',
				'selector' => '{{WRAPPER}} > .elementor-element-populated >  .elementor-background-overlay',
			]
		);

		$this->add_control(
			'background_overlay_opacity',
			[
				'label' => __( 'Opacity', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => .5,
				],
				'range' => [
					'px' => [
						'max' => 1,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} > .elementor-element-populated >  .elementor-background-overlay' => 'opacity: {{SIZE}};',
				],
				'condition' => [
					'background_overlay_background' => [ 'classic', 'gradient' ],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'css_filters',
				'selector' => '{{WRAPPER}} > .elementor-element-populated >  .elementor-background-overlay',
			]
		);

		$this->add_control(
			'overlay_blend_mode',
			[
				'label' => __( 'Blend Mode', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'' => __( 'Normal', 'elementor' ),
					'multiply' => 'Multiply',
					'screen' => 'Screen',
					'overlay' => 'Overlay',
					'darken' => 'Darken',
					'lighten' => 'Lighten',
					'color-dodge' => 'Color Dodge',
					'saturation' => 'Saturation',
					'color' => 'Color',
					'luminosity' => 'Luminosity',
				],
				'selectors' => [
					'{{WRAPPER}} > .elementor-element-populated > .elementor-background-overlay' => 'mix-blend-mode: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_background_overlay_hover',
			[
				'label' => __( 'Hover', 'elementor' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'background_overlay_hover',
				'selector' => '{{WRAPPER}}:hover > .elementor-element-populated >  .elementor-background-overlay',
			]
		);

		$this->add_control(
			'background_overlay_hover_opacity',
			[
				'label' => __( 'Opacity', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => .5,
				],
				'range' => [
					'px' => [
						'max' => 1,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}}:hover > .elementor-element-populated >  .elementor-background-overlay' => 'opacity: {{SIZE}};',
				],
				'condition' => [
					'background_overlay_hover_background' => [ 'classic', 'gradient' ],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'css_filters_hover',
				'selector' => '{{WRAPPER}}:hover > .elementor-element-populated >  .elementor-background-overlay',
			]
		);

		$this->add_control(
			'background_overlay_hover_transition',
			[
				'label' => __( 'Transition Duration', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 0.3,
				],
				'range' => [
					'px' => [
						'max' => 3,
						'step' => 0.1,
					],
				],
				'render_type' => 'ui',
				'separator' => 'before',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_border',
			[
				'label' => __( 'Border', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'tabs_border' );

		$this->start_controls_tab(
			'tab_border_normal',
			[
				'label' => __( 'Normal', 'elementor' ),
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'border',
				'selector' => '{{WRAPPER}} > .elementor-element-populated',
			]
		);

		$this->add_responsive_control(
			'border_radius',
			[
				'label' => __( 'Border Radius', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} > .elementor-element-populated, {{WRAPPER}} > .elementor-element-populated > .elementor-background-overlay, {{WRAPPER}} > .elementor-background-slideshow' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'box_shadow',
				'selector' => '{{WRAPPER}} > .elementor-element-populated',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_border_hover',
			[
				'label' => __( 'Hover', 'elementor' ),
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'border_hover',
				'selector' => '{{WRAPPER}}:hover > .elementor-element-populated',
			]
		);

		$this->add_responsive_control(
			'border_radius_hover',
			[
				'label' => __( 'Border Radius', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}}:hover > .elementor-element-populated, {{WRAPPER}}:hover > .elementor-element-populated > .elementor-background-overlay' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'box_shadow_hover',
				'selector' => '{{WRAPPER}}:hover > .elementor-element-populated',
			]
		);

		$this->add_control(
			'border_hover_transition',
			[
				'label' => __( 'Transition Duration', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'separator' => 'before',
				'default' => [
					'size' => 0.3,
				],
				'range' => [
					'px' => [
						'max' => 3,
						'step' => 0.1,
					],
				],
				'conditions' => [
					'relation' => 'or',
					'terms' => [
						[
							'name' => 'background_background',
							'operator' => '!==',
							'value' => '',
						],
						[
							'name' => 'border_border',
							'operator' => '!==',
							'value' => '',
						],
					],
				],
				'selectors' => [
					'{{WRAPPER}} > .elementor-element-populated' => 'transition: background {{background_hover_transition.SIZE}}s, border {{SIZE}}s, border-radius {{SIZE}}s, box-shadow {{SIZE}}s',
					'{{WRAPPER}} > .elementor-element-populated > .elementor-background-overlay' => 'transition: background {{background_overlay_hover_transition.SIZE}}s, border-radius {{SIZE}}s, opacity {{background_overlay_hover_transition.SIZE}}s',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// Section Typography.
		$this->start_controls_section(
			'section_typo',
			[
				'label' => __( 'Typography', 'elementor' ),
				'type' => Controls_Manager::SECTION,
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_color',
			[
				'label' => __( 'Heading Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-element-populated .elementor-heading-title' => 'color: {{VALUE}};',
				],
				'separator' => 'none',
			]
		);

		$this->add_control(
			'color_text',
			[
				'label' => __( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} > .elementor-element-populated' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'color_link',
			[
				'label' => __( 'Link Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-element-populated a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'color_link_hover',
			[
				'label' => __( 'Link Hover Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-element-populated a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'text_align',
			[
				'label' => __( 'Text Align', 'elementor' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'elementor' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'elementor' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'elementor' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} > .elementor-element-populated' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Advanced.
		$this->start_controls_section(
			'section_advanced',
			[
				'label' => __( 'Advanced', 'elementor' ),
				'type' => Controls_Manager::SECTION,
				'tab' => Controls_Manager::TAB_ADVANCED,
			]
		);

		$this->add_responsive_control(
			'margin',
			[
				'label' => __( 'Margin', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} > .elementor-element-populated' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'padding',
			[
				'label' => __( 'Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} > .elementor-element-populated' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'z_index',
			[
				'label' => __( 'Z-Index', 'elementor' ),
				'type' => Controls_Manager::NUMBER,
				'min' => 0,
				'selectors' => [
					'{{WRAPPER}}' => 'z-index: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'_element_id',
			[
				'label' => __( 'CSS ID', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'dynamic' => [
					'active' => true,
				],
				'title' => __( 'Add your custom id WITHOUT the Pound key. e.g: my-id', 'elementor' ),
				'style_transfer' => false,
				'classes' => 'elementor-control-direction-ltr',
			]
		);

		$this->add_control(
			'css_classes',
			[
				'label' => __( 'CSS Classes', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'dynamic' => [
					'active' => true,
				],
				'prefix_class' => '',
				'title' => __( 'Add your custom class WITHOUT the dot. e.g: my-class', 'elementor' ),
				'classes' => 'elementor-control-direction-ltr',
			]
		);

		// TODO: Backward comparability for deprecated controls
		$this->add_control(
			'screen_sm',
			[
				'type' => Controls_Manager::HIDDEN,
			]
		);

		$this->add_control(
			'screen_sm_width',
			[
				'type' => Controls_Manager::HIDDEN,
				'condition' => [
					'screen_sm' => [ 'custom' ],
				],
				'prefix_class' => 'elementor-sm-',
			]
		);
		// END Backward comparability

		$this->end_controls_section();

		$this->start_controls_section(
			'section_effects',
			[
				'label' => __( 'Motion Effects', 'elementor' ),
				'tab' => Controls_Manager::TAB_ADVANCED,
			]
		);

		$this->add_responsive_control(
			'animation',
			[
				'label' => __( 'Entrance Animation', 'elementor' ),
				'type' => Controls_Manager::ANIMATION,
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'animation_duration',
			[
				'label' => __( 'Animation Duration', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'slow' => __( 'Slow', 'elementor' ),
					'' => __( 'Normal', 'elementor' ),
					'fast' => __( 'Fast', 'elementor' ),
				],
				'prefix_class' => 'animated-',
				'condition' => [
					'animation!' => '',
				],
			]
		);

		$this->add_control(
			'animation_delay',
			[
				'label' => __( 'Animation Delay', 'elementor' ) . ' (ms)',
				'type' => Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'step' => 100,
				'condition' => [
					'animation!' => '',
				],
				'render_type' => 'none',
				'frontend_available' => true,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'_section_responsive',
			[
				'label' => __( 'Responsive', 'elementor' ),
				'tab' => Controls_Manager::TAB_ADVANCED,
			]
		);

		$this->add_control(
			'responsive_description',
			[
				'raw' => __( 'Responsive visibility will take effect only on preview or live page, and not while editing in Elementor.', 'elementor' ),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor',
			]
		);

		$this->add_control(
			'hide_desktop',
			[
				'label' => __( 'Hide On Desktop', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => 'elementor-',
				'label_on' => 'Hide',
				'label_off' => 'Show',
				'return_value' => 'hidden-desktop',
			]
		);

		$this->add_control(
			'hide_tablet',
			[
				'label' => __( 'Hide On Tablet', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => 'elementor-',
				'label_on' => 'Hide',
				'label_off' => 'Show',
				'return_value' => 'hidden-tablet',
			]
		);

		$this->add_control(
			'hide_mobile',
			[
				'label' => __( 'Hide On Mobile', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => 'elementor-',
				'label_on' => 'Hide',
				'label_off' => 'Show',
				'return_value' => 'hidden-phone',
			]
		);

		$this->end_controls_section();

		Plugin::$instance->controls_manager->add_custom_attributes_controls( $this );

		Plugin::$instance->controls_manager->add_custom_css_controls( $this );
	}

	/**
	 * Render column output in the editor.
	 *
	 * Used to generate the live preview, using a Backbone JavaScript template.
	 *
	 * @since 2.9.0
	 * @access protected
	 */
	protected function content_template() {
		$is_dom_optimization_active = Plugin::$instance->experiments->is_feature_active( 'e_dom_optimization' );
		$wrapper_element = $is_dom_optimization_active ? 'widget' : 'column';

		?>
		<div class="elementor-<?php echo $wrapper_element; ?>-wrap">
			<div class="elementor-background-overlay"></div>
			<?php if ( ! $is_dom_optimization_active ) { ?>
				<div class="elementor-widget-wrap"></div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Before column rendering.
	 *
	 * Used to add stuff before the column element.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function before_render() {
		$settings = $this->get_settings_for_display();

		$has_background_overlay = in_array( $settings['background_overlay_background'], [ 'classic', 'gradient' ], true ) ||
								  in_array( $settings['background_overlay_hover_background'], [ 'classic', 'gradient' ], true );

		$is_dom_optimization_active = Plugin::$instance->experiments->is_feature_active( 'e_dom_optimization' );
		$wrapper_attribute_string = $is_dom_optimization_active ? '_widget_wrapper' : '_inner_wrapper';

		$column_wrap_classes = $is_dom_optimization_active ? [ 'elementor-widget-wrap' ] : [ 'elementor-column-wrap' ];

		if ( $this->get_children() ) {
			$column_wrap_classes[] = 'elementor-element-populated';
		}

		$this->add_render_attribute( [
			'_inner_wrapper' => [
				'class' => $column_wrap_classes,
			],
			'_widget_wrapper' => [
				'class' => $is_dom_optimization_active ? $column_wrap_classes : 'elementor-widget-wrap',
			],
			'_background_overlay' => [
				'class' => [ 'elementor-background-overlay' ],
			],
		] );
		?>
		<<?php echo $this->get_html_tag() . ' ' . $this->get_render_attribute_string( '_wrapper' ); ?>>
			<div <?php echo $this->get_render_attribute_string( $wrapper_attribute_string ); ?>>
		<?php if ( $has_background_overlay ) : ?>
			<div <?php echo $this->get_render_attribute_string( '_background_overlay' ); ?>></div>
		<?php endif; ?>
		<?php if ( ! $is_dom_optimization_active ) : ?>
			<div <?php echo $this->get_render_attribute_string( '_widget_wrapper' ); ?>>
		<?php endif; ?>
		<?php
	}

	/**
	 * After column rendering.
	 *
	 * Used to add stuff after the column element.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function after_render() {
		if ( ! Plugin::$instance->experiments->is_feature_active( 'e_dom_optimization' ) ) { ?>
				</div>
		<?php } ?>
			</div>
		</<?php echo $this->get_html_tag(); ?>>
		<?php
	}

	/**
	 * Add column render attributes.
	 *
	 * Used to add attributes to the current column wrapper HTML tag.
	 *
	 * @since 1.3.0
	 * @access protected
	 */
	protected function add_render_attributes() {

		$is_inner = $this->get_data( 'isInner' );

		$column_type = ! empty( $is_inner ) ? 'inner' : 'top';

		$settings = $this->get_settings();

		$this->add_render_attribute(
			'_wrapper', 'class', [
				'elementor-column',
				'elementor-col-' . $settings['_column_size'],
				'elementor-' . $column_type . '-column',
			]
		);

		parent::add_render_attributes();
	}

	/**
	 * Get default child type.
	 *
	 * Retrieve the column child type based on element data.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $element_data Element ID.
	 *
	 * @return Element_Base Column default child type.
	 */
	protected function _get_default_child_type( array $element_data ) {
		if ( 'section' === $element_data['elType'] ) {
			return Plugin::$instance->elements_manager->get_element_types( 'section' );
		}

		return Plugin::$instance->widgets_manager->get_widget_types( $element_data['widgetType'] );
	}

	/**
	 * Get HTML tag.
	 *
	 * Retrieve the column element HTML tag.
	 *
	 * @since 1.5.3
	 * @access private
	 *
	 * @return string Column HTML tag.
	 */
	private function get_html_tag() {
		$html_tag = $this->get_settings( 'html_tag' );

		if ( empty( $html_tag ) ) {
			$html_tag = 'div';
		}

		return Utils::validate_html_tag( $html_tag );
	}
}
