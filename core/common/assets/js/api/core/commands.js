export default class Commands extends elementorModules.Module {
	/**
	 * Function constructor().
	 *
	 * Create `$e.commands` API.
	 *
	 * @param {{}} args
	 */
	constructor( ...args ) {
		super( ...args );

		this.current = {};
		this.currentArgs = {};
		this.currentTrace = [];
		this.commands = {};
		this.components = {};
	}

	/**
	 * Function getAll().
	 *
	 * Receive all loaded commands.
	 *
	 * @returns {string[]}
	 */
	getAll() {
		return Object.keys( this.commands ).sort();
	}

	/**
	 * Function register().
	 *
	 * Register new command.
	 *
	 * @param {ComponentBase|string} component
	 * @param {string} command
	 * @param {function()} callback
	 *
	 * @returns {Commands}
	 */
	register( component, command, callback ) {
		let namespace;
		if ( 'string' === typeof component ) {
			namespace = component;
			component = $e.components.get( namespace );

			if ( ! component ) {
				this.error( `'${ namespace }' component is not exist.` );
			}
		} else {
			namespace = component.getNamespace();
		}

		const fullCommand = namespace + ( command ? '/' + command : '' );

		if ( this.commands[ fullCommand ] ) {
			this.error( `\`${ fullCommand }\` is already registered.` );
		}

		this.commands[ fullCommand ] = callback;
		this.components[ fullCommand ] = namespace;

		const shortcuts = component.getShortcuts(),
			shortcut = shortcuts[ command ];

		if ( shortcut ) {
			shortcut.command = fullCommand;
			shortcut.callback = ( event ) => this.runShortcut( fullCommand, event );
			$e.shortcuts.register( shortcut.keys, shortcut );
		}

		return this;
	}

	unregister( component, command ) {
		let namespace;
		if ( 'string' === typeof component ) {
			namespace = component;
			component = $e.components.get( namespace );

			if ( ! component ) {
				this.error( `'${ namespace }' component is not exist.` );
			}
		} else {
			namespace = component.getNamespace();
		}

		const fullCommand = namespace + ( command ? '/' + command : '' );

		if ( ! this.commands[ fullCommand ] ) {
			this.error( `\`${ fullCommand }\` not exist.` );
		}

		delete this.commands[ fullCommand ];
		delete this.components[ fullCommand ];

		const shortcuts = component.getShortcuts(),
			shortcut = shortcuts[ command ];

		if ( shortcut ) {
			$e.shortcuts.unregister( shortcut.keys, shortcut );
		}

		return this;
	}

	/**
	 * Function getComponent().
	 *
	 * Receive Component of the command.
	 *
	 * @param {string} command
	 *
	 * @returns {Component}
	 */
	getComponent( command ) {
		const namespace = this.components[ command ];

		return $e.components.get( namespace );
	}

	/**
	 * Function is().
	 *
	 * Checks if current running command is the same parameter command.
	 *
	 * @param {string} command
	 *
	 * @returns {boolean}
	 */
	is( command ) {
		const component = this.getComponent( command );

		if ( ! component ) {
			return false;
		}

		return command === this.current[ component.getRootContainer() ];
	}

	/**
	 * Function isCurrentFirstTrace().
	 *
	 * Checks if parameter command is the first command in trace that currently running.
	 *
	 * @param {string} command
	 *
	 * @returns {boolean}
	 */
	isCurrentFirstTrace( command ) {
		return command === this.getCurrentFirstTrace();
	}

	/**
	 * Function getCurrent().
	 *
	 * Receive currently running components and its commands.
	 *
	 * @param {string} container
	 *
	 * @returns {{}|boolean|*}
	 */
	getCurrent( container = '' ) {
		if ( container ) {
			if ( ! this.current[ container ] ) {
				return false;
			}

			return this.current[ container ];
		}

		return this.current;
	}

	/**
	 * Function getCurrentArgs().
	 *
	 * Receive currently running command args.
	 *
	 * @param {string} container
	 *
	 * @returns {{}|boolean|*}
	 */
	getCurrentArgs( container = '' ) {
		if ( container ) {
			if ( ! this.currentArgs[ container ] ) {
				return false;
			}

			return this.currentArgs[ container ];
		}

		return this.currentArgs;
	}

	/**
	 * Function getCurrentFirst().
	 *
	 * Receive first command that currently running.
	 *
	 * @returns {string}
	 */
	getCurrentFirst() {
		return Object.values( this.current )[ 0 ];
	}

	/**
	 * Function getCurrentLast().
	 *
	 * Receive last command that currently running.
	 *
	 * @returns {string}
	 */
	getCurrentLast() {
		const current = Object.values( this.current );

		return current[ current.length - 1 ];
	}

	/**
	 * Function getCurrentFirstTrace().
	 *
	 * Receive first command in trace that currently running
	 *
	 * @returns {string}
	 */
	getCurrentFirstTrace() {
		return this.currentTrace[ 0 ];
	}

	validateRun( command, args = {} ) {
		if ( ! this.commands[ command ] ) {
			this.error( `\`${ command }\` not found.` );
		}

		return this.getComponent( command ).dependency( command, args );
	}

	/**
	 * Function beforeRun().
	 *
	 * @param {string} command
	 * @param {} args
	 *
	 * @returns {boolean} dependency result
	 */
	beforeRun( command, args = {} ) {
		const component = this.getComponent( command ),
			container = component.getRootContainer();

		this.currentTrace.push( command );

		this.current[ container ] = command;
		this.currentArgs[ container ] = args;

		if ( args.onBefore ) {
			args.onBefore.apply( component, [ args ] );
		}

		this.trigger( 'run', component, command, args );
	}

	/**
	 * Function run().
	 *
	 * Runs a command.
	 *
	 * @param {string} command
	 * @param {{}} args
	 *
	 * @returns {boolean|*} results
	 */
	run( command, args = {} ) {
		if ( ! this.validateRun( command, args ) ) {
			return false;
		}

		this.beforeRun( command, args )

		const results = this.commands[ command ].apply( this.getComponent( command ), [ args ] );

		this.afterRun( command, args, results );
		const instance = this.commands[ command ].apply( component, [ args ] );

		this.validateInstance( instance, component, command );

		this.trigger( 'run', component, command, args );

		if ( args.onBefore ) {
			args.onBefore.apply( component, [ args ] );
		}

		// If not instance or is not run-able instance ( CommandBase ), results equal to instance ( eg route )
		// else results are from run().
		const results = ! instance || 'undefined' === typeof instance.run ? instance : instance.run();

		// TODO: Consider add results to `$e.devTools`.
		if ( args.onAfter ) {
			args.onAfter.apply( component, [ args, results ] );
		}

		this.afterRun( command );

		if ( false === args.returnValue ) {
			return true;
		}

		return results;
	}

	/**
	 * Function runShortcut().
	 *
	 * Run shortcut.
	 *
	 * It's separated in order to allow override.
	 *
	 * @param {string} command
	 * @param {*} event
	 *
	 * @returns {boolean|*}
	 */
	runShortcut( command, event ) {
		return this.run( command, event );
	}

	/**
	 * Function afterRun().
	 *
	 * Method fired before the command runs.
	 *
	 * @param {string} command
	 * @param {{}} args
	 * @param {*} results
	 */
	afterRun( command, args, results ) {
		const component = this.getComponent( command ),
			container = component.getRootContainer();

		// TODO: Consider add results to `$e.devTools`.
		if ( args.onAfter ) {
			args.onAfter.apply( component, [ args, results ] );
		}

		this.currentTrace.pop();

		delete this.current[ container ];
		delete this.currentArgs[ container ];
	}

	validateInstance( instance, component, command ) {
		if ( ! instance || component !== instance.component ) {
			this.error( `invalid instance, command: '${ command }' ` );
		}
	}
	/**
	 * Function error().
	 *
	 * Throws error.
	 *
	 * @throw {Error}
	 *
	 * @param {string} message
	 */
	error( message ) {
		throw Error( `Commands: ${ message }` );
	}
}
