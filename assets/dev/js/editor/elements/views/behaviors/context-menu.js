import AddSectionBase	from 'elementor-views/add-section/base';

var ContextMenu = require( 'elementor-editor-utils/context-menu' );

module.exports = Marionette.Behavior.extend( {

	defaults: {
		groups: [],
		eventTargets: [ 'el' ],
	},

	events: function() {
		const events = {};

		this.getOption( 'eventTargets' ).forEach( function( eventTarget ) {
			let eventName = 'contextmenu';

			if ( 'el' !== eventTarget ) {
				eventName += ' ' + eventTarget;
			}

			events[ eventName ] = 'onContextMenu';
		} );

		return events;
	},

	initialize: function() {
		this.listenTo( this.view.options.model, 'request:contextmenu', this.onRequestContextMenu );
	},

	initContextMenu: function() {
		var contextMenuGroups = this.getOption( 'groups' ),
			deleteGroup = _.findWhere( contextMenuGroups, { name: 'delete' } ),
			afterGroupIndex = contextMenuGroups.indexOf( deleteGroup );

		if ( -1 === afterGroupIndex ) {
			afterGroupIndex = contextMenuGroups.length;
		}

		if ( ! this.getOption( 'outsideIframe' ) ) {
			contextMenuGroups.splice( afterGroupIndex, 0, {
				name: 'tools',
				actions: [
					{
						name: 'navigator',
						title: __( 'Navigator', 'elementor' ),
						callback: () => $e.route( 'navigator', {
							reOpen: true,
							model: this.view.model,
						} ),
					},
				],
			} );
		}

		this.contextMenu = new ContextMenu( {
			groups: contextMenuGroups,
			outsideIframe: this.getOption( 'outsideIframe' ),
		} );

		this.contextMenu.getModal().on( 'hide', this.onContextMenuHide );
	},

	getContextMenu: function() {
		if ( ! this.contextMenu ) {
			this.initContextMenu();
		}

		return this.contextMenu;
	},

	onContextMenu: function( event ) {
		if ( $e.shortcuts.isControlEvent( event ) ) {
			return;
		}

		const isAddSectionView = this.view instanceof AddSectionBase;
		if ( ! isAddSectionView && ! this.getOption( 'outsideIframe' ) && ( ! this.view.container || ! this.view.container.isDesignable() ) ) {
				return;
		}

		event.preventDefault();

		event.stopPropagation();

		this.getContextMenu().show( event );

		elementor.channels.editor.reply( 'contextMenu:targetView', this.view );
	},

	onRequestContextMenu: function( event ) {
		var modal = this.getContextMenu().getModal(),
			iframe = modal.getSettings( 'iframe' ),
			toolsGroup = _.findWhere( this.contextMenu.getSettings( 'groups' ), { name: 'tools' } );

		toolsGroup.isVisible = false;

		modal.setSettings( 'iframe', null );

		this.onContextMenu( event );

		toolsGroup.isVisible = true;

		modal.setSettings( 'iframe', iframe );
	},

	onContextMenuHide: function() {
		elementor.channels.editor.reply( 'contextMenu:targetView', null );
	},

	onDestroy: function() {
		if ( this.contextMenu ) {
			this.contextMenu.destroy();
		}
	},
} );
