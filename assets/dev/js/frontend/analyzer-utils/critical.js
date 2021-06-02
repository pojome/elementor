import dropCSS from 'dropcss';
import csso from 'csso';

export default class Critical {
	constructor( analyzer ) {
		this.analyzer = analyzer;
		this.selectors = analyzer.selectors;
		this.elements = {
			sections: () => ( analyzer.getElements( 'sections' ) ),
		};
	}

	getCriticalSections() {
		return this.elements.sections().filter( ( e ) => {
			return 1350 > e.getBoundingClientRect().top ? e : false;
		} );
	}

	getCriticalElements() {
		let criticalElements = [];

		this.getCriticalSections().forEach( ( section ) => {
			criticalElements = criticalElements.concat( [ ...section.querySelectorAll( this.selectors.elements ) ].filter( ( e ) => {
				return 1350 > e.getBoundingClientRect().top;
			} ) );
		} );

		return criticalElements;
	}

	removeNonCriticalElements() {
		[ ...document.querySelectorAll( this.selectors.sections + ':not(.critical)' ) ]
			.forEach( ( section ) => section.remove() );

		this.getCriticalSections().forEach( ( section ) => {
			[ ...section.querySelectorAll( this.selectors.elements + ':not(.critical)' ) ]
				.forEach( ( element ) => element.remove() );
		} );
	}

	onCriticalDOM() {
		this.css = this.getCriticalCSS();
		this.fonts = this.getCriticalFonts();
		this.images = this.getCriticalImages();
		this.backgroundImages = this.getCriticalBackgroundImages();
	}

	getUsedCss( html ) {
		return dropCSS( {
			html: html,
			css: this.analyzer.CSS,
		} ).css;
	}

	getCriticalCSS() {
		return csso.minify( this.getUsedCss( this.criticalThings.html ) ).css;
	}

	getCriticalFonts() {
		const fontFamilies = this.analyzer.fonts.google;
		const criticalFonts = [];
		const possibleTextNodesSelector = '.elementor-element *:not(:is(script, img, figure, style))';

		[ ...document.querySelectorAll( possibleTextNodesSelector ) ]
			.forEach( ( element ) => {
				if ( '' >= element.textContent ) {
					return;
				}

				const elementFontFamily = window.getComputedStyle( element ).getPropertyValue( 'font-family' );
				const foundFonts = fontFamilies.filter( ( font ) => elementFontFamily.includes( font ) );

				foundFonts.map( ( font ) => {
					if ( -1 === criticalFonts.indexOf( font ) ) {
						criticalFonts.push( font );
					}
				} );
			} );

		return criticalFonts;
	}

	getCriticalImages() {
		return this.analyzer.images.map( ( image ) => {
			return 0 < [ ...document.querySelectorAll( this.selectors.elements + ' img[src="' + image.src + '"]' ) ].length;
		} );
	}

	getCriticalBackgroundImages() {
		return this.analyzer.backgroundImages.map( ( image ) => {
			return ! ! [ ...document.querySelectorAll( image.cssSelector ) ].length;
		} );
	}

	processCriticalDOM() {
		this.getCriticalElements().forEach( ( e ) => {
			e.classList.add( 'critical' );
		} );

		this.getCriticalSections().forEach( ( e ) => {
			e.classList.add( 'critical' );
		} );

		const criticalMarkedHTML = document.documentElement.outerHTML;
		this.removeNonCriticalElements();
		this.criticalThings.html = document.documentElement.outerHTML;

		this.onCriticalDOM();

		document.documentElement.innerHTML = criticalMarkedHTML;
	}

	init() {
		this.criticalThings = {
			sections: [ ...this.getCriticalSections() ],
			elements: [ ...this.getCriticalElements() ],
		};

		this.processCriticalDOM();
	}
}
