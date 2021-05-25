import Kit from '../models/kit';
import KitListItem from './kit-list-item';
import { CssGrid } from '@elementor/app-ui';

export default function KitList( props ) {
	return (
		<CssGrid spacing={ 24 } colMinWidth={ 250 }>
			{
				props.data.map( ( model ) => (
					<KitListItem key={ model.id } model={ model }/>
				) )
			}
		</CssGrid>
	);
}

KitList.propTypes = {
	data: PropTypes.arrayOf( PropTypes.instanceOf( Kit ) ),
};
