import CommandData from 'elementor-api/modules/command-data';

export class Media extends CommandData {
	static getEndpointFormat() {
		// 'wp/media' to 'media' since `requestData.namespace` is 'wp'.
		return 'media';
	}

	getRequestData() {
		const requestData = super.getRequestData();

		requestData.namespace = 'wp';
		requestData.version = '2';

		return requestData;
	}

	applyBeforeCreate( args ) {
		args.headers = {
			'Content-Disposition': `attachment; filename=${ this.file.name }`,
			'Content-Type': this.file.type,
		};

		args.data = this.file;

		return args;
	}

	async run() {
		if ( ! this.args.file ) {
			this.file = await this.openFileDialog();
		} else {
			this.file = this.args.file;
		}

		return await super.run();
	}

	// This is just POC example.
	// Since the way input works - we cannot really determine cancel and other things, it will be possible to handle it using new Native File API.
	// With method called 'chooseFileSystemEntries() '.
	// Anyway data command should not trigger UI.
	openFileDialog() {
		return new Promise( ( resolve ) => {
			const inputElement = document.createElement( 'input' );

			inputElement.id = 'e-uploader';
			inputElement.type = 'file';

			inputElement.addEventListener( 'change', () => {
				if ( inputElement.files ) {
					resolve( inputElement.files[ 0 ] );
				}
			} );

			// Trigger input request.
			inputElement.dispatchEvent( new MouseEvent( 'click' ) );
		} );
	}
}
