export default class SwiperBC {
	constructor( container, config ) {
		this.config = config;

		if ( this.config.breakpoints ) {
			// The config is passed as a param to allow adjustConfig to be called outside of this wrapper
			this.config = this.adjustConfig( config );
		}

		// In case of a legacy behaviour the constructor should return a new Swiper instance instead of a Promise.
		if ( config.legacy ) {
			return this.createSwiperInstance( container, this.config );
		}

		return new Promise( ( resolve ) => {
			if ( ! elementorFrontend.config.experimentalFeatures.e_optimized_assets_loading ) {
				return resolve( this.createSwiperInstance( container, this.config ) );
			}

			elementorFrontend.utils.assetsLoader.load( 'script', 'swiper' )
				.then( () => resolve( this.createSwiperInstance( container, this.config ) ) );
		} );
	}

	createSwiperInstance( container, config ) {
		// The condition should run only once to prevent an additional overwrite of the SwiperSource.
		if ( ! SwiperBC.isSwiperLoaded && elementorFrontend.config.experimentalFeatures.e_optimized_assets_loading ) {
			SwiperSource = window.Swiper;

			SwiperBC.isSwiperLoaded = true;

			// Once the SwiperSource has the Swiper lib function, we need to overwrite window.Swiper with the legacySwiper class.
			legacySwiper();
		}

		SwiperSource.prototype.adjustConfig = this.adjustConfig;

		return new SwiperSource( container, config );
	}

	// Backwards compatibility for Elementor Pro <2.9.0 (old Swiper version - <5.0.0)
	// In Swiper 5.0.0 and up, breakpoints changed from acting as max-width to acting as min-width
	adjustConfig( config ) {
		// Only reverse the breakpoints if the handle param has been defined
		if ( ! config.handleElementorBreakpoints ) {
			return config;
		}

		const elementorBreakpoints = elementorFrontend.config.responsive.activeBreakpoints,
			elementorBreakpointValues = elementorFrontend.breakpoints.getElementorBreakpointValues();

		Object.keys( config.breakpoints ).forEach( ( configBPKey ) => {
			const configBPKeyInt = parseInt( configBPKey );
			let breakpointToUpdate;

			// The `configBPKeyInt + 1` is a BC Fix for Elementor Pro Carousels from 2.8.0-2.8.3 used with Elementor >= 2.9.0
			if ( configBPKeyInt === elementorBreakpoints.mobile.value || ( configBPKeyInt + 1 ) === elementorBreakpoints.mobile.value ) {
				// This handles the mobile breakpoint. Elementor's default sm breakpoint is never actually used,
				// so the mobile breakpoint (md) needs to be handled separately and set to the 0 breakpoint (xs)
				breakpointToUpdate = 0;
			} else if ( elementorBreakpoints.widescreen && ( configBPKeyInt === elementorBreakpoints.widescreen.value || ( configBPKeyInt + 1 ) === elementorBreakpoints.widescreen.value ) ) {
				// Widescreen is a min-width breakpoint. Since in Swiper >5.0 the breakpoint system is min-width based,
				// the value we pass to the Swiper instance in this case is the breakpoint from the user, unchanged.
				breakpointToUpdate = configBPKeyInt;
			} else {
				// Find the index of the current config breakpoint in the Elementor Breakpoints array
				const currentBPIndexInElementorBPs = elementorBreakpointValues.findIndex( ( elementorBP ) => {
					// BC Fix for Elementor Pro Carousels from 2.8.0-2.8.3 used with Elementor >= 2.9.0
					return configBPKeyInt === elementorBP || ( configBPKeyInt + 1 ) === elementorBP;
				} );

				// For all other Swiper config breakpoints, move them one breakpoint down on the breakpoint list,
				// according to the array of Elementor's global breakpoints
				breakpointToUpdate = elementorBreakpointValues[ currentBPIndexInElementorBPs - 1 ];
			}

			config.breakpoints[ breakpointToUpdate ] = config.breakpoints[ configBPKey ];

			// Then reset the settings in the original breakpoint key to the default values
			config.breakpoints[ configBPKey ] = {
				slidesPerView: config.slidesPerView,
				slidesPerGroup: config.slidesPerGroup ? config.slidesPerGroup : 1,
			};
		} );

		return config;
	}
}

// The following code is needed to support Pro version < 3.1.0.
SwiperBC.isSwiperLoaded = false;

// In the legacy behavior, window.Swiper was a class that returns an instance of the Swiper lib function after config adjustments.
function legacySwiper() {
	window.Swiper = class {
		constructor( container, config ) {
			config.legacy = true;

			return new SwiperBC( container, config );
		}
	};
}

let SwiperSource = window.Swiper;

// In case that the Swiper lib exists (meaning not in optimized mode) we overwrite the window.Swiper with a class that supports legacy behavior.
if ( SwiperSource ) {
	legacySwiper();
}
