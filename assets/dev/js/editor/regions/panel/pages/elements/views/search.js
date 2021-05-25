var PanelElementsSearchView;

PanelElementsSearchView = Marionette.ItemView.extend( {
	template: '#tmpl-elementor-panel-element-search',

	localizedValue: '',

	id: 'elementor-panel-elements-search-wrapper',

	ui: {
		input: 'input',
	},

	events: {
		'keyup @ui.input': 'onInputChanged',
	},

	clearInput: function() {
		this.ui.input.val( '' );
	},

	onInputChanged: function( event ) {
		var ESC_KEY = 27;

		if ( ESC_KEY === event.keyCode ) {
			this.clearInput();
		}

		// Don't catch keyboard shortcut.
		if ( event.shiftKey || event.ctrlKey || event.altKey ) {
			return;
		}

		// Reset localized value if the input is empty or some chars were deleted.
		if ( ! event.target.value || event.target.value.length < this.localizedValue.length ) {
			this.localizedValue = '';
		}

		const isLetter = ( event.keyCode >= 65 && event.keyCode <= 90 ),
			isSpace = ( 32 === event.keyCode );

		if ( isLetter || isSpace ) {
			this.localizedValue += String.fromCharCode( event.keyCode );
		}

		// Broadcast the localized value.
		elementor.channels.panelElements.reply( 'filter:localized', this.localizedValue );
		this.triggerMethod( 'search:change:input' );
	},
} );

module.exports = PanelElementsSearchView;
