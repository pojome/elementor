import { useCallback } from 'react';
import { useMutation } from 'react-query';

export default function useDownloadLinkMutation( model, { onError, onSuccess } ) {
	const downloadLink = useCallback(
		() => $e.data.get( 'kits/download-link', { id: model.id }, { refresh: true } ),
		[ model ]
	);

	return useMutation(
		downloadLink,
		{ onSuccess, onError }
	);
}
