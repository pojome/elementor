const sass = {
	dist: {
		options: {
			sourcemap: true,
			sourceMap: true
		},
		files: [
			{
				expand: true,
				cwd: 'assets/dev/scss/direction',
				src: '*.scss',
				dest: 'assets/css',
				ext: '.css'
			},
			{
				expand: true,
				cwd: 'assets/dev/scss/editor/themes',
				src: '*.scss',
				dest: 'assets/css/themes',
				ext: '.css'
			}
		]
	}
};

module.exports = sass;
