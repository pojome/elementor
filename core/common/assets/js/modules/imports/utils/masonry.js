import ViewModule from '../view-module';

module.exports = ViewModule.extend( {

	getDefaultSettings: function() {
		return {
			container: null,
			items: null,
			columnsCount: 3,
			verticalSpaceBetween: 30,
		};
	},

	getDefaultElements: function() {
		return {
			$container: jQuery( this.getSettings( 'container' ) ),
			$items: jQuery( this.getSettings( 'items' ) ),
		};
	},

	run: function() {
		var heights = [],
			distanceFromTop = this.elements.$container.position().top,
			settings = this.getSettings(),
			columnsCount = settings.columnsCount;

		distanceFromTop += parseInt( this.elements.$container.css( 'margin-top' ), 10 );

		this.elements.$items.each( function( index ) {
			var row = Math.floor( index / columnsCount ),
				$item = jQuery( this ),
				itemHeight = $item[ 0 ].getBoundingClientRect().height + settings.verticalSpaceBetween;

			if ( row ) {
				var itemPosition = $item.position(),
					indexAtRow = index % columnsCount,
					pullHeight = itemPosition.top - distanceFromTop - heights[ indexAtRow ];

				pullHeight -= parseInt( $item.css( 'margin-top' ), 10 );

				pullHeight *= -1;

				$item.css( 'margin-top', pullHeight + 'px' );

				heights[ indexAtRow ] += itemHeight;
			} else {
				heights.push( itemHeight );
			}
		} );
	},
} );
