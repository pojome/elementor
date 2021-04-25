import WidgetDraggable from './behaviors/widget-draggable';
import WidgetResizable from './behaviors/widget-resizeable';

var BaseElementView = require( 'elementor-elements/views/base' ),
	WidgetView;

WidgetView = BaseElementView.extend( {
	_templateType: null,

	toggleEditTools: true,

	getTemplate: function() {
		var editModel = this.getEditModel();

		if ( 'remote' !== this.getTemplateType() ) {
			return Marionette.TemplateCache.get( '#tmpl-elementor-' + editModel.get( 'widgetType' ) + '-content' );
		}
		return _.template( '' );
	},

	className: function() {
		var baseClasses = BaseElementView.prototype.className.apply( this, arguments );

		return baseClasses + ' elementor-widget ' + elementor.getElementData( this.getEditModel() ).html_wrapper_class;
	},

	events: function() {
		var events = BaseElementView.prototype.events.apply( this, arguments );

		events.click = 'onClickEdit';

		return events;
	},

	behaviors: function() {
		var behaviors = BaseElementView.prototype.behaviors.apply( this, arguments );

		_.extend( behaviors, {
			InlineEditing: {
				behaviorClass: require( 'elementor-behaviors/inline-editing' ),
				inlineEditingClass: 'elementor-inline-editing',
			},
			Draggable: {
				behaviorClass: WidgetDraggable,
			},
			Resizable: {
				behaviorClass: WidgetResizable,
			},
		} );

		return elementor.hooks.applyFilters( 'elements/widget/behaviors', behaviors, this );
	},

	getEditButtons: function() {
		const elementData = elementor.getElementData( this.model ),
			editTools = {};

		editTools.edit = {
			/* translators: %s: Element Name. */
			title: sprintf( __( 'Edit %s', 'elementor' ), elementData.title ),
			icon: 'edit',
		};

		if ( elementor.getPreferences( 'edit_buttons' ) ) {
			editTools.duplicate = {
				/* translators: %s: Element Name. */
				title: sprintf( __( 'Duplicate %s', 'elementor' ), elementData.title ),
				icon: 'clone',
			};

			editTools.remove = {
				/* translators: %s: Element Name. */
				title: sprintf( __( 'Delete %s', 'elementor' ), elementData.title ),
				icon: 'close',
			};
		}

		return editTools;
	},

	initialize: function() {
		BaseElementView.prototype.initialize.apply( this, arguments );

		var editModel = this.getEditModel();

		editModel.on( {
			'before:remote:render': this.onModelBeforeRemoteRender.bind( this ),
			'remote:render': this.onModelRemoteRender.bind( this ),
			'settings:loaded': () => setTimeout( this.render.bind( this ) ),
		} );

		if ( 'remote' === this.getTemplateType() && ! this.getEditModel().getHtmlCache() ) {
			editModel.renderRemoteServer();
		}

		var onRenderMethod = this.onRender;

		this.render = _.throttle( this.render, 300 );

		this.onRender = function() {
			_.defer( onRenderMethod.bind( this ) );
		};
	},

	getContextMenuGroups: function() {
		var groups = BaseElementView.prototype.getContextMenuGroups.apply( this, arguments ),
			transferGroupIndex = groups.indexOf( _.findWhere( groups, { name: 'clipboard' } ) );

		groups.splice( transferGroupIndex + 1, 0, {
			name: 'save',
			actions: [
				{
					name: 'save',
					title: __( 'Save as a Global', 'elementor' ),
					shortcut: jQuery( '<i>', { class: 'eicon-pro-icon' } ),
					isEnabled: () => 'global' !== this.options.model.get( 'widgetType' ),
				},
			],
		} );

		return groups;
	},

	render: function() {
		if ( this.model.isRemoteRequestActive() ) {
			this.handleEmptyWidget();

			this.$el.addClass( 'elementor-element' );

			return;
		}

		if ( elementorCommonConfig.isTesting && this.isDestroyed ) {
			return;
		}

		Marionette.CompositeView.prototype.render.apply( this, arguments );
	},

	handleEmptyWidget: function() {
		this.$el
			.addClass( 'elementor-widget-empty' )
			.append( '<i class="elementor-widget-empty-icon ' + this.getEditModel().getIcon() + '"></i>' );
	},

	getTemplateType: function() {
		if ( null === this._templateType ) {
			var editModel = this.getEditModel(),
				$template = jQuery( '#tmpl-elementor-' + editModel.get( 'widgetType' ) + '-content' );

			this._templateType = $template.length ? 'js' : 'remote';
		}

		return this._templateType;
	},

	getHTMLContent: function( html ) {
		var htmlCache = this.getEditModel().getHtmlCache();

		return htmlCache || html;
	},

	attachElContent: function( html ) {
		_.defer( () => {
			elementorFrontend.elements.window.jQuery( this.el ).empty().append( this.getHandlesOverlay(), this.getHTMLContent( html ) );

			this.bindUIElements(); // Build again the UI elements since the content attached just now
		} );

		return this;
	},

	addInlineEditingAttributes: function( key, toolbar ) {
		this.addRenderAttribute( key, {
			class: 'elementor-inline-editing',
			'data-elementor-setting-key': key,
		} );

		if ( toolbar ) {
			this.addRenderAttribute( key, {
				'data-elementor-inline-editing-toolbar': toolbar,
			} );
		}
	},

	getRepeaterSettingKey: function( settingKey, repeaterKey, repeaterItemIndex ) {
		return [ repeaterKey, repeaterItemIndex, settingKey ].join( '.' );
	},

	onModelBeforeRemoteRender: function() {
		this.$el.addClass( 'elementor-loading' );
	},

	onBeforeDestroy: function() {
		// Remove old style from the DOM.
		elementor.$previewContents.find( '#elementor-style-' + this.model.get( 'id' ) ).remove();
	},

	onModelRemoteRender: function() {
		if ( this.isDestroyed ) {
			return;
		}

		this.$el.removeClass( 'elementor-loading' );

		// If container document has been changed during the remote request, don't render.
		if ( this.getContainer().document.id !== elementor.documents.getCurrent().id ) {
			return;
		}

		this.render();
	},

	onRender: function() {
		var self = this;

		BaseElementView.prototype.onRender.apply( self, arguments );

		var editModel = self.getEditModel(),
			skinType = editModel.getSetting( '_skin' ) || 'default';

		self.$el
			.attr( 'data-widget_type', editModel.get( 'widgetType' ) + '.' + skinType )
			.removeClass( 'elementor-widget-empty' )
			.children( '.elementor-widget-empty-icon' )
			.remove();

		// TODO: Find a better way to detect if all the images have been loaded
		self.$el.imagesLoaded().always( function() {
			setTimeout( function() {
				if ( ! self.$el.children( '.elementor-widget-container' ).outerHeight() ) {
					self.handleEmptyWidget();
				}
			}, 200 );
			// Is element empty?
		} );

		const $dropArea = self.$el.find( '.elementor-drop-area' );

		if ( $dropArea.length ) {
			$dropArea.html5Droppable( {
				axis: [ 'vertical' ],
				isDroppingAllowed: true,
				currentElementClass: 'elementor-html5dnd-current-element',
				placeholderClass: 'elementor-sortable-placeholder elementor-widget-placeholder',
				hasDraggingOnChildClass: 'elementor-dragging-on-child',
				onDropping: ( side, event ) => {
					event.stopPropagation();

					// TODO: Check with @kobizz if it's necessary.
					// Triggering drag end manually, since it won't fired above iframe.
					elementor.getPreviewView().onPanelElementDragEnd();

					const elementView = elementor.channels.panelElements.request( 'element:selected' );
					const widgetType = elementView.model.get( 'widgetType' );
					const widgetTitle = elementView.model.get( 'title' );
					const targetId = this.getContainer().id;
					const tabId = event.currentTarget.dataset.tab;
					const repeaterId = event.currentTarget.dataset.repeaterId;
					const current = event.currentTarget;
					const parent = current.parentNode;

					const newElement = document.createElement( 'div' );
					newElement.innerText = widgetTitle;

					switch ( side ) {
						case 'top':
							parent.insertBefore( newElement, current );
							break;

						case 'bottom':
							if ( parent.lastChild.isSameNode( current ) ) {
								// If `current` the last child, append the new one in the end.
								parent.appendChild( newElement );
							} else {
								// If it's not the last child, append the new one after it.
								parent.insertBefore( newElement, current.nextSibling );
							}
							break;
					}

					const newIndex = Array.from( parent.children ).indexOf( newElement );

					console.log( `Dropped: ${ widgetType }` );
					console.log( `Index: ${ newIndex }` );
					console.log( `Target: ${ targetId }` );
					console.log( `Inner Element: ${ tabId } :: ${ repeaterId }` );
					console.log( `Side: ${ side }` );
				},
			} );
		}
	},

	onClickEdit: function() {
		if ( this.container.isEditable() ) {
			this.model.trigger( 'request:edit' );
		}
	},
} );

module.exports = WidgetView;
