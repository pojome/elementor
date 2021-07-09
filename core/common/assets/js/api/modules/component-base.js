import CommandBase from 'elementor-api/modules/command-base';
import CommandCallback from 'elementor-api/modules/command-callback';

export default class ComponentBase extends elementorModules.Module {
	__construct( args = {} ) {
		if ( args.manager ) {
			this.manager = args.manager;
		}

		this.commands = this.defaultCommands();
		this.commandsInternal = this.defaultCommandsInternal();
		this.hooks = this.defaultHooks();
		this.routes = this.defaultRoutes();
		this.tabs = this.defaultTabs();
		this.shortcuts = this.defaultShortcuts();
		this.utils = this.defaultUtils();
		this.data = this.defaultData();

		this.defaultRoute = '';
		this.currentTab = '';
	}

	registerAPI() {
		Object.entries( this.getTabs() ).forEach( ( tab ) => this.registerTabRoute( tab[ 0 ] ) );

		Object.entries( this.getRoutes() ).forEach( ( [ route, callback ] ) => this.registerRoute( route, callback ) );

		Object.entries( this.getCommands() ).forEach( ( [ command, callback ] ) => this.registerCommand( command, callback ) );

		Object.entries( this.getCommandsInternal() ).forEach( ( [ command, callback ] ) => this.registerCommandInternal( command, callback ) );

		Object.values( this.getHooks() ).forEach( ( instance ) => this.registerHook( instance ) );

		Object.entries( this.getData() ).forEach( ( [ command, callback ] ) => this.registerData( command, callback ) );
	}

	/**
	 * @returns {string}
	 */
	getNamespace() {
		elementorModules.ForceMethodImplementation();
	}

	getRootContainer() {
		const parts = this.getNamespace().split( '/' );
		return parts[ 0 ];
	}

	defaultTabs() {
		return {};
	}

	defaultRoutes() {
		return {};
	}

	defaultCommands() {
		return {};
	}

	defaultCommandsInternal() {
		return {};
	}

	defaultHooks() {
		return {};
	}

	defaultShortcuts() {
		return {};
	}

	defaultUtils() {
		return {};
	}

	defaultData() {
		return {};
	}

	getCommands() {
		return this.commands;
	}

	getCommandsInternal() {
		return this.commandsInternal;
	}

	getHooks() {
		return this.hooks;
	}

	getRoutes() {
		return this.routes;
	}

	getTabs() {
		return this.tabs;
	}

	getShortcuts() {
		return this.shortcuts;
	}

	getData() {
		return this.data;
	}

	/**
	 * @param {string} command
	 * @param {(function()|typeof CommandInfra)} context
	 * @param commandsAPI
	 */
	registerCommand( command, context, commandsAPI = $e.commands ) {
		const fullCommand = this.getNamespace() + '/' + command,
			instanceType = context.getInstanceType ? context.getInstanceType() : false,
			registerConfig = {
				command: fullCommand,
				component: this,
			};

		// Support pure callback.
		if ( ! instanceType ) {
			if ( $e.devTools ) {
				$e.devTools.log.warn( `Attach command-callback, on command: '${ fullCommand }', context is unknown type.` );
			}

			registerConfig.callback = context;

			context = CommandCallback;
		}

		context.setRegisterConfig( registerConfig );

		commandsAPI.register( this, command, context );
	}

	/**
	 * @param {HookBase} instance
	 */
	registerHook( instance ) {
		return instance.register();
	}

	registerCommandInternal( command, context ) {
		this.registerCommand( command, context, $e.commandsInternal );
	}

	registerRoute( route, callback ) {
		$e.routes.register( this, route, callback );
	}

	registerData( command, context ) {
		this.registerCommand( command, context, $e.data );
	}

	unregisterRoute( route ) {
		$e.routes.unregister( this, route );
	}

	registerTabRoute( tab ) {
		this.registerRoute( tab, ( args ) => this.activateTab( tab, args ) );
	}

	dependency() {
		return true;
	}

	open() {
		return true;
	}

	close() {
		if ( ! this.isOpen ) {
			return false;
		}

		this.isOpen = false;

		this.inactivate();

		$e.routes.clearCurrent( this.getNamespace() );

		$e.routes.clearHistory( this.getRootContainer() );

		return true;
	}

	activate() {
		$e.components.activate( this.getNamespace() );
	}

	inactivate() {
		$e.components.inactivate( this.getNamespace() );
	}

	isActive() {
		return $e.components.isActive( this.getNamespace() );
	}

	onRoute( route ) {
		this.toggleRouteClass( route, true );
		this.toggleHistoryClass();

		this.activate();
		this.trigger( 'route/open', route );
	}

	onCloseRoute( route ) {
		this.toggleRouteClass( route, false );

		this.inactivate();
		this.trigger( 'route/close', route );
	}

	setDefaultRoute( route ) {
		this.defaultRoute = this.getNamespace() + '/' + route;
	}

	getDefaultRoute() {
		return this.defaultRoute;
	}

	removeTab( tab ) {
		delete this.tabs[ tab ];
		this.unregisterRoute( tab );
	}

	hasTab( tab ) {
		return ! ! this.tabs[ tab ];
	}

	addTab( tab, args, position ) {
		this.tabs[ tab ] = args;
		// It can be 0.
		if ( 'undefined' !== typeof position ) {
			const newTabs = {};
			const ids = Object.keys( this.tabs );
			// Remove new tab
			ids.pop();

			// Add it to position.
			ids.splice( position, 0, tab );

			ids.forEach( ( id ) => {
				newTabs[ id ] = this.tabs[ id ];
			} );

			this.tabs = newTabs;
		}

		this.registerTabRoute( tab );
	}

	getTabsWrapperSelector() {
		return '';
	}

	getTabRoute( tab ) {
		return this.getNamespace() + '/' + tab;
	}

	renderTab( tab ) {} // eslint-disable-line

	activateTab( tab, args ) {
		this.currentTab = tab;
		this.renderTab( tab, args );

		jQuery( this.getTabsWrapperSelector() + ' .elementor-component-tab' )
			.off( 'click' )
			.on( 'click', ( event ) => {
				$e.route( this.getTabRoute( event.currentTarget.dataset.tab ), args );
			} )
			.removeClass( 'elementor-active' )
			.filter( '[data-tab="' + tab + '"]' )
			.addClass( 'elementor-active' );
	}

	getActiveTabConfig() {
		return this.tabs[ this.currentTab ] || {};
	}

	getBodyClass( route ) {
		return 'e-route-' + route.replace( /\//g, '-' );
	}

	/**
	 * If command includes uppercase character convert it to lowercase and add `-`.
	 * e.g: `CopyAll` is converted to `copy-all`.
	 */
	normalizeCommandName( commandName ) {
		return commandName.replace( /[A-Z]/g, ( match, offset ) => ( offset > 0 ? '-' : '' ) + match.toLowerCase() );
	}

	/**
	 * @param {Object.<string, CommandBase>} commandsFromImport
	 * @returns {{}} imported commands
	 */
	importCommands( commandsFromImport ) {
		const commands = {};

		// Convert `Commands` to `ComponentBase` workable format.
		Object.entries( commandsFromImport ).forEach( ( [ className, Class ] ) => {
			const command = this.normalizeCommandName( className );
			commands[ command ] = Class;
		} );

		return commands;
	}

	importHooks( hooksFromImport ) {
		const hooks = {};

		for ( const key in hooksFromImport ) {
			const hook = new hooksFromImport[ key ];

			hooks[ hook.getId() ] = hook;
		}

		return hooks;
	}

	toggleRouteClass( route, state ) {
		elementorCommon.elements.$body.toggleClass( this.getBodyClass( route ), state );
	}

	toggleHistoryClass() {
		elementorCommon.elements.$body.toggleClass( 'e-routes-has-history', !! $e.routes.getHistory( this.getRootContainer() ).length );
	}
}
