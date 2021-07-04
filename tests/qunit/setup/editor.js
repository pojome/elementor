// eslint-disable-next-line no-unused-vars
/* global jQuery, wp */

wp.heartbeat = {
	connectNow: () => {},
};

const originalGet = Marionette.TemplateCache.get;

Marionette.TemplateCache.get = function( template ) {
	if ( jQuery( template ).length ) {
		return originalGet.apply( Marionette.TemplateCache, [ template ] );
	}

	return () => `<div class="${ template.replace( '#tmpl-', '' ) }"><code>${ template }</code></div>`;
};

Marionette.Region.prototype._ensureElement = () => {
	return true;
};

Marionette.Region.prototype.attachHtml = () => {
};

Marionette.CompositeView.prototype.getChildViewContainer = ( containerView ) => {
	containerView.$childViewContainer = jQuery( containerView.el );
	containerView.$childViewContainer.appendTo(
		jQuery( '#elementor-preview-iframe' ).contents().find( '.elementor.elementor-1' )
	);
	return containerView.$childViewContainer;
};
