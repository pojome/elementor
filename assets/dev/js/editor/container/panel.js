import Base from './base';

export default class Panel extends Base {
	/**
	 * Function refresh().
	 *
	 * Refresh the panel.
	 */
	refresh() {
		if ( $e.routes.isPartOf( 'panel/editor' ) ) {
			$e.routes.refreshContainer( 'panel' );
		}
	}

	/**
	 * Function closeEditor().
	 *
	 * Route to `panel/elements/categories`
	 */
	closeEditor() {
		$e.route( 'panel/elements/categories' );
	}

	getControlView( name ) {
		const editor = elementor.getPanelView().getCurrentPageView();

		return editor.children.findByModelCid( this.getControlModel( name ).cid );
	}

	getControlModel( name ) {
		const editor = elementor.getPanelView().getCurrentPageView();

		return editor.collection.findWhere( { name: name } );
	}
}
