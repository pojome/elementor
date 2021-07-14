import DocumentHelper from 'elementor-document/helper';

const VIEW_CHOOSE_PRESET = 'choose-preset';
const VIEW_DROP_AREA = 'drop-area';

module.exports = Marionette.ItemView.extend( {
	template: '#tmpl-e-container-presets',

	className: 'elementor-empty-view e-container-select-preset',

	attributes() {
		return {
			'data-view': VIEW_CHOOSE_PRESET,
		};
	},

	ui() {
		return {
			presets: '.e-container-preset',
			closeButton: '.e-container-select-preset--close',
		};
	},

	events() {
		return {
			click: 'onClick',
			'click @ui.closeButton': 'onCloseButtonClick',
			'click @ui.presets': 'onPresetClick',
		};
	},

	/**
	 * Set the current view using `data-view` attr.
	 *
	 * @param {string} view - The view to show.
	 *
	 * @return {void}
	 */
	setView( view ) {
		this.$el[ 0 ].dataset.view = view;
	},

	/**
	 * Get the parent view's Container.
	 *
	 * @return {Container} - The parent container.
	 */
	getContainer() {
		if ( ! this.container ) {
			this.container = this._parent.container;
		}

		return this.container;
	},

	/**
	 * Close the preset view.
	 *
	 * @return {void}
	 */
	onCloseButtonClick() {
		this.setView( VIEW_DROP_AREA );
	},

	/**
	 * Create multiple container elements.
	 *
	 * @param {Number} count - Count of Containers to create.
	 * @param {Object} settings - Settings to set to each Container.
	 * @param {Container} container - The Container object to create the new Container elements inside.
	 *
	 * @return {Container[]} - Array of the newly created Containers.
	 */
	createContainers( count, settings = {}, container = this.getContainer() ) {
		const containers = [];

		for ( let i = 0; i < count; i++ ) {
			containers.push( this.createContainer( settings, container ) );
		}

		return containers;
	},

	/**
	 * Create a Container element.
	 *
	 * @param {Object} settings - Settings to set to each Container.
	 * @param {Container} container - The Container object to create the new Container elements inside.
	 *
	 * @return {Container} - The newly created Container.
	 */
	createContainer( settings = {}, container = this.getContainer() ) {
		return $e.run( 'document/elements/create', {
			container,
			model: {
				elType: 'container',
				settings,
			},
		} );
	},

	/**
	 * Change Container settings.
	 *
	 * @param {Object} settings - New settings.
	 * @param {Container} container - Container to set the settings to.
	 *
	 * @return {void}
	 */
	setContainerSettings( settings, container = this.getContainer() ) {
		$e.run( 'document/elements/settings', {
			container,
			settings,
			options: {
				external: true,
			},
		} );
	},

	/**
	 * Set the Container flex direction.
	 *
	 * @param {string} direction - New direction.
	 * @param {Container} container - Container to set the direction to.
	 *
	 * @return {void}
	 */
	setContainerDirection( direction, container = this.getContainer() ) {
		this.setContainerSettings( { container_flex_direction: direction }, container );
	},

	/**
	 * Create a preset for Container.
	 *
	 * @param {MouseEvent} e - Click event.
	 *
	 * @return {void}
	 */
	onPresetClick( e ) {
		e.stopPropagation();

		const { preset } = e.currentTarget.dataset;

		switch ( preset ) {
			case '100':
				this.onCloseButtonClick();
				break;

			case '50':
				this.setContainerDirection( 'row' );
				this.createContainers( 2 );
				break;

			case '50-50':
				this.setContainerDirection( 'row' );

				for ( let i = 0; i < 2; i++ ) {
					const parent = this.createContainer();

					this.createContainers( 2, {}, parent );
				}
				break;

			case '33':
				this.setContainerDirection( 'row' );

				this.createContainers( 3 );
				break;

			case '50-25':
				this.setContainerDirection( 'row' );

				const containers = this.createContainers( 2 );

				this.createContainers( 2, {}, containers[ 1 ] );
				break;
		}
	},

	behaviors: function() {
		return {
			contextMenu: {
				behaviorClass: require( 'elementor-behaviors/context-menu' ),
				groups: this.getContextMenuGroups(),
			},
		};
	},

	getContextMenuGroups: function() {
		return [
			{
				name: 'general',
				actions: [
					{
						name: 'paste',
						title: __( 'Paste', 'elementor' ),
						isEnabled: () => DocumentHelper.isPasteEnabled( this._parent.getContainer() ),
						callback: () => $e.run( 'document/ui/paste', {
							container: this._parent.getContainer(),
						} ),
					},
				],
			},
		];
	},

	onClick: function() {
		$e.route( 'panel/elements/categories' );
	},
} );
