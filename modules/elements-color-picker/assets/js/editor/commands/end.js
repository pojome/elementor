import CommandBase from 'elementor-api/modules/command-base';

export class End extends CommandBase {
	apply() {
		// Remove all elements & event listeners.
		elementor.$previewContents.find( 'body' ).removeClass( 'elementor-editor__ui-state__color-picker' );

		elementor.$previewContents.find( '.e-element-color-picker' ).remove();

		elementor.$previewContents.off( 'click.color-picker' );

		elementor.$previewWrapper.off( 'mouseleave.color-picker' );

		// Set the picking process trigger to in-active mode.
		this.component.currentPicker.trigger.removeClass( 'e-control-tool-disabled' );

		// Reset the current picker.
		this.component.currentPicker = {
			container: null,
			control: null,
			initialColor: null,
			trigger: null,
		};

		// Revert the lightbox block.
		this.component.lightboxTriggers.forEach( ( item ) => {
			item.dataset.elementorOpenLightbox= 'yes';
		} );

		this.component.lightboxTriggers = [];

		// Return to edit mode.
		elementor.changeEditMode( 'edit' );
	}
}
