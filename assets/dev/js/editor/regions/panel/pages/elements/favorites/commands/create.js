import CommandsBase from './base';

export class Create extends CommandsBase {
	/**
	 * @inheritDoc
	 */
	apply( args ) {
		_.each( args.widgets, ( widget ) => {
			const widgetCache = this.getWidgetCache( widget );

			if ( undefined !== widgetCache ) {
				widgetCache.categories.push( this.getCategoryName() );
			}
		} );

		if ( args.store ) {
			this.update( args.widgets, 'merge' );
		}

		this.refreshCategories();
	}
}

export default Create;
