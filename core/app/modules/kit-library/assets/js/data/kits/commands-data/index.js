export { DownloadLink } from './download-link';

export class Index extends $e.modules.CommandData {
	static getEndpointFormat() {
		return 'kits/{id}';
	}
}
