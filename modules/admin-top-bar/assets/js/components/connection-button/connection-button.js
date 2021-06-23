import BarButton from '../bar-button/bar-button';
import { useRef, useEffect } from 'react';

export default function ConnectionButton( props ) {
	const buttonRef = useRef();
	const isUserConnected = elementorCommonConfig.connect.is_user_connected;

	useEffect( () => {
		if ( ! buttonRef.current || isUserConnected ) {
			return;
		}

		jQuery( buttonRef.current ).elementorConnect( {
			UTM: () => '&utm_source=admin-top-bar&utm_medium=wp-dash&utm_campaign=admin-top-bar',
		} );
	}, [] );

	let tooltipText = __( 'Connect your account to get access to Elementor\'s Template Library & more.', 'elementor' ),
		connectUrl = elementorCommonConfig.connect.connect_url,
		buttonText = __( 'Connect Account', 'elementor' ),
		targetUrl = '_self';

	if ( isUserConnected ) {
		tooltipText = '';
		connectUrl = 'https://go.elementor.com/wp-dash-admin-bar-account/';
		buttonText = __( 'My Elementor', 'elementor' );
		targetUrl = '_blank';
	}

	return (
		<BarButton icon="eicon-user-circle-o" buttonRef={buttonRef} dataInfo={tooltipText} href={connectUrl} target={targetUrl}>{ buttonText }</BarButton>
	);
}

