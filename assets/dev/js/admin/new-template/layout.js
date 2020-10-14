var NewTemplateView = require( 'elementor-admin/new-template/view' );

module.exports = elementorCommon.views.modal.Layout.extend( {

	getModalOptions: function() {
		return {
			id: 'elementor-new-template-modal',
		};
	},

	getLogoOptions: function() {
		return {
			title: elementorAdmin.translate( 'new_template' ),
		};
	},

	initialize: function() {
		elementorCommon.views.modal.Layout.prototype.initialize.apply( this, arguments );

		this.showLogo();

		this.showContentView();
	},

	showContentView: function() {
		this.modalContent.show( new NewTemplateView() );
	},
} );
