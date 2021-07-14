import Content from 'elementor/core/app/assets/js/layout/content';
import ElementorLoading from '../../components/elementor-loading';
import ItemHeader from '../../components/item-header';
import Layout from '../../components/layout';
import OverviewContentGroup from './overview-content-group';
import OverviewSidebar from './overview-sidebar';
import useKit from '../../hooks/use-kit';
import useKitDocumentByType from '../../hooks/use-kit-document-by-type';
import { useMemo } from 'react';
import { useNavigate } from '@reach/router';

import './overview.scss';

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

export default function Overview( props ) {
	const { data: kit, isError, isLoading } = useKit( props.id );
	const { data: documentsByType } = useKitDocumentByType( kit );
	const headerButtons = useHeaderButtons( props.id );

	if ( isError ) {
		// Will be caught by the App error boundary.
		throw new Error();
	}

	if ( isLoading ) {
		return <ElementorLoading />;
	}

	return (
		<Layout
			header={ <ItemHeader model={ kit } buttons={ headerButtons }/> }
			sidebar={ <OverviewSidebar model={ kit } groupedKitContent={ documentsByType }/> }
		>
			{
				documentsByType.length > 0 &&
				<Content>
					{
						documentsByType.map( ( contentType ) => (
							<OverviewContentGroup
								key={ contentType.id }
								contentType={ contentType }
								kitId={ props.id }
							/>
						) )
					}
				</Content>
			}
		</Layout>
	);
}

Overview.propTypes = {
	id: PropTypes.string,
};
