import { finder } from '@medv/finder';
import Critical from './analyzer-utils/critical';
import Compressor from 'compressorjs';
import csso from 'csso';

class Analyzer {
	constructor( options ) {
		this.options = options;
	}

	getAllStyleSheets( elementorOnly = null ) {
		const matchElementor = /wp-content[\/|\\]plugins[\/|\\]elementor/g;
		const matchAllFrontend = /wp-content/g;
		const matchPath = elementorOnly ? matchElementor : matchAllFrontend;

		return [ ...this.styleSheets ].filter( ( styleSheet ) => {
			return null !== styleSheet.href ?
				styleSheet.href.match( matchPath ) :
				false;
		} );
	}

	getAllCSS() {
		return [ ...this.getAllStyleSheets() ].map( ( styleSheet ) => {
			try {
				return [ ...styleSheet.cssRules ].map( ( rule ) => rule.cssText ).join( '' );
			} catch ( e ) {
				console.error( 'Access to stylesheet %s is denied. Ignoring...', styleSheet.href );
			}
		} )
		.filter( Boolean )
		.join( '\n' );
	}

	getPageHTML() {
		return document.documentElement.outerHTML;
	}

	getGoogleFonts() {
		const fontParseHelper = document.createElement( 'a' );
		const fontStyleSheets = [ ...this.styleSheets ].filter( ( styleSheet ) => {
			return null !== styleSheet.href ?
				styleSheet.href.match( /^https:\/\/fonts\.googleapis\.com/ ) :
				false;
		} );
		const fontFamilies = [];

		fontStyleSheets.forEach( ( styleSheet ) => {
			fontParseHelper.href = styleSheet.href;

			const fontsParams = new URLSearchParams( fontParseHelper.search );
			const fontsArray = fontsParams.get( 'family' ).split( '|' );

			fontFamilies.push(
				fontsArray.map( ( font ) => font.substr( 0, font.indexOf( ':' ) ) )
			);
		} );

		return fontFamilies.flat();
	}

	getImages() {
		const images = [];
		const promises = [];

		[ ...document.querySelectorAll( '.elementor-element img' ) ].forEach( ( el, i ) => {
			images[ i ] = {
				clientWidth: el.clientWidth,
				clientHeight: el.clientHeight,
				parentWidget: el.closest( '.elementor-widget' ).getAttribute( 'data-id' ),
				src: el.src,
			};

			promises[ i ] = new Promise( ( resolve, reject ) => {
				const img = document.createElement( 'img' );
				img.setAttribute( 'src', el.src );

				img.addEventListener( 'load', () => {
					images[ i ].naturalWidth = img.naturalWidth;
					images[ i ].naturalHeight = img.naturalHeight;
					resolve( images[ i ] );
				} );
				img.addEventListener( 'error', ( err ) => reject( err ) );
				img.classList.add( 'analyzer-temp-img' );

				document.body.appendChild( img );
			} );
		} );

		Promise.all( [ ...promises ] ).then( () => {
			[ ...document.querySelectorAll( '.analyzer-temp-img' ) ].forEach( ( img ) => img.remove() );
		} ).catch( ( err ) => {
			console.error( err );
		} );

		return images;
	}

	getBackgroundImages() {
		const elementsToInspect = document.querySelectorAll(
			this.selectors.sections + ', ' + this.selectors.sections + ' *:not(:is(script, img, figure, style))'
		);
		const srcChecker = /url\(\s*?['"]?\s*?(\S+?)\s*?["']?\s*?\)/i;
		const fileExtension = /\S+?\.(?:jpg|jpeg|png)/i;
		const elements = [];
		const promises = [];

		[ ...elementsToInspect ].forEach( ( el ) => {
			const prop = window.getComputedStyle( el, null )
				.getPropertyValue( 'background-image' );
			const urlMatch = srcChecker.exec( prop );
			const match = urlMatch ? fileExtension.exec( urlMatch[ 1 ] ) : false;

			if ( ! match ) {
				return;
			}

			const isSelfRoot = el.classList.contains( 'elementor-element' ) &&
				el.getAttribute( 'data-id' );
			const rootNode = isSelfRoot ? el : el.closest( '.elementor-section' );
			const rootNodeID = rootNode.getAttribute( 'data-id' );
			const rootSelector = '.elementor-element-' + rootNodeID;
			const cssSelector = isSelfRoot ? rootSelector : rootSelector + ' ' + finder( el, {
				root: rootNode,
			} );

			elements.push( {
				backgroundSize: window.getComputedStyle( el, null )
					.getPropertyValue( 'background-size' ),
				cssSelector: isSelfRoot ? rootSelector : cssSelector,
				clientWidth: el.clientWidth,
				clientHeight: el.clientHeight,
				rootNodeID: rootNodeID,
				url: match[ 0 ],
			} );
		} );

		elements.forEach( ( element, ) => {
			const el = document.querySelector( element.cssSelector );

			promises.push( new Promise( ( resolve, reject ) => {
				const img = document.createElement( 'img' );
				img.setAttribute( 'src', element.url );

				img.addEventListener( 'load', () => {
					element.naturalWidth = img.naturalWidth;
					element.naturalHeight = img.naturalHeight;
					resolve( element );
				} );
				img.addEventListener( 'error', ( err ) => reject( err ) );
				img.classList.add( 'analyzer-temp-img' );

				document.body.appendChild( img );
			} ) );
		} );

		Promise.all( [ ...promises ] ).then( () => {
			[ ...document.querySelectorAll( '.analyzer-temp-img' ) ].forEach( ( img ) => img.remove() );
		} ).catch( ( err ) => {
			console.error( err );
		} );

		return elements;
	}

	async imgToBlob( img ) {
		return fetch( img ).then( function( response ) {
			return response.blob();
		} );
		/*
			.then( function( blob ) {

				this.blobs[ i ] = {
					optimized: Compressor,
					placeholder: ,
				};
			} );
		 */
	}

	optimizeImages( images, placeholders ) {
		const isBackgroundImg = ! ! images[ 0 ].url;
		const possibleSizes = placeholders ? [ 256, 128, 64, 32 ] : [ 1600, 1024, 768, 512, 256, 128, 64, 32 ];

		const self = this;
		const promises = images.map( ( image, i ) => {
			const src = image.src ? image.src : image.url;
			const sizeDeltaThreshold = placeholders ? 0.333 : 1.5;
			const maxWidth = possibleSizes.reduce( ( prev, curr ) => {
				const goal = sizeDeltaThreshold * image.clientWidth;
				return ( Math.abs( curr - goal ) < Math.abs( prev - goal ) ? curr : prev );
			} );

			return this.imgToBlob( src )
				.then( ( blob ) => {
					return new Promise( ( resolve, reject ) => {
						if ( placeholders ) {
							new Compressor( blob, {
								quality: 0.4,
								maxWidth: maxWidth,
								maxHeight: maxWidth * 3,
								mimeType: 'image/webp',
								success( result ) {
									const reader = new FileReader();
									reader.onload = ( e ) => {
										const placeholder = {};
										placeholder.data = e.target.result;
										placeholder.size = Math.min( maxWidth, image.naturalWidth );
										image.placeholder = placeholder;
										resolve();
									};
									reader.readAsDataURL( result );
								},
							} );
						} else {
							new Compressor( blob, {
								quality: 0.66,
								maxWidth: maxWidth,
								maxHeight: maxWidth * 3,
								mimeType: 'image/webp',
								success( result ) {
									const reader = new FileReader();
									reader.onload = ( e ) => {
										const optimized = {};
										optimized.data = e.target.result;
										optimized.size = Math.min( maxWidth, image.naturalWidth );
										image.optimized = optimized;
										resolve();
									};
									reader.readAsDataURL( result );
								},
							} );
						}
					} );
				} );
		} );

		return Promise.all( promises );
	}

	getElements( selector ) {
		return [ ...document.querySelectorAll( this.selectors[ selector ] ) ];
	}

	init() {
		this.selectors = {
			documents: '[data-elementor-id]',
			sections: '.elementor-section',
			elements: '.elementor-element',
			images: '.elementor-element img',
		};
		this.styleSheets = document.styleSheets;
		this.frontendStyleSheets = this.getAllStyleSheets();
		this.HTML = document.documentElement.outerHTML;
		this.CSS = this.getAllCSS();
	}

	run( task ) {
		this.fonts = { google: this.getGoogleFonts() };
		this.images = [ ...this.getImages() ];
		this.backgroundImages = [ ...this.getBackgroundImages() ];

		this.critical = new Critical( this );
		this.critical.init();

		this.critical.images.forEach( ( isCritical, i ) => this.images[ i ].critical = isCritical );
		this.critical.backgroundImages.forEach( ( isCritical, i ) => this.backgroundImages[ i ].critical = isCritical );

		this.optimizedImages = [];
		this.optimizedBackgroundImages = [];

		return Promise.all( [
			this.optimizeImages( this.images, true ),
			this.optimizeImages( this.backgroundImages, true ),
		] );
	}

	getData() {
		return {
			images: this.images,
			backgroundImages: this.backgroundImages,
			css: csso.minify( this.critical.getUsedCss( this.HTML ) ).css,
			fonts: this.fonts,
			criticalCSS: this.critical.css,
			criticalFonts: this.critical.fonts,
		};
	}
}

const optimizeImages = function( analyzer ) {
	const data = analyzer.getData();
	return Promise.all( [
		analyzer.optimizeImages( analyzer.images ),
		analyzer.optimizeImages( analyzer.backgroundImages ),
	] );
};

const sendData = function( data ) {
	jQuery.ajax( {
		type: 'post',
		dataType: 'json',
		url: analyzerAjax.ajaxurl,
		data: {
			action: 'save_analyzer_data_report',
			post_id: post_id,
			nonce: nonce,
			page_data: JSON.stringify( data ),
			analyzer: 1,
		},
		success: function( response ) {
			window.parent.postMessage( 'Analyzer data sent successfully.', '*' );
		},
		error: function( response ) {
			if ( 400 === response.status || response.error.length ) {
				window.parent.postMessage( 'Failed to send Analyzer data.', '*' );
			}
		},
	} );
};

window.addEventListener( 'DOMContentLoaded', () =>
	setTimeout( () => {
		window.parent.postMessage( 'Starting Page Analyzer.', '*' );

		const analyzer = new Analyzer();

		analyzer.init();

		window.parent.postMessage( 'Analyzing the page...', '*' );

		analyzer.run().then( () => {
			window.parent.postMessage( 'Page Analyzed.', '*' );
			window.parent.postMessage( 'Creating Report...', '*' );

			const data = analyzer.getData();

			window.parent.postMessage( data, '*' );
			window.parent.postMessage( 'Sending Report to the server...', '*' );

			sendData( data );
		} ).then( () => window.parent.postMessage( 'Report Sent.', '*' ) ).then( () => {
			window.parent.postMessage( 'Optimizing Images...', '*' );
			optimizeImages( analyzer ).then( () => {
				const optimizedImages = {};
				optimizedImages.images = analyzer.images.map( ( image ) => {
					delete image.placeholder.data;
					return image;
				} );
				optimizedImages.backgroundImages = analyzer.backgroundImages.map( ( image ) => {
					delete image.placeholder.data;
					return image;
				} );

				window.parent.postMessage( 'Images Optimized.', '*' );
				window.parent.postMessage( optimizedImages, '*' );

				window.parent.postMessage( 'Sending Optimized Images to the server...', '*' );
				sendData( {
					optimizedImages: optimizedImages,
				} );
			} );
		} );
	}, 800 )
);
