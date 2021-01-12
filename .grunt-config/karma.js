/**
 * Grunt karma task config
 * @package Elementor
 */
module.exports = {
	// Shared config.
	options: {
		configFile: 'karma.conf.js',
	},
	unit: {
		reporters: [ 'dots', 'fail-fast']
	},
	coverage: {
		reporters: [ 'progress', 'coverage', 'coverage-istanbul' ],
	},
	coverageHtml: {
		reporters: [ 'progress', 'coverage', 'coverage-istanbul' ],
		reports: [ 'text', 'html' ],
	},
	debug: {
		browsers: [ 'Chrome' ],
		singleRun: false,
		client: {
			qunit: {
				showUI: true,
			},
		},
	},
};
