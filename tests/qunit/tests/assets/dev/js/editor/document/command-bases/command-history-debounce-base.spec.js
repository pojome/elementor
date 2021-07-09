import CommandHistoryBase from 'elementor-document/command-bases/command-history-base';
import CommandInfra from 'elementor-api/modules/command-infra';
import CommandBase from 'elementor-api/modules/command-base';
import CommandInternalBase from 'elementor-api/modules/command-internal-base';
import CommandData from 'elementor-api/modules/command-data';
import CommandHistoryDebounceBase from 'elementor-document/command-bases/command-history-debounce-base';
import CommandEditorBase from 'elementor-editor/command-bases/command-editor-base';
import CommandEditorInternal from 'elementor-editor/command-bases/command-editor-internal';

jQuery( () => {
	QUnit.module( 'File: editor/document/commands/command-history-debounce-base.js', () => {
		QUnit.module( 'CommandHistoryDebounceBase', () => {
			QUnit.test( 'instanceOf(): validation', ( assert ) => {
				const validateHistoryDebounceCommand = ( historyDebounceCommand ) => {
					// Base.
					assert.equal( historyDebounceCommand instanceof CommandInfra, true );
					assert.equal( historyDebounceCommand instanceof CommandBase, true );
					assert.equal( historyDebounceCommand instanceof CommandInternalBase, false, );
					assert.equal( historyDebounceCommand instanceof CommandData, false, );
					// Editor.
					assert.equal( historyDebounceCommand instanceof CommandEditorBase, true, );
					assert.equal( historyDebounceCommand instanceof CommandEditorInternal, false );
					// Editor-Document.
					assert.equal( historyDebounceCommand instanceof CommandHistoryBase, true );
					assert.equal( historyDebounceCommand instanceof CommandHistoryDebounceBase, true );

					// Base.
					assert.equal( historyDebounceCommand instanceof $e.modules.CommandBase, true );
					assert.equal( historyDebounceCommand instanceof $e.modules.CommandInternalBase, false );
					assert.equal( historyDebounceCommand instanceof $e.modules.CommandData, false );
					// Editor.
					assert.equal( historyDebounceCommand instanceof $e.modules.editor.CommandEditorBase, true );
					assert.equal( historyDebounceCommand instanceof $e.modules.editor.CommandEditorInternal, false );
					// Editor-Document.
					assert.equal( historyDebounceCommand instanceof $e.modules.editor.document.CommandHistoryBase, true );
					assert.equal( historyDebounceCommand instanceof $e.modules.editor.document.CommandHistoryDebounceBase, true );
				};

				validateHistoryDebounceCommand( new CommandHistoryDebounceBase( { __manualConstructorHandling: true } ) );
				validateHistoryDebounceCommand( new $e.modules.editor.document.CommandHistoryDebounceBase( { __manualConstructorHandling: true } ) );
			} );
		} );
	} );
} );
