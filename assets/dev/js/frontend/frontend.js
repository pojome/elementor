/* global elementorFrontendConfig */
import '../public-path';
import DocumentsManager from './documents-manager';
import Storage from 'elementor-common/utils/storage';
import environment from 'elementor-common/utils/environment';
import YouTubeApiLoader from './utils/video-api/youtube-loader';
import VimeoApiLoader from './utils/video-api/vimeo-loader';
import URLActions from './utils/url-actions';
import Swiper from './utils/swiper-bc';
import LightboxManager from './utils/lightbox/lightbox-manager';
import AssetsLoader from './utils/assets-loader';

import Shapes from 'elementor/modules/shapes/assets/js/frontend/frontend';

const EventManager = require( 'elementor-utils/hooks' ),
	ElementsHandler = require( 'elementor-frontend/elements-handlers-manager' ),
	AnchorsModule = require( 'elementor-frontend/utils/anchors' );

export default class Frontend extends elementorModules.ViewModule {
	constructor( ...args ) {
		super( ...args );

		this.config = elementorFrontendConfig;

		this.config.legacyMode = {
			get elementWrappers() {
				if ( elementorFrontend.isEditMode() ) {
					elementorCommon.helpers.hardDeprecated( 'elementorFrontend.config.legacyMode.elementWrappers', '3.1.0', 'elementorFrontend.config.experimentalFeatures.e_dom_optimization' );
				}

				return ! elementorFrontend.config.experimentalFeatures.e_dom_optimization;
			},
		};

		this.populateActiveBreakpointsConfig();
	}

	// TODO: BC since 2.5.0
	get Module() {
		if ( this.isEditMode() ) {
			parent.elementorCommon.helpers.hardDeprecated( 'elementorFrontend.Module', '2.5.0', 'elementorModules.frontend.handlers.Base' );
		}

		return elementorModules.frontend.handlers.Base;
	}

	getDefaultSettings() {
		return {
			selectors: {
				elementor: '.elementor',
				adminBar: '#wpadminbar',
			},
			classes: {
				ie: 'elementor-msie',
			},
		};
	}

	getDefaultElements() {
		const defaultElements = {
			window: window,
			$window: jQuery( window ),
			$document: jQuery( document ),
			$head: jQuery( document.head ),
			$body: jQuery( document.body ),
			$deviceMode: jQuery( '<span>', { id: 'elementor-device-mode', class: 'elementor-screen-only' } ),
		};
		defaultElements.$body.append( defaultElements.$deviceMode );

		return defaultElements;
	}

	bindEvents() {
		this.elements.$window.on( 'resize', () => this.setDeviceModeData() );
	}

	/**
	 * @deprecated 2.4.0 Use just `this.elements` instead
	 */
	getElements( elementName ) {
		return this.getItems( this.elements, elementName );
	}

	/**
	 * @deprecated 2.4.0 This method was never in use
	 */
	getPageSettings( settingName ) {
		const settingsObject = this.isEditMode() ? elementor.settings.page.model.attributes : this.config.settings.page;

		return this.getItems( settingsObject, settingName );
	}

	getGeneralSettings( settingName ) {
		if ( this.isEditMode() ) {
			parent.elementorCommon.helpers.softDeprecated( 'getGeneralSettings', '3.0.0', 'getKitSettings and remove the `elementor_` prefix' );
		}

		return this.getKitSettings( `elementor_${ settingName }` );
	}

	getKitSettings( settingName ) {
		// TODO: use Data API.
		return this.getItems( this.config.kit, settingName );
	}

	getCurrentDeviceMode() {
		return getComputedStyle( this.elements.$deviceMode[ 0 ], ':after' ).content.replace( /"/g, '' );
	}

	getDeviceSetting( deviceMode, settings, settingKey ) {
		// Add specific handling for widescreen since it is larger than desktop.
		if ( 'widescreen' === deviceMode ) {
			return this.getWidescreenSetting( settings, settingKey );
		}

		const devices = Object.keys( this.config.responsive.activeBreakpoints ).reverse();

		// Manually add 'desktop' to the devices array to support the 'desktop' value in the deviceMode parameter.
		devices.unshift( 'desktop' );

		let deviceIndex = devices.indexOf( deviceMode );

		while ( deviceIndex > 0 ) {
			const currentDevice = devices[ deviceIndex ],
				fullSettingKey = settingKey + '_' + currentDevice,
				deviceValue = settings[ fullSettingKey ];

			if ( deviceValue ) {
				return deviceValue;
			}

			deviceIndex--;
		}

		return settings[ settingKey ];
	}

	getWidescreenSetting( settings, settingKey ) {
		const deviceMode = 'widescreen',
			widescreenSettingKey = settingKey + '_' + deviceMode;

		let settingToReturn;

		// If the device mode is 'widescreen', and the setting exists - return it.
		if ( settings[ widescreenSettingKey ] ) {
			settingToReturn = settings[ widescreenSettingKey ];
		} else {
			// Otherwise, return the desktop setting
			settingToReturn = settings[ settingKey ];
		}

		return settingToReturn;
	}

	getCurrentDeviceSetting( settings, settingKey ) {
		return this.getDeviceSetting( elementorFrontend.getCurrentDeviceMode(), settings, settingKey );
	}

	isEditMode() {
		return this.config.environmentMode.edit;
	}

	isWPPreviewMode() {
		return this.config.environmentMode.wpPreview;
	}

	initDialogsManager() {
		let dialogsManager;

		this.getDialogsManager = () => {
			if ( ! dialogsManager ) {
				dialogsManager = new DialogsManager.Instance();
			}

			return dialogsManager;
		};
	}

	initOnReadyComponents() {
		this.utils = {
			youtube: new YouTubeApiLoader(),
			vimeo: new VimeoApiLoader(),
			anchors: new AnchorsModule(),
			get lightbox() {
				return LightboxManager.getLightbox();
			},
			urlActions: new URLActions(),
			swiper: Swiper,
			environment: environment,
			assetsLoader: new AssetsLoader(),
		};

		// TODO: BC since 2.4.0
		this.modules = {
			StretchElement: elementorModules.frontend.tools.StretchElement,
			Masonry: elementorModules.utils.Masonry,
		};

		this.elementsHandler.init();

		if ( this.isEditMode() ) {
			elementor.once( 'document:loaded', () => this.onDocumentLoaded() );
		} else {
			this.onDocumentLoaded();
		}
	}

	initOnReadyElements() {
		this.elements.$wpAdminBar = this.elements.$document.find( this.getSettings( 'selectors.adminBar' ) );
	}

	addUserAgentClasses() {
		for ( const [ key, value ] of Object.entries( environment ) ) {
			if ( value ) {
				this.elements.$body.addClass( 'e--ua-' + key );
			}
		}
	}

	addIeCompatibility() {
		const el = document.createElement( 'div' ),
			supportsGrid = 'string' === typeof el.style.grid;

		if ( ! environment.ie && supportsGrid ) {
			return;
		}

		this.elements.$body.addClass( this.getSettings( 'classes.ie' ) );

		const msieCss = '<link rel="stylesheet" id="elementor-frontend-css-msie" href="' + this.config.urls.assets + 'css/frontend-msie.min.css?' + this.config.version + '" type="text/css" />';

		this.elements.$body.append( msieCss );
	}

	setDeviceModeData() {
		this.elements.$body.attr( 'data-elementor-device-mode', this.getCurrentDeviceMode() );
	}

	addListenerOnce( listenerID, event, callback, to ) {
		if ( ! to ) {
			to = this.elements.$window;
		}

		if ( ! this.isEditMode() ) {
			to.on( event, callback );

			return;
		}

		this.removeListeners( listenerID, event, to );

		if ( to instanceof jQuery ) {
			const eventNS = event + '.' + listenerID;

			to.on( eventNS, callback );
		} else {
			to.on( event, callback, listenerID );
		}
	}

	removeListeners( listenerID, event, callback, from ) {
		if ( ! from ) {
			from = this.elements.$window;
		}

		if ( from instanceof jQuery ) {
			const eventNS = event + '.' + listenerID;

			from.off( eventNS, callback );
		} else {
			from.off( event, callback, listenerID );
		}
	}

	// Based on underscore function
	debounce( func, wait ) {
		let timeout;

		return function() {
			const context = this,
				args = arguments;

			const later = () => {
				timeout = null;

				func.apply( context, args );
			};

			const callNow = ! timeout;

			clearTimeout( timeout );

			timeout = setTimeout( later, wait );

			if ( callNow ) {
				func.apply( context, args );
			}
		};
	}

	waypoint( $element, callback, options ) {
		const defaultOptions = {
			offset: '100%',
			triggerOnce: true,
		};

		options = jQuery.extend( defaultOptions, options );

		const correctCallback = function() {
			const element = this.element || this,
				result = callback.apply( element, arguments );

			// If is Waypoint new API and is frontend
			if ( options.triggerOnce && this.destroy ) {
				this.destroy();
			}

			return result;
		};

		return $element.elementorWaypoint( correctCallback, options );
	}

	muteMigrationTraces() {
		jQuery.migrateMute = true;

		jQuery.migrateTrace = false;
	}

	/**
	 * Initialize the modules' widgets handlers.
	 */
	initModules() {
		let handlers = {
			shapes: Shapes,
		};

		elementorFrontend.trigger( 'elementor/modules/init:before' );

		Object.entries( handlers ).forEach( ( [ moduleName, ModuleClass ] ) => {
			this.modulesHandlers[ moduleName ] = new ModuleClass();
		} );
	}

	populateActiveBreakpointsConfig() {
		this.config.responsive.activeBreakpoints = {};

		Object.entries( this.config.responsive.breakpoints ).forEach( ( [ breakpointKey, breakpointData ] ) => {
			if ( breakpointData.is_enabled ) {
				this.config.responsive.activeBreakpoints[ breakpointKey ] = breakpointData;
			}
		} );
	}

	init() {
		this.hooks = new EventManager();

		this.storage = new Storage();

		this.elementsHandler = new ElementsHandler( jQuery );

		this.modulesHandlers = {};

		this.addUserAgentClasses();

		this.addIeCompatibility();

		this.setDeviceModeData();

		this.initDialogsManager();

		if ( this.isEditMode() ) {
			this.muteMigrationTraces();
		}

		// Keep this line before `initOnReadyComponents` call
		this.elements.$window.trigger( 'elementor/frontend/init' );

		this.initModules();

		this.initOnReadyElements();

		this.initOnReadyComponents();
	}

	onDocumentLoaded() {
		this.documentsManager = new DocumentsManager();

		this.trigger( 'components:init' );

		new LightboxManager();
	}
}

window.elementorFrontend = new Frontend();

if ( ! elementorFrontend.isEditMode() ) {
	jQuery( () => elementorFrontend.init() );
}
