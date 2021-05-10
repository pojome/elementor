import { useContext } from 'react';
import { useNavigate } from '@reach/router';

import Layout from '../../../templates/layout';
import FileProcess from '../../../shared/file-process/file-process';

import { Context } from '../../../context/import/import-context';

import useUploadFile from 'elementor-app/hooks/use-upload-file';

export default function ImportProcess() {
	const { uploadFileStatus, setUploadFile } = useUploadFile( 'e_import_file', 'elementor_import_kit', {
			include: [ 'templates', 'content', 'site-settings' ],
		} ),
		importContext = useContext( Context ),
		navigate = useNavigate(),
		onLoad = () => {
			let fileURL = location.hash.match( 'file_url=(.+)' );

			if ( fileURL ) {
				fileURL = fileURL[ 1 ];
			}

			setUploadFile( fileURL || importContext.data.file );
		},
		onSuccess = () => navigate( '/import/success' ),
		onRetry = () => {
			importContext.dispatch( { type: 'SET_FILE', payload: null } );
			navigate( '/import' );
		};

	return (
		<Layout type="import">
			<FileProcess
				status={ uploadFileStatus.status }
				onLoad={ onLoad }
				onSuccess={ onSuccess }
				onRetry={ onRetry }
			/>
		</Layout>
	);
}
