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

		this.$pickerButton.on( 'click', () => this.onPickerButtonClick() );

		jQuery( this.colorPicker.picker.getRoot().root ).addClass( 'elementor-control-unit-1 elementor-control-tag-area' );
	}

	addTipsyToPickerButton() {
		this.$pickerButton.tipsy( {
			title: () => {
				let currentValue = this.getCurrentValue();

				// If there is a global enabled for the control, but the global has no value.
				if ( this.getGlobalKey() && ! currentValue ) {
					currentValue = `${ elementor.translate( 'invalid' ) } ${ elementor.translate( 'global_color' ) }`;
				}

				return currentValue || '';
			},
			offset: 4,
			gravity: () => 's',
		} );
	}

	getGlobalMeta() {
		return {
			commandName: this.getGlobalCommand(),
			key: this.model.get( 'name' ),
			title: this.colorPicker?.getColorTitle(),
			controlType: 'colors',
			route: 'panel/global/global-colors',
		};
	}

	getAddGlobalConfirmMessage( globalColors ) {
		const colorTitle = this.colorPicker.getColorTitle(),
			currentValue = this.getCurrentValue(),
			$message = jQuery( '<div>', { class: 'e-global__confirm-message' } ),
			$messageText = jQuery( '<div>', { class: 'e-global__confirm-message-text' } ),
			$inputWrapper = jQuery( '<div>', { class: 'e-global__confirm-input-wrapper' } ),
			$colorPreview = jQuery( '<div>', { class: 'e-global__color-preview', style: 'background-color: ' + currentValue } ),
			$input = jQuery( '<input>', { type: 'text', name: 'global-name', placeholder: colorTitle } )
				.val( colorTitle );

		let messageContent;

		// Check if the color already exists in the global colors, and display an appropriate message.
		for ( const globalColor of Object.values( globalColors ) ) {
			if ( currentValue === globalColor.value ) {
				messageContent = '<i class="eicon-info-circle"></i> ' + elementor.translate( 'global_color_already_exists' );
				break;
			} else if ( colorTitle === globalColor.title ) {
				messageContent = '<i class="eicon-info-circle"></i> ' + elementor.translate( 'global_color_name_already_exists' );
				break;
			} else {
				messageContent = elementor.translate( 'global_color_confirm_text' );
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
			$colorPreview = jQuery( '<div>', { class: 'e-global__color-preview', style: 'background-color: ' + globalData.value } ),
			$colorTitle = jQuery( '<span>', { class: 'e-global__color-title' } )
				.html( globalData.title ),
			$colorHex = jQuery( '<span>', { class: 'e-global__color-hex' } )
				.html( globalData.value );

		$color.append( $colorPreview, $colorTitle, $colorHex );

		return $color;
	}

	async getGlobalsList() {
		const result = await $e.data.get( this.getGlobalCommand() );

		return result.data;
	}

	// Create the markup for the colors in the global select dropdown.
	buildGlobalsList( globalColors ) {
		const $globalColorsPreviewContainer = jQuery( '<div>', { class: 'e-global__preview-items-container' } );

		Object.values( globalColors ).forEach( ( color ) => {
			if ( ! color.value ) {
				return;
			}

			const $color = this.createGlobalItemMarkup( color );

			$globalColorsPreviewContainer.append( $color );
		} );

		this.ui.$globalColorsPreviewContainer = $globalColorsPreviewContainer;

		return $globalColorsPreviewContainer;
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
			this.triggerMethod( 'add:global:to:list', this.getAddGlobalConfirmMessage( globalsList ) );
		} );
	}

	onBeforeDestroy() {
		if ( this.colorPicker ) {
			this.colorPicker.destroy();
		}
	}
}
