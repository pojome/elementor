import CommandBase from 'elementor-api/modules/command-base';
import { removeNamespaceHandler } from 'elementor/modules/elements-color-picker/assets/js/editor/utils';

export class End extends CommandBase {
	apply() {
		// Remove all elements & event listeners.
		elementor.$previewContents[ 0 ].querySelector( 'body' ).classList.remove( 'elementor-editor__ui-state__color-picker' );

		elementor.$previewContents[ 0 ].querySelectorAll( '.e-element-color-picker' ).forEach( ( picker ) => {
			picker.remove();
		} );

		const elementorElements = elementor.$previewContents[ 0 ].querySelectorAll( '.elementor-element' );

		removeNamespaceHandler( elementorElements, 'click.color-picker' );

		removeNamespaceHandler( elementor.$previewWrapper[ 0 ], 'mouseleave.color-picker' );

		// Set the picking process trigger to inactive mode.
		// eslint-disable-next-line no-unused-expressions
		this.component.currentPicker.trigger?.classList.remove( 'e-control-tool-disabled' );

		// Reset the current picker.
		this.component.currentPicker = {
			container: null,
			control: null,
			initialColor: null,
			trigger: null,
		};

		// Return to edit mode.
		elementor.changeEditMode( 'edit' );
	}
}
