import CommandInfra from 'elementor-api/modules/command-infra';
import CommandBase from 'elementor-api/modules/command-base';
import CommandInternalBase from 'elementor-api/modules/command-internal-base';
import CommandData from 'elementor-api/modules/command-data';
import CommandEditorBase from 'elementor-editor/command-bases/command-editor-base';
import CommandEditorInternal from 'elementor-editor/command-bases/command-editor-internal';

jQuery( () => {
	QUnit.module( 'File: assets/dev/js/editor/base/command-editor-base.js', () => {
		QUnit.module( 'CommandEditorBase', () => {
			QUnit.test( 'constructor(): without containers', ( assert ) => {
				assert.throws(
					() => {
						const instance = new CommandEditorBase( { __manualConstructorHandling: true } );

						instance.requireContainer();
					},
					new Error( 'container or containers are required.' )
				);
			} );

			QUnit.test( 'constructor(): with container & containers', ( assert ) => {
				assert.throws(
					() => {
						const instance = new CommandEditorBase( {
							__manualConstructorHandling: true,
							container: {},
							containers: [],
						} );

						instance.requireContainer();
					},
					new Error( 'container and containers cannot go together please select one of them.' )
				);
			} );

			QUnit.test( 'instanceOf(): validation', ( assert ) => {
				const validateCommandEditor = ( commandEditor ) => {
					// Base.
					assert.equal( commandEditor instanceof CommandInfra, true );
					assert.equal( commandEditor instanceof CommandBase, true );
					assert.equal( commandEditor instanceof CommandInternalBase, false, );
					assert.equal( commandEditor instanceof CommandData, false, );
					// Editor.
					assert.equal( commandEditor instanceof CommandEditorBase, true, );
					assert.equal( commandEditor instanceof CommandEditorInternal, false );

					// Base.
					assert.equal( commandEditor instanceof $e.modules.CommandBase, true );
					assert.equal( commandEditor instanceof $e.modules.CommandInternalBase, false );
					assert.equal( commandEditor instanceof $e.modules.CommandData, false );
					// Editor.
					assert.equal( commandEditor instanceof $e.modules.editor.CommandEditorBase, true );
					assert.equal( commandEditor instanceof $e.modules.editor.CommandEditorInternal, false );
				};

				validateCommandEditor( new CommandEditorBase( { __manualConstructorHandling: true } ) );
				validateCommandEditor( new $e.modules.editor.CommandEditorBase( { __manualConstructorHandling: true } ) );
			} );
		} );
	} );
} );

