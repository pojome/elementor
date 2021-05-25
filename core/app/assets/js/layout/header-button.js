import BaseButton from '../ui/molecules/button';

export default class Button extends BaseButton {
	static defaultProps = Object.assign( {} /* clone */, BaseButton.defaultProps, {
		hideText: true,
		includeHeaderBtnClass: true,
	} );

	getCssId() {
		return `eps-app-header-btn-` + super.getCssId();
	}

	getClassName() {
		// Avoid using the 'eps-app__header-btn' class to make sure it is not override custom styles.
		if ( ! this.props.includeHeaderBtnClass ) {
			return super.getClassName();
		}

		return `eps-app__header-btn ` + super.getClassName();
	}
}
