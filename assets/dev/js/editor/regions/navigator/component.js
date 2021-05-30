import ComponentBase from 'elementor-api/modules/component-base';
import ElementCollection from 'elementor-elements/collections/elements';
import DocumentElementModel from './models/document-element';

import * as commands from './commands/';
import * as hooks from './hooks/index';
import * as docTypes from './document-types';

export default class Component extends ComponentBase {
	__construct( args = {} ) {
		super.__construct( args );

		if ( elementor.config.initial_document.properties.supports_navigator_multi_documents ) {
			this.docTypes = Object.values( docTypes ).map( ( docType ) => new docType() );
		}
	}

	getNamespace() {
		return 'navigator';
	}

	defaultRoutes() {
		return {
			'': () => {},
		};
	}

	defaultCommands() {
		return this.importCommands( commands );
	}

	defaultShortcuts() {
		return {
			toggle: {
				keys: 'ctrl+i',
				dependency: () => elementor.getPreviewContainer().isEditable(),
			},
		};
	}

	defaultHooks() {
		return this.importHooks( hooks );
	}

	open( args ) {
		const { model = false } = args;

		this.manager.open( model );

		return true;
	}

	close( silent ) {
		if ( ! super.close() ) {
			return false;
		}

		this.manager.close( silent );

		return true;
	}

	getDocumentsFromFrontend() {
		const documents = [];

		// eslint-disable-next-line no-unused-vars
		Object.entries( elementorFrontend.documentsManager.documents ).forEach( ( [ documentId, document ] ) => {
			const docType = this.docTypes.find( ( doc ) => doc.getConditions( document ) );

			if ( docType ) {
				const extraSettings = {
					icon: docType.getIcon(),
					type: docType.getType(),
					title: docType.getTitle(),
				};

				document.setSettings( extraSettings );

				documents.splice( docType.getIndex(), 0, document );
			}
		} );

		// Remove empty arrays.
		return documents.filter( Boolean );
	}

	initLayout() {
		if ( ! elementor.config.initial_document.properties.supports_navigator_multi_documents ) {
			return elementor.navigator.initLayout( elementor.elementsModel );
		}

		const documentsModel = new Backbone.Model( { elements: new ElementCollection() } ),
			documentsModelElements = documentsModel.get( 'elements' );

		this.getDocumentsFromFrontend().forEach( ( document ) => {
			const settings = document.getSettings(),
				model = new DocumentElementModel( {
					id: settings.id,
					icon: settings.icon,
					title: settings.title,
					elType: 'document',
				} );

			// If it is current edited document, set the elements.
			if ( settings.id === elementor.documents.getCurrentId() ) {
				model.set( 'elements', elementor.elementsModel.get( 'elements' ) );
			}

			documentsModelElements.add( model, { silent: true } );
		} );

		elementor.navigator.initLayout( documentsModel );
	}
}
