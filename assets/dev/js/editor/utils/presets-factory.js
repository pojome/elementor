var presetsFactory;

presetsFactory = {

	getPresetsDictionary: function() {
		return {
			11: 100 / 9,
			12: 100 / 8,
			14: 100 / 7,
			16: 100 / 6,
			33: 100 / 3,
			66: 2 / 3 * 100,
			83: 5 / 6 * 100,
		};
	},

	getAbsolutePresetValues: function( preset ) {
		var clonedPreset = elementorCommon.helpers.cloneObject( preset ),
			presetDictionary = this.getPresetsDictionary();

		_.each( clonedPreset, function( unitValue, unitIndex ) {
			if ( presetDictionary[ unitValue ] ) {
				clonedPreset[ unitIndex ] = presetDictionary[ unitValue ];
			}
		} );

		return clonedPreset;
	},

	getPresets: function( columnsCount, presetIndex ) {
		var presets = elementorCommon.helpers.cloneObject( elementor.config.elements.section.presets );

		if ( columnsCount ) {
			presets = presets[ columnsCount ];
		}

		if ( presetIndex ) {
			presets = presets[ presetIndex ];
		}

		return presets;
	},

	getPresetByStructure: function( structure ) {
		var parsedStructure = this.getParsedStructure( structure );

		return this.getPresets( parsedStructure.columnsCount, parsedStructure.presetIndex );
	},

	getParsedStructure: function( structure ) {
		structure += ''; // Make sure this is a string

		return {
			columnsCount: structure.slice( 0, -1 ),
			presetIndex: structure.substr( -1 ),
		};
	},

	getPresetSVG: function( preset, svgWidth, svgHeight, separatorWidth ) {
		svgWidth = svgWidth || 100;
		svgHeight = svgHeight || 50;
		separatorWidth = separatorWidth || 2;

		var absolutePresetValues = this.getAbsolutePresetValues( preset ),
			presetSVGPath = this._generatePresetSVGPath( absolutePresetValues, svgWidth, svgHeight, separatorWidth );

		return this._createSVGPreset( presetSVGPath, svgWidth, svgHeight );
	},

	_createSVGPreset: function( presetPath, svgWidth, svgHeight ) {
		// this is here to avoid being picked up by https re-write systems
		const protocol = 'ht' + 'tp';
		var svg = document.createElementNS( protocol + '://www.w3.org/2000/svg', 'svg' );

		svg.setAttributeNS( protocol + '://www.w3.org/2000/xmlns/', 'xmlns:xlink', protocol + '://www.w3.org/1999/xlink' );
		svg.setAttribute( 'viewBox', '0 0 ' + svgWidth + ' ' + svgHeight );

		var path = document.createElementNS( protocol + '://www.w3.org/2000/svg', 'path' );

		path.setAttribute( 'd', presetPath );

		svg.appendChild( path );

		return svg;
	},

	_generatePresetSVGPath: function( preset, svgWidth, svgHeight, separatorWidth ) {
		var DRAW_SIZE = svgWidth - ( separatorWidth * ( preset.length - 1 ) );

		var xPointer = 0,
			dOutput = '';

		for ( var i = 0; i < preset.length; i++ ) {
			if ( i ) {
				dOutput += ' ';
			}

			var increment = preset[ i ] / 100 * DRAW_SIZE;

			xPointer += increment;

			dOutput += 'M' + ( +xPointer.toFixed( 4 ) ) + ',0';

			dOutput += 'V' + svgHeight;

			dOutput += 'H' + ( +( xPointer - increment ).toFixed( 4 ) );

			dOutput += 'V0Z';

			xPointer += separatorWidth;
		}

		return dOutput;
	},

	/**
	 * Return an SVG markup with text of a Container element (e.g. flex, grid, etc.).
	 *
	 * @param {string} presetId - Preset ID to retrieve.
	 * @param {string} text - The text to show on the preset (Optional - Used only in the default preset).
	 *
	 * @returns {string}
	 */
	getContainerPreset( presetId, text = '' ) {
		const presets = {
			33: `
				<svg width="90" height="44" viewBox="0 0 90 44" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect x="0.5" width="29" height="44" fill="#D5DADF"/>
					<rect x="30.5" width="29" height="44" fill="#D5DADF"/>
					<rect x="60.5" width="29" height="44" fill="#D5DADF"/>
				</svg>
			`,
			50: `
				<svg width="90" height="44" viewBox="0 0 90 44" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect x="0.5" width="44" height="44" fill="#D5DADF"/>
					<rect x="45.5" width="44" height="44" fill="#D5DADF"/>
				</svg>
			`,
			'50-25': `
				<svg width="90" height="44" viewBox="0 0 90 44" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect x="0.5" width="44" height="44" fill="#D5DADF"/>
					<rect x="45.5" width="44" height="21.5" fill="#D5DADF"/>
					<rect x="45.5" y="22.5" width="44" height="21.5" fill="#D5DADF"/>
				</svg>
			`,
			'50-50': `
				<svg width="90" height="44" viewBox="0 0 90 44" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect x="0.5" width="44" height="21.5" fill="#D5DADF"/>
					<rect x="45.5" width="44" height="21.5" fill="#D5DADF"/>
					<rect x="0.5" y="22.5" width="44" height="21.5" fill="#D5DADF"/>
					<rect x="45.5" y="22.5" width="44" height="21.5" fill="#D5DADF"/>
				</svg>
			`,
			100: `
				<svg width="90" height="44" viewBox="0 0 90 44" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect x="0.5" width="89" height="44" fill="#D5DADF"/>
				</svg>
			`,
			default: `
				<div style="--text:'${ text }'" class="e-preset--container">
					<svg width="90" height="44" viewBox="0 0 90 44" fill="none" xmlns="http://www.w3.org/2000/svg">
						<rect width="89" height="44" transform="translate(0.5)" fill="#D5DADF"/>
						<rect x="3" y="2.5" width="84" height="39" rx="2.5" stroke="#FCFCFC" stroke-linejoin="round" stroke-dasharray="3 2"/>
					</svg>
				</div>
			`,
		};

		return presets[ presetId ] || presets.default;
	},
};

module.exports = presetsFactory;
