/**
 * @memberOf elementorFrontend
 */
export default class Document extends elementorModules.ViewModule {
	getDefaultSettings() {
		return {
			selectors: {
				elements: '.elementor-element',
				nestedDocumentElements: '.elementor .elementor-element',
			},
			classes: {
				editMode: 'elementor-edit-mode',
			},
		};
	}

	getDefaultElements() {
		const selectors = this.getSettings( 'selectors' );

		return {
			$elements: this.$element.find( selectors.elements ).not( this.$element.find( selectors.nestedDocumentElements ) ),
		};
	}

	getDocumentSettings( setting ) {
		let elementSettings;

		if ( this.isEdit ) {
			elementSettings = {};

			const settings = elementor.settings.page.model;

			jQuery.each( settings.getActiveControls(), ( controlKey ) => {
				elementSettings[ controlKey ] = settings.attributes[ controlKey ];
			} );
		} else {
			elementSettings = this.$element.data( 'elementor-settings' ) || {};
		}

		return this.getItems( elementSettings, setting );
	}

	runElementsHandlers() {
		this.elements.$elements.each( ( index, element ) => elementorFrontend.elementsHandler.runReadyTrigger( element ) );
	}

	onInit() {
		this.$element = this.getSettings( '$element' );

		super.onInit();

		this.isEdit = this.$element.hasClass( this.getSettings( 'classes.editMode' ) );

		if ( this.isEdit ) {
			elementor.on( 'document:loaded', () => {
				elementor.settings.page.model.on( 'change', this.onSettingsChange.bind( this ) );
			} );
		} else {
			this.runElementsHandlers();
		}
	}

	onSettingsChange() {}
}
