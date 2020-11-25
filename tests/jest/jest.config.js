const path = require( 'path' );

const paths = {
	babelConfig: path.resolve( __dirname, '../../.grunt-config/webpack.babel.js' ),
	jestSetup: path.resolve( __dirname, './setup-tests.js' ),
	libraries: {
		react: path.resolve( __dirname, '../qunit/vendor/wp-includes/react.min.js' ),
		reactDom: path.resolve( __dirname, '../qunit/vendor/wp-includes/react-dom.min.js' ),
	},
	mocks: {
		css: path.resolve( __dirname, './__mocks__/style-mock.js' ),
	},
	webpackAlias: path.resolve( __dirname, '../../.grunt-config/webpack.alias' ),
};

module.exports = {
	moduleNameMapper: {
		...transformWebpackAliasIntoJestAlias( require( paths.webpackAlias ).resolve.alias ),
		'\\.(css|less|sass|scss)$': paths.mocks.css,
		'^react$': paths.libraries.react,
		'^react-dom(.*)$': paths.libraries.reactDom,
	},
	setupFilesAfterEnv: [ paths.jestSetup ],
	testMatch: [ './**/?(*.)+(spec|test).[jt]s?(x)' ],
	transform: {
		'\\.js$': [ 'babel-jest', { configFile: paths.babelConfig } ],
	},
	verbose: true,
};

function transformWebpackAliasIntoJestAlias( webpackAlias ) {
	return Object.keys( webpackAlias )
		.reduce( ( current, aliasKey ) => ( {
			...current,
			[ `^${ aliasKey }/(.*)$` ]: `${ webpackAlias[ aliasKey ] }/$1`,
		} ), {} );
}
