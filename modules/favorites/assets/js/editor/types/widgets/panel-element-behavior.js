export default class extends Marionette.Behavior {
	classes() {
		return {
			favorite: 'e-element-favorite-button',
			active: 'e-element-favorite-button-active',
		};
	}

	ui() {
		const classes = this.classes();

		Object.keys( classes ).map( ( key ) => {
			classes[ key ] = `.${ classes[ key ] }`;
		} );

		return classes;
	}

	events() {
		return {
			'click @ui.favorite': 'onFavoriteClick',
		};
	}

	onRender() {
		jQuery( this.ui.favorite ).addClass( this.isFavorite() ? this.classes().active : '' )
			.find( 'i' ).addClass( this.isFavorite() ? 'eicon-heart' : 'eicon-heart-o' );
	}

	isFavorite() {
		return this.view.model.get( 'categories' ).includes( 'favorites' );
	}

	onFavoriteClick() {
		this.ui.favorite.toggleClass( this.classes().active );

		$e.run( 'favorites/toggle', {
			type: 'widgets',
			favorite: this.view.model.get( 'widgetType' ),
		} );
	}
}
