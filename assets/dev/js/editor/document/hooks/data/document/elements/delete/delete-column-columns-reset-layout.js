import ResetLayoutBase from '../base/reset-layout-base';

export class DeleteColumnColumnsResetLayout extends ResetLayoutBase {
	getCommand() {
		return 'document/elements/delete';
	}

	getId() {
		return 'section-columns-reset-layout--document/elements/delete';
	}

	getContainerType() {
		return 'column';
	}
}

export default DeleteColumnColumnsResetLayout;
