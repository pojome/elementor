import { createContext, useContext, useState, useEffect, useCallback } from 'react';

const SettingsContext = createContext( {} );

/**
 * Consume the context
 *
 * @returns {{emptyTrashDays: number}}
 */
export function useSettingsContext() {
	return useContext( SettingsContext );
}

/**
 * Settings Provider
 *
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
export function SettingsProvider( props ) {
	const [ settings, setSettings ] = useState( {} );

	const updateSettings = useCallback( ( newSettings ) => {
		setSettings( ( prev ) => ( { ...prev, ...newSettings } ) );
	}, [ setSettings ] );

	useEffect( () => {
		setSettings( props.value );
	}, [ setSettings ] );

	return (
		<SettingsContext.Provider value={ { settings, setSettings, updateSettings } }>
			{ props.children }
		</SettingsContext.Provider>
	);
}

SettingsProvider.propTypes = {
	children: PropTypes.any,
	value: PropTypes.object.isRequired,
};
