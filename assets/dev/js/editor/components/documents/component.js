import ComponentBase from 'elementor-api/modules/component-base';
import Document from './document';
import * as commands from './commands/';
import * as internalCommands from './commands/internal/';

export default class Component extends ComponentBase {
	__construct( args = {} ) {
		super.__construct( args );

		/**
		 * All the documents.
		 *
		 * @type {Object.<Document>}
		 */
		this.documents = {};

		/**
		 * Current document.
		 *
		 * @type {Document}
		 */
		this.currentDocument = null;

		this.saveInitialDocumentToCache();
	}

	getNamespace() {
		return 'editor/documents';
	}

	defaultCommands() {
		return this.importCommands( commands );
	}

	defaultCommandsInternal() {
		return this.importCommands( internalCommands );
	}

	/**
	 * Function add().
	 *
	 * Add's document to the manager.
	 *
	 * @param {Document} document
	 *
	 * @returns {Document}
	 */
	add( document ) {
		const { id } = document;

		// Save the document.
		this.documents[ id ] = document;

		return document;
	}

	/**
	 * Function addDocumentByConfig().
	 *
	 * Add document to manager by config.
	 *
	 * @param {{}} config
	 *
	 * @returns {Document}
	 */
	addDocumentByConfig( config ) {
		return this.add( new Document( config ) );
	}

	/**
	 * Function get().
	 *
	 * Get document by id.
	 *
	 * @param {number} id
	 *
	 * @returns {Document|boolean}
	 */
	get( id ) {
		if ( undefined !== this.documents[ id ] ) {
			return this.documents[ id ];
		}

		return false;
	}

	/**
	 * Function getCurrent().
	 *
	 * Return's current document.
	 *
	 * @returns {Document}
	 */
	getCurrent() {
		return this.currentDocument;
	}

	/**
	 * Function getCurrentId().
	 *
	 * Return's current document id.
	 *
	 * @returns {number}
	 */
	getCurrentId() {
		return this.currentDocument.id;
	}

	/**
	 * Function setCurrent().
	 *
	 * set current document by document instance.
	 *
	 * @param {Document} document
	 */
	setCurrent( document ) {
		if ( undefined === this.documents[ document.id ] ) {
			throw Error( `The document with id: '${ document.id }' does not exist/loaded` );
		}

		if ( this.currentDocument ) {
			this.currentDocument.editor.status = 'closed';
		}

		this.currentDocument = this.documents[ document.id ];
		this.currentDocument.editor.status = 'open';

		elementorCommon.ajax.addRequestConstant( 'editor_post_id', document.id );
	}

	isCurrent( id ) {
		return parseInt( id ) === this.currentDocument.id;
	}

	unsetCurrent() {
		this.currentDocument = null;
		elementorCommon.ajax.addRequestConstant( 'editor_post_id', null );
	}

	request( id ) {
		return elementorCommon.ajax.load( this.getRequestArgs( id ), true );
	}

	invalidateCache( id ) {
		elementorCommon.ajax.invalidateCache( this.getRequestArgs( id ) );
	}

	getRequestArgs( id ) {
		id = parseInt( id );

		return {
			action: 'get_document_config',
			unique_id: `document-${ id }`,
			data: { id },
			success: ( config ) => config,
			error: ( data ) => {
				let message;

				if ( _.isString( data ) ) {
					message = data;
				} else if ( data.statusText ) {
					message = elementor.createAjaxErrorMessage( data );

					if ( 0 === data.readyState ) {
						message += ' ' + __( 'Cannot load editor', 'elementor' );
					}
				} else if ( data[ 0 ] && data[ 0 ].code ) {
					message = __( 'Server Error', 'elementor' ) + ' ' + data[ 0 ].code;
				}

				alert( message );
			},
		};
	}

	/**
	 * Temp: Don't request initial document via ajax.
	 * Keep the event `elementor:init` before `preview:loaded`.
	 */
	saveInitialDocumentToCache() {
		const document = elementor.config.initial_document;
		elementorCommon.ajax.addRequestCache( this.getRequestArgs( document.id ), document );
	}
}
