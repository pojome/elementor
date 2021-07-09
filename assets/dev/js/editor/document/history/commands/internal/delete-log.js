import CommandInternalBase from '../base/command-history-internal-base';

export class DeleteLog extends CommandInternalBase {
	apply( args ) {
		if ( args.id ) {
			this.history.deleteItem( args.id );
		}
	}
}

export default DeleteLog;
