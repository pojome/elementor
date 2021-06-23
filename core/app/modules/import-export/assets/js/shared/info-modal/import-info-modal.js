import InlineLink from 'elementor-app/ui/molecules/inline-link';
import InfoModal from './info-modal';

export default function ImportInfoModal( props ) {
	return (
		<InfoModal { ...props } title={ __( 'Import a Template Kit', 'elementor' ) }>
			<InfoModal.Section>
				<InfoModal.Heading>{ __( 'What’s a Template Kit?', 'elementor' ) }</InfoModal.Heading>
				<InfoModal.Text>{ __( 'A kit is a zip file containing anything from an entire site to individual components.', 'elementor' ) }</InfoModal.Text>
			</InfoModal.Section>

			<InfoModal.Section>
				<InfoModal.Heading>{ __( 'How does importing work?', 'elementor' ) }</InfoModal.Heading>
				<InfoModal.Text>
					<>
						{ __( 'After you select what\'s included from your imported kit, you’ll be able to choose which current site settings should stay and which the import should override. ', 'elementor' ) }
						<br />
						<InlineLink url="http://go.elementor.com/app-import-kit">{ __( 'Learn More', 'elementor' ) }</InlineLink>
					</>
				</InfoModal.Text>
			</InfoModal.Section>
		</InfoModal>
	);
}
