var PanelElementsElementsView;

PanelElementsElementsView = Marionette.CollectionView.extend( {
	childView: require( 'elementor-panel/pages/elements/views/element' ),

	id: 'elementor-panel-elements',

	className: 'elementor-responsive-panel',

	initialize: function() {
		this.listenTo( elementor.channels.panelElements, 'filter:change', this.onFilterChanged );
	},

	filter: function( childModel ) {
		const filterValue = elementor.channels.panelElements.request( 'filter:value' );

		if ( ! filterValue ) {
			return true;
		}

		if ( -1 !== childModel.get( 'title' ).toLowerCase().indexOf( filterValue.toLowerCase() ) ) {
			return true;
		}

		// Get the filter input localized value.
		const localized = elementor.channels.panelElements.request( 'filter:localized' ) || '';

		return _.any( childModel.get( 'keywords' ), function( keyword ) {
			keyword = keyword.toLowerCase();

			const regularFilter = ( -1 !== keyword.indexOf( filterValue.toLowerCase() ) ),
				localizedFilter = ( localized && -1 !== keyword.indexOf( localized.toLowerCase() ) );

			return regularFilter || localizedFilter;
		} );
	},

	onFilterChanged: function() {
		const filterValue = elementor.channels.panelElements.request( 'filter:value' );

		if ( ! filterValue ) {
			this.onFilterEmpty();
		}

		this._renderChildren();

		this.triggerMethod( 'children:render' );
	},

	onFilterEmpty: function() {
		$e.routes.refreshContainer( 'panel' );
	},
} );

module.exports = PanelElementsElementsView;
