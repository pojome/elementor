class VideoModule extends elementorModules.frontend.handlers.Base {
	getDefaultSettings() {
		return {
			selectors: {
				imageOverlay: '.elementor-custom-embed-image-overlay',
				video: '.elementor-video',
				videoIframe: '.elementor-video-iframe',
			},
		};
	}

	getDefaultElements() {
		var selectors = this.getSettings( 'selectors' );

		return {
			$imageOverlay: this.$element.find( selectors.imageOverlay ),
			$video: this.$element.find( selectors.video ),
			$videoIframe: this.$element.find( selectors.videoIframe ),
		};
	}

	getLightBox() {
		return elementorFrontend.utils.lightbox;
	}

	handleVideo() {
		if ( ! this.getElementSettings( 'lightbox' ) ) {
			this.elements.$imageOverlay.remove();

			this.playVideo();
		}
	}

	playVideo() {
		if ( this.elements.$video.length ) {
			this.elements.$video[ 0 ].play();

			return;
		}

		const $videoIframe = this.elements.$videoIframe,
			lazyLoad = $videoIframe.data( 'lazy-load' );

		if ( lazyLoad ) {
			$videoIframe.attr( 'src', lazyLoad );
		}

		const newSourceUrl = $videoIframe[ 0 ].src.replace( '&autoplay=0', '' );

		$videoIframe[ 0 ].src = newSourceUrl + '&autoplay=1';
	}

	animateVideo() {
		this.getLightBox().setEntranceAnimation( this.getCurrentDeviceSetting( 'lightbox_content_animation' ) );
	}

	handleAspectRatio() {
		this.getLightBox().setVideoAspectRatio( this.getElementSettings( 'aspect_ratio' ) );
	}

	bindEvents() {
		this.elements.$imageOverlay.on( 'click', this.handleVideo );
	}

	onElementChange( propertyName ) {
		if ( 0 === propertyName.indexOf( 'lightbox_content_animation' ) ) {
			this.animateVideo();

			return;
		}

		var isLightBoxEnabled = this.getElementSettings( 'lightbox' );

		if ( 'lightbox' === propertyName && ! isLightBoxEnabled ) {
			this.getLightBox().getModal().hide();

			return;
		}

		if ( 'aspect_ratio' === propertyName && isLightBoxEnabled ) {
			this.handleAspectRatio();
		}
	}
}

export default ( $scope ) => {
	elementorFrontend.elementsHandler.addHandler( VideoModule, { $element: $scope } );
};
