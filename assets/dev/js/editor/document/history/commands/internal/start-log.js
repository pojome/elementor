import CommandHistoryInternalBase from '../base/command-history-internal-base';

export class StartLog extends CommandHistoryInternalBase {
	initialize( args ) {
		super.initialize( args );

		if ( this.history.isItemStarted() || args.id ) {
			this.isSubItem = true;

			return;
		}

		this.args = this.component.normalizeLogTitle( args );
	}

	validateArgs( args ) {
		if ( ! this.isSubItem ) {
			this.requireArgumentType( 'type', 'string', args );
			this.requireArgumentType( 'title', 'string', args );
		}
	}

	apply( args ) {
		if ( this.isSubItem ) {
			$e.internal( 'document/history/log-sub-item', args );

			return null;
		}

		return this.history.startItem( args );
	}
}

export default StartLog;
