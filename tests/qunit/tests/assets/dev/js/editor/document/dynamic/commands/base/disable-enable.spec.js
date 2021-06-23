import DisableEnable from 'elementor-document/dynamic/commands/base/disable-enable';

jQuery( () => {
	QUnit.module( 'File: assets/dev/js/editor/document/dynamic/commands/base/disable-enable.js', () => {
		QUnit.test( 'restore()', ( assert ) => {
			const historyItem = {
				get: ( key ) => {
					if ( 'data' === key ) {
						return {
							command: 'document/dynamic/enable',
							changes: {
								1: 'fake',
							},
						};
					} else if ( 'containers' === key ) {
						const panel = { refresh: () => {} };
						return [
							{ id: 1, panel },
						];
					}
				},
			};

			const orig = $e.run;

			let tempCommand = '',
				tempArgs = '';

			// TODO: Do not override '$e.run', use 'on' method instead.
			$e.run = ( command, args ) => {
				tempCommand = command;
				tempArgs = args;
			};

			DisableEnable.restore( historyItem, false );

			$e.run = orig;

			assert.equal( tempCommand, 'document/dynamic/disable' );

			assert.propEqual( tempArgs.settings, Object.entries( historyItem.get( 'data' ).changes )[ 0 ][ 1 ] );
			assert.propEqual( tempArgs.container, historyItem.get( 'containers' )[ 0 ] );
		} );
	} );
} );
