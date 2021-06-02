import dropCSS from 'dropCSS';
import csso from 'csso';
import getCssSelector from 'css-selector-generator';

export default class CssOptimizer {
	allCSS = [ ...frontendStyleSheets ]
		.map( ( styleSheet ) => {
			try {
				return [ ...styleSheet.cssRules ]
					.map( ( rule ) => rule.cssText )
					.join( '' );
			} catch ( e ) {
				console.log( 'Access to stylesheet %s is denied. Ignoring...', styleSheet.href );
			}
		} )
		.filter( Boolean )
		.join( '\n' );

	usedCSS = dropCSS( {
		html: HTML,
		css: allCSS,
	} );
};
