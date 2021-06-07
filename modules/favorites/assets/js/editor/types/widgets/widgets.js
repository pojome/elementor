import FavoriteType from '../../favorite-type';
import PanelElementBehavior from './panel-element-behavior';

export default class Widgets extends FavoriteType {
	constructor() {
		super();

		elementor.hooks.addFilter( 'panel/element/behaviors', this.addElementsBehavior );
		elementor.hooks.addFilter( 'panel/element/before', this.addElementsFavoriteButton );
	}

	/**
	 * @inheritDoc
	 */
	getName() {
		return 'widgets';
	}

	/**
	 * @inheritDoc
	 */
	create( favorite ) {
		const widgetCache = this.getWidgetCache( favorite );

		if ( undefined !== widgetCache ) {
			widgetCache.categories.push( this.getCategorySlug() );
		}

		const result = $e.data.create(
			'favorites/index',
			{},
			{
				type: this.getName(),
				favorite,
			},
		);

		this.refreshCategories();

		return result;
	}

	/**
	 * @inheritDoc
	 */
	delete( favorite ) {
		const widgetCache = this.getWidgetCache( favorite );

		if ( undefined !== widgetCache ) {
			const categories = widgetCache.categories;

			categories.splice( categories.indexOf( this.getCategorySlug() ), 1 );
		}

		const result = $e.data.delete(
			'favorites/index',
			{
				type: this.getName(),
				favorite,
			},
		);

		this.refreshCategories();

		return result;
	}

	/**
	 * @inheritDoc
	 */
	toggle( favorite ) {
		const widgetCache = this.getWidgetCache( favorite );

		if ( undefined !== widgetCache ) {
			const args = {
				type: this.getName(),
				favorite,
			};

			if ( widgetCache.categories.includes( this.getCategorySlug() ) ) {
				return $e.run( 'favorites/delete', args );
			}

			return $e.run( 'favorites/create', args );
		}

		return false;
	}

	getCategorySlug() {
		return 'favorites';
	}

	addElementsBehavior( behaviors ) {
		return Object.assign( {}, behaviors, {
			favoriteWidgets: {
				behaviorClass: PanelElementBehavior,
			},
		} );
	}

	addElementsFavoriteButton( html ) {
		const button = jQuery( '<div>' )
				.addClass( 'e-element-favorite-button' ),

			icon = jQuery( '<i>' )
				.attr( 'aria-hidden', true );

		return html + button.append( icon )[ 0 ].outerHTML;
	}

	/**
	 * Re-render the categories view to reflect changes, while restoring
	 * scroll position.
	 */
	refreshCategories() {
		const psElement = elementor.getPanelView().perfectScrollbar.element,
			psScrollTop = psElement.scrollTop,
			psHeight = psElement.scrollHeight;

		$e.route( 'panel/elements/categories', { refresh: true } );

		psElement.scrollTop = psScrollTop + ( psElement.scrollHeight - psHeight );
	}

	/**
	 * Get the widget cache object which store widgets config.
	 *
	 * @param widget
	 * @returns {{}}
	 */
	getWidgetCache( widget ) {
		return elementor.widgetsCache[ widget ];
	}
}
