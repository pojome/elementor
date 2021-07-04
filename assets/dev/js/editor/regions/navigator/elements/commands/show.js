import CommandNavigatorVisibility from './base/command-navigator-visibility';

export class Show extends CommandNavigatorVisibility {
	shouldHide() {
		return false;
	}
}

export default Show;
