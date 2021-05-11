import { useContext } from 'react';
import { useNavigate } from '@reach/router';

import Layout from '../../../templates/layout';
import FileProcess from '../../../shared/file-process/file-process';

import { Context } from '../../../context/import/import-context';

import useAjax from 'elementor-app/hooks/use-ajax';

export default function ImportProcess() {
	const { ajaxState, setAjax } = useAjax( 'e_import_file', 'elementor_import_kit', {
			include: [ 'templates', 'content', 'site-settings' ],
		} ),
		importContext = useContext( Context ),
		navigate = useNavigate(),
		onLoad = () => {
			let fileURL = location.hash.match( 'file_url=(.+)' );

			if ( fileURL ) {
				fileURL = fileURL[ 1 ];
			}

			setAjax( {
				data: {
					e_import_file: fileURL || importContext.data.file,
					action: 'elementor_import_kit',
					data: JSON.stringify( {
						include: [ 'templates', 'content', 'site-settings' ],
					} ),
				},
			} );
		},
		onSuccess = () => navigate( '/import/success' ),
		onRetry = () => {
			importContext.dispatch( { type: 'SET_FILE', payload: null } );
			navigate( '/import' );
		};

	return (
		<Layout type="import">
			<FileProcess
				status={ ajaxState.status }
				onLoad={ onLoad }
				onSuccess={ onSuccess }
				onRetry={ onRetry }
			/>
		</Layout>
	);
}
