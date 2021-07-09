import CommandEditorBase from './command-editor-base';

export default class CommandEditorInternal extends CommandEditorBase {
	static getInstanceType() {
		return 'CommandEditorInternal';
	}

	constructor( args, commandsAPI = $e.commandsInternal ) {
		super( args, commandsAPI );
	}
}
