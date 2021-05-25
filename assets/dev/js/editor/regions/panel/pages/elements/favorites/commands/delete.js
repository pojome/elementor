import CommandsBase from './base';

export class Delete extends CommandsBase {
	/**
	 * @inheritDoc
	 */
	apply( args ) {
		_.each( args.widgets, ( widget ) => {
			const widgetCache = this.getWidgetCache( widget );

			if ( undefined !== widgetCache ) {
				const categories = widgetCache.categories;

				categories.splice( categories.indexOf( this.getCategoryName() ), 1 );
			}
		} );

		if ( args.store ) {
			this.update( args.widgets, 'delete' );
		}

		this.refreshCategories();
	}
}

export default Delete;
