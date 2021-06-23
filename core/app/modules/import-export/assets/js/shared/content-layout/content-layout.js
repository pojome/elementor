import './content-layout.scss';

export default function ContentLayout( props ) {
	return (
		<div className="e-app-import-export-content-layout">
			<div className="e-app-import-export-content-layout__container">
				{ props.children }
			</div>
		</div>
	);
}

ContentLayout.propTypes = {
	children: PropTypes.any.isRequired,
};
