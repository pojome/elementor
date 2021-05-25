import ContentType from '../../models/content-type';
import OverviewContentGroup from './overview-content-group';

import './overview-content.scss';

export default function OverviewContent( props ) {
	return props.documentsByType.map( ( contentType ) => (
		<OverviewContentGroup key={ contentType.id } contentType={ contentType }/>
	) );
}

OverviewContent.propTypes = {
	documentsByType: PropTypes.arrayOf(
		PropTypes.instanceOf( ContentType )
	),
};
