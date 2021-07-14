import globalHandler from './handlers/global';
import sectionHandlers from './handlers/section/section';
import containerHandlers from './handlers/container/container';
import columnHandlers from './handlers/column';

module.exports = function( $ ) {
	const handlersInstances = {};

	this.elementsHandlers = {
		'accordion.default': () => import( /* webpackChunkName: 'accordion' */ './handlers/accordion' ),
		'alert.default': () => import( /* webpackChunkName: 'alert' */ './handlers/alert' ),
		'counter.default': () => import( /* webpackChunkName: 'counter' */ './handlers/counter' ),
		'progress.default': () => import( /* webpackChunkName: 'progress' */ './handlers/progress' ),
		'tabs.default': () => import( /* webpackChunkName: 'tabs' */ './handlers/tabs' ),
		'toggle.default': () => import( /* webpackChunkName: 'toggle' */ './handlers/toggle' ),
		'video.default': () => import( /* webpackChunkName: 'video' */ './handlers/video' ),
		'image-carousel.default': () => import( /* webpackChunkName: 'image-carousel' */ './handlers/image-carousel' ),
		'text-editor.default': () => import( /* webpackChunkName: 'text-editor' */ './handlers/text-editor' ),
	};

	const addGlobalHandlers = () => elementorFrontend.hooks.addAction( 'frontend/element_ready/global', globalHandler );

	const addElementsHandlers = () => {
		this.elementsHandlers.section = sectionHandlers;
		this.elementsHandlers.container = containerHandlers;
		this.elementsHandlers.column = columnHandlers;

		$.each( this.elementsHandlers, ( elementName, Handlers ) => {
			const elementData = elementName.split( '.' );

			elementName = elementData[ 0 ];

			const skin = elementData[ 1 ] || null;

			this.attachHandler( elementName, Handlers, skin );
		} );
	};

	const isClassHandler = ( Handler ) => Handler.prototype?.getUniqueHandlerID;

	const addHandlerWithHook = ( elementName, Handler, skin = 'default' ) => {
		skin = skin ? '.' + skin : '';

		elementorFrontend.hooks.addAction( `frontend/element_ready/${ elementName }${ skin }`, ( $element ) => {
			if ( isClassHandler( Handler ) ) {
				this.addHandler( Handler, { $element }, true );
			} else {
				const handlerValue = Handler();

				if ( handlerValue instanceof Promise ) {
					handlerValue.then( ( { default: dynamicHandler } ) => {
						this.addHandler( dynamicHandler, { $element }, true );
					} );
				} else {
					this.addHandler( handlerValue, { $element }, true );
				}
			}
		} );
	};

	this.addHandler = function( HandlerClass, options ) {
		const elementID = options.$element.data( 'model-cid' );

		let handlerID;

		// If element is in edit mode
		if ( elementID ) {
			handlerID = HandlerClass.prototype.getConstructorID();

			if ( ! handlersInstances[ elementID ] ) {
				handlersInstances[ elementID ] = {};
			}

			const oldHandler = handlersInstances[ elementID ][ handlerID ];

			if ( oldHandler ) {
				oldHandler.onDestroy();
			}
		}

		const newHandler = new HandlerClass( options );

		if ( elementID ) {
			handlersInstances[ elementID ][ handlerID ] = newHandler;
		}
	};

	this.attachHandler = ( elementName, Handlers, skin ) => {
		if ( ! Array.isArray( Handlers ) ) {
			Handlers = [ Handlers ];
		}

		Handlers.forEach( ( Handler ) => addHandlerWithHook( elementName, Handler, skin ) );
	};

	this.getHandler = function( handlerName ) {
		if ( ! handlerName ) {
			return;
		}

		const elementHandler = this.elementsHandlers[ handlerName ];

		if ( isClassHandler( elementHandler ) ) {
			return elementHandler;
		}

		return new Promise( ( res ) => {
			elementHandler().then( ( { default: dynamicHandler } ) => {
				res( dynamicHandler );
			} );
		} );
	};

	this.getHandlers = function( handlerName ) {
		elementorCommon.helpers.softDeprecated( 'getHandlers', '3.1.0', 'elementorFrontend.elementsHandler.getHandler' );

		if ( handlerName ) {
			return this.getHandler( handlerName );
		}

		return this.elementsHandlers;
	};

	this.runReadyTrigger = function( scope ) {
		if ( elementorFrontend.config.is_static ) {
			return;
		}

		// Initializing the `$scope` as frontend jQuery instance
		const $scope = jQuery( scope ),
			elementType = $scope.attr( 'data-element_type' );

		if ( ! elementType ) {
			return;
		}

		elementorFrontend.hooks.doAction( 'frontend/element_ready/global', $scope, $ );

		elementorFrontend.hooks.doAction( 'frontend/element_ready/' + elementType, $scope, $ );

		if ( 'widget' === elementType ) {
			elementorFrontend.hooks.doAction( 'frontend/element_ready/' + $scope.attr( 'data-widget_type' ), $scope, $ );
		}
	};

	this.init = () => {
		addGlobalHandlers();

		addElementsHandlers();
	};
};
