import After from 'elementor-api/modules/hooks/data/after';

export class KitAfterSave extends After {
	getCommand() {
		return 'document/save/save';
	}

	getConditions( args ) {
		const { status, document = elementor.documents.getCurrent() } = args;
		return 'publish' === status && 'kit' === document.config.type;
	}

	getId() {
		return 'kit-footer-saver-after-save';
	}

	apply( args ) {
		if ( 'publish' === args.status ) {
			elementor.notifications.showToast( {
				message: __( 'Your changes have been updated.', 'elementor' ),
				buttons: [
					{
						name: 'back_to_editor',
						text: __( 'Back to Editor', 'elementor' ),
						callback() {
							$e.run( 'panel/global/close' );
						},
					},
				],
			} );
		}

		if ( elementor.activeBreakpointsUpdated ) {
			const reloadConfirm = elementorCommon.dialogsManager.createWidget( 'alert', {
				id: 'elementor-save-kit-refersh-page',
				headerMessage: __( 'Reload Elementor Editor', 'elementor' ),
				message: __( 'You have made modifications to the list of Active Breakpoints. For these changes to take effect, you need to reload Elementor Editor.', 'elementor' ),
				position: {
					my: 'center center',
					at: 'center center',
				},
				strings: {
					confirm: __( 'Reload Now', 'elementor' ),
				},
				onConfirm: () => location.reload(),
			} );

			reloadConfirm.show();
		}
	}
}

export default KitAfterSave;
