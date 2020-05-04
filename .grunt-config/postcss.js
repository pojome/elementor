/**
 * Grunt postcss task config
 * @package Elementor
 */
module.exports = {
	dev: {
		options: {
			map: true,

			processors: [
				require( 'autoprefixer' )( {
					browsers: 'last 10 versions'
				} )
			]
		},
		files: [ {
			src: [
				'assets/css/*.css',
				'!assets/css/*.min.css'
			]
		} ]
	},
	minify: {
		options: {
			processors: [
				require( 'autoprefixer' )( {
					browsers: 'last 10 versions'
				} ),
				require( 'cssnano' )( {
					reduceIdents: false,
					zindex: false,
				} )
			]
		},
		files: [ {
			expand: true,
			src: [
				'assets/css/**/*.css',
				'!*.min.css'
			],
			ext: '.min.css'
		} ]
	}
};
