import CommandHistoryInternalBase from '../base/command-history-internal-base';

export class LogSubItem extends CommandHistoryInternalBase {
	apply( args ) {
		if ( ! this.history.getActive() ) {
			return;
		}

		const id = args.id || this.history.getCurrentId();

		args = this.component.normalizeLogTitle( args );

		const items = this.history.getItems(),
			item = items.findWhere( { id } );

		if ( ! item ) {
			throw new Error( 'History item not found.' );
		}

		/**
		 * Sometimes `args.id` passed to `LogSubItem`, to add sub item for specific id.
		 * this `id` should not be passed as sub-item.
		 */
		if ( args.id ) {
			delete args.id;
		}

		item.get( 'items' ).unshift( args );
	}
}

export default LogSubItem;
