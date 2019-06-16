var helpers;

helpers = {
	_enqueuedFonts: [],
	_enqueuedIconFonts: [],
	_inlineSvg: [],

	elementsHierarchy: {
		section: {
			column: {
				widget: null,
				section: null,
			},
		},
	},

	enqueueCSS( url, $document ) {
		const selector = 'link[href="' + url + '"]',
			link = '<link href="' + url + '" rel="stylesheet" type="text/css">';

		if ( ! $document ) {
			return;
		}

		if ( ! $document.find( selector ).length ) {
			$document.find( 'link:last' ).after( link );
		}
	},

	enqueuePreviewStylesheet( url ) {
		this.enqueueCSS( url, elementor.$previewContents );
	},

	enqueueEditorStylesheet( url ) {
		this.enqueueCSS( url, elementorCommon.elements.$document );
	},

	/**
	 * @deprecated 2.6.0
	 */
	enqueueStylesheet( url ) {
		elementorCommon.helpers.deprecatedMethod( 'elementor.helpers.enqueueStylesheet()', '2.6.0', 'elementor.helpers.enqueuePreviewStylesheet()' );
		this.enqueuePreviewStylesheet( url );
	},

	fetchInlineSvg( svgUrl, callback = false ) {
		fetch( svgUrl )
			.then( ( response ) => response.text() )
			.then( ( data ) => {
				if ( callback ) {
					callback( data );
				}
			} );
	},

	getInlineSvg( value, view ) {
		if ( ! value.id ) {
			return;
		}

		if ( this._inlineSvg.hasOwnProperty( value.id ) ) {
			return this._inlineSvg[ value.id ];
		}

		const self = this;
		this.fetchInlineSvg( value.url, ( data ) => {
			const svgXML = $( data ).find( 'svg' )[ 0 ];
			if ( data ) {
				self._inlineSvg[ value.id ] = data; //$( data ).find( 'svg' )[ 0 ].outerHTML;
				if ( view ) {
					view.render();
				}
				elementor.channels.editor.trigger( 'svg:insertion', data, value.id );
			}
		} );
	},

	enqueueIconFonts( iconType ) {
		if ( -1 !== this._enqueuedIconFonts.indexOf( iconType ) ) {
			return;
		}

		const iconSetting = this.getIconLibrarySettings( iconType );
		if ( ! iconSetting ) {
			return;
		}

		if ( iconSetting.enqueue ) {
			iconSetting.enqueue.forEach( ( assetURL ) => {
				this.enqueuePreviewStylesheet( assetURL );
			} );
		}

		if ( iconSetting.url ) {
			this.enqueuePreviewStylesheet( iconSetting.url );
		}

		this._enqueuedIconFonts.push( iconType );

		elementor.channels.editor.trigger( 'fontIcon:insertion', iconType, iconSetting );
	},

	getIconLibrarySettings( iconType ) {
		const iconSetting = ElementorConfig.icons.filter( ( library ) => iconType === library.name );
		if ( iconSetting[ 0 ] && iconSetting[ 0 ].name ) {
			return iconSetting[ 0 ];
		}
		return false;
	},

	renderIcon( view, icon, attributes = {}, tag = 'i' ) {
		const iconType = icon.library,
			iconValue = icon.value;
		if ( 'svg' === iconType ) {
			return this.getInlineSvg( iconValue, view );
		}
		const iconSettings = this.getIconLibrarySettings( iconType );
		if ( iconSettings && ! iconSettings.hasOwnProperty( 'isCustom' ) ) {
			this.enqueueIconFonts( iconType );
			view.addRenderAttribute( tag, attributes );
			view.addRenderAttribute( tag, 'class', iconValue );
			return '<' + tag + ' ' + view.getRenderAttributeString( tag ) + '></' + tag + '>';
		}
		elementor.channels.editor.trigger( 'Icon:insertion', iconType, iconValue, attributes, tag, view );
	},

	enqueueFont( font ) {
		if ( -1 !== this._enqueuedFonts.indexOf( font ) ) {
			return;
		}

		const fontType = elementor.config.controls.font.options[ font ],
			subsets = {
				ru_RU: 'cyrillic',
				uk: 'cyrillic',
				bg_BG: 'cyrillic',
				vi: 'vietnamese',
				el: 'greek',
				he_IL: 'hebrew',
			};

		let	fontUrl;

		switch ( fontType ) {
			case 'googlefonts' :
				fontUrl = 'https://fonts.googleapis.com/css?family=' + font + ':100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic';

				if ( subsets[ elementor.config.locale ] ) {
					fontUrl += '&subset=' + subsets[ elementor.config.locale ];
				}

				break;

			case 'earlyaccess' :
				const fontLowerString = font.replace( /\s+/g, '' ).toLowerCase();
				fontUrl = 'https://fonts.googleapis.com/earlyaccess/' + fontLowerString + '.css';
				break;
		}

		if ( ! _.isEmpty( fontUrl ) ) {
			this.enqueuePreviewStylesheet( fontUrl );
		}

		this._enqueuedFonts.push( font );

		elementor.channels.editor.trigger( 'font:insertion', fontType, font );
	},

	resetEnqueuedFontsCache() {
		this._enqueuedFonts = [];
		this._enqueuedIconFonts = [];
	},

	getElementChildType( elementType, container ) {
		if ( ! container ) {
			container = this.elementsHierarchy;
		}

		if ( undefined !== container[ elementType ] ) {
			if ( jQuery.isPlainObject( container[ elementType ] ) ) {
				return Object.keys( container[ elementType ] );
			}

			return null;
		}

		let result = null;

		jQuery.each( container, ( index, type ) => {
			if ( ! jQuery.isPlainObject( type ) ) {
				return;
			}

			const childType = this.getElementChildType( elementType, type );

			if ( childType ) {
				result = childType;
				return false;
			}
		} );

		return result;
	},

	getUniqueID() {
		return Math.random().toString( 16 ).substr( 2, 7 );
	},

	/*
	* @deprecated 2.0.0
	*/
	stringReplaceAll( string, replaces ) {
		var re = new RegExp( Object.keys( replaces ).join( '|' ), 'gi' );

		return string.replace( re, function( matched ) {
			return replaces[ matched ];
		} );
	},

	isActiveControl: function( controlModel, values ) {
		let condition,
			conditions;

		// TODO: Better way to get this?
		if ( _.isFunction( controlModel.get ) ) {
			condition = controlModel.get( 'condition' );
			conditions = controlModel.get( 'conditions' );
		} else {
			condition = controlModel.condition;
			conditions = controlModel.conditions;
		}

		// Multiple conditions with relations.
		if ( conditions ) {
			return elementor.conditions.check( conditions, values );
		}

		if ( _.isEmpty( condition ) ) {
			return true;
		}

		var hasFields = _.filter( condition, function( conditionValue, conditionName ) {
			var conditionNameParts = conditionName.match( /([a-z_\-0-9]+)(?:\[([a-z_]+)])?(!?)$/i ),
				conditionRealName = conditionNameParts[ 1 ],
				conditionSubKey = conditionNameParts[ 2 ],
				isNegativeCondition = !! conditionNameParts[ 3 ],
				controlValue = values[ conditionRealName ];

			if ( values.__dynamic__ && values.__dynamic__[ conditionRealName ] ) {
				controlValue = values.__dynamic__[ conditionRealName ];
			}

			if ( undefined === controlValue ) {
				return true;
			}

			if ( conditionSubKey && 'object' === typeof controlValue ) {
				controlValue = controlValue[ conditionSubKey ];
			}

			// If it's a non empty array - check if the conditionValue contains the controlValue,
			// If the controlValue is a non empty array - check if the controlValue contains the conditionValue
			// otherwise check if they are equal. ( and give the ability to check if the value is an empty array )
			var isContains;

			if ( _.isArray( conditionValue ) && ! _.isEmpty( conditionValue ) ) {
				isContains = _.contains( conditionValue, controlValue );
			} else if ( _.isArray( controlValue ) && ! _.isEmpty( controlValue ) ) {
				isContains = _.contains( controlValue, conditionValue );
			} else {
				isContains = _.isEqual( conditionValue, controlValue );
			}

			return isNegativeCondition ? isContains : ! isContains;
		} );

		return _.isEmpty( hasFields );
	},

	cloneObject( object ) {
		elementorCommon.helpers.deprecatedMethod( 'elementor.helpers.cloneObject', '2.3.0', 'elementorCommon.helpers.cloneObject' );

		return elementorCommon.helpers.cloneObject( object );
	},

	firstLetterUppercase( string ) {
		elementorCommon.helpers.deprecatedMethod( 'elementor.helpers.upperCaseWords', '2.3.0', 'elementorCommon.helpers.upperCaseWords' );

		return elementorCommon.helpers.upperCaseWords( string );
	},

	disableElementEvents( $element ) {
		$element.each( function() {
			const currentPointerEvents = this.style.pointerEvents;

			if ( 'none' === currentPointerEvents ) {
				return;
			}

			jQuery( this )
				.data( 'backup-pointer-events', currentPointerEvents )
				.css( 'pointer-events', 'none' );
		} );
	},

	enableElementEvents( $element ) {
		$element.each( function() {
			const $this = jQuery( this ),
				backupPointerEvents = $this.data( 'backup-pointer-events' );

			if ( undefined === backupPointerEvents ) {
				return;
			}

			$this
				.removeData( 'backup-pointer-events' )
				.css( 'pointer-events', backupPointerEvents );
		} );
	},

	getColorPickerPaletteIndex( paletteKey ) {
		return [ '7', '8', '1', '5', '2', '3', '6', '4' ].indexOf( paletteKey );
	},

	wpColorPicker( $element, options ) {
		const self = this,
			colorPickerScheme = elementor.schemes.getScheme( 'color-picker' ),
			items = _.sortBy( colorPickerScheme.items, function( item ) {
				return self.getColorPickerPaletteIndex( item.key );
			} ),
			defaultOptions = {
				width: window.innerWidth >= 1440 ? 271 : 251,
				palettes: _.pluck( items, 'value' ),
			};

		if ( options ) {
			_.extend( defaultOptions, options );
		}

		return $element.wpColorPicker( defaultOptions );
	},

	isInViewport( element, html ) {
		const rect = element.getBoundingClientRect();
		html = html || document.documentElement;
		return (
			rect.top >= 0 &&
			rect.left >= 0 &&
			rect.bottom <= ( window.innerHeight || html.clientHeight ) &&
			rect.right <= ( window.innerWidth || html.clientWidth )
		);
	},

	scrollToView( $element, timeout, $parent ) {
		if ( undefined === timeout ) {
			timeout = 500;
		}

		let $scrolled = $parent;
		const $elementorFrontendWindow = elementorFrontend.elements.$window;

		if ( ! $parent ) {
			$parent = $elementorFrontendWindow;

			$scrolled = elementor.$previewContents.find( 'html, body' );
		}

		setTimeout( function() {
			var parentHeight = $parent.height(),
				parentScrollTop = $parent.scrollTop(),
				elementTop = $parent === $elementorFrontendWindow ? $element.offset().top : $element[ 0 ].offsetTop,
				topToCheck = elementTop - parentScrollTop;

			if ( topToCheck > 0 && topToCheck < parentHeight ) {
				return;
			}

			const scrolling = elementTop - ( parentHeight / 2 );

			$scrolled.stop( true ).animate( { scrollTop: scrolling }, 1000 );
		}, timeout );
	},

	getElementInlineStyle( $element, properties ) {
		const style = {},
			elementStyle = $element[ 0 ].style;

		properties.forEach( ( property ) => {
			style[ property ] = undefined !== elementStyle[ property ] ? elementStyle[ property ] : '';
		} );

		return style;
	},

	cssWithBackup( $element, backupState, rules ) {
		const cssBackup = this.getElementInlineStyle( $element, Object.keys( rules ) );

		$element
			.data( 'css-backup-' + backupState, cssBackup )
			.css( rules );
	},

	recoverCSSBackup( $element, backupState ) {
		const backupKey = 'css-backup-' + backupState;

		$element.css( $element.data( backupKey ) );

		$element.removeData( backupKey );
	},

	elementSizeToUnit: function( $element, size, unit ) {
		const window = elementorFrontend.elements.window;

		switch ( unit ) {
			case '%':
				size = ( size / ( $element.offsetParent().width() / 100 ) );
				break;
			case 'vw':
				size = ( size / ( window.innerWidth / 100 ) );
				break;
			case 'vh':
				size = ( size / ( window.innerHeight / 100 ) );
		}

		return Math.round( size * 1000 ) / 1000;
	},

	compareVersions: function( versionA, versionB, operator ) {
		const prepareVersion = ( version ) => {
			version = version + '';

			return version.replace( /[^\d.]+/, '.-1.' );
		};

		versionA = prepareVersion( versionA );
		versionB = prepareVersion( versionB );

		if ( versionA === versionB ) {
			return ! operator || /^={2,3}$/.test( operator );
		}

		const versionAParts = versionA.split( '.' ).map( Number ),
			versionBParts = versionB.split( '.' ).map( Number ),
			longestVersionParts = Math.max( versionAParts.length, versionBParts.length );

		for ( let i = 0; i < longestVersionParts; i++ ) {
			const valueA = versionAParts[ i ] || 0,
				valueB = versionBParts[ i ] || 0;

			if ( valueA !== valueB ) {
				return elementor.conditions.compare( valueA, valueB, operator );
			}
		}
	},
};

module.exports = helpers;
