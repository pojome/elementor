// Tinymce
const quicktagsL10n = { closeAllOpenTags: 'Close all open tags', closeTags: 'close tags', enterURL: 'Enter the URL', enterImageURL: 'Enter the URL of the image', enterImageDescription: 'Enter a description of the image', textdirection: 'text direction', toggleTextdirection: 'Toggle Editor Text Direction', dfw: 'Distraction-free writing mode', strong: 'Bold', strongClose: 'Close bold tag', em: 'Italic', emClose: 'Close italic tag', link: 'Insert link', blockquote: 'Blockquote', blockquoteClose: 'Close blockquote tag', del: 'Deleted text (strikethrough)', delClose: 'Close deleted text tag', ins: 'Inserted text', insClose: 'Close inserted text tag', image: 'Insert image', ul: 'Bulleted list', ulClose: 'Close bulleted list tag', ol: 'Numbered list', olClose: 'Close numbered list tag', li: 'List item', liClose: 'Close list item tag', code: 'Code', codeClose: 'Close code tag', more: 'Insert Read More tag' };

const tinyMCEPreInit = {
	baseURL: './vendor/wp-includes/tinymce',
	suffix: '',
	dragDropUpload: true, mceInit: { elementorwpeditor: { theme: 'modern', skin: 'lightgray', language: 'en', formats: { alignleft: [ { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign: 'left' } }, { selector: 'img,table,dl.wp-caption', classes: 'alignleft' } ], aligncenter: [ { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign: 'center' } }, { selector: 'img,table,dl.wp-caption', classes: 'aligncenter' } ], alignright: [ { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign: 'right' } }, { selector: 'img,table,dl.wp-caption', classes: 'alignright' } ], strikethrough: { inline: 'del' } }, relative_urls: false, remove_script_host: false, convert_urls: false, browser_spellcheck: true, fix_list_elements: true, entities: '38,amp,60,lt,62,gt', entity_encoding: 'raw', keep_styles: false, cache_suffix: 'wp-mce-4940-20190515', resize: 'vertical', menubar: false, branding: false, preview_styles: 'font-family font-size font-weight font-style text-decoration text-transform', end_container_on_empty_block: true, wpeditimage_html5_captions: true, wp_lang_attr: 'en-US', wp_keep_scroll_position: false, wp_shortcut_labels: { 'Heading 1': 'access1', 'Heading 2': 'access2', 'Heading 3': 'access3', 'Heading 4': 'access4', 'Heading 5': 'access5', 'Heading 6': 'access6', Paragraph: 'access7', Blockquote: 'accessQ', Underline: 'metaU', Strikethrough: 'accessD', Bold: 'metaB', Italic: 'metaI', Code: 'accessX', 'Align center': 'accessC', 'Align right': 'accessR', 'Align left': 'accessL', Justify: 'accessJ', Cut: 'metaX', Copy: 'metaC', Paste: 'metaV', 'Select all': 'metaA', Undo: 'metaZ', Redo: 'metaY', 'Bullet list': 'accessU', 'Numbered list': 'accessO', 'Insert\/edit image': 'accessM', 'Insert\/edit link': 'metaK', 'Remove link': 'accessS', 'Toolbar Toggle': 'accessZ', 'Insert Read More tag': 'accessT', 'Insert Page Break tag': 'accessP', 'Distraction-free writing mode': 'accessW', 'Add Media': 'accessM', 'Keyboard Shortcuts': 'accessH' }, content_css: 'http://localhost/elementor/wp-includes/css/dashicons.css?ver=5.2.2,http://localhost/elementor/wp-includes/js/tinymce/skins/wordpress/wp-content.css?ver=5.2.2,http://localhost/elementor/wp-content/themes/hello-elementor/editor-style.css', plugins: 'charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview', selector: '#elementorwpeditor', wpautop: true, indent: false, toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,wp_more,spellchecker,fullscreen,wp_adv', toolbar2: 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help', toolbar3: '', toolbar4: '', tabfocus_elements: ':prev,:next', body_class: 'elementorwpeditor post-type-post post-status-publish post-format-standard page-template-elementor_header_footer locale-en-us' } },
	qtInit: { elementorwpeditor: { id: 'elementorwpeditor', buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close' } },
	ref: { plugins: 'charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview', theme: 'modern', language: 'en' },
	load_ext: function( url, lang ) {
var sl = tinymce.ScriptLoader; sl.markDone( url + '/langs/' + lang + '.js' ); sl.markDone( url + '/langs/' + lang + '_dlg.js' );
},
};

( function() {
	var init, id, $wrap;

	if ( typeof tinymce !== 'undefined' ) {
		if ( tinymce.Env.ie && tinymce.Env.ie < 11 ) {
			tinymce.$( '.wp-editor-wrap ' ).removeClass( 'tmce-active' ).addClass( 'html-active' );
			return;
		}

		for ( id in tinyMCEPreInit.mceInit ) {
			init = tinyMCEPreInit.mceInit[ id ];
			$wrap = tinymce.$( '#wp-' + id + '-wrap' );

			if ( ( $wrap.hasClass( 'tmce-active' ) || ! tinyMCEPreInit.qtInit.hasOwnProperty( id ) ) && ! init.wp_skip_init ) {
				tinymce.init( init );

				if ( ! window.wpActiveEditor ) {
					window.wpActiveEditor = id;
				}
			}
		}
	}

	if ( typeof quicktags !== 'undefined' ) {
		for ( id in tinyMCEPreInit.qtInit ) {
			quicktags( tinyMCEPreInit.qtInit[ id ] );

			if ( ! window.wpActiveEditor ) {
				window.wpActiveEditor = id;
			}
		}
	}
}() );
