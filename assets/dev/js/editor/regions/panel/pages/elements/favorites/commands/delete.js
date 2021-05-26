import CommandsBase from './base';

export class Delete extends CommandsBase {
	/**
	 * @inheritDoc
	 */
	apply( args ) {
		const widgets = args.widgets;

		widgets.forEach( ( widget ) => {
			const widgetCache = this.getWidgetCache( widget );

			if ( undefined !== widgetCache ) {
				const categories = widgetCache.categories;

				categories.splice( categories.indexOf( this.getCategorySlug() ), 1 );
			}
		} );

		if ( args.store ) {
			$e.data.delete(
				this.component.getFavoritesDataCommand(),
				{
					key: this.component.getFavoritesCollectionKey(),
					data: widgets,
				},
			);
		}

		this.refreshCategories();
	}
}

export default Delete;
