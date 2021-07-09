import CommandHistoryInternalBase from '../base/command-history-internal-base';

export class AddTransaction extends CommandHistoryInternalBase {
	initialize( args ) {
		super.initialize( args );

		/**
		 * Debounce always send 'add-transaction' with title & subTitle, when the transaction
		 * already started, there is no need to save those args they are useless.
		 */
		if ( this.component.isTransactionStarted() ) {
			delete args.title;
			delete args.subTitle;
		}
	}

	validateArgs( args ) {
		this.requireContainer();
		this.requireArgumentType( 'type', 'string', args );
	}

	apply( args ) {
		const currentId = this.history.getCurrentId();

		if ( currentId ) {
			// If log already started chain his historyId.
			args.id = currentId;
		}

		args = this.component.normalizeLogTitle( args );

		this.component.transactions.push( args );
	}
}

export default AddTransaction;
