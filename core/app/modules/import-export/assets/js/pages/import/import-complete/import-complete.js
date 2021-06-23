import { useContext } from 'react';

import { Context } from '../../../context/context-provider';

import Layout from '../../../templates/layout';
import WizardStep from '../../../ui/wizard-step/wizard-step';
import KitData from '../../../shared/kit-data/kit-data';
import InlineLink from 'elementor-app/ui/molecules/inline-link';
import DashboardButton from 'elementor-app/molecules/dashboard-button';
import WizardFooter from 'elementor-app/organisms/wizard-footer';

export default function ImportComplete() {
	const context = useContext( Context ),
		getFooter = () => (
			<WizardFooter separator justify="end">
				<DashboardButton />
			</WizardFooter>
		),
		getTemplates = ( templates, importedData ) => {
			const kitTemplates = {};

			for ( const key in importedData?.templates?.succeed ) {
				kitTemplates[ key ] = templates[ key ];
			}

			return kitTemplates;
		},
		getContent = ( content, importedData ) => {
			const kitContent = {};

			for ( const contentType in importedData?.content ) {
				kitContent[ contentType ] = {};

				for ( const key in importedData.content[ contentType ]?.succeed ) {
					kitContent[ contentType ][ key ] = content[ contentType ][ key ];
				}
			}

			return kitContent;
		},
		getWPContent = ( content, importedData ) => {
			const kitWPContent = {};

			for ( const contentType in importedData?.[ 'wp-content' ] ) {
				const succeededItems = importedData[ 'wp-content' ][ contentType ]?.succeed;

				kitWPContent[ contentType ] = succeededItems ? Object.keys( succeededItems ) : [];
			}

			return kitWPContent;
		},
		getKitData = () => {
			if ( ! context.data.fileResponse ) {
				return {};
			}

			const manifest = context.data.fileResponse.stage1.manifest,
				importedData = context.data.fileResponse.stage2;

			return {
				templates: getTemplates( manifest.templates, importedData ),
				content: getContent( manifest.content, importedData ),
				'wp-content': getWPContent( manifest[ 'wp-content' ], importedData ),
				'site-settings': context.data.includes.includes( 'settings' ) ? manifest[ 'site-settings' ] : {},
			};
		};

	return (
		<Layout type="import" footer={ getFooter() }>
			<WizardStep
				image={ elementorAppConfig.assets_url + 'images/go-pro.svg' }
				heading={ __( 'Your kit is now live on your site!', 'elementor' ) }
				notice={ (
					<>
						<InlineLink url="https://go.elementor.com/app-what-are-kits" italic>
							{ __( 'Click Here', 'elementor' ) }
						</InlineLink> { __( 'to learn more about building your site with Elementor Kits', 'elementor' ) }
					</>
				) }
			>
				<KitData data={ getKitData() } />
			</WizardStep>
		</Layout>
	);
}
