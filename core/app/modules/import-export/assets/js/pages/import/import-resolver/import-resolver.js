import React, { useContext } from 'react';
import { useNavigate } from '@reach/router';

import { Context } from '../../../context/context-provider';

import Layout from '../../../templates/layout';
import PageHeader from '../../../ui/page-header/page-header';
import Conflict from './components/conflict/conflict';
import Panel from '../../../ui/panel/panel';
import Notice from 'elementor-app/ui/molecules/notice';
import InlineLink from 'elementor-app/ui/molecules/inline-link';
import Button from 'elementor-app/ui/molecules/button';
import Box from 'elementor-app/ui/atoms/box';
import List from 'elementor-app/ui/molecules/list';
import WizardFooter from 'elementor-app/organisms/wizard-footer';

import './import-resolver.scss';

export default function ImportResolver() {
	const context = useContext( Context ),
		navigate = useNavigate(),
		conflicts = context.data?.fileResponse?.stage1.conflicts || {},
		getFooter = () => (
			<WizardFooter separator justify="end">
				<Button
					text={ __( 'Previous', 'elementor' ) }
					variant="contained"
					onClick={ () => navigate( 'import/content' ) }
				/>

				<Button
					text={ __( 'Next', 'elementor' ) }
					variant="contained"
					color="primary"
					onClick={ () => navigate( 'import/process' ) }
				/>
			</WizardFooter>
		),
		getLearnMoreLink = () => (
			<InlineLink url="https://go.elementor.com/app-what-are-kits" italic>
				{ __( 'Learn More', 'elementor' ) }
			</InlineLink>
		),
		isHomePageOverride = () => {
			if ( context.data.includes.includes( 'content' ) ) {
				const pages = context.data?.fileResponse?.stage1.manifest.content?.page || {};

				return Object.entries( pages ).find( ( pageData ) => pageData[ 1 ].show_on_front );
			}

			return false;
		};

	return (
		<Layout type="import" footer={ getFooter() }>
			<section className="e-app-import-resolver">
				<PageHeader
					heading={ __( 'Import a Template Kit', 'elementor' ) }
					description={ [
						__( "We noticed that items in your kit overlap with what's already live on your site.", 'elementor' ),
						<React.Fragment key="description-secondary-line">
							{ __( 'Items that you leave checked here will override your current design and settings.', 'elementor' ) } { getLearnMoreLink() }
						</React.Fragment>,
					] }
				/>

				{
					isHomePageOverride() &&
					<Notice className="e-app-import-resolver__notice" label={ __( 'Note', 'elementor' ) } color="warning">
						{ __( "Your site's homepage will be determined by the kit. You can change this later.", 'elementor' ) }
					</Notice>
				}

				<Panel>
					<Panel.Header>
						<Panel.Headline>{ __( 'Select the items you want to keep and apply:', 'elementor' ) }</Panel.Headline>
					</Panel.Header>

					<Panel.Body padding="20">
						<Box className="e-app-import-resolver-conflicts__container">
							<List separated className="e-app-import-resolver-conflicts">
								{ Object.entries( conflicts ).map( ( [ id, conflict ], index ) => (
									<List.Item padding="20" key={ index } className="e-app-import-resolver-conflicts__item">
										<Conflict importedId={ parseInt( id ) } conflictData={ conflict[ 0 ] } />
									</List.Item>
								) ) }
							</List>
						</Box>
					</Panel.Body>
				</Panel>
			</section>
		</Layout>
	);
}
