import ControlsPopover from './controls-popover';

var ControlsStack;

ControlsStack = Marionette.CompositeView.extend( {
	classes: {
		popover: 'elementor-controls-popover',
	},

	activeTab: null,

	activeSection: null,

	isFiltered: false,

	className: function() {
		return 'elementor-controls-stack';
	},

	templateHelpers: function() {
		return {
			elementData: elementor.getElementData( this.model ),
		};
	},

	childViewOptions: function() {
		return {
			// TODO: elementSettingsModel is deprecated since 2.8.0.
			elementSettingsModel: this.model,
		};
	},

	ui: function() {
		return {
			tabs: '.elementor-panel-navigation-tab',
			reloadButton: '.elementor-update-preview-button',
			searchControls: '#elementor-panel-controls-search-input',
		};
	},

	events: function() {
		return {
			'click @ui.reloadButton': 'onReloadButtonClick',
			'keyup @ui.searchControls': 'onSearchControlsKeyUp',
		};
	},

	modelEvents: {
		destroy: 'onModelDestroy',
	},

	behaviors: {
		HandleInnerTabs: {
			behaviorClass: require( 'elementor-behaviors/inner-tabs' ),
		},
	},

	initialize: function( options ) {
		this.initCollection();

		if ( options.tab ) {
			this.activeTab = options.tab;
			this.activateFirstSection();
		}

		this.listenTo( elementor.channels.deviceMode, 'change', this.onDeviceModeChange );
	},

	initCollection: function() {
		this.collection = new Backbone.Collection( _.values( elementor.mergeControlsSettings( this.getOption( 'controls' ) ) ) );
	},

	// Override Backbone's base function.
	_filteredSortedModels: function( addedAt ) {
		const models = Marionette.CompositeView.prototype._filteredSortedModels.apply( this, [ addedAt ] );

		// If the user has filtered the controls.
		if ( this.isFiltered ) {
			let withSections = [];

			// Iterate over the filtered models.
			models.forEach( ( model ) => {
				// Get the section that contains the current model.
				const sectionName = ( 'section' === model.get( 'type' ) ) ? model.get( 'name' ) : model.get( 'section' );
				const section = model.collection.find( { name: sectionName } );

				// Add the section and its child-models to the output.
				if ( section && ! withSections.includes( section ) ) {
					withSections.push( section );

					const sectionControls = model.collection.filter( { section: sectionName } );
					withSections = withSections.concat( sectionControls );
				}
			} );

			return withSections;
		}

		return models;
	},

	filter: function( controlModel ) {
		// Remove underscore / hyphen, lower case & trim a string.
		const normalizeString = ( str ) => {
			return str.replace( /[-_]/ig, ' ' ).toLowerCase().trim();
		};

		if ( this.ui.searchControls ) {
			const searchTerm = normalizeString( this.ui.searchControls.val() || '' );

			// Filter the controls by the user input if present.
			if ( searchTerm ) {
				const show = normalizeString( controlModel.get( 'label' ) ).includes( searchTerm );
				this.isFiltered = show || this.isFiltered;

				return show;
			}
		}

		if ( controlModel.get( 'tab' ) !== this.activeTab ) {
			return false;
		}

		if ( 'section' === controlModel.get( 'type' ) ) {
			return true;
		}

		var section = controlModel.get( 'section' );

		return ! section || section === this.activeSection;
	},

	getControlViewByModel: function( model ) {
		return this.children.findByModelCid( model.cid );
	},

	getControlViewByName: function( name ) {
		return this.getControlViewByModel( this.getControlModel( name ) );
	},

	getControlModel: function( name ) {
		return this.collection.findWhere( { name: name } );
	},

	isVisibleSectionControl: function( sectionControlModel ) {
		return this.activeTab === sectionControlModel.get( 'tab' );
	},

	activateTab: function( tab ) {
		this.activeTab = tab;

		this.activateFirstSection();

		this._renderChildren();

		return this;
	},

	activateSection: function( sectionName ) {
		this.activeSection = sectionName;

		return this;
	},

	activateFirstSection: function() {
		var self = this;

		var sectionControls = self.collection.filter( function( controlModel ) {
			return 'section' === controlModel.get( 'type' ) && self.isVisibleSectionControl( controlModel );
		} );

		let sectionToActivate;

		if ( ! sectionControls[ 0 ] ) {
			self.activeSection = null;

			sectionToActivate = null;
		} else {
			sectionToActivate = sectionControls[ 0 ].get( 'name' );
		}

		var preActivatedSection = sectionControls.filter( function( controlModel ) {
			return self.activeSection === controlModel.get( 'name' );
		} );

		if ( preActivatedSection[ 0 ] ) {
			return;
		}

		self.activateSection( sectionToActivate );

		return this;
	},

	getChildView: function( item ) {
		var controlType = item.get( 'type' );

		return elementor.getControlView( controlType );
	},

	getNamespaceArray: function() {
		return [ elementor.getPanelView().getCurrentPageName() ];
	},

	openActiveSection: function() {
		var activeSection = this.activeSection,
			activeSectionView = this.children.filter( function( view ) {
				return activeSection === view.model.get( 'name' );
			} );

		if ( this.isFiltered ) {
			this.children.forEach( function( view ) {
				if ( 'section' === view.model.get( 'type' ) ) {
					view.$el.addClass( 'elementor-open' );
				}
			} );

			this.isFiltered = false;
		} else if ( activeSectionView[ 0 ] ) {
			activeSectionView[ 0 ].$el.addClass( 'elementor-open' );

			const eventNamespace = this.getNamespaceArray();

			eventNamespace.push( activeSection, 'activated' );

			elementor.channels.editor.trigger( eventNamespace.join( ':' ), this );
		}
	},

	onRenderCollection: function() {
		this.openActiveSection();

		ControlsStack.handlePopovers( this );
	},

	onModelDestroy: function() {
		this.destroy();
	},

	onReloadButtonClick: function() {
		elementor.reloadPreview();
	},

	onSearchControlsKeyUp: function() {
		// Debounce the render to improve performance.
		clearTimeout( this.keyUpTimeout );

		this.keyUpTimeout = setTimeout( () => {
			this._renderChildren();
		}, 100 );
	},

	onDeviceModeChange: function() {
		if ( 'desktop' === elementor.channels.deviceMode.request( 'currentMode' ) ) {
			this.$el.removeClass( 'elementor-responsive-switchers-open' );
		}
	},

	onChildviewControlSectionClicked: function( childView ) {
		var isSectionOpen = childView.$el.hasClass( 'elementor-open' );

		this.activateSection( isSectionOpen ? null : childView.model.get( 'name' ) );

		this._renderChildren();
	},

	onChildviewResponsiveSwitcherClick: function( childView, device ) {
		if ( 'desktop' === device ) {
			this.$el.toggleClass( 'elementor-responsive-switchers-open' );
		}
	},
}, {
	handlePopovers: function( view ) {
		let popover;

		view.popovers = [];

		this.removePopovers( view );

		view.children.each( ( control ) => {
			if ( popover ) {
				popover.addChild( control );
			}

			const popoverData = control.model.get( 'popover' );

			if ( ! popoverData ) {
				return;
			}

			if ( popoverData.start ) {
				popover = new ControlsPopover( control );

				view.popovers.push( popover );
			}

			if ( popoverData.end ) {
				popover = null;
			}
		} );
	},
	removePopovers: function( view ) {
		view.popovers.forEach( ( popover ) => popover.destroy() );
	},
} );

module.exports = ControlsStack;
