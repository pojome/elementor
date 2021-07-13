import ArgsObject from '../../modules/imports/args-object';
import Panel from './panel';
import ChildrenCollection from './model/children-collection';

/**
 * TODO: ViewsOptions
 * @typedef {(Marionette.View|Marionette.CompositeView|BaseElementView|SectionView|BaseContainer)} ViewsOptions
 */

export default class Container extends ArgsObject {
	// TODO: Swap those backwards compatibility is required.
	static TYPE_REPEATER = 'repeater-control';
	static TYPE_REPEATER_ITEM = 'repeater';

	/**
	 * Container type.
	 *
	 * @type {string}
	 */
	type;

	/**
	 * Container id.
	 *
	 * @type {string}
	 */
	id;

	/**
	 * Document Object.
	 *
	 * @type  {{}}
	 */
	document;

	/**
	 * Container model.
	 *
	 * @type {Backbone.Model}
	 */
	model;

	/**
	 * Container settings.
	 *
	 * @type {Backbone.Model}
	 */
	settings;

	/**
	 * Container view.
	 *
	 * @type {ViewsOptions}
	 */
	view;

	/**
	 * Container parent.
	 *
	 * @type {Container}
	 */
	parent;

	/**
	 * Container children(s).
	 *
	 * @type {ChildrenCollection}
	 */
	children = new ChildrenCollection();

	/**
	 * Container dynamic.
	 *
	 * @type {Backbone.Model}
	 */
	dynamic;

	/**
	 * Container globals.
	 *
	 * @type {Backbone.Model}
	 */
	globals;

	/**
	 * Container label.
	 *
	 * @type {string}
	 */
	label;

	/**
	 * Container controls.
	 *
	 * @type {{}}
	 */
	controls = {};

	/**
	 * Repeaters containers
	 *
	 * @type {{}}
	 */
	repeaters = {};

	/**
	 * Container renderer (The one who render).
	 *
	 * @type {Container}
	 */
	renderer;

	/**
	 * Container panel.
	 *
	 * @type {Panel}
	 */
	panel;

	/**
	 * Function constructor().
	 *
	 * Create container.
	 *
	 * @param {{}} args
	 *
	 * @throws {Error}
	 */
	constructor( args ) {
		super( args );

		// Validate args.
		this.validateArgs( args );

		args = Object.entries( args );

		// If empty.
		if ( 0 === args.length ) {
			throw Error( 'Container cannot be empty.' );
		}

		// Set properties, if not defined - keep the defaults.
		args.forEach( ( [ key, value ] ) => {
			this[ key ] = 'undefined' === typeof value ? this[ key ] : value;
		} );

		if ( 'undefined' === typeof this.renderer ) {
			this.renderer = this;
		}

		if ( ! this.document ) {
			this.document = elementor.documents.getCurrent();
		}

		this.dynamic = new Backbone.Model( this.settings.get( '__dynamic__' ) );
		this.globals = new Backbone.Model( this.settings.get( '__globals__' ) );
		this.panel = new Panel( this );

		this.initialize();
	}

	initialize() {
		if ( this.view ) {
			this.addToParent();
			this.handleChildrenRecursive();

			this.view.on( 'destroy', this.removeFromParent.bind( this ) );
		}

		this.handleRepeaterChildren();
	}

	validateArgs( args ) {
		this.requireArgumentType( 'type', 'string', args );
		this.requireArgumentType( 'id', 'string', args );

		this.requireArgumentInstance( 'settings', Backbone.Model, args );
		this.requireArgumentInstance( 'model', Backbone.Model, args );
	}

	/**
	 * Function getGroupRelatedControls().
	 *
	 * Example:
	 * Settings = { typography_typography: 'whatever', button_text_color: 'whatever' };
	 * Result { control_name: controlValue, ... - and so on };
	 * `Object.keys( Result ) = [ 'typography_typography', 'typography_font_family', 'typography_font_size', 'typography_font_size_tablet', 'typography_font_size_mobile', 'typography_font_weight', 'typography_text_transform', 'typography_font_style', 'typography_text_decoration', 'typography_line_height', 'typography_line_height_tablet', 'typography_line_height_mobile', 'typography_letter_spacing', 'typography_letter_spacing_tablet', 'typography_letter_spacing_mobile', 'button_text_color' ]`.
	 *
	 * @param {{}} settings
	 *
	 * @return {{}}
	 */
	getGroupRelatedControls( settings ) {
		const result = {};

		Object.keys( settings ).forEach( ( settingKey ) => {
			Object.values( this.controls ).forEach( ( control ) => {
				if ( settingKey === control.name ) {
					result[ control.name ] = control;
				} else if ( this.controls[ settingKey ]?.groupPrefix ) {
					const { groupPrefix } = this.controls[ settingKey ];

					if ( control.name.toString().startsWith( groupPrefix ) ) {
						result[ control.name ] = control;
					}
				}
			} );
		} );

		return result;
	}

	/**
	 * Function getUtilizedControls().
	 *
	 * Should return all controls that effecting the container.
	 */
	getUtilizedControls() {
		const result = {},
			activeControls = this.settings.getActiveControls();

		Object.entries( activeControls ).forEach( ( [ controlName, control ] ) => {
			const controlValue = this.settings.get( control.name );

			if ( control.global && ! controlValue?.length ) {
				if ( this.globals.get( control.name )?.length || this.getGlobalDefault( controlName ).length ) {
					control.global.utilized = true;

					result[ controlName ] = control;

					return;
				}
			}

			if ( control.dynamic ) {
				if ( this.dynamic.get( controlName ) ) {
					control.dynamic.utilized = true;

					result[ controlName ] = control;

					return;
				}
			}

			if ( controlValue === control.default ) {
				return;
			}

			if ( ! controlValue ) {
				return;
			}

			if ( 'object' === typeof controlValue &&
				Object.values( controlValue ).join() === Object.values( control.default ).join() ) {
				return;
			}

			result[ controlName ] = control;
		} );

		return result;
	}

	handleChildrenRecursive() {
		if ( this.view.children?.length ) {
			Object.values( this.view.children._views ).forEach( ( view ) => {
				if ( ! view.container ) {
					return;
				}
				const container = view.container;

				container.parent.children[ view._index ] = container;
				container.handleChildrenRecursive();
			} );
		} else {
			this.children.clear();
		}
	}

	addToParent() {
		if ( ! this.parent.children || this.isRepeaterItem() ) {
			return;
		}

		// On create container tell the parent where it was created.
		this.parent.children.splice( this.view._index, 0, this );
	}

	removeFromParent() {
		if ( ! this.parent.children || this.isRepeater() ) {
			return;
		}

		// When delete container its should notify its parent, that his children is dead.
		this.parent.children = this.parent.children.filter( ( filtered ) => filtered.id !== this.id );
	}

	handleRepeaterChildren() {
		Object.values( this.controls ).forEach( ( control ) => {
			if ( ! control.is_repeater ) {
				return;
			}

			const model = new Backbone.Model( {
				name: control.name,
			} );

			this.repeaters[ control.name ] = new elementorModules.editor.Container( {
				type: Container.TYPE_REPEATER,
				id: control.name,
				model,
				settings: model,
				view: this.view,
				parent: this,
				label: control.label || control.name,
				controls: {},
				renderer: this.renderer,
			} );

			this.settings.get( control.name ).forEach( ( rowModel, index ) => {
				this.addRepeaterItem( control.name, rowModel, index );
			} );
		} );

		// Backwards Compatibility: if there is only one repeater (type=repeater), set it's children as current children.
		// Since 3.0.0.
		if ( [ 'widget', 'document' ].includes( this.type ) ) {
			const repeaters = Object.values( this.controls ).filter( ( control ) => 'repeater' === control.type );

			if ( 1 === repeaters.length ) {
				Object.defineProperty( this, 'children', {
					get() {
						elementorCommon.helpers.softDeprecated( 'children', '3.0.0', 'container.repeaters[ repeaterName ].children' );
						return this.repeaters[ repeaters[ 0 ].name ].children;
					},
				} );
			}
		}
	}

	/**
	 * Function addRepeaterItem().
	 *
	 * The method add repeater item, find the repeater control by it name, and create new container for the item.
	 *
	 * @param {string} repeaterName
	 * @param {Backbone.Model} rowSettingsModel
	 * @param {number} index
	 *
	 * @returns {Container}
	 */
	addRepeaterItem( repeaterName, rowSettingsModel, index ) {
		let rowId = rowSettingsModel.get( '_id' );

		// TODO: Temp backwards compatibility. since 2.8.0.
		if ( ! rowId ) {
			rowId = 'bc-' + elementorCommon.helpers.getUniqueId();
			rowSettingsModel.set( '_id', rowId );
		}

		this.repeaters[ repeaterName ].children.splice( index, 0, new elementorModules.editor.Container( {
			type: Container.TYPE_REPEATER_ITEM,
			id: rowSettingsModel.get( '_id' ),
			model: new Backbone.Model( {
				name: repeaterName,
			} ),
			settings: rowSettingsModel,
			view: this.view,
			parent: this.repeaters[ repeaterName ],
			label: this.label + ' ' + __( 'Item', 'elementor' ),
			controls: rowSettingsModel.options.controls,
			renderer: this.renderer,
		} ) );

		return this.repeaters[ repeaterName ];
	}

	/**
	 * Function lookup().
	 *
	 * If the view were destroyed, try to find it again if it exists.
	 *
	 * TODO: Refactor.
	 *
	 * @returns {Container}
	 */
	lookup() {
		let result = this;

		if ( ! this.renderer ) {
			return this;
		}

		if ( this !== this.renderer && this.renderer.view?.isDisconnected && this.renderer.view.isDisconnected() ) {
			this.renderer = this.renderer.lookup();
		}

		if ( undefined === this.view || ! this.view.lookup || ! this.view.isDisconnected() ) {
			// Hack For repeater item the result is the parent container.
			if ( Container.TYPE_REPEATER_ITEM === this.type ) {
				this.settings = this.parent.parent.settings.get( this.model.get( 'name' ) ).findWhere( { _id: this.id } );
			}
			return result;
		}

		const lookup = this.view.lookup();

		if ( lookup ) {
			result = lookup.getContainer();

			// Hack For repeater item the result is the parent container.
			if ( Container.REPEATER === this.type ) {
				this.settings = result.settings.get( this.model.get( 'name' ) ).findWhere( { _id: this.id } );
				return this;
			}

			// If lookup were done, new container were created and parent does not know about it.
			if ( result.parent.children ) {
				result.parent.children[ result.view._index ] = result;
			}
		}

		return result;
	}

	/**
	 * Function render().
	 *
	 * Call view render.
	 *
	 * Run's `this.renderer.view.renderOnChange( this.settings ) `.
	 * When `this.renderer` exist.
	 *
	 */
	render() {
		if ( ! this.renderer ) {
			return;
		}

		this.renderer.view.renderOnChange( this.settings );
	}

	renderUI() {
		if ( ! this.renderer ) {
			return;
		}

		this.renderer.view.renderUI();
	}

	isEditable() {
		return 'edit' === elementor.channels.dataEditMode.request( 'activeMode' ) && 'open' === this.document.editor.status;
	}

	isDesignable() {
		return elementor.userCan( 'design' ) && this.isEditable();
	}

	isRepeater() {
		return Container.TYPE_REPEATER === this.type;
	}

	isRepeaterItem() {
		return Container.TYPE_REPEATER_ITEM === this.type;
	}

	getSetting( name, localOnly = false ) {
		const localValue = this.settings.get( name );

		if ( localOnly ) {
			return localValue;
		}

		// Try to get the value in the order: Global, Local, Global default.
		let globalValue;

		if ( this.getGlobalKey( name ) ) {
			globalValue = this.getGlobalValue( name );
		}

		return globalValue || localValue || this.getGlobalDefault( name );
	}

	getGlobalKey( name ) {
		return this.globals.get( name );
	}

	getGlobalValue( name ) {
		const control = this.controls[ name ],
			globalKey = this.getGlobalKey( name ),
			globalArgs = $e.data.commandExtractArgs( globalKey ),
			data = $e.data.getCache( $e.components.get( 'globals' ), globalArgs.command, globalArgs.args.query );

		if ( ! data?.value ) {
			return;
		}

		const id = data.id;

		let value;

		// it's a global settings with additional controls in group.
		if ( control.groupType ) {
			let propertyName = control.name.replace( control.groupPrefix, '' ).replace( /(_tablet|_mobile)$/, '' );

			if ( ! data.value[ elementor.config.kit_config.typography_prefix + propertyName ] ) {
				return;
			}

			propertyName = propertyName.replace( '_', '-' );

			value = `var( --e-global-${ control.groupType }-${ id }-${ propertyName } )`;

			if ( elementor.config.ui.defaultGenericFonts && control.groupPrefix + 'font_family' === control.name ) {
				value += `, ${ elementor.config.ui.defaultGenericFonts }`;
			}
		} else {
			value = `var( --e-global-${ control.type }-${ id } )`;
		}

		return value;
	}

	/**
	 * Determine if a control's global value is applied.
	 * It actually checks if the local value is different than the global value.
	 *
	 * @param {string} controlName - Control name
	 * @returns {boolean}
	 */
	isGlobalApplied( controlName ) {
		return this.getSetting( controlName ) !== this.settings.get( controlName );
	}

	getGlobalDefault( controlName ) {
		const controlGlobalArgs = this.controls[ controlName ]?.global;

		if ( controlGlobalArgs?.default ) {
			// Temp fix.
			let controlType = this.controls[ controlName ].type;

			if ( 'color' === controlType ) {
				controlType = 'colors';
			}
			// End temp fix

			// If the control is a color/typography control and default colors/typography are disabled, don't return the global value.
			if ( ! elementor.config.globals.defaults_enabled[ controlType ] ) {
				return '';
			}

			const { command, args } = $e.data.commandExtractArgs( controlGlobalArgs.default ),
				result = $e.data.getCache( $e.components.get( 'globals' ), command, args.query );

			return result?.value;
		}

		// No global default.
		return '';
	}
}
