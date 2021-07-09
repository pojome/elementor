import CommandInternalBase from '../base/command-history-internal-base';

export class EndTransaction extends CommandInternalBase {
	apply() {
		if ( ! this.component.isTransactionStarted() ) {
			return;
		}

		const firstItem = this.component.transactions[ 0 ],
			{ type } = firstItem,
			transactions = this.component.mergeTransactions( this.component.transactions );

		let { title = '', subTitle = '' } = firstItem;

		// 'elements' title for multiple containers.
		if ( transactions.length > 1 ) {
			title = __( 'Elements', 'elementor' );
			subTitle = '';
		}

		const history = {
			title,
			subTitle,
			type,
		};

		// If firstItem have id already it means that log already started for that transaction.
		if ( firstItem.id ) {
			history.id = firstItem.id;
		}

		const historyId = $e.internal( 'document/history/start-log', history );

		Object.values( transactions ).forEach( ( item ) => {
			const itemArgs = item;

			// If log already started chain his historyId.
			if ( firstItem.id ) {
				itemArgs.id = firstItem.id;
			}

			$e.internal( 'document/history/log-sub-item', itemArgs );
		} );

		$e.internal( 'document/history/end-log', { id: historyId } );

		// Clear transactions before leave.
		$e.internal( 'document/history/clear-transaction' );
	}
}

export default EndTransaction;
