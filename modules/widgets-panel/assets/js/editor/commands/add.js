import CommandBase from 'elementor-api/modules/command-base';

export class Add extends CommandBase {
	async apply( args = {} ) {
		const widgetId = args.widget ?? '';

		if ( ! widgetId.length ) {
			return false;
		}

		const result = await $e.data.create( `panel-favorites/favorites?id=${ widgetId }`, {} );
		if ( result.data ) {
			// Get the categories of widget from front-end
			const widget = elementor.widgetsCache[ `${ widgetId }` ];
			if ( widget ) {
				if ( ! widget.hasOwnProperty( 'categories' ) ) {
					widget.categories = [];
				}
				// Get element position in array
				const categoryExists = widget.categories.includes( 'favorites' );
				// Check: if element not exists in array - add it to array
				if ( ! categoryExists ) {
					widget.categories.push( 'favorites' );
					$e.route( 'panel/elements/categories', { refresh: true } );
				}
			}
		}

		return result;
	}
}
