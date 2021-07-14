import Type from '../type';

export class Video extends Type {
	/**
	 * @inheritDoc
	 */
	render() {
		$e.data.run( 'create', 'wp/media', { file: this.getFile(), options: {} } )
			.then( ( { data } ) => {
				this.getManager().createWidget( 'video', {
					video_type: 'hosted',
					hosted_url: {
						url: data.source_url,
						id: data.id,
						alt: 'hello',
						source: 'library',
					},
				} );
			} );
	}

	/**
	 * @inheritDoc
	 */
	static mediaTypes() {
		return [ 'video/*' ];
	}
}
