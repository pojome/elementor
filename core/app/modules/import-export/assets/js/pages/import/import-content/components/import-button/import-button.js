import { useState, useEffect, useContext } from 'react';
import { useNavigate } from '@reach/router';

import { Context } from '../../../../../context/context-provider';

import Button from 'elementor-app/ui/molecules/button';

export default function ImportButton() {
	const context = useContext( Context ),
		navigate = useNavigate(),
		[ isImportAllowed, setIsImportAllowed ] = useState( false );

	useEffect( () => {
		setIsImportAllowed( ! ! context.data.includes.length );
	}, [ context.data.includes ] );

	return (
		<Button
			variant="contained"
			text={ __( 'Next', 'elementor' ) }
			color={ isImportAllowed ? 'primary' : 'disabled' }
			onClick={ () => {
				if ( isImportAllowed ) {
					if ( context.data.includes.includes( 'templates' ) && context.data.fileResponse.stage1.conflicts ) {
						navigate( 'import/resolver' );
					} else {
						navigate( 'import/process' );
					}
				}
			} }
		/>
	);
}

ImportButton.propTypes = {
	setIsDownloading: PropTypes.func,
};
