import CommandBase from 'elementor-api/modules/command-base';

export class Close extends CommandBase {
	validateArgs( args ) {
		this.requireArgument( 'id', args );
	}

	async apply( args ) {
		const { id, mode, onClose } = args,
			document = elementor.documents.get( id );

		// Already closed.
		if ( 'closed' === document.editor.status ) {
			return jQuery.Deferred().resolve();
		}

		// TODO: Move to an hook.
		if ( ! mode && ( document.editor.isChanged || document.isDraft() ) ) {
			const deferred = jQuery.Deferred();
			this.getConfirmDialog( deferred ).show();
			return deferred.promise();
		}

		switch ( mode ) {
			case 'autosave':
				await $e.run( 'document/save/auto' );
				break;
			case 'save':
				await $e.run( 'document/save/update' );
				break;
			case 'discard':
				await $e.run( 'document/save/discard', { document } );
				break;
		}

		$e.internal( 'editor/documents/unload', { document } );

		if ( onClose ) {
			await onClose( document );
		}

		return jQuery.Deferred().resolve();
	}

	getConfirmDialog( deferred ) {
		if ( this.confirmDialog ) {
			return this.confirmDialog;
		}

		this.confirmDialog = elementorCommon.dialogsManager.createWidget( 'confirm', {
			id: 'elementor-document-save-on-close',
			headerMessage: __( 'Save Changes', 'elementor' ),
			message: __( 'Would you like to save the changes you\'ve made?', 'elementor' ),
			position: {
				my: 'center center',
				at: 'center center',
			},
			strings: {
				confirm: __( 'Save', 'elementor' ),
				cancel: __( 'Discard', 'elementor' ),
			},
			onHide: () => {
				// If still not action chosen. use `defer` because onHide is called before onConfirm/onCancel.
				_.defer( () => {
					if ( ! this.args.mode ) {
						deferred.reject( 'Close document has been canceled.' );
					}
				} );
			},
			onConfirm: () => {
				this.args.mode = 'save';

				// Re-run with same args.
				$e.run( 'editor/documents/close', this.args )
					.then( () => {
						deferred.resolve();
					} );
			},
			onCancel: () => {
				this.args.mode = 'discard';

				// Re-run with same args.
				$e.run( 'editor/documents/close', this.args )
					.then( () => {
						deferred.resolve();
					} );
			},
		} );

		return this.confirmDialog;
	}
}

export default Close;
