var ControlBaseMultipleItemView = require( 'elementor-controls/base-multiple' ),
	ControlBaseUnitsItemView;

ControlBaseUnitsItemView = ControlBaseMultipleItemView.extend( {

	ui: function() {
		return Object.assign( ControlBaseMultipleItemView.prototype.ui.apply( this, arguments ), {
			units: '.elementor-units-choices>input',
		} );
	},

	events: function() {
		return Object.assign( ControlBaseMultipleItemView.prototype.events.apply( this, arguments ), {
			'change @ui.units': 'onUnitChange',
		} );
	},

	updatePlaceholder: function() {
		const placeholder = this.model.get( 'placeholder' )?.unit;

		this.ui.units.removeClass( 'e-units-placeholder' );

		if ( placeholder ) {
			this.ui.units.filter( `[value="${ placeholder }"]` )
				.addClass( 'e-units-placeholder' );
		}
	},

	onRender: function() {
		ControlBaseMultipleItemView.prototype.onRender.apply( this, arguments );

		this.updatePlaceholder();
	},

	onUnitChange: function() {
		this.updatePlaceholder();
	},

	getCurrentRange: function() {
		return this.getUnitRange( this.getControlValue( 'unit' ) );
	},

	getUnitRange: function( unit ) {
		var ranges = this.model.get( 'range' );

		if ( ! ranges || ! ranges[ unit ] ) {
			return false;
		}

		return ranges[ unit ];
	},
} );

module.exports = ControlBaseUnitsItemView;
