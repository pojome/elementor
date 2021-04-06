import { Dialog } from '@elementor/app-ui';
import { useSettingsContext } from '../context/settings-context';

const { useEffect, useRef } = React;

export default function ConnectDialog( props ) {
	const { library_connect_url: libraryConnectUrl } = useSettingsContext();
	const approveButtonRef = useRef();

	useEffect( () => {
		jQuery( approveButtonRef.current ).elementorConnect( {
			success: props.onSuccess,
			error: () => props.onError( __( 'Unable to connect', 'elementor' ) ),
			UTMSource: () => '&utm_source=kit-library&utm_medium=wp-dash&utm_campaign=',
		} );
	}, [] );

	return (
		<Dialog
			title={ __( 'Connect to Template Library', 'elementor' ) }
			text={ __( 'Access this template and our entire library by creating an account', 'elementor' ) }
			approveButtonText={ __( 'Get Started', 'elementor' ) }
			approveButtonUrl={ libraryConnectUrl }
			approveButtonOnClick={ () => props.onClose() }
			approveButtonColor="primary"
			approveButtonRef={ approveButtonRef }
			dismissButtonText={ __( 'Cancel', 'elementor-pro' ) }
			dismissButtonOnClick={ () => props.onClose() }
			onClose={ () => props.onClose() }
		/>
	);
}

ConnectDialog.propTypes = {
	onClose: PropTypes.func.isRequired,
	onError: PropTypes.func.isRequired,
	onSuccess: PropTypes.func.isRequired,
};
