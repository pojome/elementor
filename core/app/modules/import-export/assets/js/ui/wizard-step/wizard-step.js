import { arrayToClassName } from 'elementor-app/utils/utils.js';

import Grid from 'elementor-app/ui/grid/grid';
import Icon from 'elementor-app/ui/atoms/icon';
import Heading from 'elementor-app/ui/atoms/heading';
import Text from 'elementor-app/ui/atoms/text';

import './wizard-step.scss';

export default function WizardStep( props ) {
	const baseClassName = 'e-app-import-export-wizard-step',
		classes = [ baseClassName, props.className ];

	return (
		<Grid className={ arrayToClassName( classes ) } justify="center" container>
			<Grid item>
				{ ( props.image || props.icon ) &&
				<Grid className="e-app-import-export-wizard-step__media-container" justify="center" alignItems="end" container>
					{
						props.image &&
						<img
							className="e-app-import-export-wizard-step__image"
							src={ props.image }
						/>
					}
					{
						props.icon &&
						<Icon className={ `e-app-import-export-wizard-step__icon ${ props.icon }` } />
					}
				</Grid>
				}

				{ props.heading &&
				<Heading variant="display-3" className="e-app-import-export-wizard-step__heading">
					{ props.heading }
				</Heading>
				}

				{ props.description &&
				<Text variant="xl" className="e-app-import-export-wizard-step__description" >
					{ props.description }
				</Text>
				}

				{ props.children &&
				<Grid item className="e-app-import-export-wizard-step__content">
					{ props.children }
				</Grid>
				}

				{ props.notice &&
				<Text variant="xs" className="e-app-import-export-wizard-step__notice">
					{ props.notice }
				</Text>
				}
			</Grid>
		</Grid>
	);
}

WizardStep.propTypes = {
	className: PropTypes.string,
	image: PropTypes.string,
	icon: PropTypes.string,
	heading: PropTypes.string,
	description: PropTypes.oneOfType( [ PropTypes.string, PropTypes.object ] ),
	notice: PropTypes.oneOfType( [ PropTypes.string, PropTypes.object ] ),
	children: PropTypes.any,
};

WizardStep.defaultProps = {
	className: '',
};
