import CommandBase from './command-base';

export default class CommandData extends CommandBase {
	/**
	 * Data returned from remote.
	 *
	 * @type {*}
	 */
	data;

	/**
	 * Fetch type.
	 *
	 * @type {DataTypes}
	 */
	type;

	static getInstanceType() {
		return 'CommandData';
	}

	constructor( args, commandsAPI = $e.data ) {
		super( args, commandsAPI );

		if ( this.args.options?.type ) {
			this.type = this.args.options.type;
		}
	}

	/**
	 * Function getEndpointFormat().
	 *
	 * @returns {(null|string)}
	 */
	static getEndpointFormat() {
		return null;
	}

	/**
	 * @param {DataTypes} type
	 *
	 * @returns {boolean|{before: (function(*=): {}), after: (function({}, *=): {})}}
	 */
	getApplyMethods( type = this.type ) {
		let before, after;
		switch ( type ) {
			case 'create':
				before = this.applyBeforeCreate;
				after = this.applyAfterCreate;
				break;

			case 'delete':
				before = this.applyBeforeDelete;
				after = this.applyAfterDelete;
				break;

			case 'get':
				before = this.applyBeforeGet;
				after = this.applyAfterGet;
				break;

			case 'update':
				before = this.applyBeforeUpdate;
				after = this.applyAfterUpdate;
				break;

			default:
				return false;
		}

		return { before, after };
	}

	/**
	 * Function getRequestData().
	 *
	 * @returns {RequestData}
	 */
	getRequestData() {
		return {
			component: this.component,
			command: this.currentCommand,
			args: this.args,
			type: this.type,
			timestamp: new Date().getTime(),
			endpoint: $e.data.commandToEndpoint( this.currentCommand, elementorCommon.helpers.cloneObject( this.args ), this.constructor.getEndpointFormat() ),
		};
	}

	apply() {
		const applyMethods = this.getApplyMethods();

		// Run 'before' method.
		this.args = applyMethods.before.apply( this, [ this.args ] );

		const requestData = this.getRequestData();

		return $e.data.fetch( requestData ).then( ( data ) => {
			this.data = data;

			// Run 'after' method.
			this.data = applyMethods.after.apply( this, [ data, this.args ] );

			this.data = { data: this.data };

			// Append requestData.
			this.data = Object.assign( { __requestData__: requestData }, this.data );

			return this.data;
		} );
	}

	/**
	 * @param [args={}]
	 * @returns {{}} filtered args
	 */
	applyBeforeCreate( args = {} ) {
		return args;
	}

	/**
	 * @param {{}} data
	 * @param [args={}]
	 * @returns {{}} filtered result
	 */
	applyAfterCreate( data, args = {} ) {// eslint-disable-line no-unused-vars
		return data;
	}

	/**
	 * @param [args={}]
	 * @returns {{}} filtered args
	 */
	applyBeforeDelete( args = {} ) {
		return args;
	}

	/**
	 * @param {{}} data
	 * @param [args={}]
	 * @returns {{}} filtered result
	 */
	applyAfterDelete( data, args = {} ) {// eslint-disable-line no-unused-vars
		return data;
	}

	/**
	 * @param [args={}]
	 * @returns {{}} filtered args
	 */
	applyBeforeGet( args = {} ) {
		return args;
	}

	/**
	 * @param {{}} data
	 * @param [args={}]
	 * @returns {{}} filtered result
	 */
	applyAfterGet( data, args = {} ) {// eslint-disable-line no-unused-vars
		return data;
	}

	/**
	 * @param [args={}]
	 * @returns {{}} filtered args
	 */
	applyBeforeUpdate( args = {} ) {
		return args;
	}

	/**
	 * @param {{}} data
	 * @param [args={}]
	 * @returns {{}} filtered result
	 */
	applyAfterUpdate( data, args = {} ) {// eslint-disable-line no-unused-vars
		return data;
	}
}
