import { Icon, Button } from '@elementor/app-ui';

import './search-input.scss';

export default function SearchInput( props ) {
	return (
		<div className={ `eps-search-input__container ${ props.className }` }>
			<input
				className={`eps-search-input eps-search-input--${ props.size }`}
				placeholder={ props.placeholder }
				value={ props.value || '' }
				onChange={ ( e ) => props.onChange( e.target.value ) }
			/>
			<Icon className={`eicon-search-bold eps-search-input__icon eps-search-input__icon--${ props.size }`}/>
			{
				props.value &&
				<Button
					text={ __( 'Clear', 'elementor' ) }
					hideText={ true }
					className={`eicon-close-circle eps-search-input__clear-icon eps-search-input__clear-icon--${ props.size }`}
					onClick={ () => props.onChange( '' ) }
				/>
			}
		</div>
	);
}

SearchInput.propTypes = {
	placeholder: PropTypes.string,
	value: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
	className: PropTypes.string,
	size: PropTypes.oneOf( [ 'md', 'sm' ] ),
};

SearchInput.defaultProps = {
	className: '',
	size: 'md',
};
