import ContentType from '../models/content-type';
import ItemContentOverviewGroupItem from './item-content-overview-group-item';
import { Heading, CssGrid } from '@elementor/app-ui';

export default function ItemContentOverviewGroup( props ) {
	if ( props.contentType?.documents?.length <= 0 ) {
		return '';
	}

	return (
		<div className="e-kit-library__content-overview-group-item">
			<Heading tag="h3" variant="h3" className="e-kit-library__content-overview-group-title">
				{ props.contentType.label }
			</Heading>
			<CssGrid spacing={ 24 } colMinWidth={ 250 }>
				{ props.contentType.documents.map( ( document ) => {
					return <ItemContentOverviewGroupItem key={ document.id } document={ document }/>;
				} ) }
			</CssGrid>
		</div>
	);
}

ItemContentOverviewGroup.propTypes = {
	contentType: PropTypes.instanceOf( ContentType ),
};
