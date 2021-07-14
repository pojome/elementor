import ElementorLoading from '../../components/elementor-loading';
import ItemHeader from '../../components/item-header';
import Layout from '../../components/layout';
import PageLoader from '../../components/page-loader';
import PreviewResponsiveControls from './preview-responsive-controls';
import useKit from '../../hooks/use-kit';
import { PreviewIframe } from './preview-iframe';
import { useLocation, useNavigate } from '@reach/router';
import { useState, useMemo } from 'react';

import './preview.scss';

export const breakpoints = [
	{
		value: 'desktop',
		label: __( 'Desktop', 'elementor' ),
		style: {
			width: '100%',
			height: '100%',
		},
	},
	{
		value: 'tablet',
		label: __( 'Tablet', 'elementor' ),
		style: {
			marginTop: '30px',
			marginBottom: '30px',
			width: '768px',
			height: '1024px',
		},
	},
	{
		value: 'mobile',
		label: __( 'Mobile', 'elementor' ),
		style: {
			marginTop: '30px',
			marginBottom: '30px',
			width: '375px',
			height: '667px',
		},
	},
];

function useHeaderButtons( id ) {
	const navigate = useNavigate();

	return useMemo( () => [
		{
			id: 'overview',
			text: __( 'Overview', 'elementor' ),
			hideText: false,
			variant: 'outlined',
			color: 'secondary',
			size: 'sm',
			onClick: () => navigate( `/kit-library/overview/${ id }` ),
			includeHeaderBtnClass: false,
		},
	], [ id ] );
}

/**
 * Get preview url.
 *
 * @param data
 * @returns {null|string}
 */
function usePreviewUrl( data ) {
	const location = useLocation();

	return useMemo( () => {
		if ( ! data ) {
			return null;
		}

		const documentId = new URLSearchParams( location.pathname.split( '?' )?.[1] ).get( 'document_id' );

		if ( ! documentId ) {
			return data.previewUrl;
		}

		return data.documents.find( ( item ) => item.id === parseInt( documentId ) )?.previewUrl || data.previewUrl;
	}, [ location, data ] );
}

export default function Preview( props ) {
	const { data, isError, isLoading } = useKit( props.id );
	const [ isIframeLoading, setIsIframeLoading ] = useState( true );
	const headersButtons = useHeaderButtons( props.id );
	const previewUrl = usePreviewUrl( data );
	const [ activeDevice, setActiveDevice ] = useState( 'desktop' );
	const iframeStyle = useMemo(
		() => breakpoints.find( ( { value } ) => value === activeDevice ).style,
		[ activeDevice ]
	);

	if ( isError ) {
		// Will be caught by the App error boundary.
		throw new Error();
	}

	if ( isLoading ) {
		return <ElementorLoading />;
	}

	return (
		<Layout header={
			<ItemHeader
				model={ data }
				buttons={ headersButtons }
				centerColumn={ <PreviewResponsiveControls active={ activeDevice } onChange={ setActiveDevice }/> }
			/>
		}>
			{ isIframeLoading && <PageLoader className="e-kit-library__preview-loader" /> }
			{
				previewUrl &&
					<PreviewIframe
						previewUrl={ previewUrl }
						style={ iframeStyle }
						onLoaded={() => setIsIframeLoading( false )}
					/>
			}
		</Layout>
	);
}

Preview.propTypes = {
	id: PropTypes.string,
};

