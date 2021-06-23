import CommonHelper from 'elementor/tests/qunit/tests/core/common/helper';
import ComponentBase from 'elementor-api/modules/component-base';
import ComponentBaseModal from 'elementor-api/modules/component-modal-base';

jQuery( () => {
	QUnit.module( 'File: core/common/assets/js/api/core/components.js', ( hooks ) => {
		hooks.beforeEach( () => {
			$e.routes.clear();
		} );

		QUnit.test( 'Register Component', ( assert ) => {
			const namespace = 'register',
				Component = class extends ComponentBase {
					getNamespace() {
						return namespace;
					}
				},
				instance = new Component();

			$e.components.register( instance );

			assert.equal( $e.components.get( namespace ), instance );
		} );

		QUnit.test( 'Register routes', ( assert ) => {
			const namespace = 'register-routes';

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				defaultRoutes() {
					return {
						routeA: () => {},
						routeB: () => {},
					};
				}
			};

			$e.components.register( new Component() );

			const routes = $e.routes.getAll();

			assert.notEqual( routes.indexOf( namespace + '/routeA' ), -1 );
			assert.notEqual( routes.indexOf( namespace + '/routeB' ), -1 );
		} );

		QUnit.test( 'Register routes via tabs', ( assert ) => {
			const namespace = 'register-via-tabs',
				Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				defaultTabs() {
					return {
						tabA: { title: 'tabA' },
						tabB: { title: 'tabB' },
					};
				}
			};

			$e.components.register( new Component() );

			const component = $e.components.get( namespace );

			assert.equal( typeof $e.routes.commands[ component.getNamespace() + '/tabA' ], 'function' );
			assert.equal( typeof $e.routes.commands[ component.getNamespace() + '/tabB' ], 'function' );
		} );

		QUnit.test( 'Register without namespace', ( assert ) => {
			const Component = class extends ComponentBase {};

			assert.throws(
				() => {
					const instance = new Component();
					instance.getNamespace();
				},
				new Error( 'Component.getNamespace() should be implemented, please provide \'getNamespace\' functionality.' )
			);
		} );

		QUnit.test( 'Register commands', ( assert ) => {
			const Component = class extends ComponentBase {
				getNamespace() {
					return 'test-commands';
				}

				defaultCommands() {
					return {
						commandA: () => {},
					};
				}
			};

			$e.components.register( new Component() );

			const component = $e.components.get( 'test-commands' );

			assert.equal( typeof $e.commands.commands[ component.getNamespace() + '/commandA' ], 'function' );
		} );

		QUnit.test( 'Register shortcuts', ( assert ) => {
			const namespace = 'register-shortcuts';

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				defaultCommands() {
					return {
						commandA: () => {},
					};
				}

				defaultShortcuts() {
					return {
						commandA: {
							keys: 'ctrl+a',
						},
					};
				}
			};

			$e.components.register( new Component() );

			const handlers = $e.shortcuts.handlers[ 'ctrl+a' ],
				keys = Object.keys( handlers );

			assert.equal( handlers[ keys[ 0 ] ].command, namespace + '/commandA' );
		} );

		QUnit.test( 'Register shortcuts missing command', ( assert ) => {
			const Component = class extends ComponentBase {
				getNamespace() {
					return 'test-shortcuts-no-command';
				}

				defaultShortcuts() {
					return {
						notExistCommand: {
							keys: 'ctrl+a',
						},
					};
				}
			};

			$e.components.register( new Component() );

			const component = $e.components.get( 'test-shortcuts-no-command' ),
				handlers = $e.shortcuts.getAll();

			assert.equal( typeof handlers[ component.getNamespace() + '/notExistCommand' ], 'undefined' );
		} );
	} );
} );
