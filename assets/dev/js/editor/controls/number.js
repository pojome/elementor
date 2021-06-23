var ControlBaseDataView = require( 'elementor-controls/base-data' ),
	ControlNumberItemView;

ControlNumberItemView = ControlBaseDataView.extend( {

	registerValidators: function() {
		ControlBaseDataView.prototype.registerValidators.apply( this, arguments );

		var validationTerms = {},
			model = this.model;

		[ 'min', 'max' ].forEach( function( term ) {
			var termValue = model.get( term );

			if ( _.isFinite( termValue ) ) {
				validationTerms[ term ] = termValue;
			}
		} );

		if ( ! jQuery.isEmptyObject( validationTerms ) ) {
			this.addValidator( new this.validatorTypes.Number( {
				validationTerms: validationTerms,
			} ) );
		}
	},
} );

module.exports = ControlNumberItemView;
