import CommandHistoryBase from '../../command-bases/command-history-base';

export class Import extends CommandHistoryBase {
	validateArgs( args ) {
		this.requireArgumentInstance( 'model', Backbone.Model, args );

		this.requireArgumentConstructor( 'data', Object, args );
	}

	getHistory( args ) {
		const { model } = args;

		return {
			type: 'add',
			title: __( 'Template', 'elementor' ),
			subTitle: model.get( 'title' ),
		};
	}

	apply( args ) {
		const { data, options = args.options || {} } = args,
			previewContainer = elementor.getPreviewContainer(),
			result = [];

		let at = isNaN( options.at ) ? previewContainer.view.collection.length : options.at;

		// Each `data.content`.
		Object.values( data.content ).forEach( ( model ) => {
			result.push( $e.run( 'document/elements/create', {
				container: elementor.getPreviewContainer(),
				model,
				options: Object.assign( options, { at } ),
			} ) );
			at++;
		} );

		if ( options.withPageSettings ) {
			$e.run( 'document/elements/settings', {
				container: elementor.settings.page.getEditedView().getContainer(),
				settings: data.page_settings,
				options: {
					external: true,
				},
			} );
		}

		return result;
	}
}

export default Import;
