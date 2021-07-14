import Component from './component';

	var TemplateLibraryCollection = require( 'elementor-templates/collections/templates' ),
	TemplateLibraryManager;

TemplateLibraryManager = function() {
	this.modalConfig = {};

	const self = this,
		templateTypes = {};

	let deleteDialog,
		errorDialog,
		templatesCollection,
		config = {},
		filterTerms = {};

	const registerDefaultTemplateTypes = function() {
		var data = {
			saveDialog: {
				description: __( 'Your designs will be available for export and reuse on any page or website', 'elementor' ),
			},
			ajaxParams: {
				success: function( successData ) {
					$e.route( 'library/templates/my-templates', {
						onBefore: () => {
							if ( templatesCollection ) {
								const itemExist = templatesCollection.findWhere( {
									template_id: successData.template_id,
								} );

								if ( ! itemExist ) {
									templatesCollection.add( successData );
								}
							}
						},
					} );
				},
				error: function( errorData ) {
					self.showErrorDialog( errorData );
				},
			},
		};

		const translationMap = {
			page: __( 'Page', 'elementor' ),
			section: __( 'Section', 'elementor' ),
			container: __( 'Container', 'elementor' ),
			[ elementor.config.document.type ]: elementor.config.document.panel.title,
		};

		jQuery.each( translationMap, function( type, title ) {
			var safeData = jQuery.extend( true, {}, data, {
				saveDialog: {
					/* translators: %s: Template type. */
					title: sprintf( __( 'Save Your %s to Library', 'elementor' ), title ),
				},
			} );

			self.registerTemplateType( type, safeData );
		} );
	};

	const registerDefaultFilterTerms = function() {
		filterTerms = {
			text: {
				callback: function( value ) {
					value = value.toLowerCase();

					if ( this.get( 'title' ).toLowerCase().indexOf( value ) >= 0 ) {
						return true;
					}

					return _.any( this.get( 'tags' ), function( tag ) {
						return tag.toLowerCase().indexOf( value ) >= 0;
					} );
				},
			},
			type: {},
			subtype: {},
			favorite: {},
		};
	};

	this.init = function() {
		registerDefaultTemplateTypes();

		registerDefaultFilterTerms();

		this.component = $e.components.register( new Component( { manager: this } ) );

		elementor.addBackgroundClickListener( 'libraryToggleMore', {
			element: '.elementor-template-library-template-more',
		} );
	};

	this.getTemplateTypes = function( type ) {
		if ( type ) {
			return templateTypes[ type ];
		}

		return templateTypes;
	};

	this.registerTemplateType = function( type, data ) {
		templateTypes[ type ] = data;
	};

	this.deleteTemplate = function( templateModel, options ) {
		var dialog = self.getDeleteDialog();

		dialog.onConfirm = function() {
			if ( options.onConfirm ) {
				options.onConfirm();
			}

			elementorCommon.ajax.addRequest( 'delete_template', {
				data: {
					source: templateModel.get( 'source' ),
					template_id: templateModel.get( 'template_id' ),
				},
				success: function( response ) {
					templatesCollection.remove( templateModel, { silent: true } );

					if ( options.onSuccess ) {
						options.onSuccess( response );
					}
				},
			} );
		};

		dialog.show();
	};

	this.importTemplate = function( model, args = {} ) {
		elementorCommon.helpers.softDeprecated( 'importTemplate', '2.8.0',
			"$e.run( 'library/insert-template' )" );

		args.model = model;

		$e.run( 'library/insert-template', args );
	};

	this.saveTemplate = function( type, data ) {
		var templateType = templateTypes[ type ];

		_.extend( data, {
			source: 'local',
			type: type,
		} );

		if ( templateType.prepareSavedData ) {
			data = templateType.prepareSavedData( data );
		}

		data.content = JSON.stringify( data.content );

		var ajaxParams = { data: data };

		if ( templateType.ajaxParams ) {
			_.extend( ajaxParams, templateType.ajaxParams );
		}

		elementorCommon.ajax.addRequest( 'save_template', ajaxParams );
	};

	this.requestTemplateContent = function( source, id, ajaxOptions ) {
		var options = {
			unique_id: id,
			data: {
				source: source,
				edit_mode: true,
				display: true,
				template_id: id,
			},
		};

		if ( ajaxOptions ) {
			jQuery.extend( true, options, ajaxOptions );
		}

		return elementorCommon.ajax.addRequest( 'get_template_data', options );
	};

	this.markAsFavorite = function( templateModel, favorite ) {
		var options = {
			data: {
				source: templateModel.get( 'source' ),
				template_id: templateModel.get( 'template_id' ),
				favorite: favorite,
			},
		};

		return elementorCommon.ajax.addRequest( 'mark_template_as_favorite', options );
	};

	this.getDeleteDialog = function() {
		if ( ! deleteDialog ) {
			deleteDialog = elementorCommon.dialogsManager.createWidget( 'confirm', {
				id: 'elementor-template-library-delete-dialog',
				headerMessage: __( 'Delete Template', 'elementor' ),
				message: __( 'Are you sure you want to delete this template?', 'elementor' ),
				strings: {
					confirm: __( 'Delete', 'elementor' ),
				},
			} );
		}

		return deleteDialog;
	};

	this.getErrorDialog = function() {
		if ( ! errorDialog ) {
			errorDialog = elementorCommon.dialogsManager.createWidget( 'alert', {
				id: 'elementor-template-library-error-dialog',
				headerMessage: __( 'An error occurred', 'elementor' ),
			} );
		}

		return errorDialog;
	};

	this.getTemplatesCollection = function() {
		return templatesCollection;
	};

	this.getConfig = function( item ) {
		if ( item ) {
			return config[ item ] ? config[ item ] : {};
		}

		return config;
	};

	this.requestLibraryData = function( options ) {
		if ( templatesCollection && ! options.forceUpdate ) {
			if ( options.onUpdate ) {
				options.onUpdate();
			}

			return;
		}

		if ( options.onBeforeUpdate ) {
			options.onBeforeUpdate();
		}

		var ajaxOptions = {
			data: {},
			success: function( data ) {
				templatesCollection = new TemplateLibraryCollection( data.templates );

				if ( data.config ) {
					config = data.config;
				}

				if ( options.onUpdate ) {
					options.onUpdate();
				}
			},
		};

		if ( options.forceSync ) {
			ajaxOptions.data.sync = true;
		}

		elementorCommon.ajax.addRequest( 'get_library_data', ajaxOptions );
	};

	this.getFilter = function( name ) {
		return elementor.channels.templates.request( 'filter:' + name );
	};

	this.setFilter = function( name, value, silent ) {
		elementor.channels.templates.reply( 'filter:' + name, value );

		if ( ! silent ) {
			elementor.channels.templates.trigger( 'filter:change' );
		}
	};

	this.getFilterTerms = function( termName ) {
		if ( termName ) {
			return filterTerms[ termName ];
		}

		return filterTerms;
	};

	this.setScreen = function( args ) {
		elementor.channels.templates.stopReplying();

		self.setFilter( 'source', args.source, true );
		self.setFilter( 'type', args.type, true );
		self.setFilter( 'subtype', args.subtype, true );

		self.showTemplates();
	};

	this.loadTemplates = function( onUpdate ) {
		self.requestLibraryData( {
			onBeforeUpdate: self.layout.showLoadingView.bind( self.layout ),
			onUpdate: function() {
				self.layout.hideLoadingView();

				if ( onUpdate ) {
					onUpdate();
				}
			},
		} );
	};

	this.showTemplates = function() {
		// The tabs should exist in DOM on loading.
		self.layout.setHeaderDefaultParts();

		self.loadTemplates( function() {
			var templatesToShow = self.filterTemplates();

			self.layout.showTemplatesView( new TemplateLibraryCollection( templatesToShow ) );
		} );
	};

	this.filterTemplates = function() {
		const activeSource = self.getFilter( 'source' );
		return templatesCollection.filter( function( model ) {
			if ( activeSource !== model.get( 'source' ) ) {
				return false;
			}

			var typeInfo = templateTypes[ model.get( 'type' ) ];

			return ! typeInfo || false !== typeInfo.showInLibrary;
		} );
	};

	this.showErrorDialog = function( errorMessage ) {
		if ( 'object' === typeof errorMessage ) {
			var message = '';

			_.each( errorMessage, function( error ) {
				if ( ! error?.message ) {
					return;
				}

				message += '<div>' + error.message + '.</div>';
			} );

			errorMessage = message;
		} else if ( errorMessage ) {
			errorMessage += '.';
		}

		if ( errorMessage ) {
			errorMessage = __( 'The following error(s) occurred while processing the request:', 'elementor' ) +
				'<div id="elementor-template-library-error-info">' + errorMessage + '</div>';
		} else {
			errorMessage = __( 'Please try again.', 'elementor' );
		}

		self.getErrorDialog()
			.setMessage( errorMessage )
			.show();
	};
};

module.exports = new TemplateLibraryManager();
