var ContextMenu = require( 'elementor-editor-utils/context-menu' );

module.exports = Marionette.Behavior.extend( {

	defaults: {
		groups: [],
		eventTargets: [ 'el' ]
	},

	events: function() {
		var events = {};

		if ( ! elementor.userCan( 'design' ) ) {
			return events;
		}

		this.getOption( 'eventTargets' ).forEach( function( eventTarget ) {
			var eventName = 'contextmenu';

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
		var contextMenuGroups = this.getOption( 'groups' );

		contextMenuGroups.push( {
			name: 'tools',
			actions: [
				{
					name: 'navigator',
					title: elementor.translate( 'navigator' ),
					callback: elementor.navigator.open.bind( elementor.navigator, this.view.model )
				}
			]
		} );

		this.contextMenu = new ContextMenu( {
			groups: contextMenuGroups
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
		if ( elementor.hotKeys.isControlEvent( event ) ) {
			return;
		}

		var activeMode = elementor.channels.dataEditMode.request( 'activeMode' );

		if ( 'edit' !== activeMode ) {
			return;
		}

		event.preventDefault();

		event.stopPropagation();

		this.getContextMenu().show( event );

		elementor.channels.editor.reply( 'contextMenu:targetView', this.view );
	},

	onRequestContextMenu: function( event ) {
		var modal = this.getContextMenu().getModal(),
			iframe = modal.getSettings( 'iframe' );

		modal.setSettings( 'iframe', null );

		this.onContextMenu( event );

		modal.setSettings( 'iframe', iframe );
	},

	onContextMenuHide: function() {
		elementor.channels.editor.reply( 'contextMenu:targetView', null );
	},

	onDestroy: function() {
		if ( this.contextMenu ) {
			this.contextMenu.destroy();
		}
	}
} );
