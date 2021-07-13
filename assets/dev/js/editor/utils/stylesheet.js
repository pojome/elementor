( function( $ ) {
	var Stylesheet = function() {
		var self = this,
			rules = {},
			rawCSS = {},
			devices = {};

		var queryToHash = function( query ) {
			var hash = [];

			$.each( query, function( endPoint ) {
				hash.push( endPoint + '_' + this );
			} );

			return hash.join( '-' );
		};

		var hashToQuery = function( hash ) {
			var query = {};

			hash = hash.split( '-' ).filter( String );

			hash.forEach( function( singleQuery ) {
				// split {max}/{min}_{device name} to separate strings
				var queryParts = singleQuery.split( /_(.+)/ ),
					endPoint = queryParts[ 0 ],
					deviceName = queryParts[ 1 ];

				query[ endPoint ] = 'max' === endPoint ? devices[ deviceName ] : Stylesheet.getDeviceMinBreakpoint( deviceName );
			} );

			return query;
		};

		var addQueryHash = function( queryHash ) {
			rules[ queryHash ] = {};

			var hashes = Object.keys( rules );

			if ( hashes.length < 2 ) {
				return;
			}

			// Sort the devices from narrowest to widest
			hashes.sort( function( a, b ) {
				if ( 'all' === a ) {
					return -1;
				}

				if ( 'all' === b ) {
					return 1;
				}

				var aQuery = hashToQuery( a ),
					bQuery = hashToQuery( b );

				return bQuery.max - aQuery.max;
			} );

			var sortedRules = {};

			hashes.forEach( function( deviceName ) {
				sortedRules[ deviceName ] = rules[ deviceName ];
			} );

			rules = sortedRules;
		};

		var getQueryHashStyleFormat = function( queryHash ) {
			var query = hashToQuery( queryHash ),
				styleFormat = [];

			$.each( query, function( endPoint ) {
				styleFormat.push( '(' + endPoint + '-width:' + this + 'px)' );
			} );

			return '@media' + styleFormat.join( ' and ' );
		};

		this.addDevice = function( newDeviceName, deviceValue ) {
			devices[ newDeviceName ] = deviceValue;

			var deviceNames = Object.keys( devices );

			if ( deviceNames.length < 2 ) {
				return self;
			}

			// Sort the devices from narrowest to widest
			deviceNames.sort( function( a, b ) {
				return devices[ a ] - devices[ b ];
			} );

			var sortedDevices = {};

			deviceNames.forEach( function( deviceName ) {
				sortedDevices[ deviceName ] = devices[ deviceName ];
			} );

			devices = sortedDevices;

			return self;
		};

		this.addRawCSS = function( key, css ) {
			rawCSS[ key ] = css;
		};

		this.addRules = function( selector, styleRules, query ) {
			var queryHash = 'all';

			if ( ! _.isEmpty( query ) ) {
				queryHash = queryToHash( query );
			}

			if ( ! rules[ queryHash ] ) {
				addQueryHash( queryHash );
			}

			if ( ! styleRules ) {
				var parsedRules = selector.match( /[^{]+\{[^}]+}/g );

				$.each( parsedRules, function() {
					var parsedRule = this.match( /([^{]+)\{([^}]+)}/ );

					if ( parsedRule ) {
						self.addRules( parsedRule[ 1 ].trim(), parsedRule[ 2 ].trim(), query );
					}
				} );

				return;
			}

			if ( ! rules[ queryHash ][ selector ] ) {
				rules[ queryHash ][ selector ] = {};
			}

			if ( 'string' === typeof styleRules ) {
				styleRules = styleRules.split( ';' ).filter( String );

				var orderedRules = {};

				try {
					$.each( styleRules, function() {
						var property = this.split( /:(.*)?/ );

						orderedRules[ property[ 0 ].trim() ] = property[ 1 ].trim().replace( ';', '' );
					} );
				} catch ( error ) { // At least one of the properties is incorrect
					return;
				}

				styleRules = orderedRules;
			}

			$.extend( rules[ queryHash ][ selector ], styleRules );

			return self;
		};

		this.getRules = function() {
			return rules;
		};

		this.empty = function() {
			rules = {};
			rawCSS = {};
		};

		this.toString = function() {
			var styleText = '';

			$.each( rules, function( queryHash ) {
				var deviceText = Stylesheet.parseRules( this );

				if ( 'all' !== queryHash ) {
					deviceText = getQueryHashStyleFormat( queryHash ) + '{' + deviceText + '}';
				}

				styleText += deviceText;
			} );

			$.each( rawCSS, function() {
				styleText += this;
			} );

			return styleText;
		};
	};

	Stylesheet.parseRules = function( rules ) {
		var parsedRules = '';

		$.each( rules, function( selector ) {
			var selectorContent = Stylesheet.parseProperties( this );

			if ( selectorContent ) {
				parsedRules += selector + '{' + selectorContent + '}';
			}
		} );

		return parsedRules;
	};

	Stylesheet.parseProperties = function( properties ) {
		var parsedProperties = '';

		$.each( properties, function( propertyKey ) {
			if ( this ) {
				parsedProperties += propertyKey + ':' + this + ';';
			}
		} );

		return parsedProperties;
	};

	Stylesheet.getDesktopPreviousDeviceKey = () => {
		let desktopPreviousDevice = '';

		const { activeBreakpoints } = elementorFrontend.config.responsive,
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
	};

	Stylesheet.getDesktopMinPoint = () => {
		const { activeBreakpoints } = elementorFrontend.config.responsive,
			desktopPreviousDevice = Stylesheet.getDesktopPreviousDeviceKey();

		return activeBreakpoints[ desktopPreviousDevice ].value + 1;
	};

	Stylesheet.getDeviceMinBreakpoint = ( deviceName ) => {
		if ( 'desktop' === deviceName ) {
			return Stylesheet.getDesktopMinPoint();
		}

		const activeBreakpoints = elementorFrontend.config.responsive.activeBreakpoints,
			breakpointNames = Object.keys( activeBreakpoints );

		let minBreakpoint;

		if ( breakpointNames[ 0 ] === deviceName ) {
			// For the lowest breakpoint, the min point is always 320.
			minBreakpoint = 320;
		} else if ( 'min' === activeBreakpoints[ deviceName ].direction ) {
			// Widescreen only has a minimum point. In this case, the breakpoint
			// value in the Breakpoints config is itself the device min point.
			minBreakpoint = activeBreakpoints[ deviceName ].value;
		} else {
			const deviceNameIndex = breakpointNames.indexOf( deviceName ),
				previousIndex = deviceNameIndex - 1;

			minBreakpoint = activeBreakpoints[ breakpointNames[ previousIndex ] ].value + 1;
		}

		return minBreakpoint;
	};

	module.exports = Stylesheet;
} )( jQuery );
