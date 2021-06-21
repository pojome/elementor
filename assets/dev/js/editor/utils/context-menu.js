module.exports = elementorModules.Module.extend( {

	getDefaultSettings: function() {
		return {
			actions: {},
			classes: {
				list: 'elementor-context-menu-list',
				group: 'elementor-context-menu-list__group',
				groupPrefix: 'elementor-context-menu-list__group-',
				item: 'elementor-context-menu-list__item',
				itemTypePrefix: 'elementor-context-menu-list__item-',
				itemTitle: 'elementor-context-menu-list__item__title',
				itemShortcut: 'elementor-context-menu-list__item__shortcut',
				iconShortcut: 'elementor-context-menu-list__item__icon',
				itemDisabled: 'elementor-context-menu-list__item--disabled',
				divider: 'elementor-context-menu-list__divider',
				hidden: 'elementor-hidden',
			},
		};
	},

	buildActionItem: function( action ) {
		var self = this,
			classes = self.getSettings( 'classes' ),
			$item = jQuery( '<div>', { class: classes.item + ' ' + classes.itemTypePrefix + action.name } ),
			$itemTitle = jQuery( '<div>', { class: classes.itemTitle } ).text( action.title ),
			$itemIcon = jQuery( '<div>', { class: classes.iconShortcut } );

		if ( action.icon ) {
			$itemIcon.html( jQuery( '<i>', { class: action.icon } ) );
		}

		$item.append( $itemIcon, $itemTitle );

		if ( action.shortcut ) {
			var $itemShortcut = jQuery( '<div>', { class: classes.itemShortcut } ).html( action.shortcut );

			$item.append( $itemShortcut );
		}

		if ( action.callback ) {
			$item.on( 'click', function() {
				self.runAction( action );
			} );
		}

		action.$item = $item;

		return $item;
	},

	buildActionsList: function() {
		var self = this,
			classes = self.getSettings( 'classes' ),
			groups = self.getSettings( 'groups' ),
			$list = jQuery( '<div>', { class: classes.list } );

		groups.forEach( function( group ) {
			var $group = jQuery( '<div>', { class: classes.group + ' ' + classes.groupPrefix + group.name } );

			group.actions.forEach( function( action ) {
				$group.append( self.buildActionItem( action ) );
			} );

			$list.append( $group );

			group.$item = $group;
		} );

		return $list;
	},

	toggleGroupVisibility: function( group, state ) {
		group.$item.toggleClass( this.getSettings( 'classes.hidden' ), ! state );
	},

	toggleActionVisibility: function( action, state ) {
		action.$item.toggleClass( this.getSettings( 'classes.hidden' ), ! state );
	},

	toggleActionUsability: function( action, state ) {
		action.$item.toggleClass( this.getSettings( 'classes.itemDisabled' ), ! state );
	},

	isActionEnabled: function( action ) {
		if ( ! action.callback && ! action.groups ) {
			return false;
		}

		return action.isEnabled ? action.isEnabled() : true;
	},

	runAction: function( action ) {
		if ( ! this.isActionEnabled( action ) ) {
			return;
		}

		action.callback();

		this.getModal().hide();
	},

	initModal: function() {
		var modal;

		this.getModal = function() {
			if ( ! modal ) {
				modal = elementorCommon.dialogsManager.createWidget( 'simple', {
					className: 'elementor-context-menu',
					message: this.buildActionsList(),
					iframe: this.getSettings( 'outsideIframe' ) ? undefined : elementor.$preview,
					effects: {
						hide: 'hide',
						show: 'show',
					},
					hide: {
						onOutsideContextMenu: true,
					},
					position: {
						my: ( elementorCommon.config.isRTL ? 'right' : 'left' ) + ' top',
						collision: 'fit',
					},
				} );
			}

			return modal;
		};
	},

	show: function( event ) {
		var self = this,
			modal = self.getModal();

		modal.setSettings( 'position', {
			of: event,
		} );

		self.getSettings( 'groups' ).forEach( function( group ) {
			var isGroupVisible = false !== group.isVisible;

			self.toggleGroupVisibility( group, isGroupVisible );

			if ( isGroupVisible ) {
				group.actions.forEach( function( action ) {
					var isActionVisible = false !== action.isVisible;

					self.toggleActionVisibility( action, isActionVisible );

					if ( isActionVisible ) {
						self.toggleActionUsability( action, self.isActionEnabled( action ) );
					}
				} );
			}
		} );

		modal.show();
	},

	destroy: function() {
		this.getModal().destroy();
	},

	onInit: function() {
		this.initModal();
	},
} );
