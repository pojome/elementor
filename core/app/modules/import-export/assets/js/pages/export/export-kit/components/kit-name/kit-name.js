import { useContext } from 'react';

import { Context } from '../../../../../context/context-provider';

import TextField from 'elementor-app/ui/atoms/text-field';

export default function KitName() {
	const context = useContext( Context );

	return (
		<TextField
			variant="outlined"
			placeholder={ __( 'Elementor Kit', 'elementor' ) }
			onChange={ ( event ) => {
				context.dispatch( { type: 'SET_KIT_TITLE', payload: event.target.value } );
			} }
		/>
	);
}
