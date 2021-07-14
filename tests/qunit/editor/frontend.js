export default {
	elements: {
		window,
		// Shortcut bind, in other words make shortcuts listen to iframe.
		$window: jQuery( '#elementor-preview-iframe' ),
		$body: jQuery( '#elementor-test' ),
	},
	config: {
		elements: { data: {}, editSettings: {} },
		breakpoints: {},
		responsive: {
			breakpoints: {
				mobile: {
					label: 'Mobile',
					value: 767,
					direction: 'max',
					is_enabled: true,
				},
				mobile_extra: {
					label: 'Mobile Extra',
					value: 880,
					direction: 'max',
					is_enabled: false,
				},
				tablet: {
					label: 'Tablet',
					value: 1024,
					direction: 'max',
					is_enabled: true,
				},
				tablet_extra: {
					label: 'Tablet Extra',
					value: 1365,
					direction: 'max',
					is_enabled: false,
				},
				laptop: {
					label: 'Laptop',
					value: 1620,
					direction: 'max',
					is_enabled: false,
				},
				widescreen: {
					label: 'Widescreen',
					value: 2400,
					direction: 'min',
					is_enabled: false,
				},
			},
			activeBreakpoints: {
				mobile: {
					label: 'Mobile',
					value: 767,
					direction: 'max',
					is_enabled: true,
				},
				tablet: {
					label: 'Tablet',
					value: 1024,
					direction: 'max',
					is_enabled: true,
				},
			},
		},
	},
	isEditMode: () => {
	},
	elementsHandler: {
		runReadyTrigger: () => {
		},
	},
	getCurrentDeviceMode: () => {
		return 'desktop';
	},
	init: () => console.log( 'elementorFrontend::init' ), // eslint-disable-line no-console
};
