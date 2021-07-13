import Type from '../type';

export class Image extends Type {
	/**
	 * @inheritDoc
	 */
	render() {
		const reader = new FileReader();

		reader.readAsDataURL( this.getFile() );

		reader.onloadend = () => {
			this.getManager().createWidget( 'image', {
				image: {
					url: reader.result,
					id: '',
					alt: 'hello',
					source: 'url',
				},
			} );
		};
	}

	/**
	 * @inheritDoc
	 */
	static mediaTypes() {
		return [ 'image/*' ];
	}
}
