import { useEffect, useContext } from 'react';
import { useNavigate } from '@reach/router';

import Layout from '../../../templates/layout';
import FileProcess from '../../../shared/file-process/file-process';

import { Context } from '../../../context/context-provider';

import useAjax from 'elementor-app/hooks/use-ajax';

export default function ExportProcess() {
	const { ajaxState, setAjax } = useAjax(),
		context = useContext( Context ),
		navigate = useNavigate(),
		onLoad = () => {
			if ( context.data.downloadUrl ) {
				setAjax( {
					url: context.data.downloadUrl,
					headers: {
						'Content-Type': 'application/json',
					},
				} );
			}
		},
		onSuccess = () => context.dispatch( { type: 'SET_FILE_RESPONSE', payload: ajaxState.response } ),
		onDialogDismiss = () => {
			context.dispatch( { type: 'SET_DOWNLOAD_URL', payload: '' } );
			navigate( 'export' );
		};

	useEffect( () => {
		if ( context.data.fileResponse ) {
			navigate( 'export/complete' );
		}
	}, [ context.data.fileResponse ] );

	return (
		<Layout type="export">
			<FileProcess
				status={ ajaxState.status }
				onLoad={ onLoad }
				onSuccess={ onSuccess }
				onDialogApprove={ () => window.open( 'https://elementor.com/help/export-kit?utm_source=import-export&utm_medium=wp-dash&utm_campaign=learn', '_blank' ) }
				onDialogDismiss={ onDialogDismiss }
			/>
		</Layout>
	);
}
