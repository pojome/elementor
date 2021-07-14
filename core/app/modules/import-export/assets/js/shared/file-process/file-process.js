import { useEffect } from 'react';
import { arrayToClassName } from 'elementor-app/utils/utils.js';

import ImportFailedDialog from '../import-failed-dialog/import-failed-dialog';
import WizardStep from '../../ui/wizard-step/wizard-step';

export default function FileProcess( props ) {
	useEffect( () => {
		props.onLoad();
	}, [] );

	useEffect( () => {
		if ( 'success' === props.status ) {
			props.onSuccess();
		}
	}, [ props.status ] );

	return (
		<WizardStep
			className={ arrayToClassName( [ 'e-app-import-export-file-process', props.className ] ) }
			icon="eicon-loading eicon-animation-spin"
			heading={ __( 'Setting up your kit...', 'elementor' ) }
			description={
				<>
					{ __( 'This usually takes a few moments.', 'elementor' ) }
					<br />
					{ __( "Don't close this window until the process is finished.", 'elementor' ) }
				</>
			}
		>
			{ 'error' === props.status &&
			<ImportFailedDialog
				onApprove={ props.onDialogApprove }
				onDismiss={ props.onDialogDismiss }
			/>
			}
		</WizardStep>
	);
}

FileProcess.propTypes = {
	className: PropTypes.string,
	status: PropTypes.oneOf( [ 'initial', 'success', 'error' ] ),
	onLoad: PropTypes.func.isRequired,
	onSuccess: PropTypes.func.isRequired,
	onDialogApprove: PropTypes.func.isRequired,
	onDialogDismiss: PropTypes.func.isRequired,
};

FileProcess.defaultProps = {
	className: '',
};
