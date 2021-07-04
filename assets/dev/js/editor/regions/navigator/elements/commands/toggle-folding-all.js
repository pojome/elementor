import CommandBase from 'elementor-api/modules/command-base';

export class ToggleFoldingAll extends CommandBase {
	apply( args ) {
		const layout = elementor.navigator.getLayout(),
			state = args.hasOwnProperty( 'state' ) ? args.state : 'expand' === layout.ui.toggleAll.data( 'elementor-action' ),
			classes = [ 'eicon-collapse', 'eicon-expand' ],
			all = [];

		elementor.getPreviewContainer().children.forEach( ( section ) => {
			all.push( section );

			section.children.forEach( ( column ) => {
				all.push( column );
			} );
		} );

		all.forEach( ( container ) => {
			$e.run( 'navigator/elements/toggle-folding', {
				container,
				state,
			} );
		} );

		layout.ui.toggleAll
			.data( 'elementor-action', state ? 'collapse' : 'expand' )
			.removeClass( classes[ +state ] )
			.addClass( classes[ +! state ] );
	}
}

export default ToggleFoldingAll;
