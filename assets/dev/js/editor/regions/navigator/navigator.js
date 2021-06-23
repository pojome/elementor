import Component from './component';

const BaseRegion = require( 'elementor-regions/base' );

import NavigatorLayout from './layout';

export default class extends BaseRegion {
	constructor( options ) {
		super( options );

		this.component = $e.components.register( new Component( { manager: this } ) );

		this.isDocked = false;
		this.setSize();

		this.indicators = {
			customPosition: {
				title: __( 'Custom Positioning', 'elementor' ),
				icon: 'cursor-move',
				settingKeys: [ '_position', '_element_width' ],
				section: '_section_position',
			},
		};

		this.ensurePosition = this.ensurePosition.bind( this );

		this.listenTo( elementor.channels.dataEditMode, 'switch', this.onEditModeSwitched );

		// TODO: Move to hook on 'editor/documents/load'.
		elementor.on( 'document:loaded', this.onDocumentLoaded.bind( this ) );
		elementor.on( 'document:unloaded', this.onDocumentUnloaded.bind( this ) );
	}

	getStorageKey() {
		return 'navigator';
	}

	getDefaultStorage() {
		return {
			visible: false,
			size: {
				width: '',
				height: '',
				top: '',
				bottom: '',
				right: '',
				left: '',
			},
		};
	}

	getLayout() {
		return this.currentView;
	}

	getDraggableOptions() {
		return {
			iframeFix: true,
			handle: '#elementor-navigator__header',
			drag: this.onDrag.bind( this ),
			stop: this.onDragStop.bind( this ),
		};
	}

	getResizableOptions() {
		return {
			handles: 'all',
			containment: 'document',
			minWidth: 150,
			maxWidth: 500,
			minHeight: 240,
			start: () => {
				elementor.$previewWrapper.addClass( 'ui-resizable-resizing' );
			},
			stop: () => {
				elementor.$previewWrapper.removeClass( 'ui-resizable-resizing' );

				if ( this.isDocked ) {
					this.storage.size.width = elementor.helpers.getElementInlineStyle( this.$el, [ 'width' ] ).width;

					elementorCommon.storage.set( 'navigator', this.storage );
				} else {
					this.saveSize();
				}
			},
			resize: ( event, ui ) => {
				this.setSize( ui.size.width + 'px' );
			},
		};
	}

	initLayout() {
		this.show( new NavigatorLayout() );

		this.$el.draggable( this.getDraggableOptions() );
		this.$el.resizable( this.getResizableOptions() );
	}

	open( model ) {
		this.$el.show();

		this.setSize();

		if ( this.storage.docked ) {
			this.dock();
		}

		if ( model ) {
			model.trigger( 'request:edit' );
		}

		this.saveStorage( 'visible', true );

		this.ensurePosition();

		elementorCommon.elements.$window.on( 'resize', this.ensurePosition );
	}

	close( silent ) {
		this.$el.hide();

		if ( this.isDocked ) {
			this.undock( true );
		}

		if ( ! silent ) {
			this.saveStorage( 'visible', false );
		}

		if ( this.$el.resizable( 'instance' ) ) {
			this.$el.resizable( 'destroy' );
		}

		elementorCommon.elements.$window.off( 'resize', this.ensurePosition );
	}

	isOpen() {
		return this.$el.is( ':visible' );
	}

	dock() {
		elementorCommon.elements.$body.addClass( 'elementor-navigator-docked' );
		this.setSize();

		const resizableOptions = this.getResizableOptions();

		this.$el.css( {
			height: '',
			top: '',
			bottom: '',
			left: '',
			right: '',
		} );

		if ( this.$el.resizable( 'instance' ) ) {
			this.$el.resizable( 'destroy' );
		}

		resizableOptions.handles = elementorCommon.config.isRTL ? 'e' : 'w';

		this.$el.resizable( resizableOptions );

		this.isDocked = true;

		this.saveStorage( 'docked', true );
	}

	undock( silent ) {
		elementorCommon.elements.$body.removeClass( 'elementor-navigator-docked' );
		this.setSize();

		elementor.$previewWrapper.css( elementorCommon.config.isRTL ? 'left' : 'right', '' );

		if ( this.$el.resizable( 'instance' ) ) {
			this.$el.resizable( 'destroy' );

			this.$el.resizable( this.getResizableOptions() );
		}

		this.isDocked = false;

		if ( ! silent ) {
			this.saveStorage( 'docked', false );
		}
	}

	/**
	 * Set the navigator size to a specific value or default to the storage-saved value.
	 *
	 * @param {String} size A specific new size.
	 */
	setSize( size = null ) {
		if ( size ) {
			this.storage.size.width = size;
		} else {
			this.storage.size.width = this.storage.size.width || elementorCommon.elements.$body.css( '--e-editor-navigator-width' );
		}

		// Set the navigator size using a CSS variable, and remove the inline CSS that was set by jQuery Resizeable.
		elementorCommon.elements.$body.css( '--e-editor-navigator-width', this.storage.size.width );
		this.$el.css( 'width', '' );
	}

	ensurePosition() {
		if ( this.isDocked ) {
			return;
		}

		const offset = this.$el.offset();

		if ( offset.left > innerWidth ) {
			this.$el.css( {
				left: '',
				right: '',
			} );
		}

		if ( offset.top > innerHeight ) {
			this.$el.css( {
				top: '',
				bottom: '',
			} );
		}
	}

	onDrag( event, ui ) {
		if ( this.isDocked ) {
			if ( ui.position.left === ui.originalPosition.left ) {
				if ( ui.position.top !== ui.originalPosition.top ) {
					return false;
				}
			} else {
				this.undock();
			}

			return;
		}

		if ( 0 > ui.position.top ) {
			ui.position.top = 0;
		}

		const isOutOfLeft = 0 > ui.position.left,
			isOutOfRight = ui.position.left + this.el.offsetWidth > innerWidth;

		if ( elementorCommon.config.isRTL ) {
			if ( isOutOfRight ) {
				ui.position.left = innerWidth - this.el.offsetWidth;
			}
		} else if ( isOutOfLeft ) {
			ui.position.left = 0;
		}

		elementorCommon.elements.$body.toggleClass( 'elementor-navigator--dock-hint', elementorCommon.config.isRTL ? isOutOfLeft : isOutOfRight );
	}

	onDragStop( event, ui ) {
		if ( this.isDocked ) {
			return;
		}

		this.saveSize();

		const elementRight = ui.position.left + this.el.offsetWidth;

		if ( 0 > ui.position.left || elementRight > innerWidth ) {
			this.dock();
		}

		elementorCommon.elements.$body.removeClass( 'elementor-navigator--dock-hint' );
	}

	onEditModeSwitched( activeMode ) {
		// Determine when the navigator should be visible.
		const visibleModes = [
			'edit',
			'picker',
		];

		if ( visibleModes.includes( activeMode ) && this.storage.visible ) {
			this.open();
		} else {
			this.close( true );
		}
	}

	onDocumentLoaded( document ) {
		if ( document.config.panel.has_elements ) {
			this.initLayout();

			if ( this.storage.visible ) {
				$e.route( 'navigator' );
			}
		}
	}

	onDocumentUnloaded() {
		if ( this.component.isOpen ) {
			this.component.close( true );
		}
	}
}
