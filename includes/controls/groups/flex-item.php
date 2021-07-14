<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Group_Control_Flex_Item extends Group_Control_Base {

	protected static $fields;

	public static function get_type() {
		return 'flex-item';
	}

	protected function init_fields() {
		$fields = [];

		$fields['flex_basis'] = [
			'label' => _x( 'Flex Basis', 'Flex Item Control', 'elementor' ),
			'type' => Controls_Manager::SLIDER,
			'range' => [
				'px' => [
					'min' => 0,
					'max' => 1000,
				],
				'%' => [
					'min' => 0,
					'max' => 100,
				],
				'vw' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'default' => [
				'unit' => '%',
			],
			'size_units' => [ 'px', '%', 'vw' ],
			'selectors' => [
				'{{SELECTOR}}' => '--flex-basis: {{SIZE}}{{UNIT}};',
			],
			'responsive' => true,
		];

		$fields['flex_size'] = [
			'label' => _x( 'Size', 'Flex Item Control', 'elementor' ),
			'type' => Controls_Manager::CHOOSE,
			'default' => '',
			'options' => [
				'none' => [
					'title' => _x( 'None', 'Flex Item Control', 'elementor' ),
					'icon' => 'eicon-ban',
				],
				'grow' => [
					'title' => _x( 'Grow', 'Flex Item Control', 'elementor' ),
					'icon' => 'eicon-h-align-stretch',
				],
				'shrink' => [
					'title' => _x( 'Shrink', 'Flex Item Control', 'elementor' ),
					'icon' => 'eicon-h-align-center',
				],
				'custom' => [
					'title' => _x( 'Custom', 'Flex Item Control', 'elementor' ),
					'icon' => 'eicon-apps',
				],
			],
			'selectors_dictionary' => [
				'grow' => '--flex-grow: 1;',
				'shrink' => '--flex-shrink: 1;',
				'custom' => '',
				'none' => '',
			],
			'selectors' => [
				'{{SELECTOR}}' => '{{VALUE}};',
			],
			'responsive' => true,
		];

		$fields['flex_grow'] = [
			'label' => _x( 'Flex Grow', 'Flex Item Control', 'elementor' ),
			'type' => Controls_Manager::NUMBER,
			'selectors' => [
				'{{SELECTOR}}' => '--flex-grow: {{VALUE}};',
			],
			'default' => 1,
			'placeholder' => 1,
			'responsive' => true,
			'condition' => [
				'flex_size' => 'custom',
			],
		];

		$fields['flex_shrink'] = [
			'label' => _x( 'Flex Shrink', 'Flex Item Control', 'elementor' ),
			'type' => Controls_Manager::NUMBER,
			'selectors' => [
				'{{SELECTOR}}' => '--flex-shrink: {{VALUE}};',
			],
			'default' => 1,
			'placeholder' => 1,
			'responsive' => true,
			'condition' => [
				'flex_size' => 'custom',
			],
		];

		$fields['align_self'] = [
			'label' => _x( 'Align Self', 'Flex Item Control', 'elementor' ),
			'type' => Controls_Manager::CHOOSE,
			'options' => [
				'flex-start' => [
					'title' => _x( 'Flex Start', 'Flex Item Control', 'elementor' ),
					'icon' => 'eicon-flex-align-start',
				],
				'center' => [
					'title' => _x( 'Center', 'Flex Item Control', 'elementor' ),
					'icon' => 'eicon-flex-align-center',
				],
				'flex-end' => [
					'title' => _x( 'Flex End', 'Flex Item Control', 'elementor' ),
					'icon' => 'eicon-flex-align-end',
				],
				'stretch' => [
					'title' => _x( 'Stretch', 'Flex Item Control', 'elementor' ),
					'icon' => 'eicon-flex-align-stretch',
				],
			],
			'default' => '',
			'selectors' => [
				'{{SELECTOR}}' => '--align-self: {{VALUE}};',
			],
			'responsive' => true,
		];

		$fields['order'] = [
			'label' => _x( 'Order', 'Flex Item Control', 'elementor' ),
			'type' => Controls_Manager::CHOOSE,
			'default' => '',
			'options' => [
				'start' => [
					'title' => _x( 'Start', 'Flex Item Control', 'elementor' ),
					'icon' => 'eicon-h-align-left',
				],
				'end' => [
					'title' => _x( 'End', 'Flex Item Control', 'elementor' ),
					'icon' => 'eicon-h-align-right',
				],
				'custom' => [
					'title' => _x( 'Custom', 'Flex Item Control', 'elementor' ),
					'icon' => 'eicon-apps',
				],
			],
			'selectors_dictionary' => [
				// Hacks to set the order to start / end.
				// For example, if the user has 10 widgets, but wants to set the 5th one to be first,
				// this hack should do the trick.
				'start' => '-99999 /* order start hack */',
				'end' => '99999 /* order end hack */',
				'custom' => '',
			],
			'selectors' => [
				'{{SELECTOR}}' => '--order: {{VALUE}};',
			],
			'responsive' => true,
		];

		$fields['order_custom'] = [
			'label' => _x( 'Order', 'Flex Item Control', 'elementor' ),
			'type' => Controls_Manager::NUMBER,
			'selectors' => [
				'{{SELECTOR}}' => '--order: {{VALUE}};',
			],
			'responsive' => true,
			'condition' => [
				'order' => 'custom',
			],
		];

		return $fields;
	}

	protected function get_default_options() {
		return [
			'popover' => false,
		];
	}
}
