import ConnectDialog from './connect-dialog';
import Header from './layout/header';
import HeaderBackButton from './layout/header-back-button';
import Kit from '../models/kit';
import useDownloadLinkMutation from '../hooks/use-download-link-mutation';
import useKitCallToAction, { TYPE_PROMOTION, TYPE_CONNECT } from '../hooks/use-kit-call-to-action';
import { Dialog } from '@elementor/app-ui';
import { useMemo, useState } from 'react';
import { useNavigate } from '@reach/router';
import { useSettingsContext } from '../context/settings-context';

import './item-header.scss';

/**
 * Returns the right call to action button.
 *
 * @param model
 * @param onConnect
 * @param onApply
 * @returns {object}
 */
function useKitCallToActionButton( model, { onConnect, setError } ) {
	const navigate = useNavigate();
	const [ type, { subscriptionPlan } ] = useKitCallToAction( model.accessLevel );

	const { mutate: apply, isLoading: isApplyLoading } = useDownloadLinkMutation(
		model,
		{
			onSuccess: ( { data } ) => navigate( `/import/process?file_url=${ encodeURIComponent( data.data.download_link ) }&nonce=${ data.meta.nonce }&referrer=kit-library` ),
			onError: () => setError( __( 'Something went wrong.', 'elementor' ) ),
		}
	);

	return useMemo( () => {
		if ( type === TYPE_CONNECT ) {
			return {
				id: 'connect',
				text: __( 'Apply Kit', 'elementor' ), // The label is Apply kit but the this is connect button
				hideText: false,
				variant: 'contained',
				color: 'primary',
				size: 'sm',
				onClick: onConnect,
				includeHeaderBtnClass: false,
			};
		}

		if ( type === TYPE_PROMOTION && subscriptionPlan ) {
			return {
				id: 'promotion',
				text: __( 'Go %s', 'elementor' ).replace( '%s', subscriptionPlan.label ),
				hideText: false,
				variant: 'contained',
				color: 'cta',
				size: 'sm',
				url: subscriptionPlan.promotion_url,
				target: '_blank',
				includeHeaderBtnClass: false,
			};
		}

		return {
			id: 'apply',
			text: __( 'Apply Kit', 'elementor' ),
			className: 'e-kit-library__apply-button',
			icon: isApplyLoading ? 'eicon-loading eicon-animation-spin' : '',
			hideText: false,
			variant: 'contained',
			color: isApplyLoading ? 'disabled' : 'primary',
			size: 'sm',
			onClick: isApplyLoading ? null : apply,
			includeHeaderBtnClass: false,
		};
	}, [ type, subscriptionPlan, isApplyLoading, apply ] );
}

export default function ItemHeader( props ) {
	const { updateSettings } = useSettingsContext();

	const [ isConnectDialogOpen, setIsConnectDialogOpen ] = useState( false );
	const [ error, setError ] = useState( false );

	const applyButton = useKitCallToActionButton( props.model, {
		onConnect: () => setIsConnectDialogOpen( true ),
		setError,
	} );

	const buttons = useMemo( () => [ applyButton, ...props.buttons ], [ props.buttons, applyButton ] );

	return (
		<>
			{
				error && <Dialog
					title={ error }
					approveButtonText={ __( 'Learn More', 'elementor-pro' ) }
					approveButtonColor="link"
					approveButtonUrl="#"
					approveButtonOnClick={ () => setError( false ) }
					dismissButtonText={ __( 'Close', 'elementor-pro' ) }
					dismissButtonOnClick={ () => setError( false ) }
					onClose={ () => setError( false ) }
				/>
			}
			{
				isConnectDialogOpen && <ConnectDialog
					onClose={ () => setIsConnectDialogOpen( false ) }
					onSuccess={ ( data ) => {
						updateSettings( {
							is_library_connected: true,
							access_level: data.kits_access_level || data.access_level || 0, // BC: Check for 'access_level' prop
						} );

						if ( data.access_level < props.model.accessLevel ) {
							return;
						}

						apply();
					} }
					onError={ ( message ) => setError( message ) }
				/>
			}
			<Header
				startColumn={ <HeaderBackButton/> }
				centerColumn={ props.centerColumn }
				buttons={ buttons }
			/>
		</>
	);
}

ItemHeader.propTypes = {
	model: PropTypes.instanceOf( Kit ).isRequired,
	centerColumn: PropTypes.node,
	buttons: PropTypes.arrayOf( PropTypes.object ),
};
