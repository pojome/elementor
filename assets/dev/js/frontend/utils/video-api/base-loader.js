export default class BaseLoader extends elementorModules.ViewModule {
	getDefaultSettings() {
		return {
			isInserted: false,
			selectors: {
				firstScript: 'script:first',
			},
		};
	}

	getDefaultElements() {
		return {
			$firstScript: jQuery( this.getSettings( 'selectors.firstScript' ) ),
		};
	}

	insertAPI() {
		// Check if consent is given for the external video service (if no `consentApi` is available, fallback to immediate loading)
		const apiUrl = this.getApiURL();
		const consentPromise = window.consentApi ? window.consentApi.unblock( apiUrl ) : Promise.resolve();
		consentPromise.then( () => {
			this.elements.$firstScript.before( jQuery( '<script>', { src: apiUrl } ) );
		} );

		this.setSettings( 'isInserted', true );
	}

	getVideoIDFromURL( url ) {
		const videoIDParts = url.match( this.getURLRegex() );

		return videoIDParts && videoIDParts[ 1 ];
	}

	onApiReady( callback ) {
		if ( ! this.getSettings( 'isInserted' ) ) {
			this.insertAPI();
		}

		if ( this.isApiLoaded() ) {
			callback( this.getApiObject() );
		} else {
			// If not ready check again by timeout..
			setTimeout( () => {
				this.onApiReady( callback );
			}, 350 );
		}
	}
}
