import ElementsHelper from './elements/helper.js';

export default class DocumentHelper {
	static UICopyPaste( source, target ) {
		ElementsHelper.copy( source );

		elementor.channels.editor.reply( 'contextMenu:targetView', target.view );

		return $e.commands.runShortcut( 'document/ui/paste',
			jQuery.Event( 'keydown', { which: 86, ctrlKey: true, metaKey: true } )
		);
	}

	static autoCreate( elType ) {
		let result = null;

		switch ( elType ) {
			case 'document':
				result = elementor.getPreviewContainer();
				break;

			case 'section':
				result = ElementsHelper.createSection( 1 );
				break;

			case 'column':
				result = ElementsHelper.createSection( 1, true );
				break;

			case 'widget':
				result = ElementsHelper.createAutoButton();
				break;

			case 'innerSection':
				result = ElementsHelper.createInnerSection(
					ElementsHelper.createSection( 1, true )
				);
				break;
		}

		return result;
	}

	// TODO: this method should move to core/common.
	static testCommands( commands ) {
		Object.values( commands ).forEach( ( reference ) => reference() );
	}
}
