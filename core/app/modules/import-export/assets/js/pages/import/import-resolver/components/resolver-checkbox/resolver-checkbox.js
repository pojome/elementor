import { useContext, useMemo, useEffect } from 'react';

import { Context } from '../../../../../context/context-provider';

import Checkbox from 'elementor-app/ui/atoms/checkbox';

export default function ResolverCheckbox( props ) {
	const context = useContext( Context ),
		isSelected = () => context.data.includes.includes( props.type ),
		setIncludes = ( event ) => {
			const isChecked = event.target.checked,
				actionType = isChecked ? 'ADD_INCLUDE' : 'REMOVE_INCLUDE';

			context.dispatch( { type: actionType, payload: props.type } );
		};

	useEffect( () => {
		context.dispatch( { type: 'ADD_INCLUDE', payload: props.type } );
	}, [] );

	return useMemo( () => (
		<Checkbox checked={ isSelected() } onChange={ setIncludes } className={ props.className } />
	), [ context.data.includes ] );
}

ResolverCheckbox.propTypes = {
	className: PropTypes.string,
	type: PropTypes.string.isRequired,
};

ResolverCheckbox.defaultProps = {
	className: '',
};
