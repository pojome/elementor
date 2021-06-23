import { createContext, useContext, useState } from 'react';

const LastFilterContext = createContext( {} );

/**
 * Consume the context
 *
 * @returns {{}}
 */
export function useLastFilterContext() {
	return useContext( LastFilterContext );
}

/**
 * Settings Provider
 *
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
export function LastFilterProvider( props ) {
	const [ lastFilter, setLastFilter ] = useState( {} );

	return (
		<LastFilterContext.Provider value={ { lastFilter, setLastFilter } }>
			{ props.children }
		</LastFilterContext.Provider>
	);
}

LastFilterProvider.propTypes = {
	children: PropTypes.any,
};
