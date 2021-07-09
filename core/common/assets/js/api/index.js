/* Alphabetical order */
import BackwardsCompatibility from './core/backwards-compatibility';
import CommandBase from './modules/command-base';
import CommandInternalBase from './modules/command-internal-base';
import CommandData from './modules/command-data';
import Commands from './core/commands';
import CommandsInternal from './core/commands-internal';
import ComponentBase from './modules/component-base';
import ComponentModalBase from './modules/component-modal-base';
import Components from './core/components';
import Data from './core/data.js';
import HashCommands from './extras/hash-commands';
import HookBreak from './modules/hook-break';
import Hooks from './core/hooks';
import Routes from './core/routes';
import Shortcuts from './core/shortcuts';

import * as hookData from './modules/hooks/data/';
import * as hookUI from './modules/hooks/ui';

export default class API {
	/**
	 * Function constructor().
	 *
	 * Creates 'elementor' api.
	 */
	constructor() {
		window.$e = this;

		this.components = new Components();

		this.commands = new Commands();
		this.commandsInternal = new CommandsInternal();

		this.hooks = new Hooks();
		this.routes = new Routes();
		this.shortcuts = new Shortcuts( jQuery( window ) );
		this.data = new Data();

		this.modules = {
			// get CommandInternalBase() {
			// 	elementorCommon.helpers.softDeprecated(
			// 		'$e.modules.CommandInternalBase',
			// 		'3.0.3',
			// 		'$e.modules.CommandInternal'
			// 	);
			//
			// 	return this.CommandInternal;
			// },

			CommandBase,
			CommandInternalBase,
			CommandData,

			ComponentBase,
			ComponentModalBase,

			HookBreak,

			hookData,
			hookUI,
		};

		this.extras = {
			hashCommands: new HashCommands(),
		};

		// Backwards compatibility should be last, in order to handle others.
		this.bc = new BackwardsCompatibility();
	}

	/**
	 * Function run().
	 *
	 * Alias of `$e.commands.run()`.
	 *
	 * @param {string} command
	 * @param {{}} [args={}]
	 *
	 * @returns {*}
	 */
	run( command, args = {} ) {
		return $e.commands.run( command, args );
	}

	/**
	 * Function internal().
	 *
	 * Alias of `$e.commandsInternal.run()`.
	 *
	 * @param {string} command
	 * @param {{}} [args={}]
	 *
	 * @returns {*}
	 */
	internal( command, args = {} ) {
		return $e.commandsInternal.run( command, args );
	}

	/**
	 * Function route().
	 *
	 * Alias of `$e.routes.to()`.
	 *
	 * @param {string} route
	 * @param [args={}]
	 */
	route( route, args = {} ) {
		return $e.routes.to( route, args );
	}

	// TODO: shortcut();
}
