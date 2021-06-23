import Heading from 'elementor-app/ui/atoms/heading';
import Text from 'elementor-app/ui/atoms/text';
import List from 'elementor-app/ui/molecules/list';

import './kit-data.scss';

export default function KitData( props ) {
	const kitData = props.data,
		getSummaryTitle = ( type, key, amount ) => {
			const data = elementorAppConfig[ 'import-export' ].summaryTitles[ type ][ key ];

			if ( data?.single ) {
				if ( ! amount ) {
					return '';
				}

				const title = amount > 1 ? data.plural : data.single;

				return amount + ' ' + title;
			}

			return data;
		},
		getTemplates = () => {
			const templates = {};

			for ( const key in kitData?.templates ) {
				const type = kitData.templates[ key ].doc_type;

				if ( ! templates[ type ] ) {
					templates[ type ] = 0;
				}

				templates[ type ]++;
			}

			return Object
					.entries( templates )
					.map( ( item ) => getSummaryTitle( 'templates', item[ 0 ], item[ 1 ] ) );
		},
		getSiteSettings = () => {
			const siteSettings = kitData?.[ 'site-settings' ] || {};

			return Object
				.entries( siteSettings )
				.map( ( item ) => getSummaryTitle( 'site-settings', item[ 1 ] ) );
		},
		getContent = () => {
			const content = kitData?.content || {},
				wpContent = kitData?.[ 'wp-content' ] || {};

			let mergedContent = { ...content };

			for ( const key in mergedContent ) {
				mergedContent[ key ] = Object.keys( mergedContent[ key ] ).concat( wpContent[ key ] || [] );
			}

			// In case that wpContent has properties that doesn't exist in the content object.
			mergedContent = { ...wpContent, ...mergedContent };

			return Object
				.entries( mergedContent )
				.map( ( item ) => getSummaryTitle( 'content', item[ 0 ], item[ 1 ].length ) );
		},
		kitContent = [
			{
				title: __( 'Templates:', 'elementor' ),
				data: getTemplates(),
			},
			{
				title: __( 'Site Settings:', 'elementor' ),
				data: getSiteSettings(),
			},
			{
				title: __( 'Content:', 'elementor' ),
				data: getContent(),
			},
		];

	return (
		<>
			<Heading variant="h6" tag="h3" className="e-app-export-complete__kit-content-title">
				{ __( 'This Template Kit includes:', 'elementor' ) }
			</Heading>

			<List className="e-app-export-complete-kit-data-list">
				{
					kitContent.map( ( item, index ) => {
						if ( ! item.data.length ) {
							return;
						}

						return (
							<List.Item key={ index } className="e-app-export-complete-kit-data-list__item">
								<Text tag="strong" variant="sm"><strong>{ item.title }</strong></Text> <Text tag="span" variant="sm">{ item.data.filter( ( value ) => value ).join( ' | ' ) }</Text>
							</List.Item>
						);
					} )
				}
			</List>
		</>
	);
}

KitData.propTypes = {
	data: PropTypes.object,
};
