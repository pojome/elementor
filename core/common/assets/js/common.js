import Helpers from './utils/helpers';
import Storage from './utils/storage';
import Debug from './utils/debug';
import Ajax from 'elementor-common-modules/ajax/assets/js/ajax';
import Finder from 'elementor-common-modules/finder/assets/js/finder';
import Connect from 'elementor-common-modules/connect/assets/js/connect';
import API from './api/';
import ComponentBase from 'elementor-api/modules/component-base';
import ComponentModalBase from 'elementor-api/modules/component-modal-base';
import HookBreak from 'elementor-api/modules/hook-break';
import ModalLayout from 'elementor-common/views/modal/layout';
import * as modules from './modules/';

class ElementorCommonApp extends modules.ViewModule {
	views = {
		modal: {
			Layout: ModalLayout,
		},
	};

	modules = {
		... modules,
	};

	get Component() {
		// `elementorCommon` isn't available during it self initialize.
		setTimeout( () => {
			elementorCommon.helpers.softDeprecated( 'elementorModules.common.Component', '2.9.0',
				'$e.modules.ComponentBase' );
		}, 2000 );
		return ComponentBase;
	}

	get ComponentModal() {
		// `elementorCommon` isn't available during it self initialize.
		setTimeout( () => {
			elementorCommon.helpers.softDeprecated( 'elementorModules.common.ComponentModal', '2.9.0',
				'$e.modules.ComponentModalBase' );
		}, 2000 );
		return ComponentModalBase;
	}

	get HookBreak() {
		// `elementorCommon` isn't available during it self initialize.
		setTimeout( () => {
			elementorCommon.helpers.softDeprecated( 'elementorModules.common.HookBreak', '2.9.0',
				'$e.modules.HookBreak' );
		}, 2000 );
		return HookBreak;
	}

	setMarionetteTemplateCompiler() {
		Marionette.TemplateCache.prototype.compileTemplate = ( rawTemplate, options ) => {
			options = {
				evaluate: /<#([\s\S]+?)#>/g,
				interpolate: /{{{([\s\S]+?)}}}/g,
				escape: /{{([^}]+?)}}(?!})/g,
			};

			return _.template( rawTemplate, options );
		};
	}

	getDefaultElements() {
		return {
			$window: jQuery( window ),
			$document: jQuery( document ),
			$body: jQuery( document.body ),
		};
	}

	initComponents() {
		this.debug = new Debug();

		this.helpers = new Helpers();

		this.storage = new Storage();

		this.dialogsManager = new DialogsManager.Instance();

		this.api = new API();

		this.initModules();
	}

	initModules() {
		const { activeModules } = this.config;

		const coreModules = {
			ajax: Ajax,
			finder: Finder,
			connect: Connect,
		};

		activeModules.forEach( ( name ) => {
			if ( coreModules[ name ] ) {
				this[ name ] = new coreModules[ name ]( this.config[ name ] );
			}
		} );
	}

	compileArrayTemplateArgs( template, templateArgs ) {
		return template.replace( /%(?:(\d+)\$)?s/g, ( match, number ) => {
			if ( ! number ) {
				number = 1;
			}

			number--;
			return undefined !== templateArgs[ number ] ? templateArgs[ number ] : match;
		} );
	}

	compileObjectTemplateArgs( template, templateArgs ) {
		return template.replace( /{{(?:([ \w]+))}}/g, ( match, name ) => {
			return templateArgs[ name ] ? templateArgs[ name ] : match;
		} );
	}

	compileTemplate( template, templateArgs ) {
		return jQuery.isPlainObject( templateArgs ) ? this.compileObjectTemplateArgs( template, templateArgs ) : this.compileArrayTemplateArgs( template, templateArgs );
	}

	translate( stringKey, context, templateArgs, i18nStack ) {
		if ( context ) {
			i18nStack = this.config[ context ].i18n;
		}

		if ( ! i18nStack ) {
			i18nStack = this.config.i18n;
		}

		let string = i18nStack[ stringKey ];

		if ( undefined === string ) {
			string = stringKey;
		}

		if ( templateArgs ) {
			string = this.compileTemplate( string, templateArgs );
		}

		return string;
	}

	onInit() {
		super.onInit();

		this.config = elementorCommonConfig;

		this.setMarionetteTemplateCompiler();
	}
}

window.elementorCommon = new ElementorCommonApp();

elementorCommon.initComponents();
