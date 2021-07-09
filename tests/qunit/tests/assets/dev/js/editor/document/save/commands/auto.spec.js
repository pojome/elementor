export const Auto = () => {
	QUnit.module( 'Auto', () => {
		QUnit.test( 'Simple', async ( assert ) => {
			// set is modified, 'true' since it will be rejected if 'editor.isChanged = false'.
			$e.internal( 'document/save/set-is-modified', { status: true } );

			const response = await $e.run( 'document/save/auto' );

			assert.equal( response.data.status, 'inherit', 'The response status is: "inherit"' );
		} );

		QUnit.test( 'rejected: "Document is not editable"', ( assert ) => {
			// Create fake document.
			const document = elementor.documents.getCurrent(),
				defaultStatus = document.editor.status;

			// Editor is not edit able!
			document.editor.status = 'closed';

			assert.expect( 1 );

			$e.tests.commands.exceptCatchApply( ( e ) => {
				assert.equal( e, 'Document is not editable' );
			} );

			$e.run( 'document/save/auto', { document } ).always( () => {
				document.editor.status = defaultStatus;
			} );
		} );

		QUnit.test( 'Resolved: "Document is not changed"', async ( assert ) => {
			const document = elementor.documents.getCurrent(),
				defaultIsChanged = document.editor.isChanged;

			// Editor is edit able!
			document.editor.status = 'open';

			// Editor is not changed.
			$e.internal( 'document/save/set-is-modified', { status: false } );

			$e.run( 'document/save/auto', { document } )
				.then( ( data ) => {
					assert.equal( data, 'Document is not changed',
						'Resolved without a request to the server' );
				} )
				.always( () => {
					// Put back as it was before.
					$e.internal( 'document/save/set-is-modified', { status: defaultIsChanged } );
				} );
		} );
	} );
};

export default Auto;
