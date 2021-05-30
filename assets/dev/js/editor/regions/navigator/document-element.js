import Element from './element';

export default class DocumentElement extends Element {
	templateHelpers() {
		const templateHelpers = super.templateHelpers();

		if ( this.isSection() ) {
			templateHelpers.icon = 'eicon-section';
		}

		return templateHelpers;
	}

	initialize() {
		super.initialize();

		// On on document expand it self to view the sections.
		if ( this.isCurrentActiveDocument() ) {
			setTimeout( this.toggleList.bind( this ) );
		}
	}

	isDocument() {
		return 'document' === this.model.get( 'elType' );
	}

	isCurrentActiveDocument() {
		return this.model.get( 'id' ) === elementor.documents.getCurrentId();
	}

	enterTitleEditing() {
		// Currently No title editing for document.
		if ( ! this.isDocument() ) {
			super.enterTitleEditing();
		}
	}

	exitTitleEditing() {
		// Currently No title editing for document.
		if ( ! this.isDocument() ) {
			super.exitTitleEditing();
		}
	}

	renderIndicators() {
		if ( ! this.isDocument() ) {
			super.renderIndicators();
		}
	}

	onRender() {
		if ( this.isRoot() ) {
			return;
		}

		if ( this.isCurrentActiveDocument() ) {
			this.$el.toggleClass( 'elementor-active', true );
		}

		super.onRender();
	}

	onItemClick() {
		if ( this.isCurrentActiveDocument() ) {
			return this.toggleList();
		}

		super.onItemClick();
	}

	onToggleClick( event ) {
		event.stopPropagation();

		if ( this.isDocument() && ! this.isCurrentActiveDocument() ) {
			$e.run( 'editor/documents/switch', {
				id: this.model.get( 'id' ),
				force: true,
			} );
		} else {
			super.onToggleClick( event );
		}
	}
}
