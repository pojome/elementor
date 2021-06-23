import { useState, useEffect } from 'react';

export const usePageTitle = ( isDashboard ) => {
	const [ pageTitle, setPageTitle ] = useState( 'Elementor' );

	useEffect( () => {
		const pageTitleElement = document.querySelector( '.wp-heading-inline' );
		if ( ! pageTitleElement && ! isDashboard ) {
			return;
		}

		if ( isDashboard ) {
			setPageTitle( __( 'Dashboard', 'elementor' ) );
		} else {
			setPageTitle( pageTitleElement.innerText );
		}
	}, [ ] );

	return pageTitle;
};
