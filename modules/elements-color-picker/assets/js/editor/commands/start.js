import CommandBase from 'elementor-api/modules/command-base';
import { addNamespaceHandler } from 'elementor/modules/elements-color-picker/assets/js/editor/utils';

export class Start extends CommandBase {
	apply( args ) {
		// Prevent elements from triggering edit mode on click.
		elementor.changeEditMode( 'picker' );

		elementor.$previewContents[ 0 ].querySelector( 'body' ).classList.add( 'elementor-editor__ui-state__color-picker', 'elementor-edit-area-active' );

		this.component.currentPicker = {
			...args,
			initialColor: args.container.getSetting( args.control ),
		};

		// Set the picking process trigger to active mode.
		this.component.currentPicker.trigger.classList.add( 'e-control-tool-disabled' );

		// Initialize a swatch on click.
		const elementorElements = elementor.$previewContents[ 0 ].querySelectorAll( '.elementor-element' );

		addNamespaceHandler( elementorElements, 'click.color-picker', ( e ) => {
			e.preventDefault();
			$e.run( 'elements-color-picker/show-swatches', { event: e } );
		} );

		// Stop the picking process when the user leaves the preview area.
		addNamespaceHandler( elementor.$previewWrapper[ 0 ], 'mouseleave.color-picker', () => {
			$e.run( 'elements-color-picker/end' );
		} );
	}
}
