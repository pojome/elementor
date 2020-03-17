export default class ArgsObject {
	static [Symbol.hasInstance]( obj ) {
		const result = super[ Symbol.hasInstance ]( obj );

		if ( result && obj ) {
			const name = this.getBaseName ? this.getBaseName() : 'anonymous_' + new Date().getTime();

			if ( ! obj.classes ) {
				obj.classes = [];
			}

			if ( -1 === obj.classes.indexOf( name ) ) {
				obj.classes.push( name );
			}
		}

		return result;
	}

	/**
	 * Function constructor().
	 *
	 * Create ArgsObject.
	 *
	 * @param {{}} args
	 */
	constructor( args ) {
		this.args = args;

		if ( ! this.classes ) {
			this.classes = [];
		}
	}

	static getBaseName() {
		return 'args-object';
	}

	/**
	 * Function requireArgument().
	 *
	 * Validate property in args.
	 *
	 * @param {string} property
	 * @param {{}} args
	 *
	 * @throws {Error}
	 *
	 */
	requireArgument( property, args = this.args ) {
		if ( ! args.hasOwnProperty( property ) ) {
			throw Error( `${ property } is required.` );
		}
	}

	/**
	 * Function requireArgumentType().
	 *
	 * Validate property in args using `type === typeof(args.whatever)`.
	 *
	 * @param {string} property
	 * @param {string} type
	 * @param {{}} args
	 *
	 * @throws {Error}
	 *
	 */
	requireArgumentType( property, type, args = this.args ) {
		this.requireArgument( property, args );

		if ( ( typeof args[ property ] !== type ) ) {
			throw Error( `${ property } invalid type: ${ type }.` );
		}
	}

	/**
	 * Function requireArgumentInstance().
	 *
	 * Validate property in args using `args.whatever instanceof instance`.
	 *
	 * @param {string} property
	 * @param {instanceof} instance
	 * @param {{}} args
	 *
	 * @throws {Error}
	 *
	 */
	requireArgumentInstance( property, instance, args = this.args ) {
		this.requireArgument( property, args );

		if ( ! ( args[ property ] instanceof instance ) ) {
			throw Error( `${ property } invalid instance.` );
		}
	}

	/**
	 * Function requireArgumentConstructor().
	 *
	 * Validate property in args using `type === args.whatever.constructor`.
	 *
	 * @param {string} property
	 * @param {*} type
	 * @param {{}} args
	 *
	 * @throws {Error}
	 *
	 */
	requireArgumentConstructor( property, type, args = this.args ) {
		this.requireArgument( property, args );

		if ( args[ property ].constructor !== type ) {
			throw Error( `${ property } invalid constructor type.` );
		}
	}
}
