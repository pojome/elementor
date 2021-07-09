import CommandInfra from 'elementor-api/modules/command-infra';
import CommandBase from 'elementor-api/modules/command-base';
import CommandInternalBase from 'elementor-api/modules/command-internal-base';
import CommandData from 'elementor-api/modules/command-data';

jQuery( () => {
	QUnit.module( 'File: core/common/assets/js/api/modules/command-infra.js', () => {
		QUnit.module( 'CommandInfra', () => {
			QUnit.test( 'constructor(): Doing it wrong', ( assert ) => {
				assert.throws(
					() => new CommandInfra( {} ),
					new RangeError( 'Doing it wrong: Each command type should have `registerConfig`.' )
				);
			} );

			QUnit.test( 'apply(): force method implementation', ( assert ) => {
				assert.throws(
					() => {
						const instance = new CommandInfra( { __manualConstructorHandling: true } );

						instance.apply( {} );
					},
					new Error( 'CommandInfra.apply() should be implemented, please provide \'apply\' functionality.' )
				);
			} );

			QUnit.test( 'instanceOf(): validation', ( assert ) => {
				const commandInfra = new CommandInfra( { __manualConstructorHandling: true } );

				assert.equal( commandInfra instanceof CommandInfra, true );
				assert.equal( commandInfra instanceof CommandBase, false );
				assert.equal( commandInfra instanceof CommandInternalBase, false, );
				assert.equal( commandInfra instanceof CommandData, false, );
				assert.equal( commandInfra instanceof $e.modules.CommandBase, false );
				assert.equal( commandInfra instanceof $e.modules.CommandInternalBase, false );
				assert.equal( commandInfra instanceof $e.modules.CommandData, false );
			} );
		} );
	} );
} );

