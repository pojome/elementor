var ImagesManager;

ImagesManager = function() {
	var self = this;

	var cache = {};

	var debounceDelay = 300;

	var registeredItems = [];

	var getNormalizedSize = function( image ) {
		var size,
			imageSize = image.size;

		if ( 'custom' === imageSize ) {
			var customDimension = image.dimension;

			if ( customDimension.width || customDimension.height ) {
				size = 'custom_' + customDimension.width + 'x' + customDimension.height;
			} else {
				return 'full';
			}
		} else {
			size = imageSize;
		}

		return size;
	};

	var viewsToUpdate = {};

	self.updateOnReceiveImage = function() {
		var elementView = elementor.getPanelView().getCurrentPageView().getOption( 'editedElementView' );

		elementView.$el.addClass( 'elementor-loading' );
		// Add per cid for multiple images in a single view.
		viewsToUpdate[ elementView.cid ] = elementView;

		elementor.channels.editor.once( 'imagesManager:detailsReceived', function() {
			if ( ! _.isEmpty( viewsToUpdate ) ) {
				_( viewsToUpdate ).each( function( view ) {
					view.render();
					view.$el.removeClass( 'elementor-loading' );
				} );
			}
			viewsToUpdate = {};
		} );
	};

	self.getImageUrl = function( image ) {
		// Register for AJAX checking
		self.registerItem( image );

		var imageUrl = self.getItem( image );

		// If it's not in cache, like a new dropped widget or a custom size - get from settings
		if ( ! imageUrl ) {
			if ( 'custom' === image.size ) {
				if ( $e.routes.isPartOf( 'panel/editor' ) && image.model ) {
					self.updateOnReceiveImage();
				}

				return;
			}

			// If it's a new dropped widget
			imageUrl = image.url;
		}

		return imageUrl;
	};

	self.getItem = function( image ) {
		var size = getNormalizedSize( image ),
			id = image.id;

		if ( ! size ) {
			return false;
		}

		if ( cache[ id ] && cache[ id ][ size ] ) {
			return cache[ id ][ size ];
		}

		return false;
	};

	self.registerItem = function( image ) {
		if ( '' === image.id ) {
			// It's a new dropped widget
			return;
		}

		if ( self.getItem( image ) ) {
			// It's already in cache
			return;
		}

		registeredItems.push( image );

		self.debounceGetRemoteItems();
	};

	self.getRemoteItems = function() {
		var requestedItems = [],
		registeredItemsLength = Object.keys( registeredItems ).length,
			image,
			index;

		// It's one item, so we can render it from remote server
		if ( 0 === registeredItemsLength ) {
			return;
			}

		for ( index in registeredItems ) {
			image = registeredItems[ index ];

			var size = getNormalizedSize( image ),
				id = image.id,
				isFirstTime = ! cache[ id ] || 0 === Object.keys( cache[ id ] ).length;

			requestedItems.push( {
				id: id,
				size: size,
				is_first_time: isFirstTime,
			} );
		}

		elementorCommon.ajax.send(
			'get_images_details', {
				data: {
					items: requestedItems,
				},
				success: ( data ) => {
					var imageId,
						imageSize;

					for ( imageId in data ) {
						if ( ! cache[ imageId ] ) {
							cache[ imageId ] = {};
						}

						for ( imageSize in data[ imageId ] ) {
							cache[ imageId ][ imageSize ] = data[ imageId ][ imageSize ];
						}
					}
					registeredItems = [];

					elementor.channels.editor.trigger( 'imagesManager:detailsReceived', data );
				},
			}
		);
	};

	self.debounceGetRemoteItems = _.debounce( self.getRemoteItems, debounceDelay );

	self.isSvgImage = function( imageUrl ) {
		return 'svg' === imageUrl.split( '.' ).pop();
	};

	self.getImageSizeFromControlOptions = function( imageSize, sizesOptions, imageCustomDimension ) {
		let imageData = { width: '', height: '' },
			imageDimensions;

		if ( 'custom' === imageSize ) {
			imageData = imageCustomDimension;
		} else {
			// Getting the images sizes values from the control options (regex: NUMBER{space}x{space}NUMBER).
			imageDimensions = sizesOptions[ imageSize ].match( /(?<=-\s)(\d*\sx\s\d*)/gi );

			if ( imageDimensions && imageDimensions.length ) {
				imageDimensions = imageDimensions[ 0 ].split( ' x ' );

				imageData.width = imageDimensions[ 0 ];
				imageData.height = imageDimensions[ 1 ];
			}
		}

		return imageData;
	};
};

module.exports = new ImagesManager();
