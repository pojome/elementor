import Module from '../modules/imports/module';

export default class extends Module {
	get( key, options ) {
		options = options || {};

		let storage;

		try {
			storage = options.session ? sessionStorage : localStorage;
		} catch ( e ) {
			return key ? undefined : {};
		}

		let elementorStorage = storage.getItem( 'elementor' );

		if ( elementorStorage ) {
			elementorStorage = JSON.parse( elementorStorage );
		} else {
			elementorStorage = {};
		}

		if ( ! elementorStorage.__expiration ) {
			elementorStorage.__expiration = {};
		}

		const expiration = elementorStorage.__expiration;

		let expirationToCheck = [];

		if ( key ) {
			if ( expiration[ key ] ) {
				expirationToCheck = [ key ];
			}
		} else {
			expirationToCheck = Object.keys( expiration );
		}

		let entryExpired = false;

		expirationToCheck.forEach( ( expirationKey ) => {
			if ( new Date( expiration[ expirationKey ] ) < new Date() ) {
				delete elementorStorage[ expirationKey ];

				delete expiration[ expirationKey ];

				entryExpired = true;
			}
		} );

		if ( entryExpired ) {
			this.save( elementorStorage, options.session );
		}

		if ( key ) {
			return elementorStorage[ key ];
		}

		return elementorStorage;
	}

	set( key, value, options ) {
		options = options || {};

		const elementorStorage = this.get( null, options );

		elementorStorage[ key ] = value;

		if ( options.lifetimeInSeconds ) {
			const date = new Date();

			date.setTime( date.getTime() + ( options.lifetimeInSeconds * 1000 ) );

			elementorStorage.__expiration[ key ] = date.getTime();
		}

		this.save( elementorStorage, options.session );
	}

	save( object, session ) {
		let storage;

		try {
			storage = session ? sessionStorage : localStorage;
		} catch ( e ) {
			return;
		}

		storage.setItem( 'elementor', JSON.stringify( object ) );
	}
}
