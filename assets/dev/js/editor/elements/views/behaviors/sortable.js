var SortableBehavior;

SortableBehavior = Marionette.Behavior.extend( {
	defaults: {
		elChildType: 'widget',
	},

	events: {
		sortstart: 'onSortStart',
		sortreceive: 'onSortReceive',
		sortupdate: 'onSortUpdate',
		sortover: 'onSortOver',
		sortout: 'onSortOut',
	},

	initialize: function() {
		this.listenTo( elementor.channels.dataEditMode, 'switch', this.onEditModeSwitched )
			.listenTo( this.view.options.model, 'request:sort:start', this.startSort )
			.listenTo( this.view.options.model, 'request:sort:update', this.updateSort )
			.listenTo( this.view.options.model, 'request:sort:receive', this.receiveSort );
	},

	onEditModeSwitched: function( activeMode ) {
		if ( 'edit' === activeMode ) {
			this.activate();
		} else {
			this.deactivate();
		}
	},

	onRender: function() {
		var self = this;

		_.defer( function() {
			self.onEditModeSwitched( elementor.channels.dataEditMode.request( 'activeMode' ) );
		} );
	},

	onDestroy: function() {
		this.deactivate();
	},

	/**
	 * Create an item placeholder in order to avoid UI jumps due to flex.
	 *
	 * @param {Object} $element - jQuery element instance to create placeholder for.
	 * @param {string} className - Placeholder class.
	 * @param {boolean} hide - Whether to hide the original element.
	 *
	 * @returns {void}
	 */
	createPlaceholder: function( $element, className = '', hide = true ) {
		// Get the actual item size.
		$element.css( 'display', '' );
		const { clientWidth: width, clientHeight: height } = $element[ 0 ];

		if ( hide ) {
			$element.css( 'display', 'none' );
		}

		jQuery( '<div />' ).css( {
			...$element.css( [
				'flex-basis',
				'flex-grow',
				'flex-shrink',
				'position',
			] ),
			width,
			height,
		} ).addClass( className ).insertAfter( $element );
	},

	/**
	 * Return a settings object for jQuery UI sortable to make it swappable.
	 *
	 * @returns {{stop: stop, start: start}}
	 */
	getSwappableOptions() {
		const $childViewContainer = this.getChildViewContainer(),
			placeholderClass = 'e-swappable--item-placeholder';

		return {
			start: ( event, ui ) => {
				$childViewContainer.sortable( 'refreshPositions' );

				// TODO: Find a better solution than this hack.
				// Used in order to prevent dragging a container into itself.
				this.createPlaceholder( ui.item, placeholderClass );
			},
			stop: () => {
				// Cleanup.
				$childViewContainer.find( `.${ placeholderClass }` ).remove();
			},
		};
	},

	activate: function() {
		if ( ! elementor.userCan( 'design' ) ) {
			return;
		}

		if ( this.getChildViewContainer().sortable( 'instance' ) ) {
			return;
		}

		var $childViewContainer = this.getChildViewContainer(),
			defaultSortableOptions = {
				placeholder: 'elementor-sortable-placeholder elementor-' + this.getOption( 'elChildType' ) + '-placeholder',
				cursorAt: {
					top: 20,
					left: 25,
				},
				helper: this._getSortableHelper.bind( this ),
				cancel: 'input, textarea, button, select, option, .elementor-inline-editing, .elementor-tab-title',
				// Fix: Sortable - Unable to drag and drop sections with huge height.
				start: () => {
					$childViewContainer.sortable( 'refreshPositions' );
				},
			},
			sortableOptions = _.extend( defaultSortableOptions, this.view.getSortableOptions() );

		// Add a swappable behavior (used for flex containers).
		if ( this.isSwappable() ) {
			$childViewContainer.addClass( 'e-swappable' );
			sortableOptions = _.extend( sortableOptions, this.getSwappableOptions() );
		}

		$childViewContainer.sortable( sortableOptions );
	},

	_getSortableHelper: function( event, $item ) {
		var model = this.view.collection.get( {
			cid: $item.data( 'model-cid' ),
		} );

		return '<div style="height: 84px; width: 125px;" class="elementor-sortable-helper elementor-sortable-helper-' + model.get( 'elType' ) + '"><div class="icon"><i class="' + model.getIcon() + '"></i></div><div class="elementor-element-title-wrapper"><div class="title">' + model.getTitle() + '</div></div></div>';
	},

	getChildViewContainer: function() {
		return this.view.getChildViewContainer( this.view );
	},

	// The natural widget index in the column is wrong, since there are other elements
	// at the beginning of the column (background-overlay, element-overlay, resizeable-handle)
	getSortedElementNewIndex( $element ) {
		const widgets = Object.values( $element.parent().find( '> .elementor-element' ) );

		return widgets.indexOf( $element[ 0 ] );
	},

	deactivate: function() {
		var childViewContainer = this.getChildViewContainer();

		if ( childViewContainer.sortable( 'instance' ) ) {
			childViewContainer.sortable( 'destroy' );
		}
	},

	/**
	 * Determine if the current instance of Sortable is swappable.
	 *
	 * @returns {boolean}
	 */
	isSwappable: function() {
		return ! ! this.view.getSortableOptions().swappable;
	},

	startSort: function( event, ui ) {
		event.stopPropagation();

		const container = elementor.getContainer( ui.item.attr( 'data-id' ) );

		elementor.channels.data
			.reply( 'dragging:model', container.model )
			.reply( 'dragging:view', container.view )
			.reply( 'dragging:parent:view', this.view )
			.trigger( 'drag:start', container.model )
			.trigger( container.model.get( 'elType' ) + ':drag:start' );
	},

	// On sorting element
	updateSort: function( ui, newIndex ) {
		if ( undefined === newIndex ) {
			newIndex = ui.item.index();
		}

		const child = elementor.channels.data.request( 'dragging:view' ).getContainer();
		this.moveChild( child, newIndex );
	},

	// On receiving element from another container
	receiveSort: function( event, ui, newIndex ) {
		event.stopPropagation();

		if ( this.view.isCollectionFilled() ) {
			jQuery( ui.sender ).sortable( 'cancel' );

			return;
		}

		var model = elementor.channels.data.request( 'dragging:model' ),
			draggedElType = model.get( 'elType' ),
			draggedIsInnerSection = 'section' === draggedElType && model.get( 'isInner' ),
			targetIsInnerColumn = 'column' === this.view.getElementType() && this.view.isInner();

		if ( draggedIsInnerSection && targetIsInnerColumn ) {
			jQuery( ui.sender ).sortable( 'cancel' );

			return;
		}

		if ( undefined === newIndex ) {
			newIndex = ui.item.index();
		}

		const child = elementor.channels.data.request( 'dragging:view' ).getContainer();

		this.moveChild( child, newIndex );
	},

	onSortStart: function( event, ui ) {
		if ( 'column' === this.options.elChildType ) {
			var uiData = ui.item.data( 'sortableItem' ),
				uiItems = uiData.items,
				itemHeight = 0;

			uiItems.forEach( function( item ) {
				if ( item.item[ 0 ] === ui.item[ 0 ] ) {
					itemHeight = item.height;
					return false;
				}
			} );

			ui.placeholder.height( itemHeight );
		}

		this.startSort( event, ui );
	},

	onSortOver: function( event ) {
		event.stopPropagation();

		var model = elementor.channels.data.request( 'dragging:model' );

		jQuery( event.target )
			.addClass( 'elementor-draggable-over' )
			.attr( {
				'data-dragged-element': model.get( 'elType' ),
				'data-dragged-is-inner': model.get( 'isInner' ),
			} );

		this.$el.addClass( 'elementor-dragging-on-child' );
	},

	onSortOut: function( event ) {
		event.stopPropagation();

		jQuery( event.target )
			.removeClass( 'elementor-draggable-over' )
			.removeAttr( 'data-dragged-element data-dragged-is-inner' );

		this.$el.removeClass( 'elementor-dragging-on-child' );
	},

	onSortReceive: function( event, ui ) {
		this.receiveSort( event, ui, this.getSortedElementNewIndex( ui.item ) );
	},

	onSortUpdate: function( event, ui ) {
		event.stopPropagation();

		if ( this.getChildViewContainer()[ 0 ] !== ui.item.parent()[ 0 ] ) {
			return;
		}

		this.updateSort( ui, this.getSortedElementNewIndex( ui.item ) );
	},

	onAddChild: function( view ) {
		view.$el.attr( 'data-model-cid', view.model.cid );
	},

	/**
	 * Move a child container to another position.
	 *
	 * @param {Container} child - The child container to move.
	 * @param {int|string} index - New index.
	 *
	 * @returns {void}
	 */
	moveChild: function( child, index ) {
		$e.run( 'document/elements/move', {
			container: child,
			target: this.view.getContainer(),
			options: {
				at: index,
			},
		} );
	},
} );

module.exports = SortableBehavior;
