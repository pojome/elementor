import Type from '../type';

export class Image extends Type {
	/**
	 * @inheritDoc
	 */
	render() {
		$e.data.run( 'create', 'wp/media', { file: this.getFile(), options: {} } )
			.then( ( { data } ) => {
				this.getManager().createWidget( 'image', {
					image: {
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
		return [ 'image/*' ];
	}
}
