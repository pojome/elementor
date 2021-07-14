import DocumentHelper from 'elementor-document/helper';

class AddSectionBase extends Marionette.ItemView {
	template() {
		return Marionette.TemplateCache.get( '#tmpl-elementor-add-section' );
	}

	attributes() {
		return {
			'data-view': 'choose-action',
		};
	}

	ui() {
		return {
			addNewSection: '.elementor-add-new-section',
			closeButton: '.elementor-add-section-close',
			addContainerButton: '.elementor-add-container-button',
			addSectionButton: '.elementor-add-section-button',
			addTemplateButton: '.elementor-add-template-button',
			selectPreset: '.elementor-select-preset',
			presets: '.elementor-preset',
		};
	}

	events() {
		return {
			'click @ui.addSectionButton': 'onAddSectionButtonClick',
			'click @ui.addTemplateButton': 'onAddTemplateButtonClick',
			'click @ui.closeButton': 'onCloseButtonClick',
			'click @ui.presets': 'onPresetSelected',
		};
	}

	behaviors() {
		return {
			contextMenu: {
				behaviorClass: require( 'elementor-behaviors/context-menu' ),
				groups: this.getContextMenuGroups(),
			},
		};
	}

	className() {
		return 'elementor-add-section elementor-visible-desktop';
	}

	setView( view ) {
		this.$el.attr( 'data-view', view );
	}

	showSelectPresets() {
		this.setView( 'select-preset' );
	}

	closeSelectPresets() {
		this.setView( 'choose-action' );
	}

	getTemplatesModalOptions() {
		return {
			importOptions: {
				at: this.getOption( 'at' ),
			},
		};
	}

	getContextMenuGroups() {
		var hasContent = function() {
			return elementor.elements.length > 0;
		};

		return [
			{
				name: 'paste',
				actions: [
					{
						name: 'paste',
						title: __( 'Paste', 'elementor' ),
						isEnabled: () => DocumentHelper.isPasteEnabled( elementor.getPreviewContainer() ),
						callback: () => $e.run( 'document/ui/paste', {
							container: elementor.getPreviewContainer(),
							options: {
								at: this.getOption( 'at' ),
								rebuild: true,
							},
							onAfter: () => this.onAfterPaste(),
						} ),
					},
				],
			}, {
				name: 'content',
				actions: [
					{
						name: 'copy_all_content',
						title: __( 'Copy All Content', 'elementor' ),
						isEnabled: hasContent,
						callback: () => $e.run( 'document/elements/copy-all' ),
					}, {
						name: 'delete_all_content',
						title: __( 'Delete All Content', 'elementor' ),
						isEnabled: hasContent,
						callback: () => $e.run( 'document/elements/empty' ),
					},
				],
			},
		];
	}

	createContainer( options = {} ) {
		$e.run( 'document/elements/create', {
			model: {
				elType: 'container',
			},
			container: elementor.getPreviewContainer(),
			options,
		} );
	}

	onAddSectionButtonClick() {
		this.showSelectPresets();
	}

	onAddTemplateButtonClick() {
		$e.run( 'library/open', this.getTemplatesModalOptions() );
	}

	onRender() {
		this.$el.html5Droppable( {
			axis: [ 'vertical' ],
			groups: [ 'elementor-element' ],
			placeholder: false,
			currentElementClass: 'elementor-html5dnd-current-element',
			hasDraggingOnChildClass: 'elementor-dragging-on-child',
			onDropping: this.onDropping.bind( this ),
		} );
	}

	onPresetSelected( event ) {
		this.closeSelectPresets();

		const selectedStructure = event.currentTarget.dataset.structure;

		// If the selected preset is a Flex Container, create one.
		if ( 'flex-container' === selectedStructure ) {
			this.createContainer( this.options );
			return;
		}

		const parsedStructure = elementor.presetsFactory.getParsedStructure( selectedStructure );

		$e.run( 'document/elements/create', {
			model: {
				elType: 'section',
			},
			container: elementor.getPreviewContainer(),
			columns: parsedStructure.columnsCount,
			structure: selectedStructure,
			options: Object.assign( {}, this.options ),
		} );
	}

	onDropping() {
		if ( elementor.helpers.maybeDisableWidget() ) {
			return;
		}

		const selectedElement = elementor.channels.panelElements.request( 'element:selected' ),
			historyId = $e.internal( 'document/history/start-log', {
				type: 'add',
				title: elementor.helpers.getModelLabel( selectedElement.model ),
			} ),
			isContainer = ( 'container' === selectedElement.model.get( 'elType' ) ),
			containingElement = $e.run( 'document/elements/create', {
				model: {
					elType: isContainer ? 'container' : 'section',
				},
				container: elementor.getPreviewContainer(),
				columns: 1,
				options: {
					at: this.getOption( 'at' ),
					// BC: Deprecated since 2.8.0 - use `$e.hooks`.
					trigger: {
						beforeAdd: 'section:before:drop',
						afterAdd: 'section:after:drop',
					},
				},
			} );

		if ( ! isContainer ) {
			// Create the element in column.
			containingElement.view.children.findByIndex( 0 ).addElementFromPanel();
		}

		$e.internal( 'document/history/end-log', { id: historyId } );
	}

	onAfterPaste() {}
}

export default AddSectionBase;
