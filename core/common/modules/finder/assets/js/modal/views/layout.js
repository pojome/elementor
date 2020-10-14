import ModalContent from './content';

export default class extends elementorCommon.views.modal.Layout {
	getModalOptions() {
		return {
			id: 'elementor-finder__modal',
			draggable: true,
			effects: {
				show: 'show',
				hide: 'hide',
			},
			position: {
				enable: false,
			},
		};
	}

	getLogoOptions() {
		return {
			title: elementorCommon.translate( 'finder', 'finder' ),
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

	showModal( ...args ) {
		super.showModal( ...args );

		this.modalContent.currentView.ui.searchInput.focus();
	}
}
