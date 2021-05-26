import CommandsBase from './base';

export class Create extends CommandsBase {
	/**
	 * @inheritDoc
	 */
	apply( args ) {
		const widgets = args.widgets;

		widgets.forEach( ( widget ) => {
			const widgetCache = this.getWidgetCache( widget );

			if ( undefined !== widgetCache ) {
				widgetCache.categories.push( this.getCategorySlug() );
			}
		} );

		if ( args.store ) {
			$e.data.update(
				this.component.getFavoritesDataCommand(),
				{},
				{
					key: this.component.getFavoritesCollectionKey(),
					data: widgets,
				},
			);
		}

		this.refreshCategories();
	}
}

export default Create;
