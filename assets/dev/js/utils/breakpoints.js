/**
 * Breakpoints
 *
 * This utility class contains helper functions relating to Elementor's breakpoints system.
 *
 * @since 3.4.0
 */
export default class Breakpoints extends elementorModules.Module {
	constructor( responsiveConfig ) {
		super();

		// The passed config is either `elementor.config.responsive` or `elementorFrontend.config.responsive`
		this.responsiveConfig = responsiveConfig;
	}

	/**
	 * Get Devices List
	 *
	 * Returns a flat array of active devices (breakpoints), INCLUDING desktop. By default, it returns the list ordered
	 * from smallest to largest breakpoint. If `true` is passed as a parameter, it reverses the order.
	 *
	 * @since 3.4.0
	 */
	getActiveDevicesList( largeToSmall = false ) {
		const { activeBreakpoints } = this.responsiveConfig,
			devices = Object.keys( activeBreakpoints ),
			// If there is an active 'widescreen' breakpoint, insert the artificial 'desktop' device below it.
			widescreenIndex = devices.indexOf( 'widescreen' ),
			indexToInsertDesktopDevice = -1 === widescreenIndex ? devices.length : devices.length - 1;

		devices.splice( indexToInsertDesktopDevice, 0, 'desktop' );

		if ( largeToSmall ) {
			devices.reverse();
		}

		return devices;
	}

	/**
	 * Get Active Breakpoints List
	 *
	 * Returns a flat array containing the active breakpoints (not devices), WITHOUT desktop. By default, it returns
	 * the list ordered from smallest to largest breakpoint. If `true` is passed as a parameter, it reverses the order.
	 *
	 * @since 3.4.0
	 */
	getActiveBreakpointsList( largeToSmall = false ) {
		const breakpointKeys = Object.keys( this.responsiveConfig.activeBreakpoints );

		if ( largeToSmall ) {
			breakpointKeys.reverse();
		}

		return breakpointKeys;
	}

	/**
	 * Get Active Breakpoint Values
	 *
	 * Returns a flat array containing the list of active breakpoint values, from smallest to largest.
	 *
	 * @since 3.4.0
	 */
	getElementorBreakpointValues() {
		const { activeBreakpoints } = this.responsiveConfig,
			breakpointValues = [];

		Object.values( activeBreakpoints ).forEach( ( breakpointConfig ) => {
			breakpointValues.push( breakpointConfig.value );
		} );

		return breakpointValues;
	}

	/**
	 * Get Desktop Previous Device Key
	 *
	 * Returns the key of the device directly under desktop (can be 'tablet', 'tablet_extra', 'laptop').
	 *
	 * @since 3.4.0
	 *
	 * @returns {string}
	 */
	getDesktopPreviousDeviceKey() {
		let desktopPreviousDevice = '';

		const { activeBreakpoints } = this.responsiveConfig,
			breakpointKeys = Object.keys( activeBreakpoints ),
			numOfDevices = breakpointKeys.length;

		if ( 'min' === activeBreakpoints[ breakpointKeys[ numOfDevices - 1 ] ].direction ) {
			// If the widescreen breakpoint is active, the device that's previous to desktop is the last one before
			// widescreen.
			desktopPreviousDevice = breakpointKeys[ numOfDevices - 2 ];
		} else {
			// If the widescreen breakpoint isn't active, we just take the last device returned by the config.
			desktopPreviousDevice = breakpointKeys[ numOfDevices - 1 ];
		}

		return desktopPreviousDevice;
	}

	/**
	 * Get Device Minimum Breakpoint
	 *
	 * Returns the minimum point in the device's display range. For each device, the minimum point of its display range
	 * is the max point of the device below it + 1px. For example, if the active devices are mobile, tablet,
	 * and desktop, and the mobile breakpoint is 767px, the minimum display point for tablet devices is 768px.
	 *
	 * @since 3.4.0
	 *
	 * @returns {number|*}
	 */
	getDesktopMinPoint() {
		const { activeBreakpoints } = this.responsiveConfig,
			desktopPreviousDevice = this.getDesktopPreviousDeviceKey();

		return activeBreakpoints[ desktopPreviousDevice ].value + 1;
	}

	/**
	 * Get Device Minimum Breakpoint
	 *
	 * Returns the minimum point in the device's display range. For each device, the minimum point of its display range
	 * is the max point of the device below it + 1px. For example, if the active devices are mobile, tablet,
	 * and desktop, and the mobile breakpoint is 767px, the minimum display point for tablet devices is 768px.
	 *
	 * @since 3.4.0
	 *
	 * @param device
	 * @returns {number|*}
	 */
	getDeviceMinBreakpoint( device ) {
		if ( 'desktop' === device ) {
			return this.getDesktopMinPoint();
		}

		const { activeBreakpoints } = this.responsiveConfig,
			breakpointNames = Object.keys( activeBreakpoints );

		let minBreakpoint;

		if ( breakpointNames[ 0 ] === device ) {
			// For the lowest breakpoint, the min point is always 320.
			minBreakpoint = 320;
		} else if ( 'min' === activeBreakpoints[ device ].direction ) {
			// Widescreen only has a minimum point. In this case, the breakpoint
			// value in the Breakpoints config is itself the device min point.
			minBreakpoint = activeBreakpoints[ device ].value;
		} else {
			const deviceNameIndex = breakpointNames.indexOf( device ),
				previousIndex = deviceNameIndex - 1;

			minBreakpoint = activeBreakpoints[ breakpointNames[ previousIndex ] ].value + 1;
		}

		return minBreakpoint;
	}

	/**
	 * Get Active Match Regex
	 *
	 * Returns a regular expression containing all active breakpoints prefixed with an underscore.
	 *
	 * @returns {RegExp}
	 */
	getActiveMatchRegex() {
		return new RegExp( this.getActiveBreakpointsList().map( ( device ) => '_' + device ).join( '|' ) + '$' );
	}
}
