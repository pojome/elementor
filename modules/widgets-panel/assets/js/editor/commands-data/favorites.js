import CommandData from 'elementor-api/modules/command-data';

export class Favorites extends CommandData {
	static getEndpointFormat() {
		return 'widgets-panel/favorites/{id}';
	}
}

export default Favorites;
