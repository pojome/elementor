export default class extends Marionette.Region {
	initialize() {
		this.show( new View() );
	}
}

export class initOptimizer extends $e.modules.hookUI.After {
	getCommand() {
		return 'document/save/save';
	}

	getId() {
		return 'init-optimizer-after-save';
	}

	apply() {
		const previewUrl = elementor.config.initial_document.urls.permalink + '?analyzer=1';

		document.getElementById( 'optimizer-iframe' ).setAttribute( 'src', previewUrl );
		window.addEventListener( 'message', ( e ) => {
			const key = e.message ? 'message' : 'data';
			const data = e[ key ];

			console.log( data );
		}, false );
	}
}

export class OptimizerComponent extends $e.modules.ComponentBase {
	getNamespace() {
		return 'optimizer';
	}

	defaultHooks() {
		return this.importHooks( { initOptimizer } );
	}
}

class View extends Marionette.ItemView {
	getTemplate() {
		return '#tmpl-elementor-templates-optimizer';
	}

	className() {
		return 'elementor-optimizer';
	}

	ui() {
		return {
			iframe: '#optimizer-iframe',
		};
	}

	events() {
		return {
			'load @ui.iframe': 'this.onIframeLoad',
		};
	}

	initialize() {
		setTimeout( () => {
			this.ui.iframe.css( {
				position: 'absolute',
				top: '-50px',
				left: '-50px',
				width: '1400px',
				height: '1200px',
				transform: 'scale(0.25)',
				'transform-origin': 'top left',
				'z-index': -1,
			} );
		}, 1000 );
	}

	onIframeLoad() {
		console.log( 'iframeloaded', this.ui.iframe );
	}
}
