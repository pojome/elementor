export default class baseTabs extends elementorModules.frontend.handlers.Base {
	getDefaultSettings() {
		return {
			selectors: {
				tabTitle: '.elementor-tab-title',
				tabContent: '.elementor-tab-content',
			},
			classes: {
				active: 'elementor-active',
			},
			showTabFn: 'show',
			hideTabFn: 'hide',
			toggleSelf: true,
			hidePrevious: true,
			autoExpand: true,
		};
	}

	getDefaultElements() {
		const selectors = this.getSettings( 'selectors' );

		return {
			$tabTitles: this.findElement( selectors.tabTitle ),
			$tabContents: this.findElement( selectors.tabContent ),
		};
	}

	activateDefaultTab() {
		const settings = this.getSettings();

		if ( ! settings.autoExpand || ( 'editor' === settings.autoExpand && ! this.isEdit ) ) {
			return;
		}

		const defaultActiveTab = this.getEditSettings( 'activeItemIndex' ) || 1,
			originalToggleMethods = {
				showTabFn: settings.showTabFn,
				hideTabFn: settings.hideTabFn,
			};

		// Toggle tabs without animation to avoid jumping
		this.setSettings( {
			showTabFn: 'show',
			hideTabFn: 'hide',
		} );

		this.changeActiveTab( defaultActiveTab );

		// Return back original toggle effects
		this.setSettings( originalToggleMethods );
	}

	deactivateActiveTab( tabIndex ) {
		const settings = this.getSettings(),
			activeClass = settings.classes.active,
			activeFilter = tabIndex ? '[data-tab="' + tabIndex + '"]' : '.' + activeClass,
			$activeTitle = this.elements.$tabTitles.filter( activeFilter ),
			$activeContent = this.elements.$tabContents.filter( activeFilter );

		// aria-selected should be applied only on the title and not on the content.
		$activeTitle.attr( 'aria-selected', 'false' ).add( $activeContent ).removeClass( activeClass );

		$activeContent[ settings.hideTabFn ]();
	}

	activateTab( tabIndex ) {
		const settings = this.getSettings(),
			activeClass = settings.classes.active,
			$requestedTitle = this.elements.$tabTitles.filter( '[data-tab="' + tabIndex + '"]' ),
			$requestedContent = this.elements.$tabContents.filter( '[data-tab="' + tabIndex + '"]' ),
			animationDuration = 'show' === settings.showTabFn ? 0 : 400;

		// aria-selected should be applied only on the title and not on the content.
		$requestedTitle.attr( 'aria-selected', 'true' ).add( $requestedContent ).addClass( activeClass );

		$requestedContent[ settings.showTabFn ]( animationDuration, () => elementorFrontend.elements.$window.trigger( 'resize' ) );
	}

	isActiveTab( tabIndex ) {
		return this.elements.$tabTitles.filter( '[data-tab="' + tabIndex + '"]' ).hasClass( this.getSettings( 'classes.active' ) );
	}

	bindEvents() {
		this.elements.$tabTitles.on( {
			keydown: ( event ) => {
				if ( 'Enter' === event.key ) {
					event.preventDefault();

					this.changeActiveTab( event.currentTarget.getAttribute( 'data-tab' ) );
				}
			},
			click: ( event ) => {
				event.preventDefault();

				this.changeActiveTab( event.currentTarget.getAttribute( 'data-tab' ) );
			},
		} );
	}

	onInit( ...args ) {
		super.onInit( ...args );

		this.activateDefaultTab();
	}

	onEditSettingsChange( propertyName ) {
		if ( 'activeItemIndex' === propertyName ) {
			this.activateDefaultTab();
		}
	}

	changeActiveTab( tabIndex ) {
		const isActiveTab = this.isActiveTab( tabIndex ),
			settings = this.getSettings();

		if ( ( settings.toggleSelf || ! isActiveTab ) && settings.hidePrevious ) {
			this.deactivateActiveTab();
		}

		if ( ! settings.hidePrevious && isActiveTab ) {
			this.deactivateActiveTab( tabIndex );
		}

		if ( ! isActiveTab ) {
			this.activateTab( tabIndex );
		}
	}
}
