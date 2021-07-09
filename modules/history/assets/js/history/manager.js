import ItemModel from './item-model';

/**
 * TODO: consider refactor this class.
 * TODO: should be `Document/History` component.
 * TODO: should be attached to elementor.history.history + BC.
 */
export default class HistoryManager {
	currentItemID = null;

	items = new Backbone.Collection( [], { model: ItemModel } );

	active = true;

	translations = {
		add: __( 'Added', 'elementor' ),
		change: __( 'Edited', 'elementor' ),
		disable: __( 'Disabled', 'elementor' ),
		duplicate: __( 'Duplicate', 'elementor' ),
		enable: __( 'Enabled', 'elementor' ),
		move: __( 'Moved', 'elementor' ),
		paste: __( 'Pasted', 'elementor' ),
		paste_style: __( 'Style Pasted', 'elementor' ),
		remove: __( 'Removed', 'elementor' ),
		reset_style: __( 'Style Reset', 'elementor' ),
		reset_settings: __( 'Settings Reset', 'elementor' ),
	};

	constructor( document ) {
		this.document = document;

		this.currentItem = new Backbone.Model( {
			id: 0,
		} );
	}

	getActionLabel( itemData ) {
		// TODO: this function should be static.
		if ( this.translations[ itemData.type ] ) {
			return this.translations[ itemData.type ];
		}

		return itemData.type;
	}

	navigate( isRedo ) {
		const currentItem = this.items.find( ( model ) => {
				return 'not_applied' === model.get( 'status' );
			} ),
			currentItemIndex = this.items.indexOf( currentItem ),
			requiredIndex = isRedo ? currentItemIndex - 1 : currentItemIndex + 1;

		if ( ( ! isRedo && ! currentItem ) || requiredIndex < 0 || requiredIndex >= this.items.length ) {
			return;
		}

		this.doItem( requiredIndex );
	}

	setActive( value ) {
		this.active = value;
	}

	getActive() {
		return this.active;
	}

	getItems() {
		return this.items;
	}

	startItem( itemData ) {
		this.currentItemID = this.addItem( itemData );

		return this.currentItemID;
	}

	endItem( id ) {
		if ( this.currentItemID !== id ) {
			return;
		}

		this.currentItemID = null;
	}

	deleteItem( id ) {
		const item = this.items.findWhere( {
			id: id,
		} );

		this.items.remove( item );

		this.currentItemID = null;
	}

	isItemStarted() {
		return null !== this.currentItemID;
	}

	getCurrentId() {
		return this.currentItemID;
	}

	addItem( itemData ) {
		if ( ! this.getActive() ) {
			return;
		}

		if ( ! this.items.length ) {
			this.items.add( {
				status: 'not_applied',
				title: __( 'Editing Started', 'elementor' ),
				subTitle: '',
				action: '',
				editing_started: true,
			} );
		}

		// Remove old applied items from top of list
		while ( this.items.length && 'applied' === this.items.first().get( 'status' ) ) {
			this.items.shift();
		}

		const id = this.currentItemID ? this.currentItemID : new Date().getTime();

		let currentItem = this.items.findWhere( {
			id: id,
		} );

		if ( ! currentItem ) {
			currentItem = new ItemModel( {
				id: id,
				title: itemData.title,
				subTitle: itemData.subTitle,
				action: this.getActionLabel( itemData ),
				type: itemData.type,
			} );

			this.startItemTitle = '';
			this.startItemAction = '';
		}

		currentItem.get( 'items' ).add( itemData, { at: 0 } );

		this.items.add( currentItem, { at: 0 } );

		this.updateCurrentItem( currentItem );

		return id;
	}

	doItem( index ) {
		// Don't track while restoring the item
		this.setActive( false );

		const item = this.items.at( index );

		if ( 'not_applied' === item.get( 'status' ) ) {
			this.undoItem( index );
		} else {
			this.redoItem( index );
		}

		this.setActive( true );

		const panel = elementor.getPanelView(),
			panelPage = panel.getCurrentPageView(),
			editedElementView = panelPage.getOption( 'editedElementView' );

		let viewToScroll;

		if ( $e.routes.isPartOf( 'panel/editor' ) && editedElementView ) {
			if ( editedElementView.isDestroyed ) {
				// If the the element isn't exist - show the history panel
				$e.route( 'panel/history/actions' );
			} else {
				// If element exist - render again, maybe the settings has been changed
				viewToScroll = editedElementView;
			}
		} else if ( item instanceof Backbone.Model && item.get( 'items' ).length ) {
			const historyItem = item.get( 'items' ).first();

			if ( historyItem.get( 'restore' ) ) {
				let container = 'sub-add' === historyItem.get( 'type' ) ?
					historyItem.get( 'data' ).containerToRestore :
					historyItem.get( 'container' ) || historyItem.get( 'containers' );

				if ( Array.isArray( container ) ) {
					container = container[ 0 ];
				}

				if ( container ) {
					viewToScroll = container.lookup().view;
				}
			}
		}

		$e.internal( 'document/save/set-is-modified', {
			status: item.get( 'id' ) !== this.document.editor.lastSaveHistoryId,
		} );

		this.updateCurrentItem( item );

		if ( viewToScroll && ! elementor.helpers.isInViewport( viewToScroll.$el[ 0 ], elementor.$previewContents.find( 'html' )[ 0 ] ) ) {
			elementor.helpers.scrollToView( viewToScroll.$el );
		}
	}

	undoItem( index ) {
		for ( let stepNum = 0; stepNum < index; stepNum++ ) {
			const item = this.items.at( stepNum );

			if ( 'not_applied' === item.get( 'status' ) ) {
				item.get( 'items' ).each( function( subItem ) {
					const restore = subItem.get( 'restore' );

					if ( restore ) {
						restore( subItem );
					}
				} );

				item.set( 'status', 'applied' );
			}
		}
	}

	redoItem( index ) {
		for ( let stepNum = this.items.length - 1; stepNum >= index; stepNum-- ) {
			const item = this.items.at( stepNum );

			if ( 'applied' === item.get( 'status' ) ) {
				var reversedSubItems = _.toArray( item.get( 'items' ).models ).reverse();

				_( reversedSubItems ).each( function( subItem ) {
					const restore = subItem.get( 'restore' );

					if ( restore ) {
						restore( subItem, true );
					}
				} );

				item.set( 'status', 'not_applied' );
			}
		}
	}

	updateCurrentItem( item ) {
		// Save last selected item.
		this.currentItem = item;

		this.updatePanelPageCurrentItem();
	}

	updatePanelPageCurrentItem() {
		if ( $e.routes.is( 'panel/history/actions' ) ) {
			elementor.getPanelView().getCurrentPageView().getCurrentTab().updateCurrentItem();
		}
	}
}
