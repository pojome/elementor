import CommandDataChangeBase from './command-data-change-base';

export default class CommandHistoryBase extends CommandDataChangeBase {
	static getInstanceType() {
		return 'CommandHistoryBase';
	}

	initialize( args = {} ) {
		/**
		 * Get History from child command.
		 *
		 * @type {{}|boolean}
		 */
		this.history = this.getHistory( args );

		/**
		 * @type {number|boolean}
		 */
		this.historyId = false;
	}

	/**
	 * Function getHistory().
	 *
	 * Get history object from child, do nothing if it false.
	 *
	 * @param [args={}]
	 *
	 * @returns {({}|boolean)}
	 */
	getHistory( args = {} ) { // eslint-disable-line no-unused-vars
		elementorModules.ForceMethodImplementation();
	}

	/**
	 * Function isHistoryActive().
	 *
	 * Return `elementor.documents.getCurrent().history.getActive()`.
	 *
	 * @returns {boolean}
	 */
	isHistoryActive() {
		return elementor.documents.getCurrent().history.getActive();
	}

	onBeforeRun( args ) {
		super.onBeforeRun( args );

		if ( this.history && this.isHistoryActive() ) {
			this.historyId = $e.internal( 'document/history/start-log', this.history );
		}
	}

	onAfterRun( args, result ) {
		super.onAfterRun( args, result );

		if ( this.history && this.isHistoryActive() ) {
			$e.internal( 'document/history/end-log', { id: this.historyId } );
		}
	}

	onCatchApply( e ) {
		// Rollback history on failure.
		if ( e instanceof $e.modules.HookBreak && this.historyId ) {
			$e.internal( 'document/history/delete-log', { id: this.historyId } );
		}

		super.onCatchApply( e );
	}

	isDataChanged() {
		// All the commands who use history are commands that changing the data.
		return true;
	}
}
