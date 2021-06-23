import ComponentBase from 'elementor-api/modules/component-base';

jQuery( () => {
	QUnit.module( 'File: core/common/assets/js/api/core/commands.js', ( hooks ) => {
		QUnit.test( 'Error on register command without component', ( assert ) => {
			assert.throws(
				() => $e.commands.register( '', 'save', () => {} ),
				new Error( "Commands: '' component is not exist." )
			);
		} );

		QUnit.test( 'Error on re-register command', ( assert ) => {
			const namespace = 're-register-command';

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				defaultCommands() {
					return { commandA: () => {} };
				}
			};

			$e.components.register( new Component() );

			assert.throws(
				() => $e.commands.register( namespace, 'commandA', () => {} ),
				new Error( `Commands: \`${ namespace + '/commandA' }\` is already registered.` )
			);
		} );

		QUnit.test( 'Error on run non exited command', ( assert ) => {
			assert.throws(
				() => {
					$e.run( 'not-existing-command' );
				},
				new Error( 'Commands: `not-existing-command` not found.' )
			);
		} );

		QUnit.test( 'Run command', ( assert ) => {
			assert.expect( 3 );

			const namespace = 'run-command',
				command = namespace + '/commandA';
			let commandStatus = 'beforeRun';
			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				defaultCommands() {
					return {
						commandA: () => {
							assert.equal( $e.commands.is( command ), true );
							commandStatus = 'afterRun';
						},
					};
				}
			};

			$e.components.register( new Component() );

			$e.run( command );

			assert.equal( commandStatus, 'afterRun' );

			assert.equal( $e.commands.is( command ), false );
		} );

		QUnit.test( 'Run command with args', ( assert ) => {
			assert.expect( 3 );

			const namespace = 'run-command-with-args';

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				defaultCommands() {
					return {
						commandA: ( args ) => {
							assert.equal( args.argA, 1 );
							assert.equal( $e.commands.getCurrentArgs( namespace ), args );
						},
					};
				}
			};

			$e.components.register( new Component() );

			$e.run( namespace + '/commandA', {
				argA: 1,
			} );

			assert.equal( $e.commands.getCurrentArgs( namespace ), false );
		} );

		QUnit.test( 'Run command with events args', ( assert ) => {
			const namespace = 'run-command-with-events-args';
			let onBeforeStatus = 'beforeRun',
				onAfterStatus = 'beforeRun';

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				defaultCommands() {
					return { commandA: () => {} };
				}
			};

			$e.components.register( new Component() );

			$e.run( namespace + '/commandA', {
				onBefore: () => {
					onBeforeStatus = 'afterRun';
				},
				onAfter: () => {
					onAfterStatus = 'afterRun';
				},
			} );

			assert.equal( onBeforeStatus, 'afterRun' );
			assert.equal( onAfterStatus, 'afterRun' );
		} );

		QUnit.test( 'Check if route to is activate the component', ( assert ) => {
			const namespace = 'route-to-activate-component';

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				defaultRoutes() {
					return { routeA: () => {} };
				}
			};

			$e.components.register( new Component() );

			$e.route( namespace + '/routeA' );

			const activeComponent = Object.keys( $e.components.activeComponents ).pop();

			assert.equal( activeComponent, namespace );
		} );

		QUnit.test( 'Ensure that run command is not activate the component', ( assert ) => {
			const namespace = 'run-command-not-activate-component';

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				defaultCommands() {
					return { commandA: () => {} };
				}
			};

			$e.components.register( new Component() );

			$e.run( namespace + '/commandA' );

			const activeComponent = Object.keys( $e.components.activeComponents ).pop();

			assert.notEqual( activeComponent, namespace );
		} );

		QUnit.test( 'Run command with dependency', ( assert ) => {
			let commandStatus = 'beforeRun';

			const Component = class extends ComponentBase {
				getNamespace() {
					return 'run-command-with-dependency';
				}

				dependency() {
					return false;
				}

				defaultCommands() {
					return { commandA: () => commandStatus = 'afterRun' };
				}
			};

			$e.components.register( new Component() );

			const component = $e.components.get( 'run-command-with-dependency' );

			$e.run( component.getNamespace() + '/commandA' );

			assert.equal( commandStatus, 'beforeRun' );
		} );
	} );
} );
