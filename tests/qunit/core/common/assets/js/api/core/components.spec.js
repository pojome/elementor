import CommonHelper from 'elementor-tests-qunit/core/common/helper';
import ComponentBase from 'elementor-api/modules/component-base';
import ComponentBaseModal from 'elementor-api/modules/component-modal-base';

/**
 * TODO: Part to files same as core files ( mirrored ).
 */
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

		QUnit.module( 'Commands' );

		QUnit.test( 'Error on register command without component', ( assert ) => {
			assert.throws(
				() => {
					$e.commands.register( '', 'save', () => {} );
				},
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
					return {
						commandA: () => {},
					};
				}
			};

			$e.components.register( new Component() );

			assert.throws(
				() => {
					$e.commands.register( namespace, 'commandA', () => {} );
				},
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
					return {
						commandA: () => {},
					};
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
					return {
						routeA: () => {},
					};
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
					return {
						commandA: () => {},
					};
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
					return {
						commandA: () => commandStatus = 'afterRun',
					};
				}
			};

			$e.components.register( new Component() );

			const component = $e.components.get( 'run-command-with-dependency' );

			$e.run( component.getNamespace() + '/commandA' );

			assert.equal( commandStatus, 'beforeRun' );
		} );

		QUnit.module( 'Routes' );

		QUnit.test( 'Error on register route without component', ( assert ) => {
			assert.throws(
				() => {
					$e.routes.register( '', 'panel', () => {} );
				},
				new Error( "Routes: '' component is not exist." )
			);
		} );

		QUnit.test( 'Error on run non exited command', ( assert ) => {
			assert.throws(
				() => {
					$e.route( 'not-existing-route' );
				},
				new Error( 'Routes: `not-existing-route` not found.' )
			);
		} );

		QUnit.test( 'Route to, is, isPartOf', ( assert ) => {
			const namespace = 'route-to',
				routeA = namespace + '/routeA',
				routeB = namespace + '/routeB';

			let routeAStatus = 'beforeRouteA',
				routeBStatus = 'beforeRouteA';

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				defaultRoutes() {
					return {
						routeA: () => {
							routeAStatus = 'afterRoute';
						},
						routeB: () => {
							routeBStatus = 'afterRoute';
						},
					};
				}
			};

			$e.components.register( new Component() );

			$e.route( routeA );

			assert.equal( $e.routes.is( routeA ), true );
			assert.equal( $e.routes.is( routeB ), false );
			assert.equal( routeAStatus, 'afterRoute' );

			$e.route( routeB );

			assert.equal( $e.routes.is( routeB ), true );
			assert.equal( $e.routes.is( routeA ), false );
			assert.equal( routeBStatus, 'afterRoute' );

			assert.equal( $e.routes.isPartOf( namespace ), true );
			assert.equal( $e.routes.isPartOf( 'notPartOf' ), false );
		} );

		QUnit.test( 'Route with args', ( assert ) => {
			assert.expect( 4 );

			const namespace = 'route-with-args',
				routeA = namespace + '/routeA';

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				defaultRoutes() {
					return {
						routeA: ( args ) => {
							assert.equal( args.argA, 1 );
						},
					};
				}
			};

			$e.components.register( new Component() );

			const args = {
				argA: 1,
			};

			$e.route( namespace + '/routeA', args );

			assert.equal( $e.routes.is( namespace + '/routeA', args ), true );
			assert.equal( $e.routes.is( routeA ), false );

			assert.equal( $e.routes.getCurrentArgs( namespace ), args );
		} );

		QUnit.test( 'Route with events args', ( assert ) => {
			const namespace = 'route-with-events-args';
			let onBeforeStatus = 'beforeRoute',
				onAfterStatus = 'beforeRoute';

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				defaultRoutes() {
					return {
						routeA: () => {},
					};
				}
			};

			$e.components.register( new Component() );

			$e.route( namespace + '/routeA', {
				onBefore: () => {
					onBeforeStatus = 'afterRoute';
				},
				onAfter: () => {
					onAfterStatus = 'afterRoute';
				},
			} );

			assert.equal( onBeforeStatus, 'afterRoute' );
			assert.equal( onAfterStatus, 'afterRoute' );
		} );

		QUnit.test( 'Route to tab & activate tab', ( assert ) => {
			const namespace = 'route-to-tab';

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				defaultTabs() {
					return {
						tabA: { title: 'tabA' },
						tabB: { title: 'tabB' },
					};
				}

				getTabsWrapperSelector() {
					return '#' + namespace;
				}
			};

			$e.components.register( new Component() );

			const $fixture = jQuery(
				'<div id="' + namespace + '">' +
				'<div class="elementor-component-tab" data-tab="tabA"></div>' +
				'<div class="elementor-component-tab" data-tab="tabB"></div>' +
				'</div>'
			);

			jQuery( 'body' ).append( $fixture );

			$e.route( namespace + '/tabA' );

			assert.equal( $fixture.find( '.elementor-active' ).data( 'tab' ), 'tabA' );

			$e.route( namespace + '/tabB' );

			assert.equal( $fixture.find( '.elementor-active' ).data( 'tab' ), 'tabB' );

			$fixture.remove();
		} );

		QUnit.test( 'Add tab', ( assert ) => {
			const namespace = 'add-tab';

			const Component = class extends ComponentBase {
				__construct( args ) {
					super.__construct( args );

					this.tabs = {
						tabA: { title: 'tabA' },
						tabC: { title: 'tabC' },
					};
				}

				getNamespace() {
					return namespace;
				}

				getTabsWrapperSelector() {
					return '#' + namespace;
				}
			};

			$e.components.register( new Component() );

			const component = $e.components.get( namespace ),
				newTabIndex = 1;

			component.addTab( 'tabB', {}, newTabIndex );

			const $fixture = jQuery(
				'<div id="' + namespace + '"></div>'
			);

			jQuery.each( component.getTabs(), ( tab ) => {
				$fixture.append( '<div class="elementor-component-tab" data-tab="' + tab + '"></div>' );
			} );

			jQuery( 'body' ).append( $fixture );

			$e.route( namespace + '/tabB' );

			assert.equal( $fixture.find( '.elementor-active' ).data( 'tab' ), 'tabB' );

			assert.equal( $fixture.find( '[data-tab=tabB]' ).index(), newTabIndex );

			$fixture.remove();
		} );

		QUnit.test( 'Check if route.to is activate the component', ( assert ) => {
			const namespace = 'route-activate-component';

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				defaultRoutes() {
					return {
						routeA: () => {},
					};
				}
			};

			$e.components.register( new Component() );

			$e.route( namespace + '/routeA' );

			const activeComponent = Object.keys( $e.components.activeComponents ).pop();

			assert.equal( activeComponent, namespace );
		} );

		QUnit.test( 'Route with dependency', ( assert ) => {
			const namespace = 'route-with-dependency';

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				dependency() {
					return false;
				}

				defaultRoutes() {
					return {
						routeA: () => {},
					};
				}
			};

			$e.components.register( new Component() );

			$e.route( namespace + '/routeA' );

			assert.equal( $e.routes.is( namespace + '/routeA' ), false );
		} );

		QUnit.test( 'Re-route is avoided', ( assert ) => {
			const namespace = 're-route-is-avoided';

			let routeCount = 0;

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				defaultRoutes() {
					return {
						routeA: () => {
							routeCount++;
						},
					};
				}
			};

			$e.components.register( new Component() );

			$e.route( namespace + '/routeA' );
			assert.equal( routeCount, 1 );
			$e.route( namespace + '/routeA' );
			assert.equal( routeCount, 1 );
		} );

		QUnit.test( 'Open component dependency', ( assert ) => {
			const namespace = 'open-component-dependency';

			let openCount = 0;

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				open() {
					openCount++;
					return false;
				}

				defaultRoutes() {
					return {
						routeA: () => {},
					};
				}
			};

			$e.components.register( new Component() );

			$e.route( namespace + '/routeA' );

			assert.equal( openCount, 1 );

			assert.equal( $e.routes.is( namespace + '/routeA' ), false );
		} );

		QUnit.test( 'Re-open component is avoided', ( assert ) => {
			const namespace = 're-open-is-avoided';

			let openCount = 0;

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				open() {
					openCount++;
					return true;
				}

				defaultRoutes() {
					return {
						routeA: () => {},
						routeB: () => {},
					};
				}
			};

			$e.components.register( new Component() );

			$e.route( namespace + '/routeA' );
			assert.equal( openCount, 1 );
			$e.route( namespace + '/routeB' );
			assert.equal( openCount, 1 );

			$e.components.get( namespace ).close();

			$e.route( namespace + '/routeA' );
			assert.equal( openCount, 2 );
		} );

		QUnit.test( 'On close route', ( assert ) => {
			const namespace = 'on-close-route';

			let routeStatus = 'notChanged';

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				onCloseRoute() {
					routeStatus = 'closed';
				}

				defaultRoutes() {
					return {
						routeA: () => {},
						routeB: () => {},
					};
				}
			};

			$e.components.register( new Component() );

			$e.route( namespace + '/routeA' );
			assert.equal( routeStatus, 'notChanged' );

			$e.route( namespace + '/routeB' );
			assert.equal( routeStatus, 'closed' );
		} );

		QUnit.test( 'On route', ( assert ) => {
			const namespace = 'on-route';

			let routeStatus = 'beforeRoute';

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				onRoute() {
					routeStatus = 'afterRoute';
				}

				defaultRoutes() {
					return {
						routeA: () => {},
					};
				}
			};

			$e.components.register( new Component() );

			$e.route( namespace + '/routeA' );
			assert.equal( routeStatus, 'afterRoute' );
		} );

		QUnit.test( 'State: save & restore', ( assert ) => {
			assert.expect( 6 ); // `restoreState` is expected to run `routeA` again.

			const namespace = 'state-save-restore';

			const routeArgs = {
				argsA: 1,
			};

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				defaultRoutes() {
					return {
						routeA: ( args ) => {
							assert.equal( args, routeArgs );
						},
					};
				}
			};

			$e.components.register( new Component() );

			$e.route( namespace + '/routeA', routeArgs );
			$e.routes.saveState( namespace );
			$e.components.get( namespace ).close();

			assert.equal( $e.routes.getCurrent( namespace ), false );
			assert.equal( $e.routes.getCurrentArgs( namespace ), false );

			$e.routes.restoreState( namespace );

			assert.equal( $e.routes.getCurrent( namespace ), namespace + '/routeA' );
			assert.equal( $e.routes.getCurrentArgs( namespace ), routeArgs );
		} );

		QUnit.test( 'Refresh container', ( assert ) => {
			assert.expect( 2 ); // `refreshContainer` is expected to run `routeA` again.

			const namespace = 'refresh-container';

			const routeArgs = {
				argsA: 1,
			};

			const Component = class extends ComponentBase {
				getNamespace() {
					return namespace;
				}

				defaultRoutes() {
					return {
						routeA: ( args ) => {
							assert.equal( args, routeArgs );
						},
					};
				}
			};

			$e.components.register( new Component() );

			$e.route( namespace + '/routeA', routeArgs );

			$e.routes.refreshContainer( namespace );
		} );

		QUnit.module( 'Shortcuts', ( _hooks ) => {
			const originalShortcuts = $e.shortcuts.handlers;

			_hooks.beforeEach( () => {
				$e.shortcuts.handlers = {};
			} );

			_hooks.afterEach( () => {
				$e.shortcuts.handlers = originalShortcuts;
			} );

			QUnit.test( 'Run shortcut', ( assert ) => {
				const namespace = 'run-shortcut';

				let commandStatus = 'beforeRun';

				const Component = class extends ComponentBase {
					getNamespace() {
						return namespace;
					}

					defaultCommands() {
						return {
							commandA: () => {
								commandStatus = 'afterRun';
							},
						};
					}

					defaultShortcuts() {
						return {
							commandA: {
								keys: 'ctrl+z',
							},
						};
					}
				};

				$e.components.register( new Component() );

				// Simulate `CTRL+Z`.
				CommonHelper.runShortcut( 90 /* z */, true );

				assert.equal( commandStatus, 'afterRun' );
			} );

			QUnit.test( 'Run shortcut with scope', ( assert ) => {
				const namespace = 'run-shortcut-with-scope';

				let commandStatus = 'beforeRunInScope';

				const Component = class extends ComponentBase {
					getNamespace() {
						return namespace;
					}

					defaultCommands() {
						return {
							commandA: () => {
								commandStatus = 'afterRunInScope';
							},
						};
					}

					defaultRoutes() {
						return {
							routeA: () => {},
						};
					}

					defaultShortcuts() {
						return {
							commandA: {
								keys: 'ctrl+z',
								scopes: [ namespace ],
							},
						};
					}
				};

				$e.components.register( new Component() );

				// Outside scope.
				CommonHelper.runShortcut( 90 /* z */, true );

				assert.equal( commandStatus, 'beforeRunInScope', 'Shortcut not ran outside scope' );

				// Inside scope.
				$e.route( namespace + '/routeA' );

				CommonHelper.runShortcut( 90 /* z */, true );

				assert.equal( commandStatus, 'afterRunInScope', 'Shortcut ran inside scope' );

				// Closed scope.
				$e.components.get( namespace ).close();

				commandStatus = 'beforeRunInScope';

				CommonHelper.runShortcut( 90 /* z */, true );

				assert.equal( commandStatus, 'beforeRunInScope', 'Shortcut not ran after close scope' );

				// Second component with same shortcut.
				let secondCommandStatus = 'beforeRun';

				const SecondComponent = class extends ComponentBase {
					getNamespace() {
						return 'second-' + namespace;
					}

					defaultCommands() {
						return {
							commandA: () => {
								secondCommandStatus = 'afterRun';
							},
						};
					}

					defaultShortcuts() {
						return {
							commandA: {
								keys: 'ctrl+z',
							},
						};
					}
				};

				$e.components.register( new SecondComponent() );

				// Activate the first component.
				$e.route( namespace + '/routeA' );

				CommonHelper.runShortcut( 90 /* z */, true );

				assert.equal( secondCommandStatus, 'beforeRun', 'Shortcut with global scope not ran because of low priority' );

				// Close the first component.
				$e.components.get( namespace ).close();

				CommonHelper.runShortcut( 90 /* z */, true );

				assert.equal( secondCommandStatus, 'afterRun', 'Shortcut with global scope ran because the scoped shortcut is closed' );
			} );

			QUnit.test( 'Modal component without a modal layout', ( assert ) => {
				const namespace = 'modal-component-without-a-modal-layout';

				const Component = class extends ComponentBaseModal {
					getNamespace() {
						return namespace;
					}
				};

				const instance = new Component();

				assert.throws(
					() => {
						instance.getModalLayout();
					},
					new Error( "Component.getModalLayout() should be implemented, please provide 'getModalLayout' functionality." )
				);
			} );

			QUnit.test( 'Modal component with esc shortcut', ( assert ) => {
				const namespace = 'modal-component-with-esc-shortcut';

				const Component = class extends ComponentBaseModal {
					getNamespace() {
						return namespace;
					}

					getModalLayout() {
						const layout = class extends elementorCommon.views.modal.Layout {
							initialize() { /* do not render */ }
						};

						return layout;
					}
				};

				$e.components.register( new Component() );

				$e.route( namespace );

				CommonHelper.runShortcut( 27 /* esc */ );

				assert.equal( $e.routes.is( namespace ), false, 'Component is closed by `esc` key.' );

				// Second component.
				const secondNamespace = 'second-' + namespace;

				const SecondComponent = class extends ComponentBaseModal {
					getNamespace() {
						return secondNamespace;
					}

					getModalLayout() {
						const layout = class extends elementorCommon.views.modal.Layout {
							initialize() { /* do not render */ }
						};

						return layout;
					}
				};

				$e.components.register( new SecondComponent() );

				const component = $e.components.get( namespace ),
					secondComponent = $e.components.get( secondNamespace );

				// Activate the second component.
				$e.route( secondNamespace );

				// Activate the first component.
				$e.route( namespace );

				// Ensure tow components are open.
				assert.equal( component.isOpen, true );
				assert.equal( secondComponent.isOpen, true );

				CommonHelper.runShortcut( 27 /* esc */ );

				// Modals should be closed in LIFO order.
				assert.equal( component.isOpen, false, 'First Component is closed first' );
				assert.equal( secondComponent.isOpen, true );

				CommonHelper.runShortcut( 27 /* esc */ );

				assert.equal( secondComponent.isOpen, false, 'Second Component is closed too' );
			} );
		} );
	} );
} );
