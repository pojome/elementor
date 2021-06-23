import { useMemo } from 'react';

export default function useSelectedTaxonomies( taxonomiesFilter ) {
	return useMemo(
		() => Object.values( taxonomiesFilter )
			.reduce( ( current, groupedTaxonomies ) => [ ...current, ...groupedTaxonomies ] ),
		[ taxonomiesFilter ]
	);
}
