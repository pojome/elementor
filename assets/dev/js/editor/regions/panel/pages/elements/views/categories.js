var PanelElementsCategoryView = require( './category' ),
	PanelElementsCategoriesView;

PanelElementsCategoriesView = Marionette.CompositeView.extend( {
	template: '#tmpl-elementor-panel-categories',

	childView: PanelElementsCategoryView,

	childViewContainer: '#elementor-panel-categories',

	id: 'elementor-panel-elements-categories',

	initialize: function() {
		this.listenTo( elementor.channels.panelElements, 'filter:change', this.onPanelElementsFilterChange );
	},

	onPanelElementsFilterChange: function() {
		if ( elementor.channels.panelElements.request( 'filter:value' ) ) {
			elementor.getPanelView().getCurrentPageView().showView( 'elements' );
		}
	},
} );

module.exports = PanelElementsCategoriesView;
