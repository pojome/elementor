import CommandBase from 'elementor-api/modules/command-base';

export class Load extends CommandBase {
	async apply( args = {} ) {
		const result = await $e.data.get( `panel-favorites/favorites`, {}, { refresh: true } );
		if ( result.data ) {
			$e.route( 'panel/elements/categories', { refresh: true } );
		}

		return result;
	}
}
