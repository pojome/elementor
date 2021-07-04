import Base from '../../../base/base';

/**
 * Navigator toggle list hook, is responsible for recursive toggling of selected widget for user indication.
 */
export class NavigatorToggleList extends Base {
	getCommand() {
		return 'panel/editor/open';
	}

	getId() {
		return 'navigator-toggle-list--/panel/editor/open';
	}

	getConditions( args ) {
		if ( ! super.getConditions() ) {
			return false;
		}

		/**
		 * Since on the first creation of new dragged widget there is no yet 'navigator.view'
		 * And the navigator is open its required to re-run the hook to apply it on first time.
		 */
		if ( ! args.view.container.navigator.view ) {
			setTimeout( () => $e.hooks.ui.get( this.getId() ).callback( args ) );
			return false;
		}

		return true;
	}

	apply( args ) {
		this.toggleList( args.view.container.navigator.view );
	}

	/**
	 * @param {e.elementor.navigator.Element} view
	 */
	toggleList( view ) {
		view.recursiveParentInvoke( 'toggleList', true );

		elementor.navigator.getLayout().elements.currentView.recursiveChildInvoke( 'removeEditingClass' );

		view.addEditingClass();

		elementor.helpers.scrollToView( view.$el, 400, elementor.navigator.getLayout().elements.$el );
	}
}

export default NavigatorToggleList;
