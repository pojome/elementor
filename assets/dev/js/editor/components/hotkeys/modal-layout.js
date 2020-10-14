import ModalContent from './modal-content';

export default class extends elementorCommon.views.modal.Layout {
	getModalOptions() {
		return {
			id: 'elementor-hotkeys__modal',
		};
	}

	getLogoOptions() {
		return {
			title: elementor.translate( 'keyboard_shortcuts' ),
		};
	}

	initialize( ...args ) {
		super.initialize( ...args );

		this.showLogo();

		this.showContentView();
	}

	showContentView() {
		this.modalContent.show( new ModalContent() );
	}
}
