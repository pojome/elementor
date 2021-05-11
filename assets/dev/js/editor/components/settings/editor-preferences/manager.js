import BaseManager from '../base/manager';

export default class extends BaseManager {
	getDefaultSettings() {
		return {
			darkModeLinkID: 'elementor-editor-dark-mode-css',
		};
	}

	constructor( ...args ) {
		super( ...args );

		this.changeCallbacks = {
			ui_theme: this.onUIThemeChanged,
			panel_width: this.onPanelWidthChanged,
			edit_buttons: this.onEditButtonsChanged,
			show_hidden_elements: this.OnShowHiddenElementsChange,
		};

		elementor.on( 'globals:loaded', this.OnShowHiddenElementsChange );
	}

	createDarkModeStylesheetLink() {
		const darkModeLinkID = this.getSettings( 'darkModeLinkID' );

		let $darkModeLink = jQuery( '#' + darkModeLinkID );

		if ( ! $darkModeLink.length ) {
			$darkModeLink = jQuery( '<link>', {
				id: darkModeLinkID,
				rel: 'stylesheet',
				href: elementor.config.ui.darkModeStylesheetURL,
			} );
		}

		this.$link = $darkModeLink;
	}

	getDarkModeStylesheetLink() {
		if ( ! this.$link ) {
			this.createDarkModeStylesheetLink();
		}

		return this.$link;
	}

	onUIThemeChanged( newValue ) {
		const $link = this.getDarkModeStylesheetLink();

		if ( 'light' === newValue ) {
			$link.remove();

			return;
		}

		$link.attr( 'media', 'auto' === newValue ? '(prefers-color-scheme: dark)' : '' ).appendTo( elementorCommon.elements.$body );
	}

	onPanelWidthChanged( newValue ) {
		elementor.panel.saveSize( { width: newValue.size + newValue.unit } );

		elementor.panel.setSize();
	}

	onEditButtonsChanged() {
		// Let the button change before the high-performance action of rendering the entire page
		setTimeout( () => elementor.getPreviewView()._renderChildren(), 300 );
	}

	OnShowHiddenElementsChange( newValue ) {
		const showHiddenElements = 'undefined' === typeof newValue ? elementor.config.settings.editorPreferences.settings.show_hidden_elements : newValue;

		if ( 'yes' === showHiddenElements ) {
			elementor.$previewContents.find( 'body' ).addClass( 'show-hidden-elements' );
			return;
		}

		elementor.$previewContents.find( 'body' ).removeClass( 'show-hidden-elements' );
	}
}
