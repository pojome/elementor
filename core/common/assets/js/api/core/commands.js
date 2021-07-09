import CommandsBackwardsCompatibility from './backwards-compatibility/commands';
import CommandBase from '../modules/command-base';

export default class Commands extends CommandsBackwardsCompatibility {
	static trace = [];

	/**
	 * Function constructor().
	 *
	 * Create `$e.commands` API.
	 *
	 * @param {{}} args
	 */
	constructor( ... args ) {
		super( ... args );

		this.current = {};
		this.currentArgs = {};
		this.currentTrace = [];
		this.commands = {};
		this.components = {};
	}

	/**
	 * @returns {CommandBase}
	 */
	getCommandClass( id ) {
		return this.commands[ id ];
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
	 * @param {{}} args
	 */
	beforeRun( command, args = {} ) {
		const component = this.getComponent( command ),
			container = component.getRootContainer();

		this.currentTrace.push( command );

		Commands.trace.push( command );

		this.current[ container ] = command;
		this.currentArgs[ container ] = args;

		if ( args.onBefore ) {
			args.onBefore.apply( component, [ args ] );
		}

		this.trigger( 'run:before', component, command, args );
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

		this.beforeRun( command, args );

		// Call to new command or callback.
		let instance = this.commands[ command ];

		if ( instance.getInstanceType ) {
			instance = new instance( args );
		}

		const currentComponent = this.getComponent( command );

		// Route?.
		if ( ! ( instance instanceof CommandBase ) ) {
			const results = instance.apply( currentComponent, [ args ] );

			this.afterRun( command, args, results );

			return results;
		}

		// TODO: Check with mati.
		if ( ! this.validateInstanceScope( instance, command, currentComponent ) ) {
			// TODO: Code duplication, handle after review with mati.
			const container = currentComponent.getRootContainer();

			this.currentTrace.pop();
			Commands.trace.pop();

			delete this.current[ container ];
			delete this.currentArgs[ container ];

			return;
		}

		return this.runInstance( command, args, instance );
	}

	runInstance( command, args, instance ) {
		let results = null;

		// For UI Hooks.
		instance.onBeforeRun( instance.args );

		try {
			// For data hooks.
			instance.onBeforeApply( instance.args );

			results = instance.run();
		} catch ( e ) {
			this.catchApply( e, instance );

			if ( e instanceof $e.modules.HookBreak ) {
				this.afterRun( command, args, e ); // To clear current.
				return false;
			}
		}

		return this.runAfter( command, instance, results );
	}

	runAfter( command, instance, result ) {
		const onAfter = ( _result ) => {
				// Run Data hooks.
				instance.onAfterApply( instance.args, _result );

				// For UI hooks.
				instance.onAfterRun( instance.args, _result );

				this.afterRun( command, instance.args, _result );
			},
			asyncOnAfter = async ( _result ) => {
				// Run Data hooks.
				const results = instance.onAfterApply( instance.args, _result ),
					promises = Array.isArray( results ) ? results.flat().filter( ( filtered ) => filtered instanceof Promise ) : [];

				if ( promises.length ) {
					// Wait for hooks before return the value.
					await Promise.all( promises );
				}

				// For UI hooks.
				instance.onAfterRun( instance.args, _result );

				this.afterRun( command, instance.args, _result );
			},
			handleResultJQueryDeferred = ( _result ) => {
				_result.fail( ( e ) => {
					this.catchApply( e, instance );
					this.afterRun( command, instance.args, e );
				} );
				_result.done( onAfter );

				return _result;
			},
			handleResultPromise = ( _result ) => {
				// Override initial result ( promise ) to await onAfter promises, first!.
				return ( async () => {
					await result.catch( ( e ) => {
						this.catchApply( e, instance );
						this.afterRun( command, instance.args, e );
					} );
					await result.then( ( __result ) => asyncOnAfter( __result ) );

					return result;
				} )();
			},
			handleResultNormal = ( _result ) => onAfter( _result );

		// TODO: Temp code determine if it's a jQuery deferred object.
		if ( result && 'object' === typeof result && result.promise && result.then && result.fail ) {
			return handleResultJQueryDeferred( result );
		} else if ( result instanceof Promise ) {
			return handleResultPromise( result );
		}

		handleResultNormal( result );

		return result;
	}

	/**
	 * @param {Error} e
	 * @param {CommandBase} instance
	 */
	catchApply( e, instance ) {
		instance.onCatchApply( e );

		// TODO: Should be part of $e.API.
		elementorCommon.helpers.consoleError( e );
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
	 * Method fired after the command runs.
	 *
	 * @param {string} command
	 * @param {{}} args
	 * @param {*} results
	 */
	afterRun( command, args, results = undefined ) {
		const component = this.getComponent( command ),
			container = component.getRootContainer();

		if ( args.onAfter ) {
			args.onAfter.apply( component, [ args, results ] );
		}

		this.trigger( 'run:after', component, command, args, results );

		this.currentTrace.pop();
		Commands.trace.pop();

		delete this.current[ container ];
		delete this.currentArgs[ container ];
	}

	validateInstanceScope( instance, command, currentComponent ) {
		if ( ! ( instance instanceof CommandBase ) ) {
			this.error( `invalid instance, command: '${ command }' ` );
		}

		// In case of different scope.
		if ( currentComponent !== instance.component ) {
			if ( $e.devTools ) {
				$e.devTools.log.warn( `Command: '${ command }' registerArgs.component: '${ instance.component.getNamespace() }' while current component is: '${ currentComponent.getNamespace() }'` );
			}

			return false;
		}

		return true;
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
