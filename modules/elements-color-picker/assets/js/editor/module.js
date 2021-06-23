import Component from './component';

export default class ElementsColorPicker extends elementorModules.ViewModule {
	onInit() {
		super.onInit();

		$e.components.register( new Component() );
	}
}
