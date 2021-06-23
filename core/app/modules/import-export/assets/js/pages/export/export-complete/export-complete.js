import { useContext, useEffect, useRef } from 'react';

import { Context } from '../../../context/context-provider';

import Layout from '../../../templates/layout';
import WizardStep from '../../../ui/wizard-step/wizard-step';
import KitData from '../../../shared/kit-data/kit-data';
import InlineLink from 'elementor-app/ui/molecules/inline-link';
import WizardFooter from 'elementor-app/organisms/wizard-footer';
import DashboardButton from 'elementor-app/molecules/dashboard-button';

import './export-complete.scss';

export default function ExportComplete() {
	const context = useContext( Context ),
		downloadLink = useRef( null ),
		getFooter = () => (
			<WizardFooter separator justify="end">
				<DashboardButton />
			</WizardFooter>
		),
		downloadFile = () => {
			if ( ! downloadLink.current ) {
				const link = document.createElement( 'a' );

				link.href = 'data:text/plain;base64,' + context.data.fileResponse.file;
				link.download = 'elementor-kit.zip';

				downloadLink.current = link;
			}

			downloadLink.current.click();
		},
		getDownloadLink = () => (
			<InlineLink onClick={ downloadFile } italic>
				{ __( 'Click Here', 'elementor' ) }
			</InlineLink>
		);

	useEffect( () => {
		if ( context.data.downloadUrl ) {
			downloadFile();
		}
	}, [ context.data.downloadUrl ] );

	return (
		<Layout type="export" footer={ getFooter() }>
			<WizardStep
				image={ elementorAppConfig.assets_url + 'images/go-pro.svg' }
				heading={ __( 'Your export is ready!', 'elementor' ) }
				description={ __( 'Now you can import this kit and use it on other sites.', 'elementor' ) }
				notice={ (
					<>
						{ __( 'Download not working?', 'elementor' ) } { getDownloadLink() } { __( 'to download', 'elementor' ) }
					</>
				) }
			>
				<KitData data={ context.data.fileResponse?.manifest } />
			</WizardStep>
		</Layout>
	);
}
