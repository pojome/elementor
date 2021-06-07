import DocumentComponent from './document/component';
import DataGlobalsComponent from './data/globals/component';

elementorCommon.elements.$window.on( 'elementor:init-components', () => {
	// TODO: Move to elementor:init-data-components
	$e.components.register( new DataGlobalsComponent() );

	$e.components.register( new DocumentComponent() );

	// TODO: Remove, BC Since 2.9.0.
	elementor.saver = $e.components.get( 'document/save' );
} );

$e.modules.document = DocumentComponent.getModules();
