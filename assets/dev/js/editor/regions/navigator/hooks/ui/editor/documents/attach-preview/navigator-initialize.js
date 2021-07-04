import After from 'elementor-api/modules/hooks/ui/after';

export class NavigatorInitialize extends After {
	getCommand() {
		return 'editor/documents/attach-preview';
	}

	getId() {
		return 'navigator-initialize--editor/documents/attach-preview';
	}

	getConditions() {
		return elementor.documents.getCurrent().config.panel.has_elements;
	}

	apply( args ) {
		elementor.navigator.initLayout();

		if ( elementor.navigator.storage.visible ) {
			$e.route( 'navigator' );
		}
	}
}

export default NavigatorInitialize;
