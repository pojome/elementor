import { useState, useEffect } from 'react';

export default function useAjax() {
	const [ ajax, setAjax ] = useState( null ),
		uploadInitialState = {
			status: 'initial',
			isComplete: false,
			response: null,
		},
		[ ajaxState, setAjaxState ] = useState( uploadInitialState );

	useEffect( () => {
		if ( ajax ) {
			const formData = new FormData();

			if ( ajax.data ) {
				for ( const key in ajax.data ) {
					formData.append( key, ajax.data[ key ] );
				}

				if ( ! ajax.data.nonce ) {
					formData.append( 'nonce', elementorCommon.config.ajax.nonce );
				}
			}

			const options = {
				type: 'post',
				url: elementorCommon.config.ajax.url,
				headers: {},
				cache: false,
				contentType: false,
				processData: false,
				...ajax,
				data: formData,
				success: ( response ) => {
					const status = response.success ? 'success' : 'error';

					setAjaxState( ( prevState ) => ( { ...prevState, status, response: response?.data } ) );
				},
				error: () => {
					setAjaxState( ( prevState ) => ( { ...prevState, status: 'error' } ) );
				},
				complete: () => {
					setAjaxState( ( prevState ) => ( { ...prevState, isComplete: true } ) );
				},
			};

			jQuery.ajax( options );
		}
	}, [ ajax ] );

	return {
		ajax,
		setAjax,
		ajaxState,
	};
}
