import CommandBase from 'elementor-api/modules/command-base';
import ignoreSectionWidgets from '../helpers/ignoreSectionWidgets';

export class Remove extends CommandBase {
	async apply( args ) {
		const widget = args.widget ?? '';

		if ( ! widget.length || ignoreSectionWidgets.includes( widget ) ) {
			return false;
		}

		const result = await $e.data.delete( `panel-favorites/favorites?id=${ widget }`, {} );
		if ( result.data ) {
			// Get the categories of widget from front-end
			const widgetsArr = elementor.widgetsCache[ widget ].categories;
			// Get element position in array
			const elmPos = widgetsArr.indexOf( 'favorites' );
			// Check - if element in array remove the element, else do nothing
			if ( -1 !== elmPos ) {
				widgetsArr.splice( elmPos, 1 );
				$e.route( 'panel/elements/categories', { refresh: true } );
			}
		}

		return result;
	}
}