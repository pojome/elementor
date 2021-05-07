<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

/**
 * Elementor tabs widget.
 *
 * Elementor widget that displays vertical or horizontal tabs with different
 * pieces of content.
 *
 * @since 1.0.0
 */
class Widget_Tabs extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve tabs widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'tabs';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve tabs widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Tabs', 'elementor' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve tabs widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-tabs';
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since 2.1.0
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'tabs', 'accordion', 'toggle' ];
	}

	/**
	 * Register tabs widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_tabs',
			[
				'label' => __( 'Tabs', 'elementor' ),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'tab_title',
			[
				'label' => __( 'Title & Description', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Tab Title', 'elementor' ),
				'placeholder' => __( 'Tab Title', 'elementor' ),
				'label_block' => true,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'tab_content',
			[
				'label' => __( 'Content', 'elementor' ),
				'default' => __( 'Tab Content', 'elementor' ),
				'placeholder' => __( 'Tab Content', 'elementor' ),
				'type' => Controls_Manager::WYSIWYG,
				'show_label' => false,
			]
		);

		$this->add_control(
			'tabs',
			[
				'label' => __( 'Tabs Items', 'elementor' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'tab_title' => __( 'Tab #1', 'elementor' ),
						'tab_content' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'elementor' ),
					],
					[
						'tab_title' => __( 'Tab #2', 'elementor' ),
						'tab_content' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'elementor' ),
					],
				],
				'title_field' => '{{{ tab_title }}}',
			]
		);

		$this->add_control(
			'view',
			[
				'label' => __( 'View', 'elementor' ),
				'type' => Controls_Manager::HIDDEN,
				'default' => 'traditional',
			]
		);

		$this->add_control(
			'html_tag',
			[
				'label' => __( 'HTML Tag', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'condition' => [
					'look' => 'line_title',
			],
			'options' =>
			[
				'h1' => 'H1',
				'h2' => 'H2',
				'h3' => 'H3',
				'h4' => 'H4',
				'h5' => 'H5',
				'h6' => 'H6',
				'div' => 'div',
				'span' => 'span',
				'p' => 'p',
			],
			default => 'h3',
			]
		);

		$this->add_control(
			'type',
			[
				'label' => __( 'Position', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'horizontal',
				'options' => [
					'horizontal' => __( 'Horizontal', 'elementor' ),
					'vertical' => __( 'Vertical', 'elementor' ),
				],
				'prefix_class' => 'elementor-tabs-view-',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'tabs_align_horizontal',
			[
				'label' => __( 'Alignment', 'elementor' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'' => [
						'title' => __( 'Start', 'elementor' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'elementor' ),
						'icon' => 'eicon-h-align-center',
					],
					'end' => [
						'title' => __( 'End', 'elementor' ),
						'icon' => 'eicon-h-align-right',
					],
					'stretch' => [
						'title' => __( 'Justified', 'elementor' ),
						'icon' => 'eicon-h-align-stretch',
					],
				],
				'prefix_class' => 'elementor-tabs-alignment-',
				'condition' => [
					'type' => 'horizontal',
				],
			]
		);

		$this->add_control(
			'tabs_align_vertical',
			[
				'label' => __( 'Alignment', 'elementor' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'' => [
						'title' => __( 'Start', 'elementor' ),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => __( 'Center', 'elementor' ),
						'icon' => 'eicon-v-align-middle',
					],
					'end' => [
						'title' => __( 'End', 'elementor' ),
						'icon' => 'eicon-v-align-bottom',
					],
					'stretch' => [
						'title' => __( 'Justified', 'elementor' ),
						'icon' => 'eicon-v-align-stretch',
					],
				],
				'prefix_class' => 'elementor-tabs-alignment-',
				'condition' => [
					'type' => 'vertical',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_tabs_style',
			[
				'label' => __( 'Tabs', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'navigation_width',
			[
				'label' => __( 'Navigation Width', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'unit' => '%',
				],
				'range' => [
					'%' => [
						'min' => 10,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-tabs-wrapper' => 'width: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'type' => 'vertical',
				],
			]
		);

		$this->add_control(
			'border_width',
			[
				'label' => __( 'Border Width', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 1,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 10,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-tab-title, {{WRAPPER}} .elementor-tab-title:before, {{WRAPPER}} .elementor-tab-title:after, {{WRAPPER}} .elementor-tab-content, {{WRAPPER}} .elementor-tabs-content-wrapper' => 'border-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'border_color',
			[
				'label' => __( 'Border Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-tab-mobile-title, {{WRAPPER}} .elementor-tab-desktop-title.elementor-active, {{WRAPPER}} .elementor-tab-title:before, {{WRAPPER}} .elementor-tab-title:after, {{WRAPPER}} .elementor-tab-content, {{WRAPPER}} .elementor-tabs-content-wrapper' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'background_color',
			[
				'label' => __( 'Background Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-tab-desktop-title.elementor-active' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .elementor-tabs-content-wrapper' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_title',
			[
				'label' => __( 'Title', 'elementor' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'tab_color',
			[
				'label' => __( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-tab-title, {{WRAPPER}} .elementor-tab-title a' => 'color: {{VALUE}}',
				],
				'global' => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
			]
		);

		$this->add_control(
			'tab_active_color',
			[
				'label' => __( 'Active Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-tab-title.elementor-active,
					 {{WRAPPER}} .elementor-tab-title.elementor-active a' => 'color: {{VALUE}}',
				],
				'global' => [
					'default' => Global_Colors::COLOR_ACCENT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'tab_typography',
				'selector' => '{{WRAPPER}} .elementor-tab-title',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'title_shadow',
				'selector' => '{{WRAPPER}} .elementor-tab-title',
			]
		);

		$this->add_control(
			'title_align',
			[
				'label' => __( 'Alignment', 'elementor' ),
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
					'{{WRAPPER}} .elementor-tab-title' => 'text-align: {{VALUE}};',
				],
				'condition' => [
					'tabs_align' => 'stretch',
				],
			]
		);

		$this->add_control(
			'heading_content',
			[
				'label' => __( 'Content', 'elementor' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'content_color',
			[
				'label' => __( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-tab-content' => 'color: {{VALUE}};',
				],
				'global' => [
					'default' => Global_Colors::COLOR_TEXT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'content_typography',
				'selector' => '{{WRAPPER}} .elementor-tab-content',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'content_shadow',
				'selector' => '{{WRAPPER}} .elementor-tab-content',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render tabs widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$tabs = $this->get_settings_for_display( 'tabs' );

		$id_int = substr( $this->get_id_int(), 0, 3 );

		$a11y_improvements_experiment = Plugin::$instance->experiments->is_feature_active( 'a11y_improvements' );

		$this->add_render_attribute( 'elementor-tabs', 'class', 'elementor-tabs' );

		?>
		<div <?php echo $this->get_render_attribute_string( 'elementor-tabs' ); ?>>
			<div class="elementor-tabs-wrapper" role="tablist" >
				<?php
				foreach ( $tabs as $index => $item ) :
					$tab_count = $index + 1;
					$tab_title_setting_key = $this->get_repeater_setting_key( 'tab_title', 'tabs', $index );
					$tab_title = $a11y_improvements_experiment ? $item['tab_title'] : '<a href="">' . $item['tab_title'] . '</a>';

					$this->add_render_attribute( $tab_title_setting_key, [
						'id' => 'elementor-tab-title-' . $id_int . $tab_count,
						'class' => [ 'elementor-tab-title', 'elementor-tab-desktop-title' ],
						'aria-selected' => 1 === $tab_count ? 'true' : 'false',
						'data-tab' => $tab_count,
						'role' => 'tab',
						'tabindex' => 1 === $tab_count ? '0' : '-1',
						'aria-controls' => 'elementor-tab-content-' . $id_int . $tab_count,
						'aria-expanded' => 'false',
					] );
					?>
					<div <?php echo $this->get_render_attribute_string( $tab_title_setting_key ); ?>><?php echo $tab_title; ?></div>
				<?php endforeach; ?>
			</div>
			<div class="elementor-tabs-content-wrapper" role="tablist" aria-orientation="vertical">
				<<?php echo Utils::validate_html_tag( $settings['title_html_tag'] ); ?> <?php echo $this->get_render_attribute_string( $tab_title_setting_key ); ?>>
						<?php if ( $has_icon ) : ?>
				<?php
				foreach ( $tabs as $index => $item ) :
					$tab_count = $index + 1;
					$hidden = 1 === $tab_count ? 'false' : 'hidden';
					$tab_content_setting_key = $this->get_repeater_setting_key( 'tab_content', 'tabs', $index );
				<?php endforeach; ?>
					$tab_title_mobile_setting_key = $this->get_repeater_setting_key( 'tab_title_mobile', 'tabs', $tab_count );

					$this->add_render_attribute( $tab_content_setting_key, [
						'id' => 'elementor-tab-content-' . $id_int . $tab_count,
						'class' => [ 'elementor-tab-content', 'elementor-clearfix' ],
						'data-tab' => $tab_count,
						'role' => 'tabpanel',
						'aria-labelledby' => 'elementor-tab-title-' . $id_int . $tab_count,
						'tabindex' => '0',
						'hidden' => $hidden,
					] );

					$this->add_render_attribute( $tab_title_mobile_setting_key, [
						'class' => [ 'elementor-tab-title', 'elementor-tab-mobile-title' ],
						'aria-selected' => 1 === $tab_count ? 'true' : 'false',
						'data-tab' => $tab_count,
						'role' => 'tab',
						'tabindex' => 1 === $tab_count ? '0' : '-1',
						'aria-controls' => 'elementor-tab-content-' . $id_int . $tab_count,
						'aria-expanded' => 'false',
					] );

					$this->add_inline_editing_attributes( $tab_content_setting_key, 'advanced' );
					?>
					<div <?php echo $this->get_render_attribute_string( $tab_title_mobile_setting_key ); ?>><?php echo $item['tab_title']; ?></div>
					<div <?php echo $this->get_render_attribute_string( $tab_content_setting_key ); ?>><?php echo $this->parse_text_editor( $item['tab_content'] ); ?></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render tabs widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 2.9.0
	 * @access protected
	 */
	protected function content_template() {
		?>
		<div class="elementor-tabs" role="tablist" aria-orientation="vertical">
			<# if ( settings.tabs ) {
				var elementUid = view.getIDInt().toString().substr( 0, 3 ); #>
				<div class="elementor-tabs-wrapper" role="tablist">
					<# _.each( settings.tabs, function( item, index ) {
						var tabCount = index + 1,
							tabUid = elementUid + tabCount,
							tabTitleKey = 'tab-title-' + tabUid;

					view.addRenderAttribute( tabTitleKey, {
						'id': 'elementor-tab-title-' + tabUid,
						'class': [ 'elementor-tab-title','elementor-tab-desktop-title' ],
						'data-tab': tabCount,
						'role': 'tab',
						'tabindex': 1 === tabCount ? '0' : '-1',
						'aria-controls': 'elementor-tab-content-' + tabUid,
						'aria-expanded': 'false',
						} );
					#>
						<div {{{ view.getRenderAttributeString( tabTitleKey ) }}}>{{{ item.tab_title }}}</div>
					<# } ); #>
				</div>
				<div class="elementor-tabs-content-wrapper">
					<# _.each( settings.tabs, function( item, index ) {
						var tabCount = index + 1,
							tabContentKey = view.getRepeaterSettingKey( 'tab_content', 'tabs',index );

						view.addRenderAttribute( tabContentKey, {
							'id': 'elementor-tab-content-' + elementUid + tabCount,
							'class': [ 'elementor-tab-content', 'elementor-clearfix', 'elementor-repeater-item-' + item._id ],
							'data-tab': tabCount,
							'role' : 'tabpanel',
							'aria-labelledby' : 'elementor-tab-title-' + elementUid + tabCount
						} );

						view.addInlineEditingAttributes( tabContentKey, 'advanced' ); #>
						<div class="elementor-tab-title elementor-tab-mobile-title" data-tab="{{ tabCount }}" role="tab">{{{ item.tab_title }}}</div>
						<div {{{ view.getRenderAttributeString( tabContentKey ) }}}>{{{ item.tab_content }}}</div>
					<# } ); #>
						var titleHTMLTag = elementor.helpers.validateHTMLTag( settings.title_html_tag );
					#>
					<div class="elementor-accordion-item">
						<{{{ titleHTMLTag }}} {{{ view.getRenderAttributeString( tabTitleKey ) }}}>
							<# if ( settings.icon || settings.selected_icon ) { #>
							<span class="elementor-accordion-icon elementor-accordion-icon-{{ settings.icon_align }}" aria-hidden="true">
								<# if ( iconHTML && iconHTML.rendered && ( ! settings.icon || migrated ) ) { #>
									<span class="elementor-accordion-icon-closed">{{{ iconHTML.value }}}</span>
									<span class="elementor-accordion-icon-opened">{{{ iconActiveHTML.value }}}</span>
								<# } else { #>
									<i class="elementor-accordion-icon-closed {{ settings.icon }}"></i>
									<i class="elementor-accordion-icon-opened {{ settings.icon_active }}"></i>
								<# } #>
							</span>
							<# } #>
							<a class="elementor-accordion-title" href="">{{{ item.tab_title }}}</a>
						</{{{ titleHTMLTag }}}>
						<div {{{ view.getRenderAttributeString( tabContentKey ) }}}>{{{ item.tab_content }}}</div>
				</div>
			<# } #>
		</div>
		<?php
	}
}
