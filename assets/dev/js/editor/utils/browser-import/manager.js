import * as types from './types';
import PanelElementsElementModel from '../../regions/panel/pages/elements/models/element';

export default class BrowserImportManager {
	/**
	 * BrowserImportManager constructor.
	 *
	 * @param container
	 * @param options
	 */
	constructor( container, options = {} ) {
		this.container = container;
		this.options = options;
	}

	/**
	 * Import files from FileList, array or string.
	 *
	 * @param files
	 * @returns {Promise<this>}
	 */
	import( files ) {
		if ( files.constructor.name === FileList.prototype[ Symbol.toStringTag ] ) {
			this.files = files;

			return Promise.resolve( this );
		} else if ( files instanceof Array ) {
			return this.importArray( files );
		}

		return this.importFile( files );
	}

	/**
	 * Import a single file (whether a File object or a string).
	 *
	 * @param file
	 * @returns {Promise<this>}
	 */
	importFile( file ) {
		const promise = file instanceof File ?
			Promise.resolve( file ) :
			this.parseStringFile( file );

		return promise.then( ( parsedFile ) => {
			return this.import(
				this.createFileList( parsedFile )
			);
		} );
	}

	/**
	 * Import array of files (whether File objects or strings).
	 *
	 * @param files
	 * @returns {Promise<this>}
	 */
	importArray( files ) {
		return Promise.all(
			files.map( ( file ) => {
				if ( file instanceof File ) {
					return file;
				}
				return this.parseStringFile( file );
			} )
		).then( ( parsedFiles ) => {
			return this.import(
				this.createFileList( parsedFiles )
			);
		} );
	}

	/**
	 * Convert a string file (usually base64) to a File object.
	 *
	 * @param file
	 * @returns {Promise<File>}
	 */
	parseStringFile( file ) {
		return fetch( file )
			.then( ( res ) => res.blob() )
			.then( ( blob ) => {
				return new File( [ blob ], null, { type: blob.type } );
			} );
	}

	/**
	 * Since FileList is a read-only object and cannot be instantiated, we use the DataTransfer which allows
	 * us to add items as files, and then get the overall FileList.
	 *
	 * @param files
	 * @returns {FileList}
	 */
	createFileList( files ) {
		const dataTransfer = new DataTransfer();

		files.map( ( file ) => dataTransfer.items.add( file ) );

		return dataTransfer.files;
	}

	/**
	 * Check whether import is allowed for this kind of files.
	 *
	 * @returns {boolean}
	 */
	isAllowed() {
		if ( ! this.getFiles().length ) {
			return false;
		}

		for ( const file of this.getFiles() ) {
			if ( ! this.getTypeClassOf( file ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Render all files as suitable widgets into the container.
	 */
	render() {
		for ( const file of this.getFiles().reverse() ) {
			new ( this.getTypeClassOf( file ) )( this, file )
				.render();
		}
	}

	/**
	 * Create new widget of the specified type into the container.
	 *
	 * @param widgetType
	 * @param options
	 */
	createWidget( widgetType, options = {} ) {
		const view = this.getContainer().view,
			{ side, event } = this.getOptions(),
			onDropping = view.onDropping || view.addNewSectionView.onDropping;

		elementor.channels.panelElements
			.reply( 'element:selected', { model: new PanelElementsElementModel( { widgetType } ) } )
			.trigger( 'element:drag:start' );

		const widget = onDropping.call( view, side, event );
		widget.settings.setExternalChange( options );
		widget.view.render();

		elementor.channels.panelElements.trigger( 'element:drag:end' );
	}

	/**
	 * Return the Type class matches the specified file.
	 *
	 * @param file
	 * @returns {Type|boolean}
	 */
	getTypeClassOf( file ) {
		for ( const type of Object.values( types ) ) {
			if ( type.isBelong( file ) ) {
				return type;
			}
		}

		return false;
	}

	/**
	 * Get all files imported as array.
	 *
	 * @returns {[]}
	 */
	getFiles() {
		return Array.from( this.files );
	}

	/**
	 * Get the FileList object.
	 *
	 * @returns {FileList}
	 */
	getFileList() {
		return this.files;
	}

	/**
	 * Get the container to import to.
	 * @returns {*}
	 */
	getContainer() {
		return this.container;
	}

	/**
	 * Get all options.
	 * @returns {{}}
	 */
	getOptions() {
		return this.options;
	}
}
