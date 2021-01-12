module.exports = ( config ) => {
	config.files = [
		...config.files,
		// Editor Fixtures.
		'tests/qunit/setup/editor/index.html',

		// Editor Tinymce.
		'tests/qunit/setup/editor/tinymce.js',
		'tests/qunit/vendor/wp-includes/quicktags.min.js',

		// Editor Config.
		'tests/qunit/setup/editor/wordpress.js',
		'tests/qunit/setup/editor/marionette.js',

		// Editor Dependencies.
		'tests/qunit/vendor/wp-includes/jquery-ui.min.js',
		'assets/lib/tipsy/tipsy.min.js',
		'assets/lib/perfect-scrollbar/js/perfect-scrollbar.min.js',
		'assets/lib/nouislider/nouislider.min.js',
		'assets/lib/imagesloaded/imagesloaded.min.js',
		'assets/dev/js/editor/utils/jquery-serialize-object.js',
		'assets/dev/js/editor/utils/jquery-html5-dnd.js',
		'assets/lib/jquery-hover-intent/jquery-hover-intent.min.js',

		// Editor.
		'assets/js/editor-modules.js',
		'assets/js/editor-document.js',

		// Tests.
		'assets/js/qunit-tests.js',
	];

	config.preprocessors[ 'tests/qunit/setup/editor/index.html' ] = [ 'html2js' ];
};
