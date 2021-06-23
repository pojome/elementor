import { useReducer } from 'react';

import { reducer } from './context-reducer';

export const Context = React.createContext();

export default function ContextProvider( props ) {
	const initialState = {
		includes: [],
		downloadUrl: '',
		fileResponse: null,
		file: null,
		overrideConditions: [],
		referrer: null,
		kitInfo: {
			title: null,
			description: null,
		},
	},
	[ data, dispatch ] = useReducer( reducer, initialState );

	return (
		<Context.Provider value={ { data, dispatch } }>
			{ props.children }
		</Context.Provider>
	);
}

ContextProvider.propTypes = {
	children: PropTypes.object.isRequired,
};
