import Content from 'elementor/core/app/assets/js/layout/content';
import OverviewContent from './overview-content';
import ItemHeader from '../../components/item-header';
import OverviewSidebar from './overview-sidebar';
import Layout from '../../components/layout';
import useKit from '../../hooks/use-kit';
import useKitDocumentByType from '../../hooks/use-kit-document-by-type';
import { useMemo } from 'react';
import { useNavigate } from '@reach/router';

function useHeaderButtons( id ) {
	const navigate = useNavigate();

	return useMemo( () => [
		{
			id: 'view-demo',
			text: __( 'View Demo', 'elementor' ),
			hideText: false,
			variant: 'outlined',
			color: 'secondary',
			size: 'sm',
			onClick: () => navigate( `/kit-library/preview/${ id }` ),
			includeHeaderBtnClass: false,
		},
	], [ id ] );
}

export default function Item( props ) {
	const { data: kit, isError, isLoading } = useKit( props.id );
	const { data: documentsByType } = useKitDocumentByType( kit );
	const headerButtons = useHeaderButtons( props.id );

	if ( isError ) {
		return __( 'Error!', 'elementor' );
	}

	if ( isLoading ) {
		return __( 'Loading...', 'elementor' );
	}

	return (
		<Layout
			header={ <ItemHeader model={ kit } buttons={ headerButtons }/> }
			sidebar={ <OverviewSidebar model={ kit } groupedKitContent={ documentsByType }/> }
		>
			{
				documentsByType.length > 0 &&
				<Content>
					<OverviewContent documentsByType={ documentsByType }/>
				</Content>
			}
		</Layout>
	);
}

Item.propTypes = {
	id: PropTypes.string,
};
