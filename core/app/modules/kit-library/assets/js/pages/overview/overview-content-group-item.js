import Document from '../../models/document';
import { Button, Card, CardBody, CardOverlay, CardHeader, CardImage, Heading } from '@elementor/app-ui';

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
				<CardImage alt={ props.document.title } src={ props.document.thumbnailUrl || '' }>
					{ props.document.previewUrl && <CardOverlay>
						<Button
							className="e-kit-library__kit-item-overlay-overview-button"
							text={ __( 'View Demo', 'elementor' ) }
							icon="eicon-preview-medium"
							url={ `/kit-library/preview/${ props.kitId }?document_id=${ props.document.id }` }
						/>
					</CardOverlay> }
				</CardImage>
			</CardBody>
		</Card>
	);
}

OverviewContentGroupItem.propTypes = {
	document: PropTypes.instanceOf( Document ).isRequired,
	kitId: PropTypes.string.isRequired,
};
