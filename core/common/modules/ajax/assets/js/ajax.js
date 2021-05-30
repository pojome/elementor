export default class extends elementorModules.Module {
	getDefaultSettings() {
		return {
			ajaxParams: {
				type: 'POST',
				url: elementorCommon.config.ajax.url,
				data: {},
				dataType: 'json',
			},
			actionPrefix: 'elementor_',
		};
	}

	constructor( ...args ) {
		super( ...args );

		this.requests = {};

		this.cache = {};

		this.initRequestConstants();

		this.debounceSendBatch = _.debounce( this.sendBatch.bind( this ), 500 );
	}

	initRequestConstants() {
		this.requestConstants = {
			_nonce: this.getSettings( 'nonce' ),
		};
	}

	addRequestConstant( key, value ) {
		this.requestConstants[ key ] = value;
	}

	getCacheKey( request ) {
		return JSON.stringify( {
			unique_id: request.unique_id,
			data: request.data,
		} );
	}

	loadObjects( options ) {
		let dataCollection = {};

		const deferredArray = [];

		if ( options.before ) {
			options.before();
		}

		options.ids.forEach( ( objectId ) => {
			deferredArray.push(
				this.load( {
						action: options.action,
						unique_id: options.data.unique_id + objectId,
						data: jQuery.extend( { id: objectId }, options.data ),
					} )
					.done( ( data ) => dataCollection = jQuery.extend( dataCollection, data ) )
			);
		} );

		jQuery.when.apply( jQuery, deferredArray ).done( () => options.success( dataCollection ) );
	}

	load( request, immediately ) {
		if ( ! request.unique_id ) {
			request.unique_id = request.action;
		}

		if ( request.before ) {
			request.before();
		}

		let deferred;

		const cacheKey = this.getCacheKey( request );

		if ( _.has( this.cache, cacheKey ) ) {
			deferred = jQuery.Deferred()
				.done( request.success )
				.resolve( this.cache[ cacheKey ] );
		} else {
			deferred = this.addRequest( request.action, {
				data: request.data,
				unique_id: request.unique_id,
				success: ( data ) => this.cache[ cacheKey ] = data,
			}, immediately ).done( request.success );
		}

		return deferred;
	}

	addRequest( action, options, immediately ) {
		options = options || {};

		if ( ! options.unique_id ) {
			options.unique_id = action;
		}

		options.deferred = jQuery.Deferred().done( options.success ).fail( options.error ).always( options.complete );

		const request = {
			action: action,
			options: options,
		};

		if ( immediately ) {
			const requests = {};

			requests[ options.unique_id ] = request;

			options.deferred.jqXhr = this.sendBatch( requests );
		} else {
			this.requests[ options.unique_id ] = request;

			this.debounceSendBatch();
		}

		return options.deferred;
	}

	sendBatch( requests ) {
		const actions = {};

		if ( ! requests ) {
			requests = this.requests;

			// Empty for next batch.
			this.requests = {};
		}

		Object.entries( requests ).forEach( ( [ id, request ] ) => actions[ id ] = {
			action: request.action,
			data: request.options.data,
		} );

		return this.send( 'ajax', {
			data: {
				actions: actions,
			},
			success: ( data ) => {
				Object.entries( data.responses ).forEach( ( [ id, response ] ) => {
					const options = requests[ id ].options;

					if ( options ) {
						if ( response.success ) {
							options.deferred.resolve( response.data );
						} else if ( ! response.success ) {
							options.deferred.reject( response.data );
						}
					}
				} );
			},
			error: ( data ) =>
				Object.values( requests ).forEach( ( args ) => {
					if ( args.options ) {
						args.options.deferred.reject( data );
					}
				} ),
		} );
	}

	prepareSend( action, options ) {
		const settings = this.getSettings(),
			ajaxParams = elementorCommon.helpers.cloneObject( settings.ajaxParams );

		options = options || {};

		action = settings.actionPrefix + action;

		jQuery.extend( ajaxParams, options );

		const requestConstants = elementorCommon.helpers.cloneObject( this.requestConstants );

		requestConstants.action = action;

		const isFormData = ajaxParams.data instanceof FormData;

		Object.entries( requestConstants ).forEach( ( [ key, value ] ) => {
			if ( isFormData ) {
				ajaxParams.data.append( key, value );
			} else {
				ajaxParams.data[ key ] = value;
			}
		} );

		const successCallback = ajaxParams.success,
			errorCallback = ajaxParams.error;

		if ( successCallback || errorCallback ) {
			ajaxParams.success = ( response ) => {
				if ( response.success && successCallback ) {
					successCallback( response.data );
				}

				if ( ( ! response.success ) && errorCallback ) {
					errorCallback( response.data );
				}
			};

			if ( errorCallback ) {
				ajaxParams.error = ( data ) => errorCallback( data );
			} else {
				ajaxParams.error = ( xmlHttpRequest ) => {
					if ( xmlHttpRequest.readyState || 'abort' !== xmlHttpRequest.statusText ) {
						this.trigger( 'request:unhandledError', xmlHttpRequest );
					}
				};
			}
		}

		return ajaxParams;
	}

	send( action, options ) {
		return jQuery.ajax( this.prepareSend( action, options ) );
	}

	addRequestCache( request, data ) {
		const cacheKey = this.getCacheKey( request );
		this.cache[ cacheKey ] = data;
	}

	invalidateCache( request ) {
		const cacheKey = this.getCacheKey( request );
		delete this.cache[ cacheKey ];
	}
}
