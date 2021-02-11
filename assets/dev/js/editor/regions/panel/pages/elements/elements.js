var PanelElementsCategoriesCollection = require( './collections/categories' ),
	PanelElementsElementsCollection = require( './collections/elements' ),
	PanelElementsCategoriesView = require( './views/categories' ),
	PanelElementsElementsView = elementor.modules.layouts.panel.pages.elements.views.Elements,
	PanelElementsSearchView = require( './views/search' ),
	PanelElementsGlobalView = require( './views/global' ),
	PanelElementsLayoutView;

PanelElementsLayoutView = Marionette.LayoutView.extend( {
	template: '#tmpl-elementor-panel-elements',

	id: 'elementor-panel-page-elements',

	options: {
		autoFocusSearch: true,
	},

	regions: {
		elements: '#elementor-panel-elements-wrapper',
		search: '#elementor-panel-elements-search-area',
	},

	regionViews: {},

	elementsCollection: null,

	categoriesCollection: null,

	initialize: function() {
		this.listenTo( elementor.channels.panelElements, 'element:selected', this.destroy );

		this.initElementsCollection();

		this.initCategoriesCollection();

		this.initRegionViews();
	},

	initRegionViews: function() {
		var regionViews = {
			elements: {
				region: this.elements,
				view: PanelElementsElementsView,
				options: { collection: this.elementsCollection },
			},
			categories: {
				region: this.elements,
				view: PanelElementsCategoriesView,
				options: { collection: this.categoriesCollection },
			},
			search: {
				region: this.search,
				view: PanelElementsSearchView,
			},
			global: {
				region: this.elements,
				view: PanelElementsGlobalView,
			},
		};

		this.regionViews = elementor.hooks.applyFilters( 'panel/elements/regionViews', regionViews );
	},

	initElementsCollection: function() {
		var elementsCollection = new PanelElementsElementsCollection(),
			sectionConfig = elementor.config.elements.section;

		elementsCollection.add( {
			title: __( 'Inner Section', 'elementor' ),
			elType: 'section',
			widgetType: 'inner-section',
			categories: [ 'basic' ],
			keywords: [ 'row', 'columns', 'nested' ],
			icon: sectionConfig.icon,
		} );

		// TODO: Change the array from server syntax, and no need each loop for initialize
		_.each( elementor.widgetsCache, function( widget ) {
			if ( elementor.config.document.panel.widgets_settings[ widget.widget_type ] ) {
				widget = _.extend( widget, elementor.config.document.panel.widgets_settings[ widget.widget_type ] );
			}

			if ( ! widget.show_in_panel ) {
				return;
			}

			elementsCollection.add( {
				title: widget.title,
				elType: widget.elType,
				categories: widget.categories,
				keywords: widget.keywords,
				icon: widget.icon,
				widgetType: widget.widget_type,
				custom: widget.custom,
				editable: widget.editable,
			} );
		} );

		jQuery.each( elementor.config.promotionWidgets, ( index, widget ) => {
			elementsCollection.add( {
				name: widget.name,
				title: widget.title,
				icon: widget.icon,
				categories: JSON.parse( widget.categories ),
				editable: false,
			} );
		} );

		this.elementsCollection = elementsCollection;
	},

	initCategoriesCollection: function() {
		var categories = {};

		this.elementsCollection.each( function( element ) {
			_.each( element.get( 'categories' ), function( category ) {
				if ( ! categories[ category ] ) {
					categories[ category ] = [];
				}

				categories[ category ].push( element );
			} );
		} );

		var categoriesCollection = new PanelElementsCategoriesCollection();

		_.each( elementor.config.document.panel.elements_categories, function( categoryConfig, categoryName ) {
			if ( ! categories[ categoryName ] ) {
				return;
			}

			// Set defaults.
			if ( 'undefined' === typeof categoryConfig.active ) {
				categoryConfig.active = true;
			}

			if ( 'undefined' === typeof categoryConfig.icon ) {
				categoryConfig.icon = 'font';
			}

			categoriesCollection.add( {
				name: categoryName,
				title: categoryConfig.title,
				icon: categoryConfig.icon,
				defaultActive: categoryConfig.active,
				items: categories[ categoryName ],
			} );
		} );

		this.categoriesCollection = categoriesCollection;
	},

	showView: function( viewName ) {
		var viewDetails = this.regionViews[ viewName ],
			options = viewDetails.options || {};

		viewDetails.region.show( new viewDetails.view( options ) );
	},

	clearSearchInput: function() {
		this.getChildView( 'search' ).clearInput();
	},

	changeFilter: function( filterValue ) {
		elementor.channels.panelElements
			.reply( 'filter:value', filterValue )
			.trigger( 'filter:change' );
	},

	clearFilters: function() {
		this.changeFilter( null );
		this.clearSearchInput();
	},

	focusSearch: function() {
		if ( ! elementor.userCan( 'design' ) || ! this.search /* default panel is not elements */ || ! this.search.currentView /* on global elements empty */ ) {
			return;
		}

		this.search.currentView.ui.input.focus();
	},

	onChildviewChildrenRender: function() {
		elementor.getPanelView().updateScrollbar();
	},

	onChildviewSearchChangeInput: function( child ) {
		this.changeFilter( child.ui.input.val(), 'search' );
	},

	onDestroy: function() {
		elementor.channels.panelElements.reply( 'filter:value', null );
	},

	onShow: function() {
		this.showView( 'search' );

		if ( this.options.autoFocusSearch ) {
			setTimeout( this.focusSearch.bind( this ) );
		}
	},
} );

module.exports = PanelElementsLayoutView;
