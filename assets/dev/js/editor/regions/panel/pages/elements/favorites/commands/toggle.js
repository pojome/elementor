import CommandsBase from './base';

export class Toggle extends CommandsBase {
	/**
	 * @inheritDoc
	 */
	apply( args ) {
		const widgets = args.widgets;

		widgets.forEach( ( widget ) => {
			const widgetCache = this.getWidgetCache( widget );

			if ( undefined !== widgetCache ) {
				let command;

				if ( widgetCache.categories.includes( this.getCategorySlug() ) ) {
					command = this.component.getDeleteCommand();
				} else {
					command = this.component.getCreateCommand();
				}

				$e.run( command, args );
			}
		} );
	}
}

export default Toggle;
