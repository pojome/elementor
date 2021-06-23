import { useState, useEffect, useContext } from 'react';
import { useNavigate } from '@reach/router';

import { Context } from '../../../../../context/context-provider';

import Button from 'elementor-app/ui/molecules/button';

export default function ExportButton() {
	const context = useContext( Context ),
		navigate = useNavigate(),
		[ isDownloadAllowed, setIsDownloadAllowed ] = useState( false ),
		getDownloadUrl = () => {
			const exportURL = elementorAppConfig[ 'import-export' ].exportURL,
				exportData = {
					elementor_export_kit: {
						include: context.data.includes,
						kitInfo: context.data.kitInfo,
					},
				};

			return exportURL + '&' + jQuery.param( exportData );
	};

	useEffect( () => {
		setIsDownloadAllowed( ! ! context.data.includes.length );
	}, [ context.data.includes ] );

	useEffect( () => {
		if ( context.data.downloadUrl ) {
			navigate( '/export/process' );
		}
	}, [ context.data.downloadUrl ] );

	return (
		<Button
			variant="contained"
			text={ __( 'Export', 'elementor' ) }
			color={ isDownloadAllowed ? 'primary' : 'disabled' }
			onClick={ () => {
				if ( isDownloadAllowed ) {
					context.dispatch( { type: 'SET_DOWNLOAD_URL', payload: getDownloadUrl() } );
				}
			} }
		/>
	);
}

ExportButton.propTypes = {
	setIsDownloading: PropTypes.func,
};
