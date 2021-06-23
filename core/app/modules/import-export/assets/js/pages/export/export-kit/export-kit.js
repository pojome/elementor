import React from 'react';

import Layout from '../../../templates/layout';
import PageHeader from '../../../ui/page-header/page-header';
import ExportButton from './components/export-button/export-button';
import KitContent from '../../../shared/kit-content/kit-content';
import Panel from '../../../ui/panel/panel';
import ExportInfoModal from '../../../shared/info-modal/export-info-modal';
import KitName from './components/kit-name/kit-name';
import KitDescription from './components/kit-description/kit-description';
import Grid from 'elementor-app/ui/grid/grid';
import Heading from 'elementor-app/ui/atoms/heading';
import InlineLink from 'elementor-app/ui/molecules/inline-link';
import WizardFooter from 'elementor-app/organisms/wizard-footer';

import './export-kit.scss';

export default function ExportKit() {
	const getFooter = () => (
			<WizardFooter separator justify="end">
				<ExportButton />
			</WizardFooter>
		),
		getLearnMoreLink = () => (
			<InlineLink url="https://go.elementor.com/app-what-are-kits" italic>
				{ __( 'Learn More', 'elementor' ) }
			</InlineLink>
		);

	return (
		<Layout type="export" footer={ getFooter() }>
			<section className="e-app-export-kit">
				<PageHeader
					heading={ __( 'Export a Template Kit', 'elementor' ) }
					description={ [
						__( 'Choose which Elementor components - templates, content and site settings - to include in your kit file.', 'elementor' ),
						<React.Fragment key="description-secondary-line">
							{ __( 'By default, all of your components will be exported.', 'elementor' ) } { getLearnMoreLink() }
						</React.Fragment>,
					] }
				/>

				<KitContent />

				<Panel className="e-app-export-kit-information">
					<Panel.Header>
						<Panel.Headline>{ __( 'Kit Information', 'elementor' ) }</Panel.Headline>
					</Panel.Header>

					<Panel.Body>
						<Grid container spacing={20}>
							<Grid item md={4}>
								<Grid container direction="column">
									<Grid className="e-app-export-kit-information__field-header" container alignItems="center">
										<Heading className="e-app-export-kit-information__label" variant="h6" tag="h4">
											{ __( 'Kit Name', 'elementor' ) }
										</Heading>

										<ExportInfoModal />
									</Grid>

									<Grid item>
										<KitName />
									</Grid>
								</Grid>
							</Grid>

							<Grid item md={4}>
								<Grid className="e-app-export-kit-information__field-header" container alignItems="center">
									<Heading className="e-app-export-kit-information__label" variant="h6" tag="h4">
										{ __( 'Kit Description', 'elementor' ) }
									</Heading>

									<ExportInfoModal />
								</Grid>

								<Grid item>
									<KitDescription />
								</Grid>
							</Grid>
						</Grid>
					</Panel.Body>
				</Panel>
			</section>
		</Layout>
	);
}
