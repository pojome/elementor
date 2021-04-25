import CommandBase from 'elementor-api/modules/command-base';

export class Preview extends CommandBase {
	validateArgs( args ) {
		this.requireArgument( 'id', args );
	}

	// TODO: Check if blocking is required.
	async apply( args ) {
		const { id } = args,
			{ footerSaver } = $e.components.get( 'document/save' ),
			document = elementor.documents.get( id );

		if ( document.editor.isChanged ) {
			// Force save even if it's saving now.
			await $e.run( 'document/save/auto', {
				force: true,
			} );
		}

		// Open immediately in order to avoid popup blockers.
		footerSaver.previewWindow = open( document.config.urls.wp_preview, `wp-preview-${ document.id }` );
	}
}

export default Preview;
