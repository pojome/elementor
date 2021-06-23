import InlineLink from 'elementor-app/ui/molecules/inline-link';
import InfoModal from './info-modal';

export default function ExportInfoModal( props ) {
	return (
		<InfoModal { ...props } title={ __( 'Export a Template Kit', 'elementor' ) }>
			<InfoModal.Section>
				<InfoModal.Heading>{ __( 'What’s a Template Kit?', 'elementor' ) }</InfoModal.Heading>
				<InfoModal.Text>{ __( 'A kit is a zip file containing anything from an entire site to individual components.', 'elementor' ) }</InfoModal.Text>
			</InfoModal.Section>

			<InfoModal.Section>
				<InfoModal.Heading>{ __( 'How does exporting work?', 'elementor' ) }</InfoModal.Heading>
				<InfoModal.Text>
					<>
						{ __( 'Select what to include from your site. We’ll use that to create a zip file.', 'elementor' ) }
						<br />
						{ __( 'That’s it!', 'elementor' ) } <InlineLink url="http://go.elementor.com/app-export-kit">{ __( 'Learn More', 'elementor' ) }</InlineLink>
					</>
				</InfoModal.Text>
			</InfoModal.Section>

			<InfoModal.Section>
				<InfoModal.Tip
					title={ __( 'Tip!', 'elementor' ) }
					description={ __( 'Once your download is complete, import your kit to another site and get it up and running quickly.', 'elementor' ) }
				/>
			</InfoModal.Section>
		</InfoModal>
	);
}
