class ReducerActions {
	static updateArray( state, key, value, action ) {
		if ( 'add' === action ) {
			// If the value already exists, then do nothing.
			if ( state[ key ].includes( value ) ) {
				return state;
			}

			return { ...state, [ key ]: [ ...state[ key ], value ] };
		} else if ( 'remove' === action ) {
			return { ...state, [ key ]: state[ key ].filter( ( item ) => item !== value ) };
		}

		return state;
	}
}

export const reducer = ( state, action ) => {
	switch ( action.type ) {
		case 'SET_DOWNLOAD_URL':
			return { ...state, downloadUrl: action.payload };
		case 'ADD_INCLUDE':
			return ReducerActions.updateArray( state, 'includes', action.payload, 'add' );
		case 'REMOVE_INCLUDE':
			return ReducerActions.updateArray( state, 'includes', action.payload, 'remove' );
		case 'SET_FILE_RESPONSE':
			return { ...state, fileResponse: action.payload };
		case 'SET_FILE':
			return { ...state, file: action.payload };
		case 'ADD_OVERRIDE_CONDITION':
			return ReducerActions.updateArray( state, 'overrideConditions', action.payload, 'add' );
		case 'REMOVE_OVERRIDE_CONDITION':
			return ReducerActions.updateArray( state, 'overrideConditions', action.payload, 'remove' );
		case 'SET_KIT_TITLE':
			return { ...state, kitInfo: { ...state.kitInfo, title: action.payload } };
		case 'SET_KIT_DESCRIPTION':
			return { ...state, kitInfo: { ...state.kitInfo, description: action.payload } };
		case 'SET_REFERRER':
			return { ...state, referrer: action.payload };
		case 'SET_INCLUDES':
			return { ...state, includes: action.payload };
		default:
			return state;
	}
};
