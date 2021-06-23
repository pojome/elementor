import { useContext } from 'react';

import { Context } from '../../../../../context/context-provider';

import TextField from 'elementor-app/ui/atoms/text-field';

export default function KitDescription() {
	const context = useContext( Context );

	return (
		<TextField
			variant="outlined"
			placeholder={ __( 'Say something about the style and content of these files...', 'elementor' ) }
			multiline
			rows={5}
			onChange={ ( event ) => {
				context.dispatch( { type: 'SET_KIT_DESCRIPTION', payload: event.target.value } );
			} }
		/>
	);
}
