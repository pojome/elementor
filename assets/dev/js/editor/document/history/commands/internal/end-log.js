import CommandHistoryInternalBase from '../base/command-history-internal-base';

export class EndLog extends CommandHistoryInternalBase {
	apply( args ) {
		if ( args.id ) {
			this.history.endItem( args.id );
		}
	}
}

export default EndLog;
