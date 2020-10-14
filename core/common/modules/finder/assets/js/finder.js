import Module from 'elementor-common/modules/imports/module';

export default class extends Module {
	async onInit() {
		// TODO: Temp fix, do not load finder in theme-builder.
		// Better to pass into '$e' constructor the app owner. ( admin, editor, preview, iframe ).
		if ( window.top !== window ) {
			return;
		}
		this.channel = Backbone.Radio.channel( 'ELEMENTOR:finder' );

		const Component = require( './component' ).default;

		$e.components.register( new Component( { manager: this } ) );
	}
}
