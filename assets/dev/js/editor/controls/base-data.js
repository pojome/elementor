import BreakpointValidator from 'elementor-validator/breakpoint';

var ControlBaseView = require( 'elementor-controls/base' ),
	TagsBehavior = require( 'elementor-dynamic-tags/control-behavior' ),
	Validator = require( 'elementor-validator/base' ),
	NumberValidator = require( 'elementor-validator/number' ),
	ControlBaseDataView;

ControlBaseDataView = ControlBaseView.extend( {
	validatorTypes: {
		Base: Validator,
		Number: NumberValidator,
		Breakpoint: BreakpointValidator,
	},

	ui: function() {
		var ui = ControlBaseView.prototype.ui.apply( this, arguments );

		_.extend( ui, {
			input: 'input[data-setting][type!="checkbox"][type!="radio"]',
			checkbox: 'input[data-setting][type="checkbox"]',
			radio: 'input[data-setting][type="radio"]',
			select: 'select[data-setting]',
			textarea: 'textarea[data-setting]',
			responsiveSwitchers: '.elementor-responsive-switcher',
			contentEditable: '[contenteditable="true"]',
		} );

		return ui;
	},

	templateHelpers: function() {
		var controlData = ControlBaseView.prototype.templateHelpers.apply( this, arguments );

		controlData.data.controlValue = this.getControlValue();

		return controlData;
	},

	events: function() {
		return {
			'input @ui.input': 'onBaseInputTextChange',
			'change @ui.checkbox': 'onBaseInputChange',
			'change @ui.radio': 'onBaseInputChange',
			'input @ui.textarea': 'onBaseInputTextChange',
			'change @ui.select': 'onBaseInputChange',
			'input @ui.contentEditable': 'onBaseInputTextChange',
			'click @ui.responsiveSwitchers': 'onResponsiveSwitchersClick',
		};
	},

	behaviors: function() {
		const behaviors = ControlBaseView.prototype.behaviors.apply( this, arguments ),
			dynamicSettings = this.options.model.get( 'dynamic' );

		if ( dynamicSettings && dynamicSettings.active ) {
			const tags = _.filter( elementor.dynamicTags.getConfig( 'tags' ), function( tag ) {
				return tag.editable && _.intersection( tag.categories, dynamicSettings.categories ).length;
			} );

			if ( tags.length || elementor.config.user.is_administrator ) {
				behaviors.tags = {
					behaviorClass: TagsBehavior,
					tags: tags,
					dynamicSettings: dynamicSettings,
				};
			}
		}

		return behaviors;
	},

	initialize: function() {
		ControlBaseView.prototype.initialize.apply( this, arguments );

		this.registerValidators();

		if ( this.model.get( 'responsive' ) ) {
			this.setPlaceholderFromParent();
		}

		// TODO: this.elementSettingsModel is deprecated since 2.8.0.
		const settings = this.container ? this.container.settings : this.elementSettingsModel;

		this.listenTo( settings, 'change:external:' + this.model.get( 'name' ), this.onAfterExternalChange );
	},

	getControlValue: function() {
		return this.container.settings.get( this.model.get( 'name' ) );
	},

	getGlobalKey: function() {
		return this.container.globals.get( this.model.get( 'name' ) );
	},

	getGlobalValue: function() {
		return this.globalValue;
	},

	getGlobalDefault: function() {
		const controlGlobalArgs = this.model.get( 'global' );

		if ( controlGlobalArgs?.default ) {
			// If the control is a color/typography control and default colors/typography are disabled, don't return the global value.
			if ( ! elementor.config.globals.defaults_enabled[ this.getGlobalMeta().controlType ] ) {
				return '';
			}

			const { command, args } = $e.data.commandExtractArgs( controlGlobalArgs.default ),
				result = $e.data.getCache( $e.components.get( 'globals' ), command, args.query );

			return result?.value;
		}

		// No global default.
		return '';
	},

	getCurrentValue: function() {
		if ( this.getGlobalKey() && ! this.globalValue ) {
			return '';
		}

		if ( this.globalValue ) {
			return this.globalValue;
		}

		const controlValue = this.getControlValue();

		if ( controlValue ) {
			return controlValue;
		}

		return this.getGlobalDefault();
	},

	isGlobalActive: function() {
		return this.options.model.get( 'global' )?.active;
	},

	setValue: function( value ) {
		this.setSettingsModel( value );
	},

	setSettingsModel: function( value ) {
		const key = this.model.get( 'name' );
		$e.run( 'document/elements/settings', {
			container: this.options.container,
			settings: {
				[ key ]: value,
			},
		} );

		this.triggerMethod( 'settings:change' );
	},

	applySavedValue: function() {
		this.setInputValue( '[data-setting="' + this.model.get( 'name' ) + '"]', this.getControlValue() );
	},

	getEditSettings: function( setting ) {
		var settings = this.getOption( 'elementEditSettings' ).toJSON();

		if ( setting ) {
			return settings[ setting ];
		}

		return settings;
	},

	setEditSetting: function( settingKey, settingValue ) {
		const settings = this.getOption( 'elementEditSettings' ) || this.getOption( 'container' ).settings;

		settings.set( settingKey, settingValue );
	},

	/**
	 * Get the responsive parent view if exists.
	 *
	 * @returns {ControlBaseDataView|null}
	 */
	getResponsiveParentView: function() {
		const parent = this.model.get( 'parent' );

		return parent && this.container.panel.getControlView( parent );
	},

	/**
	 * Get the responsive child view if exists.
	 *
	 * @returns {ControlBaseDataView|null}
	 */
	getResponsiveChildView: function() {
		const child = this.model.get( 'child' );

		return child && this.container.panel.getControlView( child );
	},

	/**
	 * Get prepared placeholder from the responsive parent, and put it into current
	 * control model as placeholder.
	 */
	setPlaceholderFromParent: function() {
		const parent = this.getResponsiveParentView();

		if ( parent ) {
			this.model.set( 'placeholder', parent.preparePlaceholderForChildren() );
		}
	},

	/**
	 * Returns the value of the current control if exists, or the parent value if not,
	 * so responsive children can set it as their placeholder. When there are multiple
	 * inputs, the inputs which are empty on this control will inherit their values
	 * from the responsive parent.
	 * For example, if on desktop the padding of all edges is 10, and on tablet only
	 * padding right and left is set to 15, the mobile control placeholder will
	 * eventually be: { top: 10, right: 15, left: 15, bottom: 10 }, because of the
	 * inheritance of multiple values.
	 *
	 * @returns {*}
	 */
	preparePlaceholderForChildren: function() {
		const parentValue = this.getResponsiveParentView()?.preparePlaceholderForChildren(),
			cleanValue = this.getCleanControlValue();

		if ( cleanValue ) {
			return Object.assign( {}, parentValue, cleanValue );
		}

		return this.getControlValue() || parentValue;
	},

	/**
	 * Start the re-rendering recursive chain from the responsive child of this
	 * control. It's useful when the current control value is changed and we want
	 * to update all responsive children. In this case, the re-rendering is supposed
	 * to be applied only from the responsive child of this control and on.
	 */
	propagatePlaceholder: function() {
		const child = this.getResponsiveChildView();

		if ( child ) {
			child.renderWithChildren();
		}
	},

	/**
	 * Re-render current control and trigger this method on the responsive child.
	 * The purpose of those actions is to recursively re-render all responsive
	 * children.
	 */
	renderWithChildren: function() {
		const child = this.getResponsiveChildView();

		this.render();

		if ( child ) {
			child.renderWithChildren();
		}
	},

	/**
	 * This method's primary implementation is written under base-multiple view,
	 * please refer there for explanation.
	 */
	getCleanControlValue: function() {},

	onAfterChange: function( control ) {
		if ( Object.keys( control.changed ).includes( this.model.get( 'name' ) ) ) {
			this.propagatePlaceholder();
		}

		ControlBaseView.prototype.onAfterChange.apply( this, arguments );
	},

	getInputValue: function( input ) {
		var $input = this.$( input );

		if ( $input.is( '[contenteditable="true"]' ) ) {
			return $input.html();
		}

		var inputValue = $input.val(),
			inputType = $input.attr( 'type' );

		if ( -1 !== [ 'radio', 'checkbox' ].indexOf( inputType ) ) {
			return $input.prop( 'checked' ) ? inputValue : '';
		}

		if ( 'number' === inputType && _.isFinite( inputValue ) ) {
			return +inputValue;
		}

		// Temp fix for jQuery (< 3.0) that return null instead of empty array
		if ( 'SELECT' === input.tagName && $input.prop( 'multiple' ) && null === inputValue ) {
			inputValue = [];
		}

		return inputValue;
	},

	setInputValue: function( input, value ) {
		var $input = this.$( input ),
			inputType = $input.attr( 'type' );

		if ( 'checkbox' === inputType ) {
			$input.prop( 'checked', !! value );
		} else if ( 'radio' === inputType ) {
			$input.filter( '[value="' + value + '"]' ).prop( 'checked', true );
		} else {
			$input.val( value );
		}
	},

	addValidator: function( validator ) {
		this.validators.push( validator );
	},

	registerValidators: function() {
		this.validators = [];

		var validationTerms = {};

		if ( this.model.get( 'required' ) ) {
			validationTerms.required = true;
		}

		if ( ! jQuery.isEmptyObject( validationTerms ) ) {
			this.addValidator( new this.validatorTypes.Base( {
				validationTerms: validationTerms,
			} ) );
		}

		const validators = this.model.get( 'validators' );

		if ( validators ) {
			Object.entries( validators ).forEach( ( [ key, args ] ) => {
				this.addValidator( new this.validatorTypes[ key ]( {
					validationTerms: args,
				} ) );
			} );
		}
	},

	onBeforeRender: function() {
		this.setPlaceholderFromParent();
	},

	onRender: function() {
		ControlBaseView.prototype.onRender.apply( this, arguments );

		if ( this.model.get( 'responsive' ) ) {
			this.renderResponsiveSwitchers();
		}

		this.applySavedValue();

		this.triggerMethod( 'ready' );

		this.toggleControlVisibility();

		this.addTooltip();
	},

	onBaseInputTextChange: function( event ) {
		this.onBaseInputChange( event );
	},

	onBaseInputChange: function( event ) {
		clearTimeout( this.correctionTimeout );

		var input = event.currentTarget,
			value = this.getInputValue( input ),
			validators = this.validators.slice( 0 ),
			settingsValidators = this.container.settings.validators[ this.model.get( 'name' ) ];

		if ( settingsValidators ) {
			validators = validators.concat( settingsValidators );
		}

		if ( validators ) {
			var oldValue = this.getControlValue( input.dataset.setting );

			var isValidValue = validators.every( function( validator ) {
				return validator.isValid( value, oldValue );
			} );

			if ( ! isValidValue ) {
				this.correctionTimeout = setTimeout( this.setInputValue.bind( this, input, oldValue ), 1200 );

				return;
			}
		}

		this.updateElementModel( value, input );

		this.triggerMethod( 'input:change', event );
	},

	onResponsiveSwitchersClick: function( event ) {
		const $switcher = jQuery( event.currentTarget ),
			device = $switcher.data( 'device' ),
			$switchersWrapper = this.ui.responsiveSwitchersWrapper,
			selectedOption = $switcher.index();

		$switchersWrapper.toggleClass( 'elementor-responsive-switchers-open' );
		$switchersWrapper[ 0 ].style.setProperty( '--selected-option', selectedOption );

		this.triggerMethod( 'responsive:switcher:click', device );

		elementor.changeDeviceMode( device );
	},

	renderResponsiveSwitchers: function() {
		var templateHtml = Marionette.Renderer.render( '#tmpl-elementor-control-responsive-switchers', this.model.attributes );

		this.ui.controlTitle.after( templateHtml );

		this.ui.responsiveSwitchersWrapper = this.$el.find( '.elementor-control-responsive-switchers' );
	},

	onAfterExternalChange: function() {
		this.hideTooltip();

		this.applySavedValue();
	},

	addTooltip: function() {
		this.ui.tooltipTargets = this.$el.find( '.tooltip-target' );

		if ( ! this.ui.tooltipTargets.length ) {
			return;
		}

		// Create tooltip on controls
		this.ui.tooltipTargets.tipsy( {
			gravity: function() {
				// `n` for down, `s` for up
				var gravity = jQuery( this ).data( 'tooltip-pos' );

				if ( undefined !== gravity ) {
					return gravity;
				}
				return 's';
			},
			title: function() {
				return this.getAttribute( 'data-tooltip' );
			},
		} );
	},

	hideTooltip: function() {
		if ( this.ui.tooltipTargets.length ) {
			this.ui.tooltipTargets.tipsy( 'hide' );
		}
	},

	updateElementModel: function( value ) {
		this.setValue( value );
	},
}, {
	// Static methods
	getStyleValue: function( placeholder, controlValue, controlData ) {
		if ( 'DEFAULT' === placeholder ) {
			return controlData.default;
		}

		return controlValue;
	},

	onPasteStyle: function() {
		return true;
	},
} );

module.exports = ControlBaseDataView;
