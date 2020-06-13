var ControlBaseView = require( 'elementor-controls/base' ),
	TagsBehavior = require( 'elementor-dynamic-tags/control-behavior' ),
	Validator = require( 'elementor-validator/base' ),
	ControlBaseDataView;

ControlBaseDataView = ControlBaseView.extend( {
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
		const behaviors = {},
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

		// TODO: this.elementSettingsModel is deprecated since 2.8.0.
		const settings = this.container ? this.container.settings : this.elementSettingsModel;

		this.listenTo( settings, 'change:external:' + this.model.get( 'name' ), this.onAfterExternalChange );
	},

	getControlValue: function( key ) {
		const controlValue = this.container.settings.get( this.model.get( 'name' ) );

		if ( this.isMultipleValue() ) {
			if ( key ) {
				let value = controlValue[ key ];

				if ( undefined === value ) {
					value = '';
				}

				return value;
			}

			return elementorCommon.helpers.cloneObject( controlValue );
		}

		return this.container.settings.get( this.model.get( 'name' ) );
	},

	setValue: function( key, value ) {
		if ( this.isMultipleValue() ) {
			const controlValue = this.getControlValue();

			if ( 'object' === typeof key ) {
				_.each( key, function( internalValue, internalKey ) {
					controlValue[ internalKey ] = internalValue;
				} );
			} else {
				controlValue[ key ] = value;
			}

			this.setSettingsModel( controlValue );
		} else {
			this.setSettingsModel( value );
		}
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
		const controlValue = this.getControlValue();

		if ( this.isMultipleValue() ) {
			const $inputs = this.$( '[data-setting]' ),
				self = this;

			_.each( controlValue, function( value, key ) {
				var $input = $inputs.filter( function() {
					return key === this.dataset.setting;
				} );

				self.setInputValue( $input, value );
			} );
		} else {
			this.setInputValue( '[data-setting="' + this.model.get( 'name' ) + '"]', controlValue );
		}
	},

	// This method checks whether a control value is of type single or multiple (array/object)
	isMultipleValue: function() {
		return jQuery.isPlainObject( this.container.settings.get( this.model.get( 'name' ) ) );
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
			this.addValidator( new Validator( {
				validationTerms: validationTerms,
			} ) );
		}
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

	updateElementModel: function( value, input ) {
		if ( this.isMultipleValue() ) {
			const key = input.dataset.setting;

			this.setValue( key, value );
		} else {
			this.setValue( null, value );
		}
	},
}, {
	// Static methods
	getStyleValue: function( placeholder, controlValue, controlData ) {
		if ( ! jQuery.isPlainObject( controlValue ) ) {
			if ( 'DEFAULT' === placeholder ) {
				return controlData.default;
			}

			return controlValue;
		}

		return controlValue[ placeholder.toLowerCase() ];
	},

	onPasteStyle: function() {
		return true;
	},
} );

module.exports = ControlBaseDataView;
