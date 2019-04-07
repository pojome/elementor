const Store = class {
	static getKey( library ) {
		const name = ( library.name ) ? library.name : library;
		return `elementor_${ name }_icons`;
	}

	save( library ) {
		this.set( Store.getKey( library ), library );
	}

	getIcons( library ) {
		const data = this.get( Store.getKey( library ) );
		if ( data && data.icons ) {
			return data.icons;
		}
		return false;
	}

	set( key, value ) {
		const preparedValue = JSON.stringify( value );
		window.localStorage.setItem( key, preparedValue );
	}

	get( key ) {
		const saved = window.localStorage.getItem( key );
		if ( ! saved ) {
			return false;
		}
		return JSON.parse( saved );
	}

	isValid( library ) {
		const saved = this.get( Store.getKey( library ) );
		if ( ! saved ) {
			return false;
		}
		if ( saved.ver !== library.ver ) {
			return false;
		}
		return ( saved.icons && saved.icons.length );
	}

	del( key ) {
		window.localStorage.removeItem( key );
	}
};

export default new Store();
