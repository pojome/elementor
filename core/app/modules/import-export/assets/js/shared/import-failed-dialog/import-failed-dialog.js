import Dialog from 'elementor-app/ui/dialog/dialog';

export default function ImportFailedDialog( props ) {
	return (
		<Dialog
			title={ __( 'Something went wrong', 'elementor' ) }
			text={ __( 'Nothing to worry about, just try again. If the problem continues, head over to the Help Center.', 'elementor' ) }
			approveButtonColor="link"
			approveButtonText={ __( 'Learn More', 'elementor' ) }
			approveButtonOnClick={ props.onApprove }
			dismissButtonText={ __( 'Dismiss', 'elementor' ) }
			dismissButtonOnClick={ props.onDismiss }
			onClose={ props.onDismiss }
		/>
	);
}

ImportFailedDialog.propTypes = {
	onApprove: PropTypes.func.isRequired,
	onDismiss: PropTypes.func.isRequired,
};
