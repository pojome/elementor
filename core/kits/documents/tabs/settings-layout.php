<?php
namespace Elementor\Core\Kits\Documents\Tabs;

use Elementor\Core\Breakpoints\Breakpoint;
use Elementor\Core\Breakpoints\Manager as Breakpoints_Manager;
use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Core\Base\Document;
use Elementor\Modules\PageTemplates\Module as PageTemplatesModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Settings_Layout extends Tab_Base {

	const ACTIVE_BREAKPOINTS_CONTROL_ID = 'active_breakpoints';

	public function get_id() {
		return 'settings-layout';
	}

	public function get_title() {
		return __( 'Layout', 'elementor' );
	}

	public function get_group() {
		return 'settings';
	}

	public function get_icon() {
		return 'eicon-layout-settings';
	}

	public function get_help_url() {
		return 'https://go.elementor.com/global-layout';
	}

	protected function register_tab_controls() {
		$breakpoints_default_config = Breakpoints_Manager::get_default_config();
		$breakpoint_key_mobile = Breakpoints_Manager::BREAKPOINT_KEY_MOBILE;
		$breakpoint_key_tablet = Breakpoints_Manager::BREAKPOINT_KEY_TABLET;

		$this->start_controls_section(
			'section_' . $this->get_id(),
			[
				'label' => __( 'Layout Settings', 'elementor' ),
				'tab' => $this->get_id(),
			]
		);

		$this->add_responsive_control(
			'container_width',
			[
				'label' => __( 'Content Width', 'elementor' ) . ' (px)',
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => '1140',
				],
				'tablet_default' => [
					'size' => $breakpoints_default_config[ $breakpoint_key_tablet ]['default_value'],
				],
				'mobile_default' => [
					'size' => $breakpoints_default_config[ $breakpoint_key_mobile ]['default_value'],
				],
				'range' => [
					'px' => [
						'min' => 300,
						'max' => 1500,
						'step' => 10,
					],
				],
				'description' => __( 'Sets the default width of the content area (Default: 1140)', 'elementor' ),
				'selectors' => [
					'.elementor-section.elementor-section-boxed > .elementor-container' => 'max-width: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'space_between_widgets',
			[
				'label' => __( 'Widgets Space', 'elementor' ) . ' (px)',
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 20,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 40,
					],
				],
				'placeholder' => '20',
				'description' => __( 'Sets the default space between widgets (Default: 20)', 'elementor' ),
				'selectors' => [
					'.elementor-widget:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'page_title_selector',
			[
				'label' => __( 'Page Title Selector', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => 'h1.entry-title',
				'placeholder' => 'h1.entry-title',
				'description' => __( 'Elementor lets you hide the page title. This works for themes that have "h1.entry-title" selector. If your theme\'s selector is different, please enter it above.', 'elementor' ),
				'label_block' => true,
				'selectors' => [
					// Hack to convert the value into a CSS selector.
					'' => '}{{VALUE}}{display: var(--page-title-display)',
				],
			]
		);

		$this->add_control(
			'stretched_section_container',
			[
				'label' => __( 'Stretched Section Fit To', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => 'body',
				'description' => __( 'Enter parent element selector to which stretched sections will fit to (e.g. #primary / .wrapper / main etc). Leave blank to fit to page width.', 'elementor' ),
				'label_block' => true,
				'frontend_available' => true,
			]
		);

		/**
		 * @var PageTemplatesModule $page_templates_module
		 */
		$page_templates_module = Plugin::$instance->modules_manager->get_modules( 'page-templates' );
		$page_templates = $page_templates_module->add_page_templates( [], null, null );

		// Removes the Theme option from the templates because 'default' is already handled.
		unset( $page_templates[ PageTemplatesModule::TEMPLATE_THEME ] );

		$page_template_control_options = [
			'label' => __( 'Default Page Layout', 'elementor' ),
			'options' => [
				// This is here because the "Theme" string is different than the default option's string.
				'default' => __( 'Theme', 'elementor' ),
			] + $page_templates,
		];

		$page_templates_module->add_template_controls( $this->parent, 'default_page_template', $page_template_control_options );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_breakpoints',
			[
				'label' => __( 'Breakpoints', 'elementor' ),
				'tab' => $this->get_id(),
			]
		);

		$prefix = Breakpoints_Manager::BREAKPOINT_SETTING_PREFIX;
		$options = [];

		foreach ( $breakpoints_default_config as $breakpoint_key => $breakpoint ) {
			$options[ $prefix . $breakpoint_key ] = $breakpoint['label'];
		}

		$this->add_control(
			self::ACTIVE_BREAKPOINTS_CONTROL_ID,
			[
				'label' => __( 'Active Breakpoints', 'elementor' ),
				'type' => Controls_Manager::HIDDEN,
				'description' => __( 'Mobile and Tablet options cannot be deleted.', 'elementor' ),
				'options' => $options,
				'default' => [
					$prefix . $breakpoint_key_mobile,
					$prefix . $breakpoint_key_tablet,
				],
				'select2options' => [
					'allowClear' => false,
				],
				'lockedOptions' => [
					$prefix . $breakpoint_key_mobile,
					$prefix . $breakpoint_key_tablet,
				],
				'label_block' => true,
				'render_type' => 'none',
				'frontend_available' => true,
				'multiple' => true,
			]
		);

		$this->add_breakpoints_controls();

		// Include the old mobile and tablet breakpoint controls as hidden for backwards compatibility.
		$this->add_control( 'viewport_md', [ 'type' => Controls_Manager::HIDDEN ] );
		$this->add_control( 'viewport_lg', [ 'type' => Controls_Manager::HIDDEN ] );

		$this->end_controls_section();
	}

	/**
	 * Before Save
	 *
	 * Runs Before the Kit document is saved.
	 *
	 * For backwards compatibility, when the mobile and tablet breakpoints are updated, we also update the
	 * old breakpoint settings ('viewport_md', 'viewport_lg' ) with the saved values + 1px. The reason 1px
	 * is added is because the old breakpoints system was min-width based, and the new system introduced in
	 * Elementor v3.2.0 is max-width based.
	 *
	 * @since 3.2.0
	 *
	 * @param array $data
	 * @return array $data
	 */
	public function before_save( array $data ) {
		// When creating a default kit, $data['settings'] is empty and should remain empty, so settings.
		if ( empty( $data['settings'] ) ) {
			return $data;
		}

		$prefix = Breakpoints_Manager::BREAKPOINT_SETTING_PREFIX;
		$mobile_breakpoint_key = $prefix . Breakpoints_Manager::BREAKPOINT_KEY_MOBILE;
		$tablet_breakpoint_key = $prefix . Breakpoints_Manager::BREAKPOINT_KEY_TABLET;

		$default_breakpoint_config = Breakpoints_Manager::get_default_config();

		// Update the old mobile breakpoint. If the setting is empty, use the default value.
		$data['settings'][ $prefix . 'md' ] = empty( $data['settings'][ $mobile_breakpoint_key ] )
			? $default_breakpoint_config[ Breakpoints_Manager::BREAKPOINT_KEY_MOBILE ]['default_value'] + 1
			: $data['settings'][ $mobile_breakpoint_key ] + 1;

		// Update the old tablet breakpoint. If the setting is empty, use the default value.
		$data['settings'][ $prefix . 'lg' ] = empty( $data['settings'][ $tablet_breakpoint_key ] )
			? $default_breakpoint_config[ Breakpoints_Manager::BREAKPOINT_KEY_TABLET ]['default_value'] + 1
			: $data['settings'][ $tablet_breakpoint_key ] + 1;

		return $data;
	}

	public function on_save( $data ) {
		if ( ! isset( $data['settings'] ) || ( isset( $data['settings']['post_status'] ) && Document::STATUS_PUBLISH !== $data['settings']['post_status'] ) ) {
			return;
		}

		$should_compile_css = false;

		$breakpoints_default_config = Breakpoints_Manager::get_default_config();

		foreach ( $breakpoints_default_config as $breakpoint_key => $default_config ) {
			$breakpoint_setting_key = Breakpoints_Manager::BREAKPOINT_SETTING_PREFIX . $breakpoint_key;

			if ( isset( $data['settings'][ $breakpoint_setting_key ] ) ) {
				$should_compile_css = true;
			}
		}

		if ( $should_compile_css ) {
			Breakpoints_Manager::compile_stylesheet_templates();
		}
	}

	private function add_breakpoints_controls() {
		$default_breakpoints_config = Breakpoints_Manager::get_default_config();
		$prefix = Breakpoints_Manager::BREAKPOINT_SETTING_PREFIX;

		// Add a control for each of the **default** breakpoints.
		foreach ( $default_breakpoints_config as $breakpoint_key => $default_breakpoint_config ) {
			$this->add_control(
				'breakpoint_' . $breakpoint_key . '_heading',
				[
					'label' => $default_breakpoint_config['label'],
					'type' => Controls_Manager::HEADING,
					'conditions' => [
						'terms' => [
							[
								'name' => 'active_breakpoints',
								'operator' => 'contains',
								'value' => $prefix . $breakpoint_key,
							],
						],
					],
				]
			);

			$control_config = [
				'label' => __( 'Breakpoint', 'elementor' ) . ' (px)',
				'type' => Controls_Manager::NUMBER,
				'placeholder' => $default_breakpoint_config['default_value'],
				'frontend_available' => true,
				'validators' => [
					'Breakpoint' => [
						'breakpointName' => $breakpoint_key,
					],
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'active_breakpoints',
							'operator' => 'contains',
							'value' => $prefix . $breakpoint_key,
						],
					],
				],
			];

			// Add the breakpoint Control itself.
			$this->add_control( $prefix . $breakpoint_key, $control_config );
		}
	}
}
