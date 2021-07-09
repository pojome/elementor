import CommandHistoryBase from '../../command-bases/command-history-base';

export class Create extends CommandHistoryBase {
	static restore( historyItem, isRedo ) {
		const data = historyItem.get( 'data' ),
			container = historyItem.get( 'container' ),
			options = historyItem.get( 'options' ) || {};

		// No clone when restoring. e.g: duplicate will generate unique ids while restoring.
		if ( options.clone ) {
			options.clone = false;
		}

		if ( isRedo ) {
			$e.run( 'document/elements/create', {
				container,
				model: data.modelToRestore,
				options,
			} );
		} else {
			$e.run( 'document/elements/delete', { container: data.containerToRestore } );
		}
	}

	validateArgs( args ) {
		this.requireContainer( args );

		// Avoid Backbone model & etc.
		this.requireArgumentConstructor( 'model', Object, args );
	}

	getHistory( args ) {
		const { model, containers = [ args.container ] } = args;

		return {
			containers,
			model,
			type: 'add',
			title: elementor.helpers.getModelLabel( model ),
		};
	}

	apply( args ) {
		const { model, options = {}, containers = [ args.container ] } = args;

		let result = [];

		// BC: Deprecated since 2.8.0 - use `$e.hooks`.
		if ( ! options.trigger ) {
			options.trigger = {
				beforeAdd: 'element:before:add',
				afterAdd: 'element:after:add',
			};
		}

		containers.forEach( ( container ) => {
			container = container.lookup();

			const createdContainer = container.view.addElement( model, options ).getContainer();

			result.push( createdContainer );

			/**
			 * Acknowledge history of each created item, because we cannot pass the elements when they do not exist
			 * in getHistory().
			 */
			if ( this.isHistoryActive() ) {
				$e.internal( 'document/history/log-sub-item', {
					container,
					type: 'sub-add',
					restore: this.constructor.restore,
					options,
					data: {
						containerToRestore: createdContainer,
						modelToRestore: createdContainer.model.toJSON(),
					},
				} );
			}
		} );

		if ( 1 === result.length ) {
			result = result[ 0 ];
		}

		return result;
	}
}

export default Create;
