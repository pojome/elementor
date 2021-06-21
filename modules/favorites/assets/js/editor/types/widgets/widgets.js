import FavoriteType from '../../favorite-type';

export default class Widgets extends FavoriteType {
	constructor() {
		super();

		elementor.hooks.addFilter( 'panel/element/contextMenuGroups', this.addContextMenuGroups.bind( this ) );
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

		return false;
	}

	/**
	 * @inheritDoc
	 */
	delete( favorite ) {
		const widgetCache = this.getWidgetCache( favorite );

		if ( undefined !== widgetCache ) {
			widgetCache.categories.splice( widgetCache.categories.indexOf( this.getCategorySlug() ), 1 );

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

		return false;
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

			if ( this.isFavorite( favorite ) ) {
				return $e.run( 'favorites/delete', args );
			}

			return $e.run( 'favorites/create', args );
		}

		return false;
	}

	/**
	 * Check whether a given widget is already favorite.
	 *
	 * @param widget
	 * @returns {boolean}
	 */
	isFavorite( widget ) {
		const widgetCache = this.getWidgetCache( widget );

		if ( undefined !== widgetCache ) {
			return widgetCache.categories
				.includes( this.getCategorySlug() );
		}

		return false;
	}

	/**
	 * Get favorites category name in widgets panel.
	 *
	 * @returns {string}
	 */
	getCategorySlug() {
		return 'favorites';
	}

	/**
	 * A filter callback to add the favorites context menu groups to
	 * element view.
	 *
	 * @param groups
	 * @param context
	 * @returns {[]}
	 */
	addContextMenuGroups( groups, context ) {
		const widget = context.options.model.get( 'widgetType' ),
			toggleText = this.isFavorite( widget ) ?
			__( 'Remove from Favorites', 'elementor' ) :
			__( 'Add to Favorites', 'elementor' );

		return groups.concat( [
			{
				name: 'favorite-toggle',
				actions: [
					{
						name: 'toggle',
						icon: 'eicon-star',
						title: toggleText,
						callback: () => this.toggle( widget ),
					},
				],
			},
			{
				name: 'favorite-reset',
				actions: [
					{
						name: 'reset',
						icon: 'eicon-undo',
						title: __( 'Reset All Favorites', 'elementor' ),
						callback: () => this.reset(),
					},
				],
			},
		] );
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
