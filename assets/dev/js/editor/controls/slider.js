var ControlBaseUnitsItemView = require( 'elementor-controls/base-units' ),
	ControlSliderItemView;

ControlSliderItemView = ControlBaseUnitsItemView.extend( {
	ui: function() {
		var ui = ControlBaseUnitsItemView.prototype.ui.apply( this, arguments );

		ui.slider = '.elementor-slider';

		return ui;
	},

	templateHelpers: function() {
		const templateHelpers = ControlBaseUnitsItemView.prototype.templateHelpers.apply( this, arguments );

		templateHelpers.isMultiple = this.isMultiple();

		return templateHelpers;
	},

	isMultiple: function() {
		const sizes = this.getControlValue( 'sizes' );

		return ! jQuery.isEmptyObject( sizes );
	},

	initSlider: function() {
		// Slider does not exist in tests.
		if ( ! this.ui.slider[ 0 ] ) {
			return;
		}

		this.destroySlider();

		const isMultiple = this.isMultiple(),
			unitRange = elementorCommon.helpers.cloneObject( this.getCurrentRange() ),
			step = unitRange.step;

		let sizes = this.getSize();

		if ( isMultiple ) {
			sizes = Object.values( sizes );
		} else {
			sizes = [ sizes ];

			this.ui.input.attr( unitRange );
		}

		delete unitRange.step;

		let tooltips;

		const self = this;

		if ( isMultiple ) {
			tooltips = [];

			sizes.forEach( () => tooltips.push( {
				to: ( value ) => value + self.getControlValue( 'unit' ),
			} ) );
		}

		const sliderInstance = noUiSlider.create( this.ui.slider[ 0 ], {
			start: sizes,
			range: unitRange,
			step: step,
			tooltips: tooltips,
			connect: isMultiple,
			format: {
				to: ( value ) => Math.round( value * 1000 ) / 1000,
				from: ( value ) => +value,
			},
		} );

		sliderInstance.on( 'slide', this.onSlideChange.bind( this ) );
	},

	applySavedValue: function() {
		ControlBaseUnitsItemView.prototype.applySavedValue.apply( this, arguments );
		// Slider does not exist in tests.
		if ( this.ui.slider[ 0 ] && this.ui.slider[ 0 ].noUiSlider ) {
			this.ui.slider[ 0 ].noUiSlider.set( this.getSize() );
		}
	},

	getSize: function() {
		const property = this.isMultiple() ? 'sizes' : 'size',
			value = this.getControlValue( property );

		if ( value && '' !== value ) {
			return value;
		}

		return this.model.get( 'placeholder' )?.[ property ];
	},

	resetSize: function() {
		if ( this.isMultiple() ) {
			this.setValue( 'sizes', {} );
		} else {
			this.setValue( 'size', '' );
		}

		this.initSlider();
	},

	destroySlider: function() {
		// Slider does not exist in tests.
		if ( this.ui.slider[ 0 ] && this.ui.slider[ 0 ].noUiSlider ) {
			this.ui.slider[ 0 ].noUiSlider.destroy();
		}
	},

	onReady: function() {
		if ( this.isMultiple() ) {
			this.$el.addClass( 'elementor-control-type-slider--multiple elementor-control-type-slider--handles-' + this.model.get( 'handles' ) );
		}

		this.initSlider();
	},

	onSlideChange: function( values, index ) {
		if ( this.isMultiple() ) {
			const sizes = elementorCommon.helpers.cloneObject( this.getSize() ),
				key = Object.keys( sizes )[ index ];

			sizes[ key ] = values[ index ];

			this.setValue( 'sizes', sizes );
		} else {
			this.setValue( 'size', values[ 0 ] );

			this.ui.input.val( values[ 0 ] );
		}
	},

	onInputChange: function( event ) {
		var dataChanged = event.currentTarget.dataset.setting;

		if ( 'size' === dataChanged ) {
			this.ui.slider[ 0 ].noUiSlider.set( this.getSize() );
		} else if ( 'unit' === dataChanged ) {
			this.resetSize();
		}
	},

	onBeforeDestroy: function() {
		this.destroySlider();

		this.$el.remove();
	},
} );

module.exports = ControlSliderItemView;
