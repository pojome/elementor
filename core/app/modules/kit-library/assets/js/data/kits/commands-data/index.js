export { DownloadLink } from './download-link';
export { Favorites } from './favorites';

export class Index extends $e.modules.CommandData {
	static getEndpointFormat() {
		return 'kits/{id}';
	}
}
