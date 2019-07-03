class GlobalHandler extends elementorModules.frontend.handlers.Base {
	getWidgetType() {
		return 'global';
	}

	animate() {
		const $element = this.$element,
			animation = this.getAnimation();

		if ( 'none' === animation ) {
			$element.removeClass( 'elementor-invisible' );
			return;
		}

		const elementSettings = this.getElementSettings(),
			animationDelay = elementSettings._animation_delay || elementSettings.animation_delay || 0;

		$element.removeClass( animation );

		if ( this.currentAnimation ) {
			$element.removeClass( this.currentAnimation );
		}

		this.currentAnimation = animation;

		setTimeout( function() {
			$element.removeClass( 'elementor-invisible' ).addClass( 'animated ' + animation );
		}, animationDelay );
	}

	getAnimation() {
		return this.getCurrentDeviceSetting( 'animation' ) || this.getCurrentDeviceSetting( '_animation' );
	}

	onInit() {
		elementorModules.frontend.handlers.Base.prototype.onInit.apply( this, arguments );

		if ( this.getAnimation() ) {
			elementorFrontend.waypoint( this.$element, this.animate.bind( this ) );
		}
	}

	onElementChange( propertyName ) {
		if ( /^_?animation/.test( propertyName ) ) {
			this.animate();
		}
	}
}

export default ( $scope ) => {
	elementorFrontend.elementsHandler.addHandler( GlobalHandler, { $element: $scope } );
};
