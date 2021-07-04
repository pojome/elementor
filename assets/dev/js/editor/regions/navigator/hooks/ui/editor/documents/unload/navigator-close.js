import After from 'elementor-api/modules/hooks/ui/after';

export class NavigatorClose extends After {
	getCommand() {
		return 'editor/documents/unload';
	}

	getId() {
		return 'navigator-close--editor/documents/unload';
	}

	getConditions() {
		return $e.components.get( 'navigator' ).isOpen;
	}

	apply( args ) {
		$e.components.get( 'navigator' ).close();
	}
}

export default NavigatorClose;
