import AdminTopBar from './admin-top-bar';

const AppWrapper = elementorCommon.config.isDebug ? React.StrictMode : React.Fragment;
document.addEventListener( 'DOMContentLoaded', () => {
	const adminTopBarElement = document.getElementById( 'e-admin-top-bar-root' );
	const isTopBarOptionWidgetChecked = !! document.querySelector( '#e-dashboard-widget-admin-top-bar-hide' );
	const elementorMenuItemIds = [
		'toplevel_page_elementor',
		'menu-posts-elementor_library',
	];

	const menuItemSelector = elementorMenuItemIds
		.map( ( itemId ) => `#${ itemId } .wp-menu-open` )
		.join( ', ' );

	const isElementorPage = !! document.querySelector( menuItemSelector );

	if ( isElementorPage || isTopBarOptionWidgetChecked ) {
		ReactDOM.render(
			<AppWrapper>
				<AdminTopBar isDashboard={isTopBarOptionWidgetChecked} />
			</AppWrapper>,
			adminTopBarElement
		);
	} else {
		adminTopBarElement.classList.add( 'e-admin-top-bar--inactive' );
	}
} );
