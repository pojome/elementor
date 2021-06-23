/**
 * Grunt copy task config
 * @package Elementor
 */
const getBuildFiles = [
	'**',
	'!.git/**',
	'!.github/**',
	'!.gitignore',
	'!.gitmodules',
	'!.jscsrc',
	'!karma.conf.js',
	'!.jshintignore',
	'!.jshintrc',
	'!.travis.yml',
	'!assets/**/*.map',
	'!assets/dev/**',
	'!assets/js/qunit-tests*',
	'!bin/**',
	'!build/**',
	'!composer.json',
	'!composer.lock',
	'!core/**/assets/**',
	'!cypress.json',
	'!docker-compose.yml',
	'!docs/**',
	'!Gruntfile.js',
	'!local-site/**',
	'!logo.png',
	'!modules/**/assets/**',
	'!nightwatch.conf.js',
	'!node_modules/**',
	'!npm-debug.log',
	'!package-lock.json',
	'!package.json',
	'!phpunit.xml',
	'!CHANGELOG.md',
	'!README.md',
	'!phpcs.xml',
	'!tests/**',
	'!vendor/**',
	'!yarn.lock',
	'!*~',
	'!commitlint.config.js',

	// Conflict with above rule.
	'core/files/assets/**',
];
/**
 * @type {{main: {src: string[], expand: boolean, dest: string}, secondary: {src: string[], expand: boolean, dest: string}}}
 */
const copy = {
	main: {
		src: getBuildFiles,
		expand: true,
		dest: 'build/'
	},
	secondary: {
		src: getBuildFiles,
		expand: true,
		dest: '/tmp/elementor-builds/<%= pkg.version %>/'
	}
};

module.exports = copy;
