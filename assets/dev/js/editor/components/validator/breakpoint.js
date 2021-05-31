import Stylesheet from 'elementor-editor-utils/stylesheet';

const NumberValidator = require( 'elementor-validator/number' );

export default class BreakpointValidator extends NumberValidator {
	getDefaultSettings() {
		return {
			validationTerms: {
				// Max width we allow in general
				max: 5120,
			},
		};
	}

	initBreakpointProperties() {
		const validationTerms = this.getSettings( 'validationTerms' );

		this.breakpointKeysArray = Object.keys( elementorFrontend.config.responsive.activeBreakpoints );
		this.breakpointIndex = this.breakpointKeysArray.indexOf( validationTerms.breakpointName );
		this.topBreakpoint = elementorFrontend.config.responsive.activeBreakpoints[ this.breakpointKeysArray[ this.breakpointIndex + 1 ] ]?.value;
		this.bottomBreakpoint = Stylesheet.getDeviceMinBreakpoint( validationTerms.breakpointName );
	}

	validationMethod( newValue ) {
		const validationTerms = this.getSettings( 'validationTerms' ),
			errors = NumberValidator.prototype.validationMethod.call( this, newValue );

		// Validate both numeric and empty values, since breakpoints utilize default values when empty.
		if ( _.isFinite( newValue ) || '' === newValue ) {
			if ( ! this.validateMinMaxForBreakpoint( newValue, validationTerms ) ) {
				errors.push( 'Value is not between the breakpoints above or under the edited breakpoint' );
			}
		}

		return errors;
	}

	validateMinMaxForBreakpoint( newValue, validationTerms ) {
		const breakpointDefaultValue = elementorFrontend.config.responsive.breakpoints[ validationTerms.breakpointName ].default_value;

		let isValid = true;

		this.initBreakpointProperties();

		// Since the following comparison is <=, allow usage of the 320px value for the mobile breakpoint.
		if ( 'mobile' === validationTerms.breakpointName && 320 === this.bottomBreakpoint ) {
			this.bottomBreakpoint -= 1;
		}

		// If there is a breakpoint below the currently edited breakpoint
		if ( this.bottomBreakpoint ) {
			// Check that the new value is not under the bottom breakpoint's value.
			if ( '' !== newValue && newValue <= this.bottomBreakpoint ) {
				isValid = false;
			}

			// If the new value is empty, check that the default breakpoint value is not below the bottom breakpoint.
			if ( '' === newValue && breakpointDefaultValue <= this.bottomBreakpoint ) {
				isValid = false;
			}
		}

		// If there is a breakpoint above the currently edited breakpoint.
		if ( this.topBreakpoint ) {
			// Check that the value is not above the top breakpoint's value.
			if ( '' !== newValue && newValue >= this.topBreakpoint ) {
				isValid = false;
			}

			// If the new value is empty, check that the default breakpoint value is not above the top breakpoint.
			if ( '' === newValue && breakpointDefaultValue >= this.topBreakpoint ) {
				isValid = false;
			}
		}

		return isValid;
	}
}
