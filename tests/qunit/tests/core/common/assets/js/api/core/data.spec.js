import { CREATABLE, DELETABLE, EDITABLE, READABLE } from 'elementor-api/core/data';
import ComponentBase from 'elementor-api/modules/component-base';
import CommandData from 'elementor-api/modules/command-data';
import * as eData from 'elementor/tests/qunit/mock/e-data';

// Test cache module.
require( './data/cache.spec.js' );

// TODO: Each time creating component requires too many lines of code use helper.
jQuery( () => {
	QUnit.module( 'File: core/common/assets/js/api/core/data.js', ( hooks ) => {
		hooks.beforeEach( () => {
			$e.data.cache.storage.clear();
		} );

		QUnit.test( 'getHTTPMethod(): validate all options', ( assert ) => {
			assert.equal( $e.data.getHTTPMethod( 'create' ), 'POST' );
			assert.equal( $e.data.getHTTPMethod( 'delete' ), 'DELETE' );
			assert.equal( $e.data.getHTTPMethod( 'get' ), 'GET' );
			assert.equal( $e.data.getHTTPMethod( 'update' ), 'PUT' );
		} );

		QUnit.test( 'getAllowedMethods(): validate all options', ( assert ) => {
			assert.deepEqual( $e.data.getAllowedMethods( 'create' ), CREATABLE );
			assert.deepEqual( $e.data.getAllowedMethods( 'delete' ), DELETABLE );
			assert.deepEqual( $e.data.getAllowedMethods( 'get' ), READABLE );
			assert.deepEqual( $e.data.getAllowedMethods( 'update' ), EDITABLE );
		} );

		QUnit.test( 'commandToEndpoint(): command with query', ( assert ) => {
			const command = 'component/command',
				args = { query: {} };

			args.query.paramA = 'valueA';
			args.query.paramB = 'valueB';

			const endpoint = $e.data.commandToEndpoint( command, args );

			assert.equal( endpoint, 'component/command?paramA=valueA&paramB=valueB', 'Valid endpoint.' );
		} );

		QUnit.test( 'commandToEndpoint(): command with magic params', ( assert ) => {
			const command = 'component/command',
				format = 'component/{paramA}/{paramB}/command',
				args = { query: {} };

			args.query.paramA = 'valueA';
			args.query.paramB = 'valueB';

			const endpoint = $e.data.commandToEndpoint( command, args, format );

			assert.equal( endpoint, 'component/valueA/valueB/command', 'Valid endpoint.' );
		} );

		QUnit.test( 'commandToEndpoint(): command with magic params and one *first* parameter missing', ( assert ) => {
			const command = 'component/command',
				format = 'component/{paramA}/command/{paramB}',
				args = { query: {} };

			args.query.paramB = 'valueB';

			const endpoint = $e.data.commandToEndpoint( command, args, format );

			assert.equal( endpoint, 'component', 'Valid endpoint.' );
		} );

		QUnit.test( 'commandToEndpoint(): command with magic params and one *second* parameter missing', ( assert ) => {
			const command = 'component/command',
				format = 'component/{paramA}/command/{paramB}',
				args = { query: {} };

			args.query.paramA = 'valueA';

			const endpoint = $e.data.commandToEndpoint( command, args, format );

			assert.equal( endpoint, 'component/valueA/command', 'Valid endpoint.' );
		} );

		QUnit.test( 'commandToEndpoint(): command with magic params and one *format* parameter missing', ( assert ) => {
			const command = 'component/command',
				format = 'component/command/{paramA}',
				args = { query: {} };

			args.query.paramA = 'valueA';
			args.query.paramB = 'valueB';

			const endpoint = $e.data.commandToEndpoint( command, args, format );

			assert.equal( endpoint, 'component/command/valueA?paramB=valueB', 'Valid endpoint.' );
		} );

		QUnit.test( 'commandToEndpoint(): with query params that has "/" should replaced to encoded version', ( assert ) => {
			const command = 'component/command',
				format = 'component/command',
				args = { query: {} };

			args.query.paramA = 'a/b';
			args.query.paramB = 'c/d';

			const endpoint = $e.data.commandToEndpoint( command, args, format );

			assert.equal( endpoint, 'component/command?paramA=a%2Fb&paramB=c%2Fd', 'Valid endpoint.' );
		} );

		QUnit.test( 'commandExtractArgs(): simple', ( assert ) => {
			const queryCommand = 'component/command?paramA=valueA',
				args = {},
				extractedCommand = $e.data.commandExtractArgs( queryCommand, args );

			assert.equal( extractedCommand.command, 'component/command', 'Valid pure command.' );
			assert.deepEqual( extractedCommand.args.query, { paramA: 'valueA' }, 'Valid args.query.' );
		} );

		QUnit.test( 'commandExtractArgs(): merged', ( assert ) => {
			const queryCommand = 'component/command?paramA=valueA',
				args = { merge: true },
				extractedCommand = $e.data.commandExtractArgs( queryCommand, args );

			assert.equal( extractedCommand.command, 'component/command', 'Valid pure command.' );
			assert.deepEqual( extractedCommand.args.query, { paramA: 'valueA' }, 'Valid args.query.' );
			assert.deepEqual( extractedCommand.args.merge, true, 'Valid args merge.' );
		} );

		QUnit.test( 'validateRequestData', ( assert ) => {
			assert.throws( () => {
					$e.data.validateRequestData( {} );
				},
				new Error( 'component is required.' )
			);
			assert.throws( () => {
					$e.data.validateRequestData( {
						component: {},
					} );
				},
				new Error( 'command is required.' )
			);
			assert.throws( () => {
					$e.data.validateRequestData( {
						component: {},
						command: '',
					} );
				},
				new Error( 'endpoint is required.' )
			);
		} );

		QUnit.test( 'prepareHeaders(): with GET', ( assert ) => {
			const requestData = {
					paramA: 'valueA',
					type: 'get',
				},
				params = $e.data.prepareHeaders( requestData );

			assert.equal( params.headers[ 'X-WP-Nonce' ], wpApiSettings.nonce );
		} );

		QUnit.test( 'prepareHeaders(): with POST', ( assert ) => {
			const requestData = {
					paramA: 'valueA',
					type: 'create',
					args: {
						data: { paramA: 'valueA' },
					},
				},
				params = $e.data.prepareHeaders( requestData );

			assert.equal( params.body, '{"paramA":"valueA"}' );
		} );

		QUnit.test( 'prepareHeaders(): with invalid type', ( assert ) => {
			const type = 'some-invalid-type';

			assert.throws( () => $e.data.prepareHeaders( { type } ),
				new Error( `Invalid type: '${ type }'` )
			);
		} );

		QUnit.test( 'prepareHeaders(): post without data', ( assert ) => {
			const requestData = {
					paramA: 'valueA',
					type: 'create',
				};

			assert.throws( () => $e.data.prepareHeaders( requestData ),
				new Error( `Invalid requestData.args.data` )
			);
		} );

		QUnit.test( 'prepareEndpoint(): basic endpoint should not be changed ', ( assert ) => {
			const { version, namespace } = $e.data.args,
				endpoint = 'component/command?a=1&b=2',
				result = $e.data.prepareEndpoint( { endpoint } ),
				expected = `${ $e.data.args.baseEndpointAddress }${ namespace }/v${ version }/${ endpoint }`;

			assert.equal( result, expected );
		} );

		QUnit.test( 'prepareEndpoint(): permalinks in plain mode ', ( assert ) => {
			const baseEndpointAddressOrig = $e.data.args.baseEndpointAddress;

			$e.data.args.baseEndpointAddress = 'https://example.com/index.php?route=/';

			const { version, namespace } = $e.data.args,
				endpoint = 'component/command?a=1&b=2',
				result = $e.data.prepareEndpoint( { endpoint } ),
				expectedPrefix = `${ $e.data.args.baseEndpointAddress }${ namespace }/v${ version }/`;

			assert.equal( result, expectedPrefix + 'component/command&a=1&b=2' );

			$e.data.args.baseEndpointAddress = baseEndpointAddressOrig;
		} );

		/**
		 * FETCH, tests: is not mean to test if the *cache* mechanism is working correctly, but
		 * to test *fetch* with different approaches.
		 */
		QUnit.test( 'fetch(): simple', async ( assert ) => {
			const component = $e.components.register( new class TestComponent extends ComponentBase {
					getNamespace() {
						return 'test-component-fetch-simple';
					}

					defaultData() {
						return this.importCommands( {
							TestCommand: class TestCommand extends CommandData {
							},
						} );
					}
				} ),
				command = 'test-component-fetch-simple/test-command',
				args = {
					options: {
						refresh: true,
					},
					query: {
						param1: 'valueA',
					},
				},
				endpoint = $e.data.commandToEndpoint( command, args ),
				requestData = {
					type: 'get',
					endpoint,
					component,
					command,
					args,
				};

			eData.restoreFetch();

			await $e.data.fetch( requestData, ( input ) => {
				assert.equal( input, $e.data.getEndpointAddress( requestData, command + '?param1=valueA' ) );
				return Promise.resolve( new Response( JSON.stringify( {} ) ) );
			} );
		} );

		QUnit.test( 'fetch(): with cache', async ( assert ) => {
			eData.restoreFetch();

			const component = $e.components.register( new class TestComponent extends ComponentBase {
					getNamespace() {
						return 'test-component-fetch-cache';
					}

					defaultData() {
						return this.importCommands( {
							TestCommand: class TestCommand extends CommandData {
							},
						} );
					}
				} ),
				fakeResponse = { test: true },
				command = 'test-component-fetch-cache/test-command',
				args = {
					query: {
						param1: 'valueA',
					},
				},
				endpoint = $e.data.commandToEndpoint( command, args ),
				requestData = {
					type: 'get',
					endpoint,
					component,
					command,
					args,
				},
				result = await $e.data.fetch( requestData, ( input ) => {
					assert.equal( input, $e.data.getEndpointAddress( requestData, command + '?param1=valueA' ) );
					return Promise.resolve( new Response( JSON.stringify( fakeResponse ) ) );
				} );

			assert.deepEqual( result, fakeResponse );

			// Validate cache.
			assert.deepEqual( $e.data.cache.get( requestData ), result );
		} );

		QUnit.test( 'fetch(): with cache loaded manually', async ( assert ) => {
			// Register test data command.
			const component = $e.components.register( new class TestComponent extends ComponentBase {
					getNamespace() {
						return 'test-component-cache-manually';
					}

					defaultData() {
						return this.importCommands( {
							TestCommand: class TestCommand extends CommandData {
							},
						} );
					}
				} ),
				command = 'test-component-cache-manually/test-command',
				data = { someProp: 'someValue' },
				query = { paramA: 'valueB' },
				requestData = {
					type: 'get',
					component,
					command,
					endpoint: $e.data.commandToEndpoint( command, { query } ),
					args: { query },
				};

			// This test case relies on cache.
			$e.data.setCache( component, command, query, data );

			eData.attachCache();

			// Get cache.
			const result = await $e.data.fetch( requestData );

			// Validate if data is same as result.data.
			assert.deepEqual( data, result );
		} );

		QUnit.test( 'getCache(): simple', ( assert ) => {
			const component = $e.components.register( new class TestComponent extends ComponentBase {
					getNamespace() {
						return 'get-cache-simple-component';
					}
				} ),
				requestData = {
					endpoint: $e.data.commandToEndpoint( component.getNamespace(), {} ),
					command: component.getNamespace(),
					component: component,
				},
				someData = {
					someKey: 'someValue',
				};

			$e.data.cache.set( requestData, someData );

			const result = $e.data.getCache( component, component.getNamespace() );

			assert.deepEqual( result, someData );
		} );

		QUnit.test( 'getCache(): with query', ( assert ) => {
			const component = $e.components.register( new class TestComponent extends ComponentBase {
					getNamespace() {
						return 'get-cache-query-component';
					}
				} ),
				args = {
					query: {
						paramA: 'valueA',
					},
				},
				requestData = {
					endpoint: $e.data.commandToEndpoint( component.getNamespace(), args ),
					command: component.getNamespace(),
					component: component,
					args,
				},
				someData = {
					someKey: 'someValue',
				};

			$e.data.cache.set( requestData, someData );

			const result = $e.data.getCache( component, component.getNamespace(), args.query );

			assert.deepEqual( result, someData );
		} );

		QUnit.test( 'setCache(): with simple data', ( assert ) => {
			const component = $e.components.register( new class TestComponent extends ComponentBase {
					getNamespace() {
						return 'load-cache-simple-component';
					}
				} ),
				someData = {
					someKey: 'someValue',
				};

			$e.data.setCache( component, component.getNamespace(), {}, someData );

			const result = $e.data.getCache( component, component.getNamespace() );

			assert.deepEqual( result, someData );
		} );

		QUnit.test( 'setCache(): with query', ( assert ) => {
			const component = $e.components.register( new class TestComponent extends ComponentBase {
					getNamespace() {
						return 'load-cache-query-component';
					}
				} ),
				query = {
					param: 'value',
				},
				someData = {
					someKey: 'someValue',
				};

			$e.data.setCache( component, component.getNamespace(), query, someData );

			const result = $e.data.getCache( component, component.getNamespace(), query );

			assert.deepEqual( result, someData );
		} );

		QUnit.test( 'updateCache(): component', ( assert ) => {
			const component = $e.components.register( new class TestComponent extends ComponentBase {
					getNamespace() {
						return 'update-cache-component';
					}
				} ),
				olData = {
					param: 'oldValue',
				},
				newData = {
					param: 'new-value',
				};

			$e.data.setCache( component, component.getNamespace(), {}, olData );

			let result = $e.data.getCache( component, component.getNamespace() );

			assert.deepEqual( result, olData );

			$e.data.updateCache( component, component.getNamespace(), {}, newData );

			result = $e.data.getCache( component, component.getNamespace() );

			assert.deepEqual( result, newData );
		} );

		QUnit.test( 'updateCache(): specific data, by endpoint', ( assert ) => {
			const component = $e.components.register( new class TestComponent extends ComponentBase {
					getNamespace() {
						return 'update-cache-specific-data-endpoint-component';
					}
				} ),
				olData = {
					objectA: {
						paramA: 'valueA',
					},
				},
				change = {
					paramA: 'new-value',
				},
				newData = {
					objectA: change,
				};

			$e.data.setCache( component, component.getNamespace(), {}, olData );

			let result = $e.data.getCache( component, component.getNamespace() );

			assert.deepEqual( result, olData );

			$e.data.updateCache( component, component.getNamespace() + '/objectA', {}, change );

			result = $e.data.getCache( component, component.getNamespace() );

			assert.deepEqual( result, newData );
		} );

		QUnit.test( 'updateCache(): specific data, by data object', ( assert ) => {
			const component = $e.components.register( new class TestComponent extends ComponentBase {
					getNamespace() {
						return 'update-cache-specific-data-object-component';
					}
				} ),
				olData = {
					objectA: {
						paramA: 'valueA',
					},
				},
				change = {
					objectA: {
						paramA: 'new-value',
					},
				};

			$e.data.setCache( component, component.getNamespace(), {}, olData );

			let result = $e.data.getCache( component, component.getNamespace() );

			assert.deepEqual( result, olData );

			$e.data.updateCache( component, component.getNamespace(), {}, change );

			result = $e.data.getCache( component, component.getNamespace() );

			assert.deepEqual( result, change );
		} );

		QUnit.test( 'deleteCache(): simple', ( assert ) => {
			const component = $e.components.register( new class TestComponent extends ComponentBase {
				getNamespace() {
					return 'delete-cache-simple-component';
				}
			} );

			$e.data.setCache( component, component.getNamespace(), {}, {} );

			$e.data.deleteCache( component, component.getNamespace(), {} );

			assert.equal( $e.data.getCache( component, component.getNamespace() ), null );
		} );
	} );
} );
