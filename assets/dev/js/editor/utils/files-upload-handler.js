export default class FilesUploadHandler {
	static isUploadEnabled( mediaType ) {
		const unfilteredFilesTypes = [ 'svg', 'application/json' ];

		if ( ! unfilteredFilesTypes.includes( mediaType ) ) {
			return true;
		}

		return elementor.config.filesUpload.unfilteredFiles;
	}

	static setUploadTypeCaller( frame ) {
		frame.uploader.uploader.param( 'uploadTypeCaller', 'elementor-wp-media-upload' );
	}

	static getUnfilteredFilesNotEnabledDialog( callback ) {
		const onConfirm = () => {
			elementorCommon.ajax.addRequest( 'enable_unfiltered_files_upload', {}, true );
			elementor.config.filesUpload.unfilteredFiles = true;
			callback();
		};

		return elementor.helpers.getSimpleDialog(
			'e-enable-unfiltered-files-dialog',
			__( 'Enable Unfiltered File Uploads', 'elementor' ),
			__( 'Before you enable unfiltered files upload, note that this kind of files include a security risk. Elementor does run a process to remove possible malicious code, but there is still risk involved when using such files.', 'elementor' ),
			__( 'Enable', 'elementor' ),
			onConfirm
		);
	}
}
