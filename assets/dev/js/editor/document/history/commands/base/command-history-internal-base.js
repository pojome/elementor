import CommandEditorInternal from 'elementor-editor/command-bases/command-editor-internal';

export default class CommandHistoryInternalBase extends CommandEditorInternal {
	initialize( args ) {
		super.initialize( args );

		/**
		 * @type {HistoryManager}
		 */
		this.history = elementor.documents.getCurrent().history;
	}
}
