import CommandBase from 'elementor-api/modules/command-base';

export class Open extends CommandBase {
	validateArgs( args ) {
		this.requireArgument( 'id', args );
	}

	apply( args ) {
		const { id } = args,
			currentDocument = elementor.documents.getCurrent();

		// Already opened.
		if ( currentDocument && id === currentDocument.id ) {
			return jQuery.Deferred().resolve();
		}

		// TODO: move to $e.hooks.ui.
		if ( elementor.loaded ) {
			elementor.$previewContents.find( `.elementor-${ id }` ).addClass( 'loading' );
		}

		return elementor.documents.request( id )
			.then( ( config ) => {
				elementorCommon.elements.$body.addClass( `elementor-editor-${ config.type }` );

				// Tell the editor to load the document.
				return $e.internal( 'editor/documents/load', { config } );
			} )
			.always( () => {
				// TODO: move to $e.hooks.ui.
				if ( elementor.loaded ) {
					elementor.$previewContents.find( `.elementor-${ id }` ).removeClass( 'loading' );
				}
			} );
	}
}

export default Open;
