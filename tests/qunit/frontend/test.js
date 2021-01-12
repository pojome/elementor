import Frontend from 'elementor-frontend/frontend-base';
import config from './config';

export default class FrontendTest extends Frontend {
	getConfig() {
		return config;
	}
}
