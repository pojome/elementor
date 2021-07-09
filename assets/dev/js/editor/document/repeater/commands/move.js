import CommandHistoryBase from '../../command-bases/command-history-base';

export class Move extends CommandHistoryBase {
	validateArgs( args ) {
		this.requireContainer( args );

		this.requireArgumentType( 'name', 'string', args );
		this.requireArgumentType( 'sourceIndex', 'number', args );
		this.requireArgumentType( 'targetIndex', 'number', args );
	}

	getHistory( args ) {
		const { containers = [ args.container ] } = args;

		return {
			containers,
			type: 'move',
			subTitle: __( 'Item', 'elementor' ),
		};
	}

	apply( args ) {
		const { sourceIndex, targetIndex, name, containers = [ args.container ] } = args,
			result = [];

		containers.forEach( ( container ) => {
			const collection = container.settings.get( name ),
				model = elementorCommon.helpers.cloneObject( collection.at( sourceIndex ) );

			$e.run( 'document/repeater/remove', {
				container,
				name,
				index: sourceIndex,
			} );

			result.push( $e.run( 'document/repeater/insert', {
				container,
				name,
				model,
				options: { at: targetIndex },
			} ) );
		} );

		if ( 1 === result.length ) {
			return result[ 0 ];
		}

		return result;
	}
}

export default Move;
