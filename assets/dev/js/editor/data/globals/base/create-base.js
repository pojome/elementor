import CommandEditorBase from 'elementor-editor/command-bases/command-editor-base';

export default class CreateBase extends CommandEditorBase {
	validateArgs( args = {} ) {
		this.requireContainer( args );
		this.requireArgumentType( 'setting', 'string', args );
		this.requireArgumentType( 'title', 'string', args );
	}
}
