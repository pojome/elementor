import Type from '../type';

export class Video extends Type {
	/**
	 * @inheritDoc
	 */
	render() {
		this.getManager.createWidget( 'video', {
			video_type: 'hosted',
			insert_url: 'yes',
			external_url: {
				url: 'test',
				is_external: '',
				nofollow: '',
				custom_attributes: '',
			},
		} );
	}

	/**
	 * @inheritDoc
	 */
	static widgetType() {
		return 'video';
	}

	/**
	 * @inheritDoc
	 */
	static mediaTypes() {
		return [ 'video/*' ];
	}
}
