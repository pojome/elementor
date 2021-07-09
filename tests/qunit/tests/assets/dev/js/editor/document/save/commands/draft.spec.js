import ElementsHelper from 'elementor/tests/qunit/tests/assets/dev/js/editor/document/elements/helper';

let tempPostStatus,
	tempEditorStatus;

export const Draft = () => {
	QUnit.module( 'Draft', ( hooks ) => {
		hooks.before( () => {
			// Save the 'post_status' before the test, to put it back later.
			const currentDocument = elementor.documents.getCurrent();

			tempPostStatus = currentDocument.config.settings.settings.post_status;
			tempEditorStatus = currentDocument.editor.status;
		} );

		hooks.after( () => {
			// Put back as it was before.
			const currentDocument = elementor.documents.getCurrent();

			currentDocument.container.settings.set( 'post_status', tempPostStatus );
			currentDocument.editor.status = tempEditorStatus;
		} );

		QUnit.test( 'Document post_status is "publish"', async ( assert ) => {
			const document = elementor.documents.getCurrent();

			document.editor.status = 'open';

			ElementsHelper.settings( document.container, {
				post_status: 'publish',
			} );

			const result = await $e.run( 'document/save/draft' );

			assert.equal( result.data.status, 'inherit' );

			$e.internal( 'document/save/set-is-modified', { status: false } );
		} );

		QUnit.test( 'Document post_status is "private"', async ( assert ) => {
			const document = elementor.documents.getCurrent();

			document.editor.status = 'open';

			ElementsHelper.settings( document.container, {
				post_status: 'private',
			} );

			const result = await $e.run( 'document/save/draft' );

			assert.equal( result.data.status, 'inherit' );

			$e.internal( 'document/save/set-is-modified', { status: false } );
		} );

		QUnit.test( 'Document post_status is "draft"', async ( assert ) => {
			const document = elementor.documents.getCurrent();

			document.editor.status = 'open';

			ElementsHelper.settings( document.container, {
				post_status: 'draft',
			} );

			const result = await $e.run( 'document/save/draft' );

			// Ensure the document is published.
			assert.equal( result.data.status, 'draft' );
		} );

		QUnit.test( 'rejected: "Document is not editable"', ( assert ) => {
			// Create fake document.
			const document = elementor.documents.getCurrent(),
				defaultStatus = document.editor.status;

			// Editor is not edit able!
			document.editor.status = 'closed';

			// Put something that is not 'draft' to reach reject.
			ElementsHelper.settings( document.container, {
				post_status: 'private',
			} );

			/**
			 * TODO: Currently the commands mechanism not works as except trigger two catch applies.
			 * one for `document/save/auto'.
			 * and another which passed to 'document/save/draft'.
			 */
			assert.expect( 2 );

			$e.tests.commands.exceptCatchApply( ( e ) => {
				assert.equal( e, 'Document is not editable' );
			}, 2 );

			// Put back as it was before.
			$e.run( 'document/save/draft', { document } ).always( () => {
				document.editor.status = defaultStatus;
			} );
		} );
	} );
};

export default Draft;
