import { MenuItem } from '@elementor/app-ui';

export default function IndexSidebar( props ) {
	return (
		<>
			<MenuItem
				text={__( 'All Kits', 'elementor' )}
				className="eps-menu-item__link eps-menu-item--active"
				icon="eicon-filter"
				url="/kit-library"
			/>
			{ props.tagsFilterSlot }
		</>
	);
}

IndexSidebar.propTypes = {
	tagsFilterSlot: PropTypes.node,
};
