import CommandNavigatorVisibility from './base/command-navigator-visibility';

export class Hide extends CommandNavigatorVisibility {
	shouldHide() {
		return true;
	}
}

export default Hide;
