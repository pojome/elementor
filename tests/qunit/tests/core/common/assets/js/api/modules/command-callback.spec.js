import CommandInfra from 'elementor-api/modules/command-infra';
import CommandBase from 'elementor-api/modules/command-base';
import CommandInternalBase from 'elementor-api/modules/command-internal-base';
import CommandData from 'elementor-api/modules/command-data';
import CommandCallback from 'elementor-api/modules/command-callback';

jQuery( () => {
	QUnit.module( 'File: core/common/assets/js/api/modules/command-callback.js', () => {
		QUnit.module( 'CommandCallback', () => {
			QUnit.test( 'instanceOf(): validation', ( assert ) => {
				const commandCallback = new CommandCallback( { __manualConstructorHandling: true } );

				assert.equal( commandCallback instanceof CommandInfra, true );
				assert.equal( commandCallback instanceof CommandBase, true );
				assert.equal( commandCallback instanceof CommandInternalBase, false, );
				assert.equal( commandCallback instanceof CommandData, false, );
				assert.equal( commandCallback instanceof CommandCallback, true, );
				assert.equal( commandCallback instanceof $e.modules.CommandBase, true );
				assert.equal( commandCallback instanceof $e.modules.CommandInternalBase, false );
				assert.equal( commandCallback instanceof $e.modules.CommandData, false );
			} );

			// TODO: Add more tests. register a component with callback and validate it instance.
		} );
	} );
} );

