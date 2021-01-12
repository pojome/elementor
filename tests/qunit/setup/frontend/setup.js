module.exports = ( config ) => {
	config.files = [
		...config.files,
		// Fixtures.
		'tests/qunit/setup/frontend/index.html',

		'assets/js/frontend-modules.js',

		'assets/js/qunit-tests.js',
	];

	config.preprocessors[ 'tests/qunit/setup/frontend/index.html' ] = [ 'html2js' ];
};
