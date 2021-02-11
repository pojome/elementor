import CommandBase from 'elementor-api/modules/command-base';

export class Remove extends CommandBase {
	async apply( args ) {
		const widgetId = args.widget ?? '';

		if ( ! widgetId.length ) {
			return false;
		}

		const result = await $e.data.delete( `panel-favorites/favorites?id=${ widgetId }`, {} );
		if ( result.data ) {
			// Get the categories of widget from front-end
			const widgetCategories = elementor.widgetsCache[ widgetId ].categories;
			// Get element position in array
			const categoryPosition = widgetCategories.indexOf( 'favorites' );
			// Check: if element in array - remove the element, else do nothing
			if ( -1 !== categoryPosition ) {
				widgetCategories.splice( categoryPosition, 1 );
				$e.route( 'panel/elements/categories', { refresh: true } );
			}
		}

		return result;
	}
}
