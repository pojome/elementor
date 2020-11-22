( function( $ ) {
	var ElementorAdmin = elementorModules.ViewModule.extend( {

		maintenanceMode: null,

		config: elementorAdminConfig,

		getDefaultElements: function() {
			var elements = {
				$switchMode: $( '#elementor-switch-mode' ),
				$goToEditLink: $( '#elementor-go-to-edit-page-link' ),
				$switchModeInput: $( '#elementor-switch-mode-input' ),
				$switchModeButton: $( '#elementor-switch-mode-button' ),
				$elementorLoader: $( '.elementor-loader' ),
				$builderEditor: $( '#elementor-editor' ),
				$importButton: $( '#elementor-import-template-trigger' ),
				$importArea: $( '#elementor-import-template-area' ),
				$settingsForm: $( '#elementor-settings-form' ),
				$settingsTabsWrapper: $( '#elementor-settings-tabs-wrapper' ),
				$menuGetHelpLink: $( 'a[href="admin.php?page=go_knowledge_base_site"]' ),
				$reMigrateGlobalsButton: $( '.elementor-re-migrate-globals-button' ),
			};

			elements.$settingsFormPages = elements.$settingsForm.find( '.elementor-settings-form-page' );

			elements.$activeSettingsPage = elements.$settingsFormPages.filter( '.elementor-active' );

			elements.$settingsTabs = elements.$settingsTabsWrapper.children();

			elements.$activeSettingsTab = elements.$settingsTabs.filter( '.nav-tab-active' );

			return elements;
		},

		toggleStatus: function() {
			var isElementorMode = this.isElementorMode();

			elementorCommon.elements.$body
				.toggleClass( 'elementor-editor-active', isElementorMode )
				.toggleClass( 'elementor-editor-inactive', ! isElementorMode );
		},

		bindEvents: function() {
			var self = this;

			self.elements.$switchModeButton.on( 'click', function( event ) {
				event.preventDefault();

				if ( self.isElementorMode() ) {
					elementorCommon.dialogsManager.createWidget( 'confirm', {
						message: self.translate( 'back_to_wordpress_editor_message' ),
						headerMessage: self.translate( 'back_to_wordpress_editor_header' ),
						strings: {
							confirm: self.translate( 'yes' ),
							cancel: self.translate( 'cancel' ),
						},
						defaultOption: 'confirm',
						onConfirm: function() {
							self.elements.$switchModeInput.val( '' );
							self.toggleStatus();
						},
					} ).show();
				} else {
					self.elements.$switchModeInput.val( true );

					var $wpTitle = $( '#title' );

					if ( ! $wpTitle.val() ) {
						$wpTitle.val( 'Elementor #' + $( '#post_ID' ).val() );
					}

					if ( wp.autosave ) {
						wp.autosave.server.triggerSave();
					}

					self.animateLoader();

					$( document ).on( 'heartbeat-tick.autosave', function() {
						elementorCommon.elements.$window.off( 'beforeunload.edit-post' );

						location.href = self.elements.$goToEditLink.attr( 'href' );
					} );
					self.toggleStatus();
				}
			} );

			self.elements.$goToEditLink.on( 'click', function() {
				self.animateLoader();
			} );

			$( 'div.notice.elementor-message-dismissed' ).on( 'click', 'button.notice-dismiss, .elementor-button-notice-dismiss', function( event ) {
				event.preventDefault();

				$.post( ajaxurl, {
					action: 'elementor_set_admin_notice_viewed',
					notice_id: $( this ).closest( '.elementor-message-dismissed' ).data( 'notice_id' ),
				} );

				var $wrapperElm = $( this ).closest( '.elementor-message-dismissed' );
				$wrapperElm.fadeTo( 100, 0, function() {
					$wrapperElm.slideUp( 100, function() {
						$wrapperElm.remove();
					} );
				} );
			} );

			$( '#elementor-clear-cache-button' ).on( 'click', function( event ) {
				event.preventDefault();
				var $thisButton = $( this );

				$thisButton.removeClass( 'success' ).addClass( 'loading' );

				$.post( ajaxurl, {
					action: 'elementor_clear_cache',
					_nonce: $thisButton.data( 'nonce' ),
				} )
					.done( function() {
						$thisButton.removeClass( 'loading' ).addClass( 'success' );
					} );
			} );

			$( '#elementor-library-sync-button' ).on( 'click', function( event ) {
				event.preventDefault();
				var $thisButton = $( this );

				$thisButton.removeClass( 'success' ).addClass( 'loading' );

				$.post( ajaxurl, {
					action: 'elementor_reset_library',
					_nonce: $thisButton.data( 'nonce' ),
				} )
					.done( function() {
						$thisButton.removeClass( 'loading' ).addClass( 'success' );
					} );
			} );

			$( '#elementor-replace-url-button' ).on( 'click', function( event ) {
				event.preventDefault();
				var $this = $( this ),
					$tr = $this.parents( 'tr' ),
					$from = $tr.find( '[name="from"]' ),
					$to = $tr.find( '[name="to"]' );

				$this.removeClass( 'success' ).addClass( 'loading' );

				$.post( ajaxurl, {
					action: 'elementor_replace_url',
					from: $from.val(),
					to: $to.val(),
					_nonce: $this.data( 'nonce' ),
				} )
					.done( function( response ) {
						$this.removeClass( 'loading' );

						if ( response.success ) {
							$this.addClass( 'success' );
						}

						elementorCommon.dialogsManager.createWidget( 'alert', {
							message: response.data,
						} ).show();
					} );
			} );

			$( '#elementor_upgrade_fa_button' ).on( 'click', function( event ) {
				event.preventDefault();
				const $updateButton = $( this );
				$updateButton.addClass( 'loading' );
				elementorCommon.dialogsManager.createWidget( 'confirm', {
					id: 'confirm_fa_migration_admin_modal',
					message: self.translate( 'confirm_fa_migration_admin_modal_body' ),
					headerMessage: self.translate( 'confirm_fa_migration_admin_modal_head' ),
					strings: {
						confirm: self.translate( 'yes' ),
						cancel: self.translate( 'cancel' ),
					},
					defaultOption: 'confirm',
					onConfirm: () => {
						$updateButton.removeClass( 'error' ).addClass( 'loading' );
						$.post( ajaxurl, $updateButton.data() )
							.done( function( response ) {
								$updateButton.removeClass( 'loading' ).addClass( 'success' );
								$( '#elementor_upgrade_fa_button' ).parent().append( response.data.message );
								const redirectTo = ( location.search.split( 'redirect_to=' )[ 1 ] || '' ).split( '&' )[ 0 ];
								if ( redirectTo ) {
									location.href = decodeURIComponent( redirectTo );
									return;
								}
								history.go( -1 );
							} )
							.fail( function() {
								$updateButton.removeClass( 'loading' ).addClass( 'error' );
							} );
					},
					onCancel: () => {
						$updateButton.removeClass( 'loading' ).addClass( 'error' );
					},
				} ).show();
			} );

			self.elements.$settingsTabs.on( {
				click: function( event ) {
					event.preventDefault();

					event.currentTarget.focus(); // Safari does not focus the tab automatically
				},
				focus: function() { // Using focus event to enable navigation by tab key
					var hrefWithoutHash = location.href.replace( /#.*/, '' );

					history.pushState( {}, '', hrefWithoutHash + this.hash );

					self.goToSettingsTabFromHash();
				},
			} );

			$( 'select.elementor-rollback-select' ).on( 'change', function() {
				var $this = $( this ),
					$rollbackButton = $this.next( '.elementor-rollback-button' ),
					placeholderText = $rollbackButton.data( 'placeholder-text' ),
					placeholderUrl = $rollbackButton.data( 'placeholder-url' );

				$rollbackButton.html( placeholderText.replace( '{VERSION}', $this.val() ) );
				$rollbackButton.attr( 'href', placeholderUrl.replace( 'VERSION', $this.val() ) );
			} ).trigger( 'change' );

			$( '.elementor-rollback-button' ).on( 'click', function( event ) {
				event.preventDefault();

				var $this = $( this );

				elementorCommon.dialogsManager.createWidget( 'confirm', {
					headerMessage: self.translate( 'rollback_to_previous_version' ),
					message: self.translate( 'rollback_confirm' ),
					strings: {
						confirm: self.translate( 'yes' ),
						cancel: self.translate( 'cancel' ),
					},
					onConfirm: function() {
						$this.addClass( 'loading' );

						location.href = $this.attr( 'href' );
					},
				} ).show();
			} );

			self.elements.$reMigrateGlobalsButton.on( 'click', ( event ) => {
				event.preventDefault();

				const $this = $( event.currentTarget );

				elementorCommon.dialogsManager.createWidget( 'confirm', {
					headerMessage: self.translate( 're_migrate_globals' ),
					message: self.translate( 're_migrate_globals_confirm' ),
					strings: {
						confirm: self.translate( 'yes' ),
						cancel: self.translate( 'cancel' ),
					},
					onConfirm: () => {
						$this.removeClass( 'success' ).addClass( 'loading' );

						elementorCommon.ajax.addRequest( 're_migrate_globals', {
							success: () => $this.removeClass( 'loading' ).addClass( 'success' ),
						} );
					},
				} ).show();
			} );

			$( '.elementor_css_print_method select' ).on( 'change', function() {
				var $descriptions = $( '.elementor-css-print-method-description' );

				$descriptions.hide();
				$descriptions.filter( '[data-value="' + $( this ).val() + '"]' ).show();
			} ).trigger( 'change' );
		},

		onInit: function() {
			elementorModules.ViewModule.prototype.onInit.apply( this, arguments );

			this.initTemplatesImport();

			this.initMaintenanceMode();

			this.goToSettingsTabFromHash();

			this.openGetHelpInNewTab();

			this.roleManager.init();
		},

		openGetHelpInNewTab: function() {
			this.elements.$menuGetHelpLink.attr( 'target', '_blank' );
		},

		initTemplatesImport: function() {
			if ( ! elementorCommon.elements.$body.hasClass( 'post-type-elementor_library' ) ) {
				return;
			}

			var self = this,
				$importButton = self.elements.$importButton,
				$importArea = self.elements.$importArea;

			self.elements.$formAnchor = $( 'h1' );

			$( '#wpbody-content' ).find( '.page-title-action:last' ).after( $importButton );

			self.elements.$formAnchor.after( $importArea );

			$importButton.on( 'click', function() {
				$( '#elementor-import-template-area' ).toggle();
			} );
		},

		initMaintenanceMode: function() {
			var MaintenanceMode = require( 'elementor-admin/maintenance-mode' );

			this.maintenanceMode = new MaintenanceMode();
		},

		isElementorMode: function() {
			return ! ! this.elements.$switchModeInput.val();
		},

		animateLoader: function() {
			this.elements.$goToEditLink.addClass( 'elementor-animate' );
		},

		goToSettingsTabFromHash: function() {
			var hash = location.hash.slice( 1 );

			if ( hash ) {
				this.goToSettingsTab( hash );
			}
		},

		goToSettingsTab: function( tabName ) {
			const $pages = this.elements.$settingsFormPages;

			if ( ! $pages.length ) {
				return;
			}

			const $activePage = $pages.filter( '#' + tabName );

			this.elements.$activeSettingsPage.removeClass( 'elementor-active' );

			this.elements.$activeSettingsTab.removeClass( 'nav-tab-active' );

			const $activeTab = this.elements.$settingsTabs.filter( '#elementor-settings-' + tabName );

			$activePage.addClass( 'elementor-active' );

			$activeTab.addClass( 'nav-tab-active' );

			this.elements.$settingsForm.attr( 'action', 'options.php#' + tabName );

			this.elements.$activeSettingsPage = $activePage;

			this.elements.$activeSettingsTab = $activeTab;
		},

		translate: function( stringKey, templateArgs ) {
			return elementorCommon.translate( stringKey, null, templateArgs, this.config.i18n );
		},

		roleManager: {
			selectors: {
				body: 'elementor-role-manager',
				row: '.elementor-role-row',
				label: '.elementor-role-label',
				excludedIndicator: '.elementor-role-excluded-indicator',
				excludedField: 'input[name="elementor_exclude_user_roles[]"]',
				controlsContainer: '.elementor-role-controls',
				toggleHandle: '.elementor-role-toggle',
				arrowUp: 'dashicons-arrow-up',
				arrowDown: 'dashicons-arrow-down',
			},
			toggle: function( $trigger ) {
				var self = this,
					$row = $trigger.closest( self.selectors.row ),
					$toggleHandleIcon = $row.find( self.selectors.toggleHandle ).find( '.dashicons' ),
					$controls = $row.find( self.selectors.controlsContainer );

				$controls.toggleClass( 'hidden' );
				if ( $controls.hasClass( 'hidden' ) ) {
					$toggleHandleIcon.removeClass( self.selectors.arrowUp ).addClass( self.selectors.arrowDown );
				} else {
					$toggleHandleIcon.removeClass( self.selectors.arrowDown ).addClass( self.selectors.arrowUp );
				}
				self.updateLabel( $row );
			},
			updateLabel: function( $row ) {
				var self = this,
					$indicator = $row.find( self.selectors.excludedIndicator ),
					excluded = $row.find( self.selectors.excludedField ).is( ':checked' );
				if ( excluded ) {
					$indicator.html( $indicator.data( 'excluded-label' ) );
				} else {
					$indicator.html( '' );
				}
				self.setAdvancedState( $row, excluded );
			},
			setAdvancedState: function( $row, state ) {
				var self = this,
					$controls = $row.find( 'input[type="checkbox"]' ).not( self.selectors.excludedField );

				$controls.each( function( index, input ) {
					$( input ).prop( 'disabled', state );
				} );
			},
			bind: function() {
				var self = this;
				$( document ).on( 'click', self.selectors.label + ',' + self.selectors.toggleHandle, function( event ) {
					event.stopPropagation();
					event.preventDefault();
					self.toggle( $( this ) );
				} ).on( 'change', self.selectors.excludedField, function() {
					self.updateLabel( $( this ).closest( self.selectors.row ) );
				} );
			},
			init: function() {
				var self = this;
				if ( ! $( 'body[class*="' + self.selectors.body + '"]' ).length ) {
					return;
				}
				self.bind();
				$( self.selectors.row ).each( function( index, row ) {
					self.updateLabel( $( row ) );
				} );
			},
		},
	} );

	$( function() {
		window.elementorAdmin = new ElementorAdmin();

		elementorCommon.elements.$window.trigger( 'elementor/admin/init' );

		$( '.elementor_finder' ).on( 'click', function(e) {
			e.preventDefault();

			$e.run( 'finder/open' );
		} );
	} );
}( jQuery ) );
