import CommandBase from 'elementor-api/modules/command-base';

export default class CommandsBase extends CommandBase {
	/**
	 * @inheritDoc
	 */
	initialize( args = {} ) {
		args = _.assign( { store: true }, args );

		if ( 'string' === typeof args.widgets ) {
			args.widgets = [ args.widgets ];
		}

		this.args = args;
	}

	/**
	 * @inheritDoc
	 */
	validateArgs( args = {} ) {
		try {
			this.requireArgumentType( 'widgets', 'string', args );
		} catch ( e ) {
			this.requireArgumentType( 'widgets', 'array', args );
		}
	}

	/**
	 * Send update request along with the widgets and the action needed.
	 *
	 * @param widgets
	 * @param action
	 */
	update( widgets, action ) {
		$e.data.update(
			this.component.getFavoritesDataCommand(),
			{
				action: action,
				data: widgets,
			},
			{ key: 'widgets' },
		);
	}

	/**
	 * Re-render the categories view to reflect changes, while restoring
	 * scroll position.
	 */
	refreshCategories() {
		$e.route( 'panel/elements/categories', { refresh: true } );
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

	/**
	 * Get the favorites category name.
	 *
	 * @returns {string}
	 */
	getCategoryName() {
		return 'favorites';
	}
}
