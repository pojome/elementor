import ResetLayoutBase from '../base/reset-layout-base';

export class CreateSectionColumnsResetLayout extends ResetLayoutBase {
	getCommand() {
		return 'document/elements/create';
	}

	getId() {
		return 'section-columns-reset-layout--document/elements/create';
	}

	getContainerType() {
		return 'section';
	}
}

export default CreateSectionColumnsResetLayout;
