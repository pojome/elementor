// TODO: Remove - Backwards compatibility.
window.elementorModules = new Proxy( {}, {
	get: ( object, field ) => {
		if ( 'common' === field ) {
			return new Proxy( {}, {
				get: ( _object, _field ) => {
					elementorCommon.helpers.softDeprecated( `elementorModules.common.${ _field }`, '3.0.0', `elementorCommon.${ _field }` );

					return elementorCommon[ _field ];
				},

				set( target, _field, value ) {
					elementorCommon.helpers.softDeprecated( `elementorModules.common.${ _field }`, '3.0.0', `elementorCommon.${ _field }` );

					throw new Error( 'Deprecated' );
				},
			} );
		}

		elementorCommon.helpers.softDeprecated( `elementorModules.${ field }`, '3.0.0', `elementorCommon.modules.${ field }` );

		return elementorCommon.modules[ field ];
	},

	set( object, field, value ) {
		elementorCommon.helpers.softDeprecated( `elementorModules.${ field }`, '3.0.0', `elementorCommon.modules.${ field }` );

		elementorCommon.modules[ field ] = value;

		return true;
	},
} );
