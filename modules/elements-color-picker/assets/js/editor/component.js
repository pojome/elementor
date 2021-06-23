import ComponentBase from 'elementor-api/modules/component-base';
import * as commands from './commands/';

export default class Component extends ComponentBase {
	/**
	 * @typedef {object} currentPicker - The current triggering picker data
	 *
	 * @property {object|null} kit - Kit data if it's a kit.
	 * @property {Container} container - Element container to set the settings to.
	 * @property {string} control - Control name which triggered the picking.
	 * @property {HTMLElement} trigger - The actual HTML element of the control which triggered the picking.
	 * @property {string|null} initialColor - The initial value of the color control.
	 */
	currentPicker = this.getDefaultPicker();

	/**
	 * Get the default picker object.
	 *
	 * @returns {object}
	 */
	getDefaultPicker() {
		return {
			kit: null,
			container: null,
			control: null,
			trigger: null,
			initialColor: null,
		};
	}

	/**
	 * Reset the current picker to its default.
	 *
	 * @returns {void}
	 */
	resetPicker() {
		this.currentPicker = this.getDefaultPicker();
	}

	/**
	 * Silently render the UI using a new color value.
	 *
	 * @param {string} value - The new color to set.
	 *
	 * @returns {void}
	 */
	renderUI( value ) {
		const { container, control, kit } = this.currentPicker;

		// Silent.
		container.settings.set( control, value );

		const { view } = container;

		// If the container has a View that can be rendered.
		if ( view?.renderUI ) {
			view.renderUI();
		}

		// If it's a kit which uses CSS variables.
		if ( kit ) {
			const { id } = kit.config,
				cssVar = `--e-global-color-${ container.id }`;

			elementor.$previewContents[ 0 ].querySelector( `.elementor-kit-${ id }` ).style.setProperty( cssVar, value );
		}
	}

	getNamespace() {
		return 'elements-color-picker';
	}

	defaultCommands() {
		return this.importCommands( commands );
	}

	defaultShortcuts() {
		return {
			end: {
				keys: 'esc',
				scopes: [ 'panel', 'preview' ],
			},
		};
	}
}
