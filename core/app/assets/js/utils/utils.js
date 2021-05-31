export const pxToRem = ( pixels ) => {
	if ( ! pixels ) {
		return;
	} else if ( 'string' !== typeof pixels ) {
		pixels = pixels.toString();
	}

	return pixels
		.split( ' ' )
		.map( ( value ) => `${ value * 0.0625 }rem` )
		.join( ' ' );
};

export const arrayToClassName = ( array ) => {
	return array.filter( ( classItem ) => '' !== classItem ).join( ' ' );
};

export const stringToRemValues = ( string ) => {
	return string
		.split( ' ' )
		.map( ( value ) => pxToRem( value ) )
		.join( ' ' );
};

export const rgbToHex = ( r, g, b ) => '#' + [ r, g, b ].map( ( x ) => {
	const hex = x.toString( 16 );
	return 1 === hex.length ? '0' + hex : hex;
} ).join( '' );

export const isOneOf = ( filetype, filetypeOptions ) => {
	return filetypeOptions.some( ( type ) => filetype.includes( type ) );
};
