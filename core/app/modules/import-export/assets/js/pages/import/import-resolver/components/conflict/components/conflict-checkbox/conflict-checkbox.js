import { useContext, useEffect } from 'react';

import { Context } from '../../../../../../../context/context-provider';

import Checkbox from 'elementor-app/ui/atoms/checkbox';

export default function ConflictCheckbox( props ) {
	const context = useContext( Context ),
		isSelected = () => context.data.overrideConditions.includes( props.id ),
		updateOverrideCondition = ( event ) => {
			const isChecked = event.target.checked,
				actionType = isChecked ? 'ADD_OVERRIDE_CONDITION' : 'REMOVE_OVERRIDE_CONDITION';

			context.dispatch( { type: actionType, payload: props.id } );
		};

	useEffect( () => {
		if ( ! context.data.overrideConditions.length ) {
			context.dispatch( { type: 'ADD_OVERRIDE_CONDITION', payload: props.id } );
		}
	}, [] );

	return (
		<Checkbox
			checked={ isSelected() }
			onChange={ updateOverrideCondition }
			className={ props.className }
		/>
	);
}

ConflictCheckbox.propTypes = {
	className: PropTypes.string,
	id: PropTypes.number.isRequired,
};

ConflictCheckbox.defaultProps = {
	className: '',
};
