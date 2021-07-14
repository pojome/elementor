export default class extends Marionette.Behavior {
	events() {
		return {
			resizestart: 'onResizeStart',
			resizestop: 'onResizeStop',
			resize: 'onResize',
		};
	}

	initialize() {
		super.initialize();

		this.listenTo( elementor.channels.dataEditMode, 'switch', this.toggle );

		// Save this instance for external use eg: ( hooks ).
		this.view.options.resizeable = this;
	}

	activate() {
		this.$el.resizable( {
			handles: 'e, w',
		} );
	}

	deactivate() {
		if ( ! this.$el.resizable( 'instance' ) ) {
			return;
		}

		this.$el.resizable( 'destroy' );
	}

	toggle() {
		const editModel = this.view.getEditModel(),
			isAbsolute = editModel.getSetting( '_position' ),
			isInline = 'initial' === editModel.getSetting( '_element_width' ),
			isRowContainer = this.isContainerItem() && [ 'row', 'row-reverse', '' ].includes( this.getParentFlexDirection() ),
			isRegularItem = ! this.isContainerItem(),
			shouldHaveHandles = isRowContainer || isRegularItem;

		this.deactivate();

		if ( ! shouldHaveHandles ) {
			return;
		}

		if ( ( ( isAbsolute || isInline ) && this.view.container.isDesignable() ) || this.isContainerItem() ) {
			this.activate();
		}
	}

	/**
	 * Determine if the current element is a flex container item.
	 *
	 * @returns {boolean}
	 */
	isContainerItem() {
		return 'container' === this.view.getContainer().parent?.model?.get( 'elType' );
	}

	/**
	 * Get the parent container flex direction.
	 *
	 * @returns {null|string}
	 */
	getParentFlexDirection() {
		if ( ! this.isContainerItem() ) {
			return null;
		}

		const currentDeviceMode = elementorFrontend.getCurrentDeviceMode(),
			deviceSuffix = 'desktop' === currentDeviceMode ? '' : '_' + currentDeviceMode;

		return this.view.getContainer().parent?.model?.getSetting( `container_flex_direction${ deviceSuffix }` );
	}

	onRender() {
		_.defer( () => this.toggle() );
	}

	onDestroy() {
		this.deactivate();
	}

	onResizeStart( event ) {
		event.stopPropagation();

		this.view.model.trigger( 'request:edit' );
	}

	onResizeStop( event, ui ) {
		event.stopPropagation();

		const currentDeviceMode = elementorFrontend.getCurrentDeviceMode(),
			deviceSuffix = 'desktop' === currentDeviceMode ? '' : '_' + currentDeviceMode,
			editModel = this.view.getEditModel(),
			widthKey = this.isContainerItem() ? '_flex_flex_basis' : '_element_custom_width',
			unit = editModel.getSetting( widthKey + deviceSuffix ).unit,
			width = elementor.helpers.elementSizeToUnit( this.$el, ui.size.width, unit ),
			settingToChange = {};

		settingToChange[ '_element_width' + deviceSuffix ] = this.isContainerItem() ? '' : 'initial';
		settingToChange[ widthKey + deviceSuffix ] = { unit, size: width };

		if ( this.isContainerItem() ) {
			settingToChange[ '_flex_flex_size' + deviceSuffix ] = 'custom';
			settingToChange[ '_flex_flex_shrink' + deviceSuffix ] = 0;
			settingToChange[ '_flex_flex_grow' + deviceSuffix ] = 0;
		}

		$e.run( 'document/elements/settings', {
			container: this.view.container,
			settings: settingToChange,
			options: {
				external: true,
			},
		} );

		this.$el.css( {
			width: '',
			height: '',
			left: '',
			flexBasis: '',
		} );
	}

	onResize( event, ui ) {
		event.stopPropagation();

		if ( ! this.isContainerItem() ) {
			return;
		}

		// Set grow & shrink to 0 in order to set a specific size and prevent UI glitches.
		this.$el.css( {
			left: '',
			right: '',
			'flex-shrink': 0,
			'flex-grow': 0,
			'flex-basis': ui.size.width + 'px',
		} );
	}
}
