// TODO: Wrong location used as `elementorModules.ForceMethodImplementation(); should be` `elementorUtils.forceMethodImplementation()`;

export class ForceMethodImplementation extends Error {
	constructor( info = {} ) {
		super( `${ info.isStatic ? 'static ' : '' }${ info.fullName }() should be implemented, please provide '${ info.functionName || info.fullName }' functionality.` );

		Error.captureStackTrace( this, ForceMethodImplementation );
	}
}

export default () => {
	const stack = Error().stack,
		caller = stack.split( '\n' )[ 2 ].trim(),
		callerName = caller.startsWith( 'at new' ) ?
			'constructor' : caller.split( ' ' )[ 1 ],
		info = {};

	info.functionName = callerName;
	info.fullName = callerName;

	if ( info.functionName.includes( '.' ) ) {
		const parts = info.functionName.split( '.' );

		info.className = parts[ 0 ];
		info.functionName = parts[ 1 ];
	} else {
		info.isStatic = true;
	}

	throw new ForceMethodImplementation( info );
};
