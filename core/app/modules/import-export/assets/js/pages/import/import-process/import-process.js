import { useEffect, useContext, useRef } from 'react';
import { useNavigate } from '@reach/router';

import Layout from '../../../templates/layout';
import FileProcess from '../../../shared/file-process/file-process';

import { Context } from '../../../context/context-provider';

import useAjax from 'elementor-app/hooks/use-ajax';

export default function ImportProcess() {
	const { ajaxState, setAjax } = useAjax(),
		context = useContext( Context ),
		navigate = useNavigate(),
		fileURL = location.hash.match( 'file_url=([^&]+)' ),
		onLoad = () => {
			const ajaxConfig = {
				data: {
					action: 'elementor_import_kit',
				},
			};

			if ( fileURL || context.data.fileResponse ) {
				if ( fileURL ) {
					fileURL[ 1 ] = decodeURIComponent( fileURL[ 1 ] );

					context.dispatch( { type: 'SET_FILE', payload: fileURL } );

					ajaxConfig.data.e_import_file = fileURL[ 1 ];
					ajaxConfig.data.data = JSON.stringify( {
						stage: 1,
					} );

					const referrer = location.hash.match( 'referrer=([^&]+)' );

					if ( referrer ) {
						context.dispatch( { type: 'SET_REFERRER', payload: referrer[ 1 ] } );
					}
				} else {
					ajaxConfig.data.data = {
						stage: 2,
						session: context.data.fileResponse.stage1.session,
						include: context.data.includes,
						overrideConditions: context.data.overrideConditions,
					};

					if ( context.data.referrer ) {
						ajaxConfig.data.data.referrer = context.data.referrer;
					}

					ajaxConfig.data.data = JSON.stringify( ajaxConfig.data.data );
				}

				setAjax( ajaxConfig );
			}
		},
		onSuccess = () => {
			if ( context.data.fileResponse?.stage1 ) {
				const previousFileResponse = context.data.fileResponse,
					fileResponse = { ...previousFileResponse, stage2: ajaxState.response };

				context.dispatch( { type: 'SET_FILE_RESPONSE', payload: fileResponse } );
			} else {
				context.dispatch( { type: 'SET_FILE_RESPONSE', payload: { stage1: ajaxState.response } } );
			}
		},
		onDialogDismiss = () => {
			context.dispatch( { type: 'SET_FILE', payload: null } );
			navigate( '/import' );
		};

	useEffect( () => {
		if ( 'success' === ajaxState.status ) {
			if ( context.data.fileResponse.hasOwnProperty( 'stage2' ) ) {
				navigate( '/import/complete' );
			} else {
				navigate( '/import/content' );
			}
		}
	}, [ context.data.fileResponse ] );

	return (
		<Layout type="import">
			<FileProcess
				status={ ajaxState.status }
				onLoad={ onLoad }
				onSuccess={ onSuccess }
				onDialogApprove={ () => {} }
				onDialogDismiss={ onDialogDismiss }
			/>
		</Layout>
	);
}
