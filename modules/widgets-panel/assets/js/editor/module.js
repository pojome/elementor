/* global jQuery */
import FavoritesComponent from './favorites-component';

export default class Module extends elementorModules.ViewModule {
	/**
	 * Init
	 */
	onInit() {
		super.onInit();
		$e.components.register( new FavoritesComponent() );
	}
}
