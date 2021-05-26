module.exports = Marionette.ItemView.extend( {
	template: '#tmpl-elementor-element-library-element',

	className: function() {
		let className = 'elementor-element-wrapper';

		if ( ! this.isEditable() ) {
			className += ' elementor-element--promotion';
		}

		return className;
	},

	events: function() {
		const events = {
			'click @ui.favorite': 'onFavoriteClick',
		};

		if ( ! this.isEditable() ) {
			events.mousedown = 'onMouseDown';
		}

		return events;
	},

	ui: {
		element: '.elementor-element',
		favorite: '.e-element-favorite',
	},

	isEditable: function() {
		return false !== this.model.get( 'editable' );
	},

	onRender: function() {
		if ( ! elementor.userCan( 'design' ) || ! this.isEditable() ) {
			return;
		}

		this.ui.element.html5Draggable( {
			onDragStart: () => {
				elementor.channels.panelElements
						.reply( 'element:selected', this )
						.trigger( 'element:drag:start' );
			},

			onDragEnd: () => {
				elementor.channels.panelElements.trigger( 'element:drag:end' );
			},

			groups: [ 'elementor-element' ],
		} );
	},

	onMouseDown: function() {
		const title = this.model.get( 'title' );

		elementor.promotion.showDialog( {
			/* translators: %s: Widget Title. */
			headerMessage: sprintf( __( '%s Widget', 'elementor' ), title ),
			/* translators: %s: Widget Title. */
			message: sprintf( __( 'Use %s widget and dozens more pro features to extend your toolbox and build sites faster and better.', 'elementor' ), title ),
			top: '-7',
			element: this.el,
			actionURL: elementor.config.elementPromotionURL.replace( '%s', this.model.get( 'name' ) ),
		} );
	},

	onFavoriteClick: function() {
		$e.run( 'favorites/toggle', {
			widgets: [ this.model.get( 'widgetType' ) ],
		} );
	},
} );
