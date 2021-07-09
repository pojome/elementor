import CommandHistoryBase from '../../command-bases/command-history-base';

export class Duplicate extends CommandHistoryBase {
	validateArgs( args ) {
		this.requireContainer( args );
	}

	getHistory( args ) {
		const { containers = [ args.container ] } = args;

		return {
			containers,
			type: 'duplicate',
		};
	}

	apply( args ) {
		const { containers = [ args.container ] } = args,
			result = [];

		containers.forEach( ( container ) => {
			const parent = container.parent,
				at = container.view._index + 1;

			result.push( $e.run( 'document/elements/create', {
				container: parent,
				model: container.model.toJSON(),
				options: {
					at,
					clone: true,
				},
			} ) );
		} );

		if ( 1 === result.length ) {
			return result[ 0 ];
		}

		return result;
	}
}

export default Duplicate;
