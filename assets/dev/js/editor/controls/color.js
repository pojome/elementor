import ControlBaseDataView from './base-data';
import ColorPicker from '../utils/color-picker';

export default class extends ControlBaseDataView {
	ui() {
		const ui = super.ui();

		ui.pickerContainer = '.elementor-color-picker-placeholder';

		return ui;
	}

	applySavedValue() {
		// Gets the current OR default value of the control.
		const currentValue = this.getCurrentValue();

		if ( this.colorPicker ) {
			// When there is a global set on the control but there is no value/it doesn't exist, don't show a value.
			if ( currentValue ) {
				// Set the picker color without triggering the 'onChange' event.
				const parsedColor = this.colorPicker.picker._parseLocalColor( currentValue );

				this.colorPicker.picker.setHSVA( ...parsedColor.values, false );
			} else {
				this.colorPicker.picker._clearColor( true );
			}
		} else {
			this.initPicker();
		}

		this.$el.toggleClass( 'e-control-color--no-value', ! currentValue );
	}

	initPicker() {
		const options = {
			picker: {
				el: this.ui.pickerContainer[ 0 ],
				default: this.getCurrentValue(),
				components: {
					opacity: this.model.get( 'alpha' ),
				},
				defaultRepresentation: 'HEX',
			},
			// Don't create the add button in the Global Settings color pickers.
			addButton: this.model.get( 'global' )?.active,
			onChange: () => this.onPickerChange(),
			onClear: () => this.onPickerClear(),
			onAddButtonClick: () => this.onAddGlobalButtonClick(),
		};

		this.colorPicker = new ColorPicker( options );

		this.$pickerButton = jQuery( this.colorPicker.picker.getRoot().button );

		this.addTipsyToPickerButton();

		this.addEyedropper();

		this.$pickerButton.on( 'click', () => this.onPickerButtonClick() );

		jQuery( this.colorPicker.picker.getRoot().root ).addClass( 'elementor-control-unit-1 elementor-control-tag-area' );
	}

	addTipsyToPickerButton() {
		this.$pickerButton.tipsy( {
			title: () => {
				let currentValue = this.getCurrentValue();

				// If there is a global enabled for the control, but the global has no value.
				if ( this.getGlobalKey() && ! currentValue ) {
					currentValue = `${ __( 'Invalid Global Color', 'elementor' ) }`;
				}

				return currentValue || '';
			},
			offset: 4,
			gravity: () => 's',
		} );
	}

	addEyedropper() {
		if ( ! elementorCommon.config.experimentalFeatures[ 'elements-color-picker' ] ) {
			return;
		}

		const $colorPicker = jQuery( Marionette.Renderer.render( '#tmpl-elementor-control-element-color-picker' ) ),
			currentElement = elementor.getPanelView().getCurrentPageView().getOption( 'editedElementView' ),
			$colorPickerToolsContainer = this.colorPicker.$pickerToolsContainer;

		$colorPicker.on( 'click', () => {
			$e.run( 'elements-color-picker/start', {
				container: currentElement.container,
				control: this.model.get( 'name' ),
				trigger: $colorPicker[ 0 ],
			} );
		} );

		$colorPickerToolsContainer.append( $colorPicker );
	}

	getGlobalMeta() {
		return {
			commandName: this.getGlobalCommand(),
			key: this.model.get( 'name' ),
			controlType: 'colors',
			route: 'panel/global/global-colors',
		};
	}

	getNameAlreadyExistsMessage() {
		return '<i class="eicon-info-circle"></i> ' + __( 'Please note that the same exact color already exists in your Global Colors list. Are you sure you want to create it?', 'elementor' );
	}

	getConfirmTextMessage() {
		return __( 'Are you sure you want to create a new Global Color?', 'elementor' );
	}

	getAddGlobalConfirmMessage( globalColors ) {
		const colorTitle = __( 'New Global Color', 'elementor' ),
			currentValue = this.getCurrentValue(),
			$message = jQuery( '<div>', { class: 'e-global__confirm-message' } ),
			$messageText = jQuery( '<div>', { class: 'e-global__confirm-message-text' } ),
			$inputWrapper = jQuery( '<div>', { class: 'e-global__confirm-input-wrapper' } ),
			$colorPreview = this.createColorPreviewBox( currentValue ),
			$input = jQuery( '<input>', { type: 'text', name: 'global-name', placeholder: colorTitle } )
				.val( colorTitle );

		let messageContent;

		// Check if the color already exists in the global colors, and display an appropriate message.
		for ( const globalColor of Object.values( globalColors ) ) {
			if ( currentValue === globalColor.value ) {
				messageContent = this.getNameAlreadyExistsMessage();
				break;
			} else if ( colorTitle === globalColor.title ) {
				messageContent = this.getConfirmTextMessage();
				break;
			} else {
				messageContent = __( 'Are you sure you want to create a new Global Color?', 'elementor' );
			}
		}

		$messageText.html( messageContent );

		$inputWrapper.append( $colorPreview, $input );

		$message.append( $messageText, $inputWrapper );

		return $message;
	}

	getGlobalCommand() {
		return 'globals/colors';
	}

	// The globalData parameter is received from the Data API.
	createGlobalItemMarkup( globalData ) {
		const $color = jQuery( '<div>', { class: 'e-global__preview-item e-global__color', 'data-global-id': globalData.id } ),
			$colorPreview = this.createColorPreviewBox( globalData.value ),
			$colorTitle = jQuery( '<span>', { class: 'e-global__color-title' } )
				.html( globalData.title ),
			$colorHex = jQuery( '<span>', { class: 'e-global__color-hex' } )
				.html( globalData.value );

		$color.append( $colorPreview, $colorTitle, $colorHex );

		return $color;
	}

	createColorPreviewBox( color ) {
		const $colorPreviewContainer = jQuery( '<div>', { class: 'e-global__color-preview-container' } ),
			$colorPreviewColor = jQuery( '<div>', { class: 'e-global__color-preview-color', style: 'background-color: ' + color } ),
			$colorPreviewBg = jQuery( '<div>', { class: 'e-global__color-preview-transparent-bg' } );

		$colorPreviewContainer.append( $colorPreviewBg, $colorPreviewColor );

		return $colorPreviewContainer;
	}

	async getGlobalsList() {
		const result = await $e.data.get( this.getGlobalCommand() );

		return result.data;
	}

	// Create the markup for the colors in the global select dropdown.
	buildGlobalsList( globalColors, $globalPreviewItemsContainer ) {
		Object.values( globalColors ).forEach( ( color ) => {
			if ( ! color.value ) {
				return;
			}

			const $color = this.createGlobalItemMarkup( color );

			$globalPreviewItemsContainer.append( $color );
		} );
	}

	onPickerChange() {
		this.setValue( this.colorPicker.picker.getColor().toHEXA().toString() );

		if ( ! this.isCustom ) {
			this.triggerMethod( 'value:type:change' );

			this.colorPicker.toggleClearButtonState( true );

			if ( this.$el.hasClass( 'e-control-color--no-value' ) ) {
				this.$el.removeClass( 'e-control-color--no-value' );
			}

			this.isCustom = true;
		}
	}

	onPickerClear() {
		this.isCustom = false;

		// Empty the value saved in the control.
		this.setValue( '' );

		// Adjust the Global select box text according to the cleared value.
		this.triggerMethod( 'value:type:change' );

		this.applySavedValue();

		this.colorPicker.toggleClearButtonState( false );
	}

	onPickerButtonClick() {
		if ( this.getGlobalKey() ) {
			this.triggerMethod( 'unset:global:value' );
		} else if ( this.isGlobalActive() && ! this.getControlValue() && this.getGlobalDefault() ) {
			this.triggerMethod( 'unlink:global:default' );
		}

		// If there is a value in the control, set the clear button to active, if not, deactivate it.
		this.colorPicker.toggleClearButtonState( !! this.getCurrentValue() );
	}

	onAddGlobalButtonClick() {
		this.getGlobalsList().then( ( globalsList ) => {
			this.globalsList = globalsList;

			this.triggerMethod( 'add:global:to:list', this.getAddGlobalConfirmMessage( globalsList ) );
		} );
	}

	onBeforeDestroy() {
		if ( this.colorPicker ) {
			this.colorPicker.destroy();
		}
	}
}
