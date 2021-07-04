import After from 'elementor-api/modules/hooks/ui/after';

export default class Base extends After {
	getConditions() {
		return $e.components.get( 'navigator' ).isOpen;
	}
}
