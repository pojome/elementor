import Button from 'elementor-app/ui/molecules/button';

export default function DialogWrapper( props ) {
	let WrapperTag = 'div';

	if ( props.onSubmit ) {
		WrapperTag = 'form';
	}

	return (
		<section className="eps-modal__overlay">
			<WrapperTag className="eps-modal eps-dialog" onSubmit={ props.onSubmit }>
				{
					props.onClose &&
					<Button
						onClick={ props.onClose }
						text={ __( 'Close', 'elementor' ) }
						hideText={ true }
						icon="eicon-close"
						className="eps-dialog__close-button"
					/>
				}
				{ props.children }
			</WrapperTag>
		</section>
	);
}

DialogWrapper.propTypes = {
	onClose: PropTypes.func,
	onSubmit: PropTypes.func,
	children: PropTypes.any,
};
