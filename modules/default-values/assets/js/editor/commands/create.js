import { parseType, pipe } from '../utils';

export class Create extends $e.modules.CommandBase {
	validateArgs( args ) {
		this.requireArgument( 'elementId', args );
	}

	async apply( { elementId } ) {
		const container = elementor.getContainer( elementId );

		// e.g: heading, button, section.
		const type = parseType( container.settings );

		// Get all the "styled" settings that differently from the hardcoded defaults.
		const settings = this.getSettingsForSave( container );

		// Get all the elements that should recreate (e.g: type = 'heading' it will recreate all the heading)
		const elementsToRecreate = this.getAllElementsForRecreate( type, settings );

		// Save those settings into default entity.
		await $e.data.create( 'default-values/index', { settings }, { type } );

		this.component.handlers.forEach(
			( handler ) => handler.afterSaved( type, settings )
		);

		// Will recreate all the elements with the same type to apply the default values.
		$e.commandsInternal.run( 'document/elements/recreate', { models: elementsToRecreate } );
	}

	/**
	 * Get all the settings that should be save.
	 *
	 * @param container
	 * @returns {{}}
	 */
	getSettingsForSave( container ) {
		return pipe(
			...this.component.handlers.map( ( handler ) => handler.appendSettingsForSave )
		)( {}, container );
	}

	/**
	 * Get all the elements that should recreate after the creating the new default.
	 *
	 * @param type
	 * @param newDefaultSettings
	 * @returns {{}}
	 */
	getAllElementsForRecreate( type, newDefaultSettings ) {
		const elements = {};

		elementor.getPreviewContainer().forEachChildrenRecursive( ( element ) => {
			const currentType = parseType( element.settings );

			if ( type !== currentType ) {
				return;
			}

			elements[ element.id ] = pipe(
				...this.component.handlers.map( ( handler ) => handler.appendSettingsForRecreate )
			)( element.model.toJSON( { remove: [ 'default' ] } ), newDefaultSettings );
		} );

		return elements;
	}
}

export default Create;
