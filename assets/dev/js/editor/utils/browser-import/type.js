export default class Type {
	/**
	 * Type constructor.
	 *
	 * @param manager
	 * @param file
	 */
	constructor( manager, file ) {
		this.manager = manager;
		this.file = file;
	}

	/**
	 * Render the file as suitable widget.
	 */
	render() {}

	/**
	 * Retrieve the BrowserImportManager that handles the type class.
	 *
	 * @returns {*}
	 */
	getManager() {
		return this.manager;
	}

	/**
	 * Get the file to import.
	 *
	 * @returns {*}
	 */
	getFile() {
		return this.file;
	}

	/**
	 * Get the allowed media types by this type.
	 *
	 * @returns {*[]}
	 */
	static mediaTypes() {
		return [];
	}

	/**
	 * Check whether a file is compatible with this type.
	 *
	 * @param file
	 * @returns {boolean}
	 */
	static isBelong( file ) {
		for ( const mediaType of this.mediaTypes() ) {
			const mediaTypeRegex = new RegExp( `${ mediaType.replace( '/', '\\/' ).replace( '*', '\\w+' ) }`, 'i' );

			if ( mediaTypeRegex.test( file.type ) ) {
				return true;
			}
		}

		return false;
	}
}
