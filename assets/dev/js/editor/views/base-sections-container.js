var BaseContainer = require( 'elementor-views/base-container' ),
	BaseSectionsContainerView;

BaseSectionsContainerView = BaseContainer.extend( {
	getChildView( model ) {
		let ChildView;
		const elType = model.get( 'elType' );

		switch ( elType ) {
			case 'section':
				ChildView = require( 'elementor-elements/views/section' );
				break;

			case 'container':
				ChildView = require( 'elementor-elements/views/container' );
				break;
		}

		return ChildView;
	},

	behaviors: function() {
		var behaviors = {
			Sortable: {
				behaviorClass: require( 'elementor-behaviors/sortable' ),
				elChildType: 'section',
			},
		};

		return elementor.hooks.applyFilters( 'elements/base-section-container/behaviors', behaviors, this );
	},

	getSortableOptions: function() {
		return {
			handle: '> .elementor-element-overlay .elementor-editor-element-edit',
			items: '> .elementor-section, > .e-container',
		};
	},

	getChildType: function() {
		return [ 'section', 'container' ];
	},

	initialize: function() {
		BaseContainer.prototype.initialize.apply( this, arguments );

		this.listenTo( elementor.channels.panelElements, 'element:drag:start', this.onPanelElementDragStart )
			.listenTo( elementor.channels.panelElements, 'element:drag:end', this.onPanelElementDragEnd );
	},

	onPanelElementDragStart: function() {
		// A temporary workaround in order to fix Chrome's 70+ dragging above nested iframe bug
		this.$el.find( '.elementor-background-video-embed' ).hide();

		elementor.helpers.disableElementEvents( this.$el.find( 'iframe' ) );
	},

	onPanelElementDragEnd: function() {
		this.$el.find( '.elementor-background-video-embed' ).show();

		elementor.helpers.enableElementEvents( this.$el.find( 'iframe' ) );
	},
} );

module.exports = BaseSectionsContainerView;
