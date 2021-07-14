<?php

namespace Elementor\Core\Settings\EditorPreferences;

use Elementor\Controls_Manager;
use Elementor\Core\Settings\Base\Model as BaseModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Model extends BaseModel {

	/**
	 * Get element name.
	 *
	 * Retrieve the element name.
	 *
	 * @return string The name.
	 * @since 2.8.0
	 * @access public
	 *
	 */
	public function get_name() {
		return 'editor-preferences';
	}

	/**
	 * Get panel page settings.
	 *
	 * Retrieve the page setting for the current panel.
	 *
	 * @since 2.8.0
	 * @access public
	 */
	public function get_panel_page_settings() {
		return [
			'title' => __( 'User Preferences', 'elementor' ),
		];
	}

	/**
	 * @since 3.1.0
	 * @access protected
	 */
	protected function register_controls() {
		$this->start_controls_section( 'preferences', [
			'tab' => Controls_Manager::TAB_SETTINGS,
			'label' => __( 'Preferences', 'elementor' ),
		] );

		$this->add_control(
			'ui_theme',
			[
				'label' => __( 'UI Theme', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'description' => __( 'Set light or dark mode, or use Auto Detect to sync it with your OS setting.', 'elementor' ),
				'default' => 'auto',
				'options' => [
					'auto' => __( 'Auto Detect', 'elementor' ),
					'light' => __( 'Light', 'elementor' ),
					'dark' => __( 'Dark', 'elementor' ),
				],
			]
		);

		$this->add_control(
			'panel_width',
			[
				'label' => __( 'Panel Width', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 680,
					],
				],
				'default' => [
					'size' => 300,
				],
			]
		);

		$this->add_control(
			'edit_buttons',
			[
				'label' => __( 'Editing Handles', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'description' => __( 'Show editing handles when hovering over the element edit button.', 'elementor' ),
			]
		);

		$this->add_control(
			'lightbox_in_editor',
			[
				'label' => __( 'Enable Lightbox In Editor', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'responsive_heading',
			[
				'label' => __( 'Responsive Preview', 'elementor' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_hidden_elements',
			[
				'label' => __( 'Hidden Elements', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => 'Show',
				'label_off' => 'Hide',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'default_device_view',
			[
				'label' => __( 'Default Device View ', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => __( 'Default', 'elementor' ),
					'mobile' => __( 'Mobile', 'elementor' ),
					'tablet' => __( 'Tablet', 'elementor' ),
					'desktop' => __( 'Desktop', 'elementor' ),
				],
			]
		);

		$this->end_controls_section();
	}
}
