import ComponentBase from 'elementor-api/modules/component-base';
import * as commands from './commands/';

export default class Component extends ComponentBase {
	__construct( args ) {
		super.__construct( args );

		// Remember last used tab.
		this.activeTabs = {};
	}

	getNamespace() {
		return 'panel/editor';
	}

	defaultTabs() {
		return {
			content: { title: __( 'Content', 'elementor' ) },
			style: { title: __( 'Style', 'elementor' ) },
			advanced: { title: __( 'Advanced', 'elementor' ) },
			layout: { title: __( 'Layout', 'elementor' ) },
		};
	}

	defaultCommands() {
		return this.importCommands( commands );
	}

	getTabsWrapperSelector() {
		return '.elementor-panel-navigation';
	}

	renderTab( tab, args ) {
		const { model, view } = args,
			/* translators: %s: Element Name. */
			title = sprintf( __( 'Edit %s', 'elementor' ), elementor.getElementData( model ).title );

		elementor.getPanelView().setPage( 'editor', title, {
			tab,
			model: model,
			controls: elementor.getElementControls( model ),
			editedElementView: view,
		} );
	}

	activateTab( tab, args ) {
		this.activeTabs[ args.model.id ] = tab;

		super.activateTab( tab, args );
	}

	setDefaultTab( args ) {
		let defaultTab;

		const editSettings = args.model.get( 'editSettings' );

		if ( this.activeTabs[ args.model.id ] ) {
			defaultTab = this.activeTabs[ args.model.id ];
		} else if ( editSettings && editSettings.get( 'defaultEditRoute' ) ) {
			defaultTab = editSettings.get( 'defaultEditRoute' );
		}

		if ( defaultTab ) {
			// Ensure tab is exist.
			const controlsTabs = elementor.getElementData( args.model ).tabs_controls;

			// Fallback to first tab.
			if ( ! controlsTabs[ defaultTab ] ) {
				defaultTab = Object.keys( controlsTabs )[ 0 ];
			}

			this.setDefaultRoute( defaultTab );

			return true;
		}

		return false;
	}

	/**
	 * Callback on route open under the current namespace.
	 *
	 * @param {string} route - Route ID.
	 *
	 * @return {void}
	 */
	onRoute( route ) {
		super.onRoute( route );

		// Get the current element and set the direction mode.
		const container = elementor.getCurrentElement().container,
			direction = container?.view.getCurrentUiStates?.().directionMode;

		if ( direction ) {
			$e.uiStates.set( 'document/direction-mode', direction );
			return;
		}

		$e.uiStates.remove( 'document/direction-mode' );
	}

	/**
	 * Callback on route close under the current namespace.
	 *
	 * @param {string } route - Route ID.
	 *
	 * @return {void}
	 */
	onCloseRoute( route ) {
		super.onCloseRoute( route );

		$e.uiStates.remove( 'document/direction-mode' );
	}
}
