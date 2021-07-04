import Element from './elements/view/element';

export default class Layout extends Marionette.LayoutView {
	getTemplate() {
		return '#tmpl-elementor-navigator';
	}

	id() {
		return 'elementor-navigator__inner';
	}

	ui() {
		return {
			toggleAll: '#elementor-navigator__toggle-all',
			close: '#elementor-navigator__close',
		};
	}

	events() {
		return {
			'click @ui.toggleAll': () => $e.run( 'navigator/elements/toggle-folding-all' ),
			'click @ui.close': () => $e.run( 'navigator/close' ),
		};
	}

	regions() {
		return {
			elements: '#elementor-navigator__elements',
		};
	}

	activateElementsMouseInteraction() {
		this.elements.currentView.recursiveChildInvoke( 'activateMouseInteraction' );
	}

	deactivateElementsMouseInteraction() {
		this.elements.currentView.recursiveChildInvoke( 'deactivateMouseInteraction' );
	}

	onShow() {
		this.elements.show( new Element( {
			model: elementor.elementsModel,
		} ) );
	}
}
