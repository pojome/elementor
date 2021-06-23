import { Empty as _Empty } from 'elementor-document/elements/commands';
import ElementsHelper from '../helper';

export const Empty = () => {
	QUnit.module( 'Empty', () => {
		QUnit.test( 'Single Selection', ( assert ) => {
			const eColumn = ElementsHelper.createSection( 1, true );

			ElementsHelper.createButton( eColumn );
			ElementsHelper.createButton( eColumn );

			// Ensure editor saver.
			$e.internal( 'document/save/set-is-modified', { status: false } );

			ElementsHelper.empty();

			// Check.
			assert.equal( elementor.getPreviewContainer().children.length, 0,
				'all elements were removed.' );
			assert.equal( elementor.saver.isEditorChanged(), true, 'Command applied the saver editor is changed.' );
		} );

		QUnit.test( 'Restore()', ( assert ) => {
			const random = Math.random(),
				historyItem = {
					get: ( key ) => {
						if ( 'data' === key ) {
							return random;
						}
					},
				};

			let orig = $e.run,
				tempCommand = '';

			// TODO: Do not override '$e.run', use 'on' method instead.
			$e.run = ( command ) => {
				tempCommand = command;
			};

			// redo: `true`
			_Empty.restore( historyItem, true );

			$e.run = orig;

			assert.equal( tempCommand, 'document/elements/empty' );

			const addChildModelOrig = elementor.getPreviewView().addChildModel;

			// Clear.
			orig = $e.run;
			tempCommand = '';

			let tempData = '';

			elementor.getPreviewView().addChildModel = ( data ) => tempData = data;

			// TODO: Do not override '$e.run', use 'on' method instead.
			$e.run = ( command ) => {
				tempCommand = command;
			};

			// redo: `false`
			_Empty.restore( historyItem, false );

			$e.run = orig;

			elementor.getPreviewView().addChildModel = addChildModelOrig;

			assert.equal( tempData, random );
		} );
	} );
};

export default Empty;
