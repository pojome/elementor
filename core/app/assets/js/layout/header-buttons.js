import Button from './header-button';

export default function HeaderButtons( props ) {
	let tools = '';

	if ( props.buttons.length ) {
		const buttons = props.buttons.map( ( button ) => {
			return <Button key={ button.id } {...button } />;
		} );

		tools = (
			<>
				{ buttons }
			</>
		);
	}

	return (
		<div className="eps-app__header-buttons">
			<Button
				text={ __( 'Close', 'elementor' ) }
				icon="eicon-close"
				className="eps-app__close-button"
				onClick={ () => {
					if ( window.top === window ) {
						// Directly.
						window.top.location = elementorAppConfig.return_url;
					} else {
						// Iframe.
						window.top.$e.run( 'app/close' );
					}
				} }
			/>
			{ tools }
		</div>
	);
}

HeaderButtons.propTypes = {
	buttons: PropTypes.arrayOf( PropTypes.object ),
};

HeaderButtons.defaultProps = {
	buttons: [],
};
