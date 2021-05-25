import Sidebar from '../../../../../../assets/js/layout/sidebar';

export default function Index( props ) {
	return (
		<div className="eps-app__lightbox">
			<div className="eps-app">
				{ props.header }
				<div className="eps-app__main">
					{
						props.sidebar &&
							<Sidebar>
								{ props.sidebar }
							</Sidebar>
					}
					{ props.children }
				</div>
			</div>
		</div>
	);
}

Index.propTypes = {
	header: PropTypes.node,
	sidebar: PropTypes.node,
	children: PropTypes.node,
};
