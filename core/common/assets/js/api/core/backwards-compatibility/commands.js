import Module from 'elementor-common/modules/imports/module';

export default class CommandsBackwardsCompatibility extends Module {
	__construct() {
		this.onOrig = this.on;
	}

	on = ( eventName, callback ) => {
		if ( 'run' === eventName ) {
			let componentName = this.getConstructorID();

			// Regex takes the first letter and convert it to lower case.
			componentName = componentName.replace( /^./, ( val ) => val.toLowerCase() );

			elementorCommon.helpers.softDeprecated(
				`$e.${ componentName }.on( 'run', ... )`,
				'3.0.0',
				`$e.${ componentName }.on( 'run:before', ... )`
			);

			this.onOrig( 'run:before', callback );

			return;
		}

		this.onOrig( eventName, callback );
	};
}
