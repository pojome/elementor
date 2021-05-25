import ItemHeader from '../../components/item-header';
import Layout from '../../components/layout';
import PreviewResponsiveControls from './preview-responsive-controls';
import useKit from '../../hooks/use-kit';
import { PreviewIframe } from './preview-iframe';
import { useNavigate } from '@reach/router';
import { useState, useMemo } from 'react';

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

export default function Preview( props ) {
	const { data, isError, isLoading } = useKit( props.id );
	const headersButtons = useHeaderButtons( props.id );
	const [ activeDevice, setActiveDevice ] = useState( 'desktop' );
	const iframeStyle = useMemo(
		() => breakpoints.find( ( { value } ) => value === activeDevice ).style,
		[ activeDevice ]
	);

	if ( isError ) {
		return __( 'Error!', 'elementor' );
	}

	if ( isLoading ) {
		return __( 'Loading...', 'elementor' );
	}

	return (
		<Layout header={
			<ItemHeader
				model={ data }
				buttons={ headersButtons }
				centerColumn={ <PreviewResponsiveControls active={ activeDevice } onChange={ setActiveDevice }/> }
			/>
		}>
			{ data.previewUrl && <PreviewIframe previewUrl={ data.previewUrl } style={ iframeStyle }/> }
		</Layout>
	);
}

Preview.propTypes = {
	id: PropTypes.string,
};

