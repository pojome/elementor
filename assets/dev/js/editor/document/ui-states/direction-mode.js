import UiStateBase from 'elementor-api/core/states/ui-state-base';

export const DIRECTION_ROW = 'row';
export const DIRECTION_COLUMN = 'column';

export class DirectionMode extends UiStateBase {
	getId() {
		return 'direction-mode';
	}

	getOptions() {
		return {
			[ DIRECTION_ROW ]: '',
			[ DIRECTION_COLUMN ]: '',
		};
	}
}
