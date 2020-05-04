import BaseManager from '../base/manager';

export default class extends BaseManager {
	getDefaultSettings() {
		return {
			darkModeLinkID: 'elementor-editor-dark-mode-css',
			lightModeLinkID: 'elementor-editor-light-mode-css',
		};
	}

	constructor( ...args ) {
		super( ...args );

		this.changeCallbacks = {
			ui_theme: this.onUIThemeChanged,
			edit_buttons: this.onEditButtonsChanged,
		};
	}

	createThemeStylesheetLinks( mode ) {
		const lightModeLinkID = this.getSettings( 'lightModeLinkID' ),
			darkModeLinkID = this.getSettings( 'darkModeLinkID' );

		let $darkModeLink = jQuery( '#' + darkModeLinkID ),
			$lightModeLink = jQuery( '#' + lightModeLinkID );

		if ( 'light' !== mode ) {
			if ( ! $darkModeLink.length ) {
				$darkModeLink = jQuery( '<link>', {
					id: darkModeLinkID,
					rel: 'stylesheet',
					href: elementor.config.ui.darkModeStylesheetURL,
				} );
			}
		}

		if ( 'dark' !== mode ) {
			if ( ! $lightModeLink.length ) {
				$lightModeLink = jQuery( '<link>', {
					id: lightModeLinkID,
					rel: 'stylesheet',
					href: elementor.config.ui.lightModeStylesheetURL,
				} );
			}
		}

		this.links = {
			$darkModeLink: $darkModeLink,
			$lightModeLink: $lightModeLink,
		};
	}

	getThemeStylesheetLinks( mode ) {
		this.createThemeStylesheetLinks( mode );

		return this.links;
	}

	onUIThemeChanged( newValue ) {
		const links = this.getThemeStylesheetLinks( newValue ),
			$body = elementorCommon.elements.$body;

		jQuery.each( [ 'light', 'dark' ], ( i, mode ) => {
			const $linkElement = links[ '$' + mode + 'ModeLink' ];
			$linkElement.remove();

			if ( 'auto' === newValue ) {
				$linkElement.attr( 'media', '(prefers-color-scheme: ' + mode + ')' );
			}

			if ( mode === newValue || 'auto' === newValue ) {
				$linkElement.appendTo( $body );
			}
		} );
	}

	onEditButtonsChanged() {
		// Let the button change before the high-performance action of rendering the entire page
		setTimeout( () => elementor.getPreviewView().render(), 300 );
	}
}
