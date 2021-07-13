var ControlBaseDataView = require( 'elementor-controls/base-data' ),
	ControlChooseItemView;

ControlChooseItemView = ControlBaseDataView.extend( {
	ui: function() {
		var ui = ControlBaseDataView.prototype.ui.apply( this, arguments );

		ui.inputs = '[type="radio"]';

		return ui;
	},

	events: function() {
		return _.extend( ControlBaseDataView.prototype.events.apply( this, arguments ), {
			'mousedown label': 'onMouseDownLabel',
			'click @ui.inputs': 'onClickInput',
			'change @ui.inputs': 'onBaseInputChange',
		} );
	},

	updatePlaceholder: function() {
		const placeholder = this.model.get( 'placeholder' );

		if ( ! this.getControlValue() && placeholder ) {
			// Find the input which has value equals to the placeholder (which is the parent's value),
			// and add it a placeholder class, to indicate which value is selected in the parent.
			this.ui.inputs.filter( `[value="${ this.model.get( 'placeholder' ) }"]` )
				.addClass( 'e-choose-placeholder' );
		} else {
			this.ui.inputs.removeClass( 'e-choose-placeholder' );
		}
	},

	onReady: function() {
		this.updatePlaceholder();
	},

	applySavedValue: function() {
		const currentValue = this.getControlValue();

		if ( currentValue ) {
			this.ui.inputs.filter( '[value="' + currentValue + '"]' ).prop( 'checked', true );
		} else {
			this.ui.inputs.filter( ':checked' ).prop( 'checked', false );
		}
	},

	onMouseDownLabel: function( event ) {
		var $clickedLabel = this.$( event.currentTarget ),
			$selectedInput = this.$( '#' + $clickedLabel.attr( 'for' ) );

		$selectedInput.data( 'checked', $selectedInput.prop( 'checked' ) );
	},

	onClickInput: function( event ) {
		if ( ! this.model.get( 'toggle' ) ) {
			return;
		}

		var $selectedInput = this.$( event.currentTarget );

		if ( $selectedInput.data( 'checked' ) ) {
			$selectedInput.prop( 'checked', false ).trigger( 'change' );
		}
	},

	onBaseInputChange: function( event ) {
		ControlBaseDataView.prototype.onBaseInputChange.apply( this, arguments );

		this.updatePlaceholder();
	},
}, {

	onPasteStyle: function( control, clipboardValue ) {
		return '' === clipboardValue || undefined !== control.options[ clipboardValue ];
	},
} );

module.exports = ControlChooseItemView;
