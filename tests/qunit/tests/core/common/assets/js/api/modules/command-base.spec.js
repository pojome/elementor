import CommandBase from 'elementor-api/modules/command-base';
import CommandInfra from 'elementor-api/modules/command-infra';
import CommandInternalBase from 'elementor-api/modules/command-internal-base';
import CommandData from 'elementor-api/modules/command-data';

jQuery( () => {
	QUnit.module( 'File: core/common/assets/js/api/modules/command-base.js', () => {
		QUnit.module( 'CommandBase', () => {
			QUnit.test( 'run(): on catch apply', ( assert ) => {
				const random = Math.random().toString();

				assert.throws(
					() => {
						const instance = new CommandBase( { __manualConstructorHandling: true } );

						instance.apply = () => {
							throw new Error( random );
						};

						instance.onCatchApply = ( e ) => {
							throw e;
						};

						instance.run( {} );
					},
					new Error( random )
				);
			} );

			QUnit.test( 'onCatchApply()', ( assert ) => {
				const random = Math.random().toString();

				assert.throws(
					() => {
						const instance = new CommandBase( { __manualConstructorHandling: true } );

						instance.apply = () => {
							throw new Error( random );
						};

						instance.run( {} );
					},
					new Error( random )
				);
			} );

			QUnit.test( 'instanceOf(): validation', ( assert ) => {
				const validateCommand = ( commandBase ) => {
					assert.equal( commandBase instanceof CommandInfra, true );
					assert.equal( commandBase instanceof CommandBase, true );
					assert.equal( commandBase instanceof CommandInternalBase, false, );
					assert.equal( commandBase instanceof CommandData, false, );
					assert.equal( commandBase instanceof $e.modules.CommandBase, true );
					assert.equal( commandBase instanceof $e.modules.CommandInternalBase, false );
					assert.equal( commandBase instanceof $e.modules.CommandData, false );
				};

				validateCommand( new CommandBase( { __manualConstructorHandling: true } ) );
				validateCommand( new $e.modules.CommandBase( { __manualConstructorHandling: true } ) );
			} );
		} );
	} );
} );

