import FilesUploadHandler from '../utils/files-upload-handler';

const ControlMultipleBaseItemView = require( 'elementor-controls/base-multiple' );

class ControlIconsView extends ControlMultipleBaseItemView {
	constructor( ...args ) {
		super( ...args );
		this.cache = {
			loaded: false,
			dialog: false,
			enableClicked: false,
			fa4Mapping: false,
			migratedFlag: {},
		};
		this.dataKeys = {
			migratedKey: '__fa4_migrated',
			fa4MigrationFlag: 'fa4compatibility',
		};
	}

	enqueueIconFonts( iconType ) {
		const iconSetting = elementor.helpers.getIconLibrarySettings( iconType );
		if ( false === iconSetting || ! this.isMigrationAllowed() ) {
			return;
		}

		if ( iconSetting.enqueue ) {
			iconSetting.enqueue.forEach( ( assetURL ) => {
				elementor.helpers.enqueueEditorStylesheet( assetURL );
				elementor.helpers.enqueuePreviewStylesheet( assetURL );
			} );
		}

		if ( iconSetting.url ) {
			elementor.helpers.enqueueEditorStylesheet( iconSetting.url );
			elementor.helpers.enqueuePreviewStylesheet( iconSetting.url );
		}
	}

	ui() {
		const ui = super.ui(),
			skin = this.model.get( 'skin' );

		ui.controlMedia = '.elementor-control-media';
		ui.svgUploader = 'media' === skin ? '.elementor-control-svg-uploader' : '.elementor-control-icons--inline__svg';
		ui.iconPickers = 'media' === skin ? '.elementor-control-icon-picker, .elementor-control-media__preview, .elementor-control-media-upload-button' : '.elementor-control-icons--inline__icon';
		ui.deleteButton = 'media' === skin ? '.elementor-control-media__remove' : '.elementor-control-icons--inline__none';
		ui.previewPlaceholder = '.elementor-control-media__preview';
		ui.previewContainer = '.elementor-control-preview-area';
		ui.inlineIconContainer = '.elementor-control-inline-icon';
		ui.inlineDisplayedIcon = '.elementor-control-icons--inline__displayed-icon';
		ui.radioInputs = '[type="radio"]';

		return ui;
	}

	events() {
		return jQuery.extend( ControlMultipleBaseItemView.prototype.events.apply( this, arguments ), {
			'click @ui.iconPickers': 'openPicker',
			'click @ui.svgUploader': 'openFrame',
			'click @ui.radioInputs': 'onClickInput',
			'click @ui.deleteButton': 'deleteIcon',
		} );
	}

	getControlValue() {
		const value = super.getControlValue(),
			model = this.model,
			valueToMigrate = this.getValueToMigrate();

		if ( ! this.isMigrationAllowed() ) {
			return valueToMigrate;
		}

		// Bail if no migration flag or no value to migrate
		if ( ! valueToMigrate ) {
			return value;
		}

		const didMigration = this.elementSettingsModel.get( this.dataKeys.migratedKey ),
			controlName = model.get( 'name' );

		// Check if migration had been done and is stored locally
		if ( this.cache.migratedFlag[ controlName ] ) {
			return this.cache.migratedFlag[ controlName ];
		}
		// Check if already migrated
		if ( didMigration && didMigration[ controlName ] ) {
			return value;
		}

		// Do migration
		return this.migrateFa4toFa5( valueToMigrate );
	}

	migrateFa4toFa5( fa4Value ) {
		const fa5Value = elementor.helpers.mapFa4ToFa5( fa4Value );
		this.cache.migratedFlag[ this.model.get( 'name' ) ] = fa5Value;
		this.enqueueIconFonts( fa5Value.library );
		return fa5Value;
	}

	setControlAsMigrated( controlName ) {
		const didMigration = this.elementSettingsModel.get( this.dataKeys.migratedKey ) || {};
		didMigration[ controlName ] = true;
		this.elementSettingsModel.set( this.dataKeys.migratedKey, didMigration, { silent: true } );
	}

	isMigrationAllowed() {
		return ! elementor.config[ 'icons_update_needed' ];
	}

	getValueToMigrate() {
		const controlToMigrate = this.model.get( this.dataKeys.fa4MigrationFlag );
		if ( ! controlToMigrate ) {
			return false;
		}

		// Check if there is a value to migrate
		const valueToMigrate = this.container.settings.get( controlToMigrate );
		if ( valueToMigrate ) {
			return valueToMigrate;
		}
		return false;
	}

	onReady() {
		// is migration allowed from fa4
		if ( ! this.isMigrationAllowed() ) {
			const migrationPopupTrigger = 'media' === this.model.get( 'skin' ) ? this.ui.previewContainer[ 0 ] : this.ui.inlineIconContainer[ 0 ];

			migrationPopupTrigger.addEventListener( 'click', ( event ) => {
				// Prevent default to prevent marking the inline icons as selected on click when migration is not allowed
				event.preventDefault();
				event.stopPropagation();

				const onConfirm = () => {
					window.location.href = elementor.config.tools_page_link + '&redirect_to=' + encodeURIComponent( document.location.href ) + '#tab-fontawesome4_migration';
				};
				const enableMigrationDialog = elementor.helpers.getSimpleDialog(
					'elementor-enable-fa5-dialog',
					__( 'Elementor\'s New Icon Library', 'elementor' ),
					__( 'Elementor v2.6 includes an upgrade from Font Awesome 4 to 5. In order to continue using icons, be sure to click "Update".', 'elementor' ) + ' <a href="https://go.elementor.com/fontawesome-migration/" target="_blank">' + __( 'Learn More', 'elementor' ) + '</a>',
					__( 'Update', 'elementor' ),
					onConfirm
				);
				enableMigrationDialog.show();
				return false;
			}, true );
		}

		const controlName = this.model.get( 'name' );
		if ( this.cache.migratedFlag[ controlName ] ) {
			this.setControlAsMigrated( controlName );
			setTimeout( () => {
				this.setValue( this.cache.migratedFlag[ controlName ] );
			}, 10 );
		}
	}

	onRender() {
		super.onRender();
		if ( this.isMigrationAllowed() ) {
			elementor.iconManager.loadIconLibraries();
		}
	}

	initFrame() {
		// Set current doc id to attach uploaded images.
		wp.media.view.settings.post.id = elementor.config.document.id;
		this.frame = wp.media( {
			button: {
				text: __( 'Insert Media', 'elementor' ),
			},
			library: { type: [ 'image/svg+xml' ] },
			states: [
				new wp.media.controller.Library( {
					title: __( 'Insert Media', 'elementor' ),
					library: wp.media.query( { type: [ 'image/svg+xml' ] } ),
					multiple: false,
					date: false,
				} ),
			],
		} );

		const handleSelect = () => this.selectSvg();

		// When a file is selected, run a callback.
		this.frame.on( 'insert select', handleSelect );

		this.setUploadMimeType( this.frame, 'svg' );
	}

	setUploadMimeType( frame, ext ) {
		// Set svg as only allowed upload extensions
		const oldExtensions = _wpPluploadSettings.defaults.filters.mime_types[ 0 ].extensions;
		frame.on( 'ready', () => {
			_wpPluploadSettings.defaults.filters.mime_types[ 0 ].extensions = ext;
		} );

		this.frame.on( 'close', () => {
			// restore allowed upload extensions
			_wpPluploadSettings.defaults.filters.mime_types[ 0 ].extensions = oldExtensions;
		} );
	}

	/**
	 * Callback handler for when an attachment is selected in the media modal.
	 * Gets the selected image information, and sets it within the control.
	 */
	selectSvg() {
		this.trigger( 'before:select' );

		// Get the attachment from the modal frame.
		const attachment = this.frame.state().get( 'selection' ).first().toJSON();

		if ( attachment.url ) {
			this.setValue( {
				value: {
					url: attachment.url,
					id: attachment.id,
				},
				library: 'svg',
			} );

			this.applySavedValue();
		}
		this.trigger( 'after:select' );
	}

	openFrame() {
		if ( ! FilesUploadHandler.isUploadEnabled( 'svg' ) ) {
			FilesUploadHandler.getUnfilteredFilesNotEnabledDialog( () => this.openFrame() ).show();

			return false;
		}

		if ( ! this.frame ) {
			this.initFrame();
		}

		this.frame.open();

		// Set params to trigger sanitizer
		FilesUploadHandler.setUploadTypeCaller( this.frame );

		const selectedId = this.getControlValue( 'id' );
		if ( ! selectedId ) {
			return;
		}

		const selection = this.frame.state().get( 'selection' );
		selection.add( wp.media.attachment( selectedId ) );
	}

	openPicker() {
		elementor.iconManager.show( { view: this } );
	}

	applySavedValue() {
		const controlValue = this.getControlValue(),
			skin = this.model.get( 'skin' ),
			iconContainer = 'inline' === skin ? this.ui.inlineDisplayedIcon : this.ui.previewPlaceholder,
			defaultIcon = this.model.get( 'default' );

		let iconValue = controlValue.value,
			iconType = controlValue.library;

		if ( ! this.isMigrationAllowed() && ! iconValue && this.getValueToMigrate() ) {
			iconValue = this.getControlValue();
			iconType = '';
		}

		if ( 'media' === skin ) {
			this.ui.controlMedia.toggleClass( 'elementor-media-empty', ! iconValue );
		} else {
			this.markChecked( iconType );
		}

		if ( ! iconValue ) {
			if ( 'inline' === skin ) {
				this.setDefaultIconLibraryLabel( defaultIcon, iconContainer );
				return;
			}

			this.ui.previewPlaceholder.html( '' );
			return;
		}

		if ( 'svg' === iconType && 'inline' !== skin ) {
			return elementor.helpers.fetchInlineSvg( iconValue.url, ( data ) => {
				this.ui.previewPlaceholder.html( data );
			} );
		}

		if ( 'media' === skin || 'svg' !== iconType ) {
			const previewHTML = '<i class="' + iconValue + '"></i>';

			iconContainer.html( previewHTML );
		}

		this.enqueueIconFonts( iconType );
	}

	setDefaultIconLibraryLabel( defaultIcon, iconContainer ) {
		// Check if the control has a default icon
		if ( '' !== defaultIcon.value && 'svg' !== defaultIcon.library ) {
			// If the default icon is not an SVG, set the icon-library label's icon to the default icon
			iconContainer.html( '<i class="' + defaultIcon.value + '"></i>' );
		} else {
			// If (1) the control does NOT have a default icon,
			// OR (2) the control DOES have a default icon BUT the default icon is an SVG,
			// set the default icon-library label's icon to a simple circle
			iconContainer.html( '<i class="eicon-circle"></i>' );
		}
	}

	markChecked( iconType ) {
		this.ui.radioInputs.filter( ':checked' ).prop( 'checked', false );

		if ( ! iconType ) {
			return this.ui.radioInputs.filter( '[value="none"]' ).prop( 'checked', true );
		}

		if ( 'svg' !== iconType ) {
			iconType = 'icon';
		}

		this.ui.radioInputs.filter( '[value="' + iconType + '"]' ).prop( 'checked', true );
	}

	onClickInput() {
		this.markChecked( this.getControlValue().library );
	}

	deleteIcon( event ) {
		event.stopPropagation();

		this.setValue( {
			value: '',
			library: '',
		} );

		this.applySavedValue();
	}

	onBeforeDestroy() {
		this.$el.remove();
	}
}
module.exports = ControlIconsView;
