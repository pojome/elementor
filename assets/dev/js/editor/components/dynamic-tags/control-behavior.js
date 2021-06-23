var TagPanelView = require( 'elementor-dynamic-tags/tag-panel-view' );

module.exports = Marionette.Behavior.extend( {

	tagView: null,

	listenerAttached: false,

	initialize: function() {
		if ( ! this.listenerAttached ) {
			this.listenTo( this.view.options.container.settings, 'change:external:__dynamic__', this.onAfterExternalChange );
			this.listenerAttached = true;
		}
	},

	renderTools: function() {
		// If the user has Elementor Pro and the current control has no dynamic tags available, don't generate the dynamic switcher.
		// If the user has the core version only, we do display the dynamic switcher for the promotion.
		if ( this.getOption( 'dynamicSettings' ).default || ( elementor.helpers.hasPro() && ! this.getOption( 'tags' ).length ) ) {
			return;
		}

		const $dynamicSwitcher = jQuery( Marionette.Renderer.render( '#tmpl-elementor-control-dynamic-switcher' ) );

		$dynamicSwitcher.on( 'click', ( event ) => this.onDynamicSwitcherClick( event ) );

		this.$el.find( '.elementor-control-dynamic-switcher-wrapper' ).append( $dynamicSwitcher );

		this.ui.dynamicSwitcher = $dynamicSwitcher;

		if ( 'color' === this.view.model.get( 'type' ) ) {
			if ( this.view.colorPicker ) {
				this.moveDynamicSwitcherToColorPicker();
			} else {
				setTimeout( () => this.moveDynamicSwitcherToColorPicker() );
			}
		}

		// Add a Tipsy Tooltip to the Dynamic Switcher
		this.ui.dynamicSwitcher.tipsy( {
			title() {
				return this.getAttribute( 'data-tooltip' );
			},
			gravity: 's',
		} );
	},

	moveDynamicSwitcherToColorPicker: function() {
		const $colorPickerToolsContainer = this.view.colorPicker.$pickerToolsContainer;

		this.ui.dynamicSwitcher.removeClass( 'elementor-control-unit-1' ).addClass( 'e-control-tool' );

		const $eyedropper = $colorPickerToolsContainer.find( '.elementor-control-element-color-picker' );

		if ( $eyedropper.length ) {
			this.ui.dynamicSwitcher.insertBefore( $eyedropper );
		} else {
			$colorPickerToolsContainer.append( this.ui.dynamicSwitcher );
		}
	},

	toggleDynamicClass: function() {
		this.$el.toggleClass( 'elementor-control-dynamic-value', this.isDynamicMode() );
	},

	isDynamicMode: function() {
		var dynamicSettings = this.view.container.settings.get( '__dynamic__' );

		return ! ! ( dynamicSettings && dynamicSettings[ this.view.model.get( 'name' ) ] );
	},

	createTagsList: function() {
		var tags = _.groupBy( this.getOption( 'tags' ), 'group' ),
			groups = elementor.dynamicTags.getConfig( 'groups' ),
			$tagsList = this.ui.tagsList = jQuery( '<div>', { class: 'elementor-tags-list' } ),
			$tagsListInner = jQuery( '<div>', { class: 'elementor-tags-list__inner' } );

		$tagsList.append( $tagsListInner );

		jQuery.each( groups, function( groupName ) {
			var groupTags = tags[ groupName ];

			if ( ! groupTags ) {
				return;
			}

			var group = this,
				$groupTitle = jQuery( '<div>', { class: 'elementor-tags-list__group-title' } ).text( group.title );

			$tagsListInner.append( $groupTitle );

			groupTags.forEach( function( tag ) {
				var $tag = jQuery( '<div>', { class: 'elementor-tags-list__item' } );

				$tag.text( tag.title ).attr( 'data-tag-name', tag.name );

				$tagsListInner.append( $tag );
			} );
		} );

		// Create and inject pro dynamic teaser template if Pro is not installed
		if ( ! elementor.helpers.hasPro() && Object.keys( tags ).length ) {
			const proTeaser = Marionette.Renderer.render( '#tmpl-elementor-dynamic-tags-promo' );

			$tagsListInner.append( proTeaser );
		}

		$tagsListInner.on( 'click', '.elementor-tags-list__item', this.onTagsListItemClick.bind( this ) );

		elementorCommon.elements.$body.append( $tagsList );
	},

	getTagsList: function() {
		if ( ! this.ui.tagsList ) {
			this.createTagsList();
		}

		return this.ui.tagsList;
	},

	toggleTagsList: function() {
		var $tagsList = this.getTagsList();

		if ( $tagsList.is( ':visible' ) ) {
			$tagsList.hide();

			return;
		}

		const direction = elementorCommon.config.isRTL ? 'left' : 'right';

		$tagsList.show().position( {
			my: `${ direction } top`,
			at: `${ direction } bottom+5`,
			of: this.ui.dynamicSwitcher,
		} );
	},

	setTagView: function( id, name, settings ) {
		if ( this.tagView ) {
			this.tagView.destroy();
		}

		const tagView = this.tagView = new TagPanelView( {
			id: id,
			name: name,
			settings: settings,
			controlName: this.view.model.get( 'name' ),
			dynamicSettings: this.getOption( 'dynamicSettings' ),
		} ),
			elementContainer = this.view.options.container,
			tagViewLabel = elementContainer.controls[ tagView.options.controlName ].label;

		tagView.options.container = new elementorModules.editor.Container( {
			type: 'dynamic',
			id: id,
			model: tagView.model,
			settings: tagView.model,
			view: tagView,
			parent: elementContainer,
			label: elementContainer.label + ' ' + tagViewLabel,
			controls: tagView.model.options.controls,
			renderer: elementContainer,
		} );

		tagView.render();

		this.$el.find( '.elementor-control-tag-area' ).after( tagView.el );

		this.listenTo( tagView, 'remove', this.onTagViewRemove.bind( this ) );
	},

	setDefaultTagView: function() {
		var tagData = elementor.dynamicTags.tagTextToTagData( this.getDynamicValue() );

		this.setTagView( tagData.id, tagData.name, tagData.settings );
	},

	tagViewToTagText: function() {
		var tagView = this.tagView;

		return elementor.dynamicTags.tagDataToTagText( tagView.getOption( 'id' ), tagView.getOption( 'name' ), tagView.model );
	},

	getDynamicValue: function() {
		return this.view.container.dynamic.get( this.view.model.get( 'name' ) );
	},

	destroyTagView: function() {
		if ( this.tagView ) {
			this.tagView.destroy();

			this.tagView = null;
		}
	},

	showPromotion: function() {
		const message = __( 'Create more personalized and dynamic sites by populating data from various sources with dozens of dynamic tags to choose from.', 'elementor' );

		elementor.promotion.showDialog( {
			headerMessage: __( 'Dynamic Content', 'elementor' ),
			message: message,
			top: '-10',
			element: this.ui.dynamicSwitcher,
			actionURL: elementor.config.dynamicPromotionURL,
		} );
	},

	onRender: function() {
		this.$el.addClass( 'elementor-control-dynamic' );

		this.renderTools();

		this.toggleDynamicClass();

		if ( this.isDynamicMode() ) {
			this.setDefaultTagView();
		}
	},

	onDynamicSwitcherClick: function( event ) {
		event.stopPropagation();

		if ( this.getOption( 'tags' ).length ) {
			this.toggleTagsList();
		} else {
			this.showPromotion();
		}
	},

	onTagsListItemClick: function( event ) {
		const $tag = jQuery( event.currentTarget );

		this.setTagView( elementorCommon.helpers.getUniqueId(), $tag.data( 'tagName' ), {} );

		// If an element has an active global value, disable it before applying the dynamic value.
		if ( this.view.getGlobalKey() ) {
			this.view.triggerMethod( 'unset:global:value' );
		}

		if ( this.isDynamicMode() ) {
			$e.run( 'document/dynamic/settings', {
				container: this.view.options.container,
				settings: {
					[ this.view.model.get( 'name' ) ]: this.tagViewToTagText(),
				},
			} );
		} else {
			$e.run( 'document/dynamic/enable', {
				container: this.view.options.container,
				settings: {
					[ this.view.model.get( 'name' ) ]: this.tagViewToTagText(),
				},
			} );
		}

		this.toggleDynamicClass();
		this.toggleTagsList();

		if ( this.tagView.getTagConfig().settings_required ) {
			this.tagView.showSettingsPopup();
		}
	},

	onTagViewRemove: function() {
		$e.run( 'document/dynamic/disable', {
			container: this.view.options.container,
			settings: {
				// Set value for `undo` command.
				[ this.view.model.get( 'name' ) ]: this.tagViewToTagText(),
			},
		} );

		this.toggleDynamicClass();
	},

	onAfterExternalChange: function() {
		this.destroyTagView();

		if ( this.isDynamicMode() ) {
			this.setDefaultTagView();
		}

		this.toggleDynamicClass();
	},

	onDestroy: function() {
		this.destroyTagView();

		if ( this.ui.tagsList ) {
			this.ui.tagsList.remove();
		}
	},
} );
