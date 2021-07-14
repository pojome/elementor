export default function ElementorLoading() {
	return (
		<div className="elementor-loading">
			<div className="elementor-loader-wrapper">
				<div className="elementor-loader">
					<div className="elementor-loader-boxes">
						<div className="elementor-loader-box"/>
						<div className="elementor-loader-box"/>
						<div className="elementor-loader-box"/>
						<div className="elementor-loader-box"/>
					</div>
				</div>
				<div className="elementor-loading-title">{ __( 'Loading', 'elementor' ) }</div>
			</div>
		</div>
	);
}
