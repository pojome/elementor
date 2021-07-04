import ElementEmpty from './element-empty';
import RootEmpty from './root-empty';
import DocumentHelper from 'elementor-document/helper';

/**
 * @memberOf e.elementor.navigator
 */
export default class Element extends Marionette.CompositeView {
	getTemplate() {
		return '#tmpl-elementor-navigator__elements';
	}

	ui() {
		return {
			item: '> .elementor-navigator__item',
			title: '> .elementor-navigator__item .elementor-navigator__element__title__text',
			toggle: '> .elementor-navigator__item > .elementor-navigator__element__toggle',
			toggleList: '> .elementor-navigator__item > .elementor-navigator__element__list-toggle',
			indicators: '> .elementor-navigator__item > .elementor-navigator__element__indicators',
			indicator: '> .elementor-navigator__item > .elementor-navigator__element__indicators > .elementor-navigator__element__indicator',
			elements: '> .elementor-navigator__elements',
		};
	}

	events() {
		return {
			contextmenu: 'onContextMenu',
			'click @ui.item': 'onItemClick',
			'click @ui.toggle': 'onToggleClick',
			'click @ui.toggleList': 'onToggleListClick',
			'click @ui.indicator': 'onIndicatorClick',
			'dblclick @ui.title': 'onTitleDoubleClick',
			'keydown @ui.title': 'onTitleKeyDown',
			'paste @ui.title': 'onTitlePaste',
			'sortstart @ui.elements': 'onSortStart',
			'sortover @ui.elements': 'onSortOver',
			'sortout @ui.elements': 'onSortOut',
			'sortstop @ui.elements': 'onSortStop',
			'sortupdate @ui.elements': 'onSortUpdate',
			'sortreceive @ui.elements': 'onSortReceive',
		};
	}

	getEmptyView() {
		if ( this.isRoot() ) {
			return RootEmpty;
		}

		if ( this.hasChildren() ) {
			return ElementEmpty;
		}

		return null;
	}

	childViewOptions() {
		return {
			indent: this.getIndent() + 10,
		};
	}

	className() {
		const elType = this.model.get( 'elType' );

		let classes = 'elementor-navigator__element';

		if ( elType ) {
			classes += ' elementor-navigator__element-' + elType;
		}

		if ( this.hasChildren() ) {
			classes += ' elementor-navigator__element--has-children';
		}

		return classes;
	}

	attributes() {
		return {
			'data-id': this.model.id,
			'data-model-cid': this.model.cid,
		};
	}

	templateHelpers() {
		const helpers = {};

		if ( ! this.isRoot() ) {
			helpers.title = this.model.getTitle();

			helpers.icon = 'section' === this.model.get( 'elType' ) ? '' : this.model.getIcon();
		}

		return helpers;
	}

	initialize() {
		this.collection = this.model.get( 'elements' );

		this.childViewContainer = '.elementor-navigator__elements';

		/**
		 * Since the way elements are rendered.
		 * If you create element on exists column, everything is fine because JS engine callstack is empty
		 * and it will create the widget container, but if u drag element it will be 2 ticks.
		 * 1. Create section & column.
		 * 2. Create the widget in the column.
		 * For this situation 'linkContainer' without `setTimeout` will fail because the widget container does not exist yet.
		 */
		setTimeout( this.linkContainer.bind( this ) );
	}

	linkContainer() {
		if ( ! this.isRoot() ) {
			this.container = elementor.getContainer( this.model.id );
			this.container.navigator.view = this;
			this.container.navigator.model = this.model;
		}
	}

	getIndent() {
		return this.getOption( 'indent' ) || 0;
	}

	isRoot() {
		return ! this.model.get( 'elType' );
	}

	hasChildren() {
		return 'widget' !== this.model.get( 'elType' );
	}

	toggleList( state, callback ) {
		if ( this.container ) {
			const args = {
				container: this.container,
			};

			args.state = state;

			if ( callback ) {
				args.callback = callback;
			}

			$e.run( 'navigator/elements/toggle-folding', args );
		}
	}

	toggleHiddenClass() {
		this.$el.toggleClass( 'elementor-navigator__element--hidden', !! this.model.get( 'hidden' ) );
	}

	recursiveChildInvoke( method, ...restArgs ) {
		this[ method ].apply( this, restArgs );

		this.children.each( ( child ) => {
			if ( ! ( child instanceof this.constructor ) ) {
				return;
			}

			child.recursiveChildInvoke.apply( child, arguments );
		} );
	}

	recursiveParentInvoke( method, ...restArgs ) {
		if ( ! ( this._parent instanceof this.constructor ) ) {
			return;
		}

		this._parent[ method ].apply( this._parent, restArgs );

		this._parent.recursiveParentInvoke.apply( this._parent, arguments );
	}

	recursiveChildAgreement( method, ...restArgs ) {
		if ( ! this[ method ].apply( this, restArgs ) ) {
			return false;
		}

		let hasAgreement = true;

		for ( const child of Object.values( this.children._views ) ) {
			if ( ! ( child instanceof this.constructor ) ) {
				continue;
			}

			if ( ! child.recursiveChildAgreement.apply( child, arguments ) ) {
				hasAgreement = false;

				break;
			}
		}

		return hasAgreement;
	}

	activateMouseInteraction() {
		this.$el.on( {
			mouseenter: this.onMouseEnter.bind( this ),
			mouseleave: this.onMouseLeave.bind( this ),
		} );
	}

	deactivateMouseInteraction() {
		this.$el.off( 'mouseenter mouseleave' );
	}

	dragShouldBeIgnored( draggedModel ) {
		return ! DocumentHelper.isValidChild( draggedModel, this.model );
	}

	addEditingClass() {
		this.ui.item.addClass( 'elementor-editing' );
	}

	removeEditingClass() {
		this.ui.item.removeClass( 'elementor-editing' );
	}

	enterTitleEditing() {
		this.ui.title.attr( 'contenteditable', true ).focus();

		document.execCommand( 'selectAll' );

		elementor.addBackgroundClickListener( 'navigator', {
			ignore: this.ui.title,
			callback: this.exitTitleEditing.bind( this ),
		} );
	}

	exitTitleEditing() {
		this.ui.title.attr( 'contenteditable', false );

		$e.run( 'document/elements/settings', {
			container: this.container,
			settings: {
				_title: this.ui.title.text().trim(),
			},
		} );

		elementor.removeBackgroundClickListener( 'navigator' );
	}

	activateSortable() {
		if ( ! elementor.userCan( 'design' ) ) {
			return;
		}

		this.ui.elements.sortable( {
			items: '> .elementor-navigator__element',
			placeholder: 'ui-sortable-placeholder',
			axis: 'y',
			forcePlaceholderSize: true,
			connectWith: '.elementor-navigator__element-' + this.model.get( 'elType' ) + ' > .elementor-navigator__elements',
			cancel: '[contenteditable="true"]',
		} );
	}

	renderIndicators() {
		const settings = this.model.get( 'settings' ).attributes;

		this.ui.indicators.empty();

		jQuery.each( elementor.navigator.indicators, ( indicatorName, indicatorSettings ) => {
			const isShouldBeIndicated = indicatorSettings.settingKeys.some( ( key ) => settings[ key ] );

			if ( ! isShouldBeIndicated ) {
				return;
			}

			const $indicator = jQuery( '<div>', { class: 'elementor-navigator__element__indicator', title: indicatorSettings.title } )
				.attr( 'data-section', indicatorSettings.section )
				.html( `<i class="eicon-${ indicatorSettings.icon }"></i>` );

			this.ui.indicators.append( $indicator );

			// Added delay of 500ms because the indicators bar has a CSS transition attribute of .5s
			$indicator.tipsy( { delayIn: 300, gravity: 's' } );
		} );
	}

	onRender() {
		this.activateSortable();

		if ( this.isRoot() ) {
			return;
		}

		this.ui.item.css( 'padding-' + ( elementorCommon.config.isRTL ? 'right' : 'left' ), this.getIndent() );

		this.toggleHiddenClass();

		this.renderIndicators();
	}

	onItemClick() {
		const container = this.container;

		$e.run( 'panel/editor/open', {
			model: container.model,
			view: container.view,
		} );

		// Will not effect recording.
		elementor.helpers.scrollToView( container.view.$el );
	}

	onToggleClick( event ) {
		event.stopPropagation();

		const container = this.container;

		$e.run( 'navigator/elements/toggle-visibility', { container } );
	}

	onTitleDoubleClick() {
		this.enterTitleEditing();
	}

	onTitleKeyDown( event ) {
		const ENTER_KEY = 13;

		if ( ENTER_KEY === event.which ) {
			event.preventDefault();

			this.exitTitleEditing();
		}
	}

	onTitlePaste( event ) {
		event.preventDefault();

		document.execCommand( 'insertHTML', false, event.originalEvent.clipboardData.getData( 'text/plain' ) );
	}

	onToggleListClick( event ) {
		event.stopPropagation();

		this.toggleList();
	}

	onSortStart( event, ui ) {
		this.model.trigger( 'request:sort:start', event, ui );

		jQuery( ui.item ).children( '.elementor-navigator__item' ).trigger( 'click' );

		elementor.navigator.getLayout().activateElementsMouseInteraction();
	}

	onSortStop() {
		elementor.navigator.getLayout().deactivateElementsMouseInteraction();
	}

	onSortOver( event ) {
		event.stopPropagation();

		this.$el.addClass( 'elementor-dragging-on-child' );
	}

	onSortOut( event ) {
		event.stopPropagation();

		this.$el.removeClass( 'elementor-dragging-on-child' );
	}

	onSortUpdate( event, ui ) {
		event.stopPropagation();

		if ( ! this.ui.elements.is( ui.item.parent() ) ) {
			return;
		}

		this.model.trigger( 'request:sort:update', ui );
	}

	onSortReceive( event, ui ) {
		this.model.trigger( 'request:sort:receive', event, ui );
	}

	onMouseEnter( event ) {
		event.stopPropagation();

		const dragShouldBeIgnored = this.recursiveChildAgreement( 'dragShouldBeIgnored', elementor.channels.data.request( 'dragging:model' ) );

		if ( dragShouldBeIgnored ) {
			return;
		}

		this.autoExpandTimeout = setTimeout( () => {
			this.toggleList( true, () => {
				this.ui.elements.sortable( 'refreshPositions' );
			} );
		}, 500 );
	}

	onMouseLeave( event ) {
		event.stopPropagation();

		clearTimeout( this.autoExpandTimeout );
	}

	onContextMenu( event ) {
		this.model.trigger( 'request:contextmenu', event );
	}

	onIndicatorClick( event ) {
		const section = event.currentTarget.dataset.section;

		setTimeout( () => {
			const editor = elementor.getPanelView().currentPageView,
				tab = editor.getControlModel( section ).get( 'tab' );

			editor.activateSection( section );

			editor.activateTab( tab );

			editor.render();
		} );
	}
}
