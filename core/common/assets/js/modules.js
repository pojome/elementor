require( 'elementor-assets-js/modules/modules' );

// // TODO: Remove - Backwards compatibility.
// elementorModules.common = new Proxy( {}, {
// 	get: ( target, field ) => {
// 		elementorCommon.helpers.softDeprecated( `elementorModules.common.${ field }`, '3.0.0', `elementorCommon.${ field }` );
//
// 		return elementorCommon[ field ];
// 	},
//
// 	set( target, field, value ) {
// 		elementorCommon.helpers.softDeprecated( `elementorModules.common.${ field }`, '3.0.0', `elementorCommon.${ field }` );
//
// 		throw new Error( 'Deprecated' );
// 	},
// } );
