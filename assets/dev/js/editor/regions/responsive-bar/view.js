export default class View extends Marionette.ItemView {
	getTemplate() {
		return '#tmpl-elementor-templates-responsive-bar';
	}

	className() {
		return 'e-responsive-bar';
	}

	ui() {
		const prefix = '.' + this.className();

		return {
			switcherInput: prefix + '-switcher__option input',
			switcherLabel: prefix + '-switcher__option',
			switcher: prefix + '-switcher',
			sizeInputWidth: prefix + '__input-width',
			sizeInputHeight: prefix + '__input-height',
			closeButton: prefix + '__close-button',
			breakpointSettingsButton: prefix + '__settings-button',
		};
	}

	events() {
		return {
			'change @ui.switcherInput': 'onBreakpointSelected',
			'input @ui.sizeInputWidth': 'onSizeInputChange',
			'input @ui.sizeInputHeight': 'onSizeInputChange',
			'click @ui.closeButton': 'onCloseButtonClick',
			'click @ui.breakpointSettingsButton': 'onBreakpointSettingsOpen',
		};
	}

	initialize() {
		this.listenTo( elementor.channels.deviceMode, 'change', this.onDeviceModeChange );
		this.listenTo( elementor.channels.responsivePreview, 'resize', this.onPreviewResize );
	}

	addTipsyToIconButtons() {
		this.ui.switcherLabel.add( this.ui.closeButton ).add( this.ui.breakpointSettingsButton ).tipsy(
			{
				html: true,
				gravity: 'n',
				title() {
					return jQuery( this ).data( 'tooltip' );
				},
			}
		);
	}

	restoreLastValidPreviewSize() {
		const lastSize = elementor.channels.responsivePreview.request( 'size' );

		this.ui.sizeInputWidth
			.val( lastSize.width )
			.tipsy( {
				html: true,
				trigger: 'manual',
				gravity: 'n',
				title: () => __( 'The value inserted isn\'t in the breakpoint boundaries', 'elementor' ),
			} );

		const tipsy = this.ui.sizeInputWidth.data( 'tipsy' );

		tipsy.show();

		setTimeout( () => tipsy.hide(), 3000 );
	}

	onDeviceModeChange() {
		const currentDeviceMode = elementor.channels.deviceMode.request( 'currentMode' ),
			$currentDeviceSwitcherInput = this.ui.switcherInput.filter( '[value=' + currentDeviceMode + ']' );

		this.ui.switcherLabel.attr( 'aria-selected', false );
		$currentDeviceSwitcherInput.closest( 'label' ).attr( 'aria-selected', true );

		if ( ! $currentDeviceSwitcherInput.prop( 'checked' ) ) {
			$currentDeviceSwitcherInput.prop( 'checked', true );
		}
	}

	onBreakpointSelected( e ) {
		const currentDeviceMode = elementor.channels.deviceMode.request( 'currentMode' ),
			selectedDeviceMode = e.target.value;

		if ( currentDeviceMode !== selectedDeviceMode ) {
			elementor.changeDeviceMode( selectedDeviceMode, false );
		}
	}

	onBreakpointSettingsOpen() {
		const isWPPreviewMode = elementorCommon.elements.$body.hasClass( 'elementor-editor-preview' );

		if ( isWPPreviewMode ) {
			elementor.exitPreviewMode();
		}

		const isInSettingsPanelActive = 'panel/global/menu' === elementor.documents.currentDocument.config.panel.default_route;

		if ( isInSettingsPanelActive ) {
			$e.run( 'panel/global/close' );

			return;
		}

		//  Open Settings Panel for Global/Layout/Breakpoints Settings
		$e.run( 'editor/documents/switch', {
			id: elementor.config.kit_id,
			mode: 'autosave',
		} )
			.then( () => $e.route( 'panel/global/settings-layout' ) )
			// TODO: Replace with a standard routing solution once one is available
			.then( () => jQuery( '.elementor-control-section_breakpoints' ).trigger( 'click' ) );
	}

	onPreviewResize() {
		if ( this.updatingPreviewSize ) {
			return;
		}

		const size = elementor.channels.responsivePreview.request( 'size' );

		this.ui.sizeInputWidth.val( Math.round( size.width ) );
		this.ui.sizeInputHeight.val( Math.round( size.height ) );
	}

	onRender() {
		this.addTipsyToIconButtons();
	}

	onCloseButtonClick() {
		elementor.changeDeviceMode( 'desktop' );

		// Force exit if device mode is already desktop
		elementor.exitDeviceMode();
	}

	onSizeInputChange() {
		clearTimeout( this.restorePreviewSizeTimeout );

		const size = {
			width: this.ui.sizeInputWidth.val(),
			height: this.ui.sizeInputHeight.val(),
		};

		const currentDeviceConstrains = elementor.getCurrentDeviceConstrains();

		if ( size.width < currentDeviceConstrains.minWidth || size.width > currentDeviceConstrains.maxWidth ) {
			this.restorePreviewSizeTimeout = setTimeout( () => this.restoreLastValidPreviewSize(), 1500 );

			return;
		}

		this.updatingPreviewSize = true;

		setTimeout( () => this.updatingPreviewSize = false, 300 );

		elementor.updatePreviewSize( size );
	}
}
