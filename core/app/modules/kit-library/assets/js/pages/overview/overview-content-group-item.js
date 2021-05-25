import Document from '../../models/document';
import { Card, CardBody, CardHeader, CardImage, Heading } from '@elementor/app-ui';

export default function OverviewContentGroupItem( props ) {
	return (
		<Card>
			<CardHeader>
				<Heading
					tag="h3"
					title={ props.document.title }
					variant="h5"
					className="eps-card__headline"
				>
					{ props.document.title }
				</Heading>
			</CardHeader>
			<CardBody>
				<CardImage alt={ props.document.title } src={ props.document.thumbnailUrl || '' } />
			</CardBody>
		</Card>
	);
}

OverviewContentGroupItem.propTypes = {
	document: PropTypes.instanceOf( Document ),
};
