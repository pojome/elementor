import { useState } from 'react';

import Page from 'elementor-app/layout/page';
import ContentLayout from '../shared/content-layout/content-layout';
import { infoButtonProps } from '../shared/info-modal/info-modal';
import ImportInfoModal from '../shared/info-modal/import-info-modal';
import ExportInfoModal from '../shared/info-modal/export-info-modal';

export default function Layout( props ) {
	const [ showInfoModal, setShowInfoModal ] = useState( false ),
		getContent = () => {
			const infoModalProps = {
				show: showInfoModal,
				setShow: setShowInfoModal,
			};

			return (
				<ContentLayout>
					{ props.children }

					{ 'import' === props.type ? <ImportInfoModal { ...infoModalProps } /> : <ExportInfoModal { ...infoModalProps } /> }
				</ContentLayout>
			)
		},
		getInfoButtonProps = () => {
			return {
				...infoButtonProps,
				onClick: () => {
					setShowInfoModal( true );
				},
			};
		},
		config = {
			title: 'import' === props.type ? __( 'Import', 'elementor' ) : __( 'Export', 'elementor' ),
			headerButtons: [ getInfoButtonProps(), ...props.headerButtons ],
			content: getContent(),
			footer: props.footer,
		};

	return <Page { ...config } />;
}

Layout.propTypes = {
	type: PropTypes.oneOf( [ 'import', 'export' ] ),
	headerButtons: PropTypes.arrayOf( PropTypes.object ),
	children: PropTypes.object.isRequired,
	footer: PropTypes.object,
};

Layout.defaultProps = {
	headerButtons: [],
};
