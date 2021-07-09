import CommandInfra from './command-infra';

export default class CommandBase extends CommandInfra {
	static getInstanceType() {
		return 'CommandBase';
	}

	onBeforeRun( args = {} ) {
		$e.hooks.runUIBefore( this.command, args );
	}

	onAfterRun( args = {}, result ) {
		$e.hooks.runUIAfter( this.command, args, result );
	}

	onBeforeApply( args = {} ) {
		$e.hooks.runDataDependency( this.command, args );
	}

	onAfterApply( args = {}, result ) {
		$e.hooks.runDataAfter( this.command, args, result );
	}

	onCatchApply( e ) {
		this.runCatchHooks( e );
	}

	/**
	 * Run all the catch hooks.
	 *
	 * @param {Error} e
	 */
	runCatchHooks( e ) {
		$e.hooks.runDataCatch( this.command, this.args, e );
		$e.hooks.runUICatch( this.command, this.args, e );
	}
}
