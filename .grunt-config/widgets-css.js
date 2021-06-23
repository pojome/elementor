const path = require( 'path' );
const fs = require( 'fs' );
const write = require('write');

class WidgetsCss {
	constructor( env ) {
		this.env = env;

		// Can't be empty.
		this.tempFilePrefix = 'widget-';

		this.sourceScssFolder = path.resolve( __dirname, '../assets/dev/scss/frontend/widgets' );

		// Temporary SCSS files are created and eventually transpiled into production CSS files.
		this.tempScssFolder = path.resolve( __dirname, '../assets/dev/scss/direction' );

		this.cssDestinationFolder = path.resolve( __dirname, '../assets/css' );
	}

	createWidgetsTempScssFiles() {
		if ( fs.existsSync( this.sourceScssFolder ) ) {
			fs.readdirSync( this.sourceScssFolder ).forEach( ( fileName ) => {
				const widgetName = fileName.replace( '.scss', '' ),
					widgetScssFileDest = path.join( this.tempScssFolder, this.tempFilePrefix + fileName ),
					widgetScssRtlFileDest = path.join( this.tempScssFolder, this.tempFilePrefix + fileName.replace( '.scss', '-rtl.scss' ) );

				write.sync( widgetScssFileDest, this.getWidgetScssContent( widgetName, 'ltr' ) );
				write.sync( widgetScssRtlFileDest, this.getWidgetScssContent( widgetName, 'rtl' ) );
			} );
		}
	}

	getWidgetScssContent( widgetName, direction ) {
		return `$direction: ${ direction };

@import "../helpers/direction";

@import "../../../../assets/dev/scss/helpers/variables";
@import "../../../../assets/dev/scss/helpers/mixins";
@import "../../../../assets/dev/scss/frontend/breakpoints/breakpoints";

@import "../frontend/widgets/${ widgetName }";
		`;
	}

	removeFile( filePath ) {
		fs.unlink( filePath, err => {
			if ( err ) throw err;
		} );
	}

	removeWidgetsUnusedStyleFiles() {
		const tempFilesFolders = [ this.tempScssFolder, this.cssDestinationFolder ];

		tempFilesFolders.forEach( ( folder ) => {
			if ( fs.existsSync( folder ) ) {
				fs.readdirSync( folder ).forEach( ( fileName ) => {
					const filePath = path.join( folder, fileName );

					// In the assets/css folder we should only keep the .min.css files because the widgets CSS conditional loading is active only in production mode.
					if ( 0 === fileName.indexOf( this.tempFilePrefix ) && fs.existsSync( filePath ) && -1 === fileName.indexOf( '.min.css' ) ) {
						fs.unlink( filePath, err => {
							if ( err ) throw err;
						} );
					}
				} );
			}
		} );
	}
}

module.exports = WidgetsCss;
