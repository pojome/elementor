import config from '../mock/config/editor';
import settings from '../mock/config/settings';
import document from '../mock/documents/document-1';
import postCustomField from '../mock/dynamic-tags/post-custom-field';
import postDate from '../mock/dynamic-tags/post-date';
import button from '../mock/elments/button';
import column from '../mock/elments/column';
import container from '../mock/elments/container';
import form from '../mock/elments/form';
import heading from '../mock/elments/heading';
import section from '../mock/elments/section';
import tabs from '../mock/elments/tabs';
import SchemeItems from '../mock/schemes/items';

// Document.
config.initial_document = document;
config.document = document;

// Widgets.
config.document.widgets = {
	button,
	heading,
	tabs,
	form,
};

// Settings.
config.settings = settings;

// Elements.
config.elements = {
	section,
	container,
	column,
};

const controls = {
	font: {
		options: {},
	},
	repeater: {
		item_actions: {
			add: true,
			duplicate: true,
			remove: true,
			sort: true,
		},
	},
};

// Controls.
config.controls = new Proxy( {}, {
	get( target, name ) {
		return controls[ name ] || {
			features: [],
		};
	},
} );

// Schemes.
config.schemes = {
	items: SchemeItems,
	enabled_schemes: [ 'color', 'typography', 'color-picker' ],
};

// Dynamic Tags.
config.dynamicTags.tags = {
	'post-date': postDate,
	'post-custom-field': postCustomField,
};

// TinyMCE.
config.wp_editor = '<div id="wp-elementorwpeditor-wrap" class="wp-core-ui wp-editor-wrap tmce-active"><div id="wp-elementorwpeditor-editor-tools" class="wp-editor-tools hide-if-no-js"><div id="wp-elementorwpeditor-media-buttons" class="wp-media-buttons"><button type="button" id="insert-media-button" class="button insert-media add_media" data-editor="elementorwpeditor"><span class="wp-media-buttons-icon"></span> Add Media</button></div>\n' +
	'<div class="wp-editor-tabs"><button type="button" id="elementorwpeditor-tmce" class="wp-switch-editor switch-tmce" data-wp-editor-id="elementorwpeditor">Visual</button>\n' +
	'<button type="button" id="elementorwpeditor-html" class="wp-switch-editor switch-html" data-wp-editor-id="elementorwpeditor">Text</button>\n' +
	'</div>\n' +
	'</div>\n' +
	'<div id="wp-elementorwpeditor-editor-container" class="wp-editor-container"><div id="qt_elementorwpeditor_toolbar" class="quicktags-toolbar"></div><textarea class="elementor-wp-editor wp-editor-area" style="height: 250px" autocomplete="off" cols="40" name="elementorwpeditor" id="elementorwpeditor">%%EDITORCONTENT%%</textarea></div>\n' +
	'</div>\n';

// Reset URL
config.rest_url = 'http://test-rest/wp-json/';

export default config;
