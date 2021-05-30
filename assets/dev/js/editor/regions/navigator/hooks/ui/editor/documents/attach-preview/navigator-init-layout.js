import After from 'elementor-api/modules/hooks/ui/after';

export class NavigatorInitLayout extends After {
	getCommand() {
		return 'editor/documents/attach-preview';
	}

	getId() {
		return 'navigator-init-layout--editor/documents/attach-preview';
	}

	getConditions() {
		return elementor.documents.getCurrent().config.panel.has_elements;
	}

	apply( args ) {
		const component = $e.components.get( 'navigator' );

		component.initLayout();

		if ( elementor.navigator.storage.visible ) {
			$e.route( 'navigator' );
		}
	}
}
