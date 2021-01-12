const karmaParameters = [],
	karmaArguments = [];

if ( process.argv[ process.argv.length - 1 ] ) {
	process.argv[ process.argv.length - 1 ].split( ':' ).forEach(
		( param ) => karmaParameters.push( param )
	);

	// Remove 'karma'.
	karmaParameters.shift();
}

module.exports = function( config ) {
	// Support arguments like '--whatever:value' when running 'karma:unit' from grunt.
	config.client.args.forEach( ( argument ) => {
		argument = argument.split( ':' );
		argument[ 0 ] = argument[ 0 ].replace( /--/g, '' );

		karmaArguments.push( argument );
	} );

	let target = 'editor',
		targetArgument = karmaArguments.find( ( arg ) => 'target' === arg[ 0 ] );

	if ( ! targetArgument ) {
		targetArgument = global.arguments ? [ 'target', global.arguments.target ] : false;
	}

	if ( targetArgument ) {
		target = targetArgument[ 1 ];
	}

	const karmaConfig = {
		basePath: './',
		frameworks: [ 'qunit' ],
		files: [
			{
				pattern: 'assets/js/**/*.js.map',
				included: false,
			},

			// Base Libraries.
			'tests/qunit/vendor/wp-includes/jquery.js',
			'tests/qunit/vendor/wp-includes/underscore.min.js',
			'tests/qunit/vendor/wp-includes/backbone.min.js',
			'tests/qunit/vendor/wp-includes/react.min.js',
			'tests/qunit/vendor/wp-includes/react-dom.min.js',
			'assets/lib/backbone/backbone.marionette.min.js',
			'assets/lib/backbone/backbone.radio.min.js',

			// Elementor Common.
			'tests/qunit/setup/common/elementor-common.js',
			'assets/lib/dialog/dialog.js',
			'assets/js/common-modules.js',
			'assets/js/common.js',
		],
		preprocessors: {
			'assets/js/common-modules.js': [ 'coverage' ],
			'assets/js/common.js': [ 'coverage' ],
			'assets/js/editor-document.js': [ 'coverage' ],
		},
		reporters: [ 'progress' ],
		coverageIstanbulReporter: {
			reports: [ 'text' ],
			fixWebpackSourcePaths: true,
			// enforce percentage thresholds
			// anything under these percentages will cause karma to fail with an exit code of 1 if not running in watch mode
			thresholds: {
				emitWarning: false, // set to `true` to not fail the test command when thresholds are not met
				// thresholds for all files
				global: {
					statements: 50, /* TEMP: initial value */
					lines: 55, /* TEMP: initial value */
					branches: 25, /* TEMP: initial value */
					functions: 55, /* TEMP: initial value */
				},
			},
		},
		// web server port
		port: 9876,
		colors: true,
		logLevel: config.LOG_INFO,
		browsers: [ 'ChromeHeadless' ],
		// Continuous Integration mode
		// if true, Karma captures browsers, runs the tests and exits
		singleRun: true,

		// client configuration
		client: {
			karmaParameters,
			karmaArguments,
			target,
			clearContext: true,
			qunit: {
				isDebug: karmaParameters.includes( 'debug' ),
				showUI: false,
				testTimeout: 5000,
			},
		},
	};

	if ( 'editor' === target && karmaConfig.client.qunit.isDebug ) {
		const fs = require( 'fs' );

		if ( fs.existsSync( '../elementor-dev-tools' ) ) {
			const last = karmaConfig.files.pop();

			karmaConfig.files.push( { pattern: '../elementor-dev-tools/assets/js/editor.js', type: 'module' } );

			karmaConfig.files.push( last );
		}
	}

	const setupHandler = require( `../elementor/tests/qunit/setup/${ target }/setup` );

	console.log( `preparing tests environment for: '${ target }'.` );

	setupHandler( karmaConfig );

	config.set( karmaConfig );
};
