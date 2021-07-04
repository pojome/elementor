export default class ElementEmpty extends Marionette.ItemView {
	getTemplate() {
		return '#tmpl-elementor-navigator__elements--empty';
	}

	className() {
		return 'elementor-empty-view';
	}
}
