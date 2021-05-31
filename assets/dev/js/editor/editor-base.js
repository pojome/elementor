/* global ElementorConfig */

import ColorControl from './controls/color';
import DateTimeControl from 'elementor-controls/date-time';
import EditorDocuments from 'elementor-editor/component';
import environment from 'elementor-common/utils/environment';
import HistoryManager from 'elementor/modules/history/assets/js/module';
import HotkeysScreen from './components/hotkeys/hotkeys';
import IconsManager from './components/icons-manager/icons-manager';
import PanelMenu from 'elementor-panel/pages/menu/menu';
import Promotion from './utils/promotion';
import KitManager from '../../../../core/kits/assets/js/manager.js';
import Navigator from './regions/navigator/navigator';
import NoticeBar from './utils/notice-bar';
import Preview from 'elementor-views/preview';
import PopoverToggleControl from 'elementor-controls/popover-toggle';
import ResponsiveBar from './regions/responsive-bar/responsive-bar';
import Stylesheet from './utils/stylesheet';
import DevTools from 'elementor/modules/dev-tools/assets/js/editor/dev-tools';
import LandingPageLibraryModule from 'elementor/modules/landing-pages/assets/js/editor/module';
import ElementsColorPicker from 'elementor/modules/elements-color-picker/assets/js/editor/module';

export default class EditorBase extends Marionette.Application {
	widgetsCache = {};

	config = {};

	loaded = false;

	previewLoadedOnce = false;

	helpers = require( 'elementor-editor-utils/helpers' );
	imagesManager = require( 'elementor-editor-utils/images-manager' ); // TODO: Unused.
	schemes = require( 'elementor-editor-utils/schemes' );
	presetsFactory = require( 'elementor-editor-utils/presets-factory' );
	templates = require( 'elementor-templates/manager' );

	// TODO = BC Since 2.3.0
	ajax = elementorCommon.ajax;
	conditions = require( 'elementor-editor-utils/conditions' );
	history = require( 'elementor/modules/history/assets/js/module' );

	channels = {
		editor: Backbone.Radio.channel( 'ELEMENTOR:editor' ),
		data: Backbone.Radio.channel( 'ELEMENTOR:data' ),
		panelElements: Backbone.Radio.channel( 'ELEMENTOR:panelElements' ),
		dataEditMode: Backbone.Radio.channel( 'ELEMENTOR:editmode' ),
		deviceMode: Backbone.Radio.channel( 'ELEMENTOR:deviceMode' ),
		templates: Backbone.Radio.channel( 'ELEMENTOR:templates' ),
		responsivePreview: Backbone.Radio.channel( 'ELEMENTOR:responsivePreview' ),
	};

	get debug() {
		elementorCommon.helpers.softDeprecated(
			'elementor.debug',
			'3.0.0',
			'elementorCommon.debug'
		);

		return elementorCommon.debug;
	}

	backgroundClickListeners = {
		popover: {
			element: '.elementor-controls-popover',
			ignore: '.elementor-control-popover-toggle-toggle, .elementor-control-popover-toggle-toggle-label, .select2-container, .pcr-app',
		},
		globalControlsSelect: {
			element: '.e-global__popover',
			ignore: '.e-global__popover-toggle',
		},
		tagsList: {
			element: '.elementor-tags-list',
			ignore: '.elementor-control-dynamic-switcher',
		},
		panelFooterSubMenus: {
			element: '.elementor-panel-footer-tool.elementor-toggle-state',
			ignore: '.elementor-panel-footer-tool.elementor-toggle-state, #elementor-panel-saver-button-publish-label',
			callback: ( $elementsToHide ) => {
				$elementsToHide.removeClass( 'elementor-open' );
			},
		},
		panelResponsiveSwitchers: {
			element: '.elementor-control-responsive-switchers',
			callback: ( $elementsToHide ) => {
				$elementsToHide.removeClass( 'elementor-responsive-switchers-open' );
			},
		},
		promotion: {
			ignore: '.elementor-panel-category-items',
			callback: () => {
				const dialog = elementor.promotion.dialog;

				if ( dialog ) {
					dialog.hide();
				}
			},
		},
	};

	/**
	 * Exporting modules that can be used externally
	 * TODO: All of the following entries should move to `elementorModules.editor`
	 */
	modules = {
		// TODO: Deprecated alias since 2.3.0
		get Module() {
			elementorCommon.helpers.hardDeprecated( 'elementor.modules.Module', '2.3.0', 'elementorModules.Module' );

			return elementorModules.Module;
		},
		components: {
			templateLibrary: {
				views: {
					// TODO: Deprecated alias since 2.4.0
					get BaseModalLayout() {
						elementorCommon.helpers.hardDeprecated( 'elementor.modules.components.templateLibrary.views.BaseModalLayout', '2.4.0', 'elementorModules.common.views.modal.Layout' );

						return elementorModules.common.views.modal.Layout;
					},
				},
			},
			saver: {
				behaviors: {
					FooterSaver: require( './document/save/behaviors/footer-saver' ),
				},
			},
		},
		saver: {
			get footerBehavior() {
				elementorCommon.helpers.softDeprecated( 'elementor.modules.saver.footerBehavior.',
					'2.9.0',
					'elementor.modules.components.saver.behaviors.FooterSaver' );

				return elementor.modules.components.saver.behaviors.FooterSaver;
			},
		},
		controls: {
			Animation: require( 'elementor-controls/select2' ),
			Base: require( 'elementor-controls/base' ),
			BaseData: require( 'elementor-controls/base-data' ),
			BaseMultiple: require( 'elementor-controls/base-multiple' ),
			Box_shadow: require( 'elementor-controls/box-shadow' ),
			Button: require( 'elementor-controls/button' ),
			Choose: require( 'elementor-controls/choose' ),
			Code: require( 'elementor-controls/code' ),
			Color: ColorControl,
			Date_time: DateTimeControl,
			Dimensions: require( 'elementor-controls/dimensions' ),
			Exit_animation: require( 'elementor-controls/select2' ),
			Font: require( 'elementor-controls/font' ),
			Gallery: require( 'elementor-controls/gallery' ),
			Hidden: require( 'elementor-controls/hidden' ),
			Hover_animation: require( 'elementor-controls/select2' ),
			Icon: require( 'elementor-controls/icon' ),
			Icons: require( 'elementor-controls/icons' ),
			Image_dimensions: require( 'elementor-controls/image-dimensions' ),
			Media: require( 'elementor-controls/media' ),
			Number: require( 'elementor-controls/number' ),
			Order: require( 'elementor-controls/order' ),
			Popover_toggle: PopoverToggleControl,
			Repeater: require( 'elementor-controls/repeater' ),
			RepeaterRow: require( 'elementor-controls/repeater-row' ),
			Section: require( 'elementor-controls/section' ),
			Select: require( 'elementor-controls/select' ),
			Select2: require( 'elementor-controls/select2' ),
			Slider: require( 'elementor-controls/slider' ),
			Structure: require( 'elementor-controls/structure' ),
			Switcher: require( 'elementor-controls/switcher' ),
			Tab: require( 'elementor-controls/tab' ),
			Text_shadow: require( 'elementor-controls/box-shadow' ),
			Url: require( 'elementor-controls/url' ),
			Wp_widget: require( 'elementor-controls/wp_widget' ),
			Wysiwyg: require( 'elementor-controls/wysiwyg' ),
		},
		elements: {
			models: {
				// TODO: Deprecated alias since 2.4.0
				get BaseSettings() {
					elementorCommon.helpers.hardDeprecated( 'elementor.modules.elements.models.BaseSettings', '2.4.0', 'elementorModules.editor.elements.models.BaseSettings' );

					return elementorModules.editor.elements.models.BaseSettings;
				},
				Element: require( 'elementor-elements/models/element' ),
			},
			views: {
				Widget: require( 'elementor-elements/views/widget' ),
			},
		},
		layouts: {
			panel: {
				pages: {
					elements: {
						views: {
							Global: require( 'elementor-panel/pages/elements/views/global' ),
							Elements: require( 'elementor-panel/pages/elements/views/elements' ),
						},
					},
					menu: {
						Menu: PanelMenu,
					},
				},
			},
		},
		views: {
			// TODO: Deprecated alias since 2.4.0
			get ControlsStack() {
				elementorCommon.helpers.hardDeprecated( 'elementor.modules.views.ControlsStack', '2.4.0', 'elementorModules.editor.views.ControlsStack' );

				return elementorModules.editor.views.ControlsStack;
			},
		},
	};

	userCan( capability ) {
		return -1 === this.config.user.restrictions.indexOf( capability );
	}

	addControlView( controlID, ControlView ) {
		this.modules.controls[ elementorCommon.helpers.upperCaseWords( controlID ) ] = ControlView;
	}

	checkEnvCompatibility() {
		return environment.firefox || environment.webkit;
	}

	getElementData( model ) {
		const elType = model.get( 'elType' );

		if ( 'widget' === elType ) {
			const widgetType = model.get( 'widgetType' );

			if ( ! this.widgetsCache[ widgetType ] ) {
				return false;
			}

			if ( ! this.widgetsCache[ widgetType ].commonMerged ) {
				jQuery.extend( this.widgetsCache[ widgetType ].controls, this.widgetsCache.common.controls );

				this.widgetsCache[ widgetType ].commonMerged = true;
			}

			return this.widgetsCache[ widgetType ];
		}

		if ( ! this.config.elements[ elType ] ) {
			return false;
		}

		const elementConfig = elementorCommon.helpers.cloneObject( this.config.elements[ elType ] );

		if ( 'section' === elType && model.get( 'isInner' ) ) {
			elementConfig.title = __( 'Inner Section', 'elementor' );
		}

		return elementConfig;
	}

	getElementControls( modelElement ) {
		const elementData = this.getElementData( modelElement );

		if ( ! elementData ) {
			return false;
		}

		const isInner = modelElement.get( 'isInner' ),
			controls = {};

		_.each( elementData.controls, ( controlData, controlKey ) => {
			if ( ( isInner && controlData.hide_in_inner ) || ( ! isInner && controlData.hide_in_top ) ) {
				return;
			}

			controls[ controlKey ] = controlData;
		} );

		return controls;
	}

	mergeControlsSettings( controls ) {
		_.each( controls, ( controlData, controlKey ) => {
			controls[ controlKey ] = jQuery.extend( true, {}, this.config.controls[ controlData.type ], controlData );
		} );

		return controls;
	}

	getControlView( controlID ) {
		const capitalizedControlName = elementorCommon.helpers.upperCaseWords( controlID );
		let View = this.modules.controls[ capitalizedControlName ];

		if ( ! View ) {
			const controlData = this.config.controls[ controlID ],
				isUIControl = controlData && -1 !== controlData.features.indexOf( 'ui' );

			View = this.modules.controls[ isUIControl ? 'Base' : 'BaseData' ];
		}

		return View;
	}

	getPanelView() {
		return this.panel.currentView;
	}

	getPreviewView() {
		return this.previewView;
	}

	/**
	 * @returns {Container}
	 */
	getPreviewContainer() {
		return this.getPreviewView().getContainer();
	}

	getContainer( id ) {
		if ( 'document' === id ) {
			return this.getPreviewContainer();
		}

		return $e.components.get( 'document' ).utils.findContainerById( id );
	}

	initComponents() {
		const EventManager = require( 'elementor-utils/hooks' ),
			DynamicTags = require( 'elementor-dynamic-tags/manager' ),
			Settings = require( 'elementor-editor/components/settings/settings' ),
			Notifications = require( 'elementor-editor-utils/notifications' );

		this.hooks = new EventManager();

		this.settings = new Settings();

		this.dynamicTags = new DynamicTags();

		this.initDialogsManager();

		this.notifications = new Notifications();

		this.kitManager = new KitManager();

		this.hotkeysScreen = new HotkeysScreen();

		this.iconManager = new IconsManager();

		this.noticeBar = new NoticeBar();

		this.history = new HistoryManager();

		this.promotion = new Promotion();

		this.devTools = new DevTools();

		this.documents = $e.components.register( new EditorDocuments() );

		// Adds the Landing Page tab to the Template library modal when editing Landing Pages.
		if ( elementorCommon.config.experimentalFeatures[ 'landing-pages' ] ) {
			this.modules.landingLibraryPageModule = new LandingPageLibraryModule();
		}

		if ( elementorCommon.config.experimentalFeatures[ 'elements-color-picker' ] ) {
			this.modules.elementsColorPicker = new ElementsColorPicker();
		}

		elementorCommon.elements.$window.trigger( 'elementor:init-components' );
	}

	// TODO: BC method since 2.3.0
	initDialogsManager() {
		this.dialogsManager = elementorCommon.dialogsManager;
	}

	initElements() {
		const ElementCollection = require( 'elementor-elements/collections/elements' );

		let config = this.config.document.elements;

		// If it's an reload, use the not-saved data
		if ( this.elements && this.elements.length && this.config.document.id === this.config.initial_document.id ) {
			config = this.elements.toJSON();
		}

		this.elements = new ElementCollection( config );

		this.elementsModel = new Backbone.Model( {
			elements: this.elements,
		} );
	}

	initPreview() {
		const $ = jQuery,
			previewIframeId = 'elementor-preview-iframe';

		this.$previewWrapper = $( '#elementor-preview' );

		this.$previewResponsiveWrapper = $( '#elementor-preview-responsive-wrapper' );

		// Make sure the iFrame does not exist.
		if ( ! this.$preview ) {
			this.$preview = $( '<iframe>', {
				id: previewIframeId,
				src: this.config.initial_document.urls.preview,
				allowfullscreen: 1,
			} );

			this.$previewResponsiveWrapper.append( this.$preview );
		}

		this.$preview.on( 'load', this.onPreviewLoaded.bind( this ) );
	}

	initPreviewView( document ) {
		elementor.trigger( 'document:before:preview', document );

		const preview = new Preview( { el: document.$element[ 0 ], model: elementor.elementsModel } );

		preview.$el.empty();

		// In order to force rendering of children
		preview.isRendered = true;

		preview._renderChildren();

		preview.triggerMethod( 'render' );

		this.previewView = preview;
	}

	initFrontend() {
		const frontendWindow = this.$preview[ 0 ].contentWindow;

		window.elementorFrontend = frontendWindow.elementorFrontend;

		frontendWindow.elementor = this;

		frontendWindow.elementorCommon = elementorCommon;

		elementorFrontend.init();

		this.trigger( 'frontend:init' );
	}

	initClearPageDialog() {
		let dialog;

		this.getClearPageDialog = () => {
			if ( dialog ) {
				return dialog;
			}

			dialog = elementorCommon.dialogsManager.createWidget( 'confirm', {
				id: 'elementor-clear-page-dialog',
				headerMessage: __( 'Delete All Content', 'elementor' ),
				message: __( 'Attention: We are going to DELETE ALL CONTENT from this page. Are you sure you want to do that?', 'elementor' ),
				position: {
					my: 'center center',
					at: 'center center',
				},
				strings: {
					confirm: __( 'Delete', 'elementor' ),
					cancel: __( 'Cancel', 'elementor' ),
				},
				onConfirm: () => $e.run( 'document/elements/empty', { force: true } ),
			} );

			return dialog;
		};
	}

	getCurrentElement() {
		const isPreview = ( -1 !== [ 'BODY', 'IFRAME' ].indexOf( document.activeElement.tagName ) && 'BODY' === elementorFrontend.elements.window.document.activeElement.tagName );

		if ( ! isPreview && ! elementorCommonConfig.isTesting ) {
			return false;
		}

		let targetElement = elementor.channels.editor.request( 'contextMenu:targetView' );

		if ( ! targetElement ) {
			const panel = elementor.getPanelView();

			if ( $e.routes.isPartOf( 'panel/editor' ) ) {
				targetElement = panel.getCurrentPageView().getOption( 'editedElementView' );
			}
		}

		if ( ! targetElement ) {
			targetElement = elementor.getPreviewView();
		}

		return targetElement;
	}

	initPanel() {
		this.addRegions( { panel: require( 'elementor-regions/panel/panel' ) } );

		this.trigger( 'panel:init' );
	}

	initResponsiveBar() {
		this.addRegions( {
			responsiveBar: {
				el: '#elementor-responsive-bar',
				regionClass: ResponsiveBar,
			},
		} );

		this.trigger( 'responsiveBar:init' );
	}

	initNavigator() {
		this.addRegions( {
			navigator: {
				el: '#elementor-navigator',
				regionClass: Navigator,
			},
		} );

		this.trigger( 'navigator:init' );
	}

	setAjax() {
		elementorCommon.ajax.addRequestConstant( 'editor_post_id', this.config.document.id );
		elementorCommon.ajax.addRequestConstant( 'initial_document_id', this.config.initial_document.id );

		elementorCommon.ajax.on( 'request:unhandledError', ( xmlHttpRequest ) => {
			elementor.notifications.showToast( {
				message: elementor.createAjaxErrorMessage( xmlHttpRequest ),
			} );
		} );
	}

	createAjaxErrorMessage( xmlHttpRequest ) {
		let message;

		if ( 4 === xmlHttpRequest.readyState ) {
			message = __( 'Server Error', 'elementor' );

			if ( 200 !== xmlHttpRequest.status ) {
				message += ' (' + xmlHttpRequest.status + ' ' + xmlHttpRequest.statusText + ')';
			}
		} else if ( 0 === xmlHttpRequest.readyState ) {
			message = __( 'Connection Lost', 'elementor' );
		} else {
			message = __( 'Unknown Error', 'elementor' );
		}

		return message + '.';
	}

	activatePreviewResizable() {
		const $responsiveWrapper = this.$previewResponsiveWrapper;

		if ( $responsiveWrapper.resizable( 'instance' ) ) {
			return;
		}

		$responsiveWrapper.resizable( {
			handles: 'e, s, w',
			stop: () => {
				$responsiveWrapper.css( { width: '', height: '', left: '', right: '', top: '', bottom: '' } );
			},
			resize: ( event, ui ) => {
				$responsiveWrapper.css( {
					right: '0',
					left: '0',
					top: '0',
					bottom: '0',
				} );

				// Old versions of jQuery don't support custom properties
				const style = $responsiveWrapper[ 0 ].style;

				style.setProperty( '--e-editor-preview-width', ui.size.width + 'px' );
				style.setProperty( '--e-editor-preview-height', ui.size.height + 'px' );
			},
		} );
	}

	destroyPreviewResizable() {
		if ( this.$previewResponsiveWrapper.resizable( 'instance' ) ) {
			this.$previewResponsiveWrapper.resizable( 'destroy' );
		}
	}

	broadcastPreviewResize() {
		this.channels.responsivePreview
			.reply( 'size', {
				width: this.$preview.innerWidth(),
				height: this.$preview.innerHeight(),
			} )
			.trigger( 'resize' );
	}

	getCurrentDeviceConstrains() {
		const currentBreakpoint = elementor.channels.deviceMode.request( 'currentMode' ),
			{ activeBreakpoints } = elementorFrontend.config.responsive,
			currentBreakpointData = activeBreakpoints[ currentBreakpoint ],
			currentBreakpointMinPoint = Stylesheet.getDeviceMinBreakpoint( currentBreakpoint );

		return {
			maxWidth: currentBreakpointData.value,
			minWidth: currentBreakpointMinPoint,
		};
	}

	getBreakpointResizeOptions( currentBreakpoint ) {
		const specialBreakpointsHeights = {
			mobile: {
				minHeight: 480,
				height: 667,
				maxHeight: 896,
			},
			tablet: {
				minHeight: 768,
				height: 1024,
				maxHeight: 1024,
			},
		};

		let deviceConstrains = this.getCurrentDeviceConstrains();

		if ( specialBreakpointsHeights[ currentBreakpoint ] ) {
			deviceConstrains = { ...deviceConstrains, ...specialBreakpointsHeights[ currentBreakpoint ] };
		}

		return deviceConstrains;
	}

	updatePreviewResizeOptions( preserveCurrentSize = false ) {
		const $responsiveWrapper = this.$previewResponsiveWrapper,
			currentBreakpoint = elementor.channels.deviceMode.request( 'currentMode' );

		if ( 'desktop' === currentBreakpoint ) {
			this.destroyPreviewResizable();

			// Old versions of jQuery don't support custom properties
			const style = $responsiveWrapper[ 0 ].style;

			style.setProperty( '--e-editor-preview-width', '' );
			style.setProperty( '--e-editor-preview-height', '' );
		} else {
			this.activatePreviewResizable();

			const breakpointResizeOptions = this.getBreakpointResizeOptions( currentBreakpoint );

			let widthToShow = breakpointResizeOptions.minWidth;

			if ( preserveCurrentSize ) {
				const currentSize = elementor.channels.responsivePreview.request( 'size' );

				if ( currentSize.width > breakpointResizeOptions.maxWidth ) {
					widthToShow = breakpointResizeOptions.maxWidth;
				} else if ( currentSize.width >= breakpointResizeOptions.minWidth ) {
					widthToShow = currentSize.width;
				}
			}

			$responsiveWrapper.resizable( 'option', { ...breakpointResizeOptions } );

			// Old versions of jQuery don't support custom properties
			const style = $responsiveWrapper[ 0 ].style;

			style.setProperty( '--e-editor-preview-width', widthToShow + 'px' );
			style.setProperty( '--e-editor-preview-height', breakpointResizeOptions.height + 'px' );
		}
	}

	preventClicksInsideEditor() {
		this.$previewContents.on( 'submit', ( event ) =>
			event.preventDefault()
		);

		// Cannot use arrow function here since it use `this.contains`.
		this.$previewContents.on( 'click', function( event ) {
			const $target = jQuery( event.target ),
				isClickInsideElementor = !! $target.closest( '.elementor-edit-area, .pen-menu' ).length,
				isTargetInsideDocument = this.contains( $target[ 0 ] );

			if ( $target.closest( 'a:not(.elementor-clickable)' ).length ) {
				event.preventDefault();
			}

			if ( ( isClickInsideElementor && elementor.getPreviewContainer().isEditable() ) || ! isTargetInsideDocument ) {
				return;
			}

			// It's a click on the preview area, not in the edit area,
			// and a document is open and has an edit area.
			if ( ! isClickInsideElementor && elementor.documents.getCurrent()?.$element ) {
				$e.internal( 'panel/open-default' );
			}
		} );
	}

	addBackgroundClickArea( element ) {
		element.addEventListener( 'click', this.onBackgroundClick.bind( this ), true );
	}

	addBackgroundClickListener( key, listener ) {
		this.backgroundClickListeners[ key ] = listener;
	}

	removeBackgroundClickListener( key ) {
		delete this.backgroundClickListeners[ key ];
	}

	showFatalErrorDialog( options ) {
		const defaultOptions = {
			id: 'elementor-fatal-error-dialog',
			headerMessage: '',
			message: '',
			position: {
				my: 'center center',
				at: 'center center',
			},
			strings: {
				confirm: __( 'Learn More', 'elementor' ),
				cancel: __( 'Go Back', 'elementor' ),
			},
			onConfirm: null,
			onCancel: () => parent.history.go( -1 ),
			hide: {
				onBackgroundClick: false,
				onButtonClick: false,
			},
		};

		options = jQuery.extend( true, defaultOptions, options );

		elementorCommon.dialogsManager.createWidget( 'confirm', options ).show();
	}

	showFlexBoxAttentionDialog() {
		const introduction = new elementorModules.editor.utils.Introduction( {
			introductionKey: 'flexbox',
			dialogType: 'confirm',
			dialogOptions: {
				id: 'elementor-flexbox-attention-dialog',
				headerMessage: __( 'Note: Flexbox Changes', 'elementor' ),
				message: __( 'Elementor 2.5 introduces key changes to the layout using CSS Flexbox. Your existing pages might have been affected, please review your page before publishing.', 'elementor' ),
				position: {
					my: 'center center',
					at: 'center center',
				},
				strings: {
					confirm: __( 'Learn More', 'elementor' ),
					cancel: __( 'Got It', 'elementor' ),
				},
				hide: {
					onButtonClick: false,
				},
				onCancel: () => {
					introduction.setViewed();

					introduction.getDialog().hide();
				},
				onConfirm: () => open( this.config.help_flexbox_bc_url, '_blank' ),
			},
		} );

		introduction.show();
	}

	checkPageStatus() {
		if ( elementor.documents.getCurrent().isDraft() ) {
			this.notifications.showToast( {
				message: __( 'This is just a draft. Play around and when you\'re done - click update.', 'elementor' ),
				buttons: [
					{
						name: 'view_revisions',
						text: __( 'View All Revisions', 'elementor' ),
						callback: () => $e.route( 'panel/history/revisions' ),
					},
				],
			} );
		}
	}

	enterDeviceMode() {
		elementorCommon.elements.$body.addClass( 'e-is-device-mode' );

		this.activatePreviewResizable();

		this.resizeListenerThrottled = false;

		this.broadcastPreviewResize();

		elementorFrontend.elements.$window.on( 'resize.deviceModeDesktop', () => {
			if ( this.resizeListenerThrottled ) {
				return;
			}

			this.resizeListenerThrottled = true;

			this.broadcastPreviewResize();

			setTimeout( () => {
				this.resizeListenerThrottled = false;

				this.broadcastPreviewResize();
			}, 300 );
		} );
	}

	exitDeviceMode() {
		elementorCommon.elements.$body.removeClass( 'e-is-device-mode' );

		this.destroyPreviewResizable();

		elementorCommon.elements.$window.off( 'resize.deviceModeDesktop' );
	}

	isDeviceModeActive() {
		return elementorCommon.elements.$body.hasClass( 'e-is-device-mode' );
	}

	updatePreviewSize( size ) {
		// Old versions of jQuery don't support custom properties
		const style = this.$previewResponsiveWrapper[ 0 ].style;

		style.setProperty( '--e-editor-preview-width', size.width + 'px' );
		style.setProperty( '--e-editor-preview-height', size.height + 'px' );
	}

	enterPreviewMode( hidePanel ) {
		let $elements = elementorFrontend.elements.$body;

		if ( hidePanel ) {
			$elements = $elements.add( elementorCommon.elements.$body );
		}

		$elements
			.removeClass( 'elementor-editor-active' )
			.addClass( 'elementor-editor-preview' );

		const $element = this.documents.getCurrent().$element;

		if ( $element ) {
			$element.removeClass( 'elementor-edit-area-active' );
		}
	}

	exitPreviewMode() {
		elementorFrontend.elements.$body.add( elementorCommon.elements.$body )
			.removeClass( 'elementor-editor-preview' )
			.addClass( 'elementor-editor-active' );

		if ( elementor.config.document.panel.has_elements ) {
			this.documents.getCurrent().$element.addClass( 'elementor-edit-area-active' );
		}
	}

	changeEditMode( newMode ) {
		const dataEditMode = elementor.channels.dataEditMode,
			oldEditMode = dataEditMode.request( 'activeMode' );

		dataEditMode.reply( 'activeMode', newMode );

		if ( newMode !== oldEditMode ) {
			dataEditMode.trigger( 'switch', newMode );
		}
	}

	reloadPreview() {
		// TODO: Should be command?
		jQuery( '#elementor-preview-loading' ).show();

		this.$preview[ 0 ].contentWindow.location.reload( true );
	}

	changeDeviceMode( newDeviceMode, hideBarOnDesktop = true ) {
		const oldDeviceMode = this.channels.deviceMode.request( 'currentMode' );

		if ( oldDeviceMode === newDeviceMode ) {
			return;
		}

		elementorCommon.elements.$body
			.removeClass( 'elementor-device-' + oldDeviceMode )
			.addClass( 'elementor-device-' + newDeviceMode );

		this.channels.deviceMode
			.reply( 'previousMode', oldDeviceMode )
			.reply( 'currentMode', newDeviceMode )
			.trigger( 'change' );

		if ( this.isDeviceModeActive() && hideBarOnDesktop ) {
			if ( 'desktop' === newDeviceMode ) {
				this.exitDeviceMode();
			}
		} else if ( 'desktop' !== newDeviceMode ) {
			this.enterDeviceMode();
		}
	}

	translate( stringKey, templateArgs, i18nStack ) {
		// TODO: BC since 2.3.0, it always should be `this.config.i18n`
		if ( ! i18nStack ) {
			i18nStack = this.config.i18n;
		}

		return elementorCommon.translate( stringKey, null, templateArgs, i18nStack );
	}

	logSite() {
		let text = '',
			style = '';

		if ( environment.firefox ) {
			const asciiText = [
				' ;;;;;;;;;;;;;;; ',
				';;;  ;;       ;;;',
				';;;  ;;;;;;;;;;;;',
				';;;  ;;;;;;;;;;;;',
				';;;  ;;       ;;;',
				';;;  ;;;;;;;;;;;;',
				';;;  ;;;;;;;;;;;;',
				';;;  ;;       ;;;',
				' ;;;;;;;;;;;;;;; ',
			];

			text += '%c' + asciiText.join( '\n' ) + '\n';

			style = 'color: #C42961';
		} else {
			text += '%c00';

			style = 'font-size: 22px; background-image: url("' + elementorCommon.config.urls.assets + 'images/logo-icon.png"); color: transparent; background-repeat: no-repeat';
		}

		setTimeout( console.log.bind( console, text, style ) ); // eslint-disable-line

		text = '%cLove using Elementor? Join our growing community of Elementor developers: %chttps://github.com/elementor/elementor';

		setTimeout( console.log.bind( console, text, 'color: #9B0A46', '' ) ); // eslint-disable-line
	}

	requestWidgetsConfig() {
		const excludeWidgets = {};

		jQuery.each( this.widgetsCache, ( widgetName, widgetConfig ) => {
			if ( widgetConfig.controls ) {
				excludeWidgets[ widgetName ] = true;
			}
		} );

		elementorCommon.ajax.addRequest( 'get_widgets_config', {
			data: {
				exclude: excludeWidgets,
			},
			success: ( data ) => {
				this.addWidgetsCache( data );

				if ( this.loaded ) {
					this.kitManager.renderGlobalsDefaultCSS();

					$e.internal( 'panel/state-ready' );
				} else {
					this.once( 'panel:init', () => {
						$e.internal( 'panel/state-ready' );
					} );
				}
			},
		} );
	}

	getPreferences( key ) {
		const settings = elementor.settings.editorPreferences.model.attributes;

		if ( key ) {
			return settings[ key ];
		}

		return settings;
	}

	getConfig() {
		return ElementorConfig;
	}

	onStart() {
		this.config = this.getConfig();

		Backbone.Radio.DEBUG = false;
		Backbone.Radio.tuneIn( 'ELEMENTOR' );

		this.initComponents();

		if ( ! this.checkEnvCompatibility() ) {
			this.onEnvNotCompatible();
		}

		this.initPreview();

		this.requestWidgetsConfig();

		this.channels.dataEditMode.reply( 'activeMode', 'edit' );

		this.listenTo( this.channels.dataEditMode, 'switch', this.onEditModeSwitched );

		this.listenTo( elementor.channels.deviceMode, 'change', this.updatePreviewResizeOptions );

		this.initClearPageDialog();

		this.addBackgroundClickArea( document );

		this.addDeprecatedConfigProperties();

		elementorCommon.elements.$window.trigger( 'elementor:loaded' );

		$e.run( 'editor/documents/open', { id: this.config.initial_document.id } )
			.then( () => {
				elementorCommon.elements.$window.trigger( 'elementor:init' );
				this.initNavigator();
			} );

		this.logSite();
	}

	onPreviewLoaded() {
		const previewWindow = this.$preview[ 0 ].contentWindow;

		if ( ! previewWindow.elementorFrontend ) {
			this.onPreviewLoadingError();

			return;
		}

		this.$previewContents = this.$preview.contents();

		this.initFrontend();

		this.schemes.init();

		this.preventClicksInsideEditor();

		this.addBackgroundClickArea( elementorFrontend.elements.window.document );

		if ( ! this.previewLoadedOnce ) {
			this.onFirstPreviewLoaded();
		}

		this.$previewContents.children().addClass( 'elementor-html' );

		const $frontendBody = elementorFrontend.elements.$body;

		$frontendBody.addClass( 'elementor-editor-active' );

		if ( ! elementor.userCan( 'design' ) ) {
			$frontendBody.addClass( 'elementor-editor-content-only' );
		}

		this.changeDeviceMode( 'desktop' );

		_.defer( function() {
			elementorFrontend.elements.window.jQuery.holdReady( false );
		} );

		$e.shortcuts.bindListener( elementorFrontend.elements.$window );

		this.trigger( 'preview:loaded', ! this.loaded /* isFirst */ );

		$e.internal( 'editor/documents/attach-preview' ).then( () => jQuery( '#elementor-loading, #elementor-preview-loading' ).fadeOut( 600 ) );

		this.loaded = true;
	}

	onFirstPreviewLoaded() {
		this.initPanel();

		this.initResponsiveBar();

		this.previewLoadedOnce = true;
	}

	onEditModeSwitched() {
		const activeMode = this.channels.dataEditMode.request( 'activeMode' );

		if ( 'edit' === activeMode ) {
			this.exitPreviewMode();
		} else {
			this.enterPreviewMode( 'preview' === activeMode );
		}
	}

	onEnvNotCompatible() {
		this.showFatalErrorDialog( {
			headerMessage: __( 'Your browser isn\'t compatible', 'elementor' ),
			message: __( 'Your browser isn\'t compatible with all of Elementor\'s editing features. We recommend you switch to another browser like Chrome or Firefox.', 'elementor' ),
			strings: {
				confirm: __( 'Proceed Anyway', 'elementor' ),
			},
			hide: {
				onButtonClick: true,
			},
			onConfirm: () => this.hide(),
		} );
	}

	onPreviewLoadingError() {
		const debugUrl = this.config.document.urls.preview + '&preview-debug',
			previewDebugLinkText = __( 'Click here for preview debug', 'elementor' ),
			previewDebugLink = '<div id="elementor-preview-debug-link-text"><a href="' + debugUrl + '" target="_blank">' + previewDebugLinkText + '</a></div>',
			debugData = elementor.config.preview.debug_data,
			dialogOptions = {
				className: 'elementor-preview-loading-error',
				headerMessage: debugData.header,
				message: debugData.message + previewDebugLink,
				onConfirm: () => open( debugData.doc_url, '_blank' ),
			};

		if ( debugData.error ) {
			this.showFatalErrorDialog( dialogOptions );
			return;
		}

		jQuery.get( debugUrl, () => {
			this.showFatalErrorDialog( dialogOptions );
		} ).fail( ( response ) => { //Iframe can't be loaded
			this.showFatalErrorDialog( {
				className: 'elementor-preview-loading-error',
				headerMessage: debugData.header,
				message: response.statusText + ' ' + response.status + ' ' + previewDebugLink,
				onConfirm: () => {
					const url = 500 <= response.status ? elementor.config.preview.help_preview_http_error_500_url : elementor.config.preview.help_preview_http_error_url;
					open( url, '_blank' );
				},
			} );
		} );
	}

	onPreviewElNotFound() {
		let args = this.$preview[ 0 ].contentWindow.elementorPreviewErrorArgs;

		if ( ! args ) {
			args = {
				headerMessage: __( 'Sorry, the content area was not found in your page.', 'elementor' ),
				message: __( 'You must call \'the_content\' function in the current template, in order for Elementor to work on this page.', 'elementor' ),
				confirmURL: elementor.config.help_the_content_url,
			};
		}

		args.onConfirm = () => open( args.confirmURL, '_blank' );

		this.showFatalErrorDialog( args );
	}

	onBackgroundClick( event ) {
		jQuery.each( this.backgroundClickListeners, ( index, config ) => {
			let $clickedTarget = jQuery( event.target );
			// If it's a label that associated with an input
			if ( $clickedTarget[ 0 ].control ) {
				$clickedTarget = $clickedTarget.add( $clickedTarget[ 0 ].control );
			}

			if ( config.ignore && $clickedTarget.closest( config.ignore ).length ) {
				return;
			}

			const $clickedTargetClosestElement = $clickedTarget.closest( config.element ),
				$elementsToHide = jQuery( config.element ).not( $clickedTargetClosestElement );

			if ( config.callback ) {
				config.callback( $elementsToHide );
				return;
			}

			$elementsToHide.hide();
		} );
	}

	compileTemplate( template, data ) {
		return Marionette.TemplateCache.prototype.compileTemplate( template )( data );
	}

	addWidgetsCache( widgets ) {
		jQuery.each( widgets, ( widgetName, widgetConfig ) => {
			this.widgetsCache[ widgetName ] = jQuery.extend( true, {}, this.widgetsCache[ widgetName ], widgetConfig );
		} );
	}

	addDeprecatedConfigProperties() {
		const map = {
			data: {
				replacement: 'elements',
				value: () => elementor.config.document.elements,
			},
			current_user_can_publish: {
				replacement: 'user.can_publish',
				value: () => elementor.config.document.user.can_publish,
			},
			locked_user: {
				replacement: '',
				value: () => elementor.config.document.user.locked,
			},
			revisions_enabled: {
				replacement: 'revisions.enabled',
				value: () => elementor.config.document.revisions.enabled,
			},
			current_revision_id: {
				replacement: 'revisions.current_id',
				value: () => elementor.config.document.revisions.current_id,
			},
		};

		jQuery.each( map, ( key, data ) => {
			// Use `defineProperty` because `get property()` fails during the `Marionette...extend`.
			Object.defineProperty( this.config, key, {
				get() {
					const replacement = data.replacement ? 'elementor.config.document.' + data.replacement : '';
					elementorCommon.helpers.softDeprecated( 'elementor.config.' + key, '2.9.0', replacement );
					// return from current document.
					return data.value();
				},
				set() {
					elementorCommon.helpers.softDeprecated( 'elementor.config.' + key, '2.9.0', 'elementor.config.document.' + data.replacement );
					throw Error( 'Deprecated' );
				},
			} );
		} );

		Object.defineProperty( this.config.settings, 'page', {
			get() {
				elementorCommon.helpers.softDeprecated( 'elementor.config.settings.page', '2.9.0', 'elementor.config.document.settings' );
				return elementor.config.document.settings;
			},
		} );

		Object.defineProperty( this.config, 'widgets', {
			get() {
				elementorCommon.helpers.softDeprecated( 'elementor.config.widgets', '2.9.0', 'elementor.widgetsCache' );
				return elementor.widgetsCache;
			},
		} );

		Object.defineProperty( this, '$previewElementorEl', {
			get() {
				elementorCommon.helpers.softDeprecated( 'elementor.$previewElementorEl', '2.9.4', 'elementor.documents.getCurrent().$element' );

				return elementor.documents.getCurrent().$element;
			},
		} );
	}

	toggleDocumentCssFiles( document, state ) {
		const selectors = [
			`#elementor-post-${ document.config.id }-css`,
			`#elementor-preview-${ document.config.revisions.current_id }`,
		],
			$files = this.$previewContents.find( selectors.join( ',' ) ),
			type = state ? 'text/css' : 'elementor/disabled-css';

		$files.attr( { type } );
	}
}
