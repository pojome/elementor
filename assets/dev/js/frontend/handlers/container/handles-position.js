/**
 * TODO: Try to merge with `section/handles-position.js` and create a generic solution using `.elementor-element`.
 */
export default class HandlesPosition extends elementorModules.frontend.handlers.Base {
	isActive() {
		return elementorFrontend.isEditMode();
	}

	isFirstContainer() {
		return this.$element[ 0 ] === document.querySelector( '.elementor-edit-mode .e-container:first-child' );
	}

	isOverflowHidden() {
		return 'hidden' === this.$element.css( 'overflow' );
	}

	getOffset() {
		if ( 'body' === elementor.config.document.container ) {
			return this.$element.offset().top;
		}

		const $container = jQuery( elementor.config.document.container );
		return this.$element.offset().top - $container.offset().top;
	}

	setHandlesPosition() {
		const document = elementor.documents.getCurrent();

		if ( ! document || ! document.container.isEditable() ) {
			return;
		}

		const isOverflowHidden = this.isOverflowHidden();

		if ( ! isOverflowHidden && ! this.isFirstContainer() ) {
			return;
		}

		const offset = isOverflowHidden ? 0 : this.getOffset(),
			$handlesElement = this.$element.find( '> .elementor-element-overlay > .elementor-editor-section-settings' ),
			insideHandleClass = 'e-handles-inside';

		if ( offset < 25 ) {
			this.$element.addClass( insideHandleClass );

			if ( offset < -5 ) {
				$handlesElement.css( 'top', -offset );
			} else {
				$handlesElement.css( 'top', '' );
			}
		} else {
			this.$element.removeClass( insideHandleClass );
		}
	}

	onInit() {
		if ( ! this.isActive() ) {
			return;
		}

		this.setHandlesPosition();

		this.$element.on( 'mouseenter', this.setHandlesPosition.bind( this ) );
	}
}
