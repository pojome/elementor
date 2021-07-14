import { Text, Button } from '@elementor/app-ui';

import './envato-promotion.scss';

export default function EnvatoPromotion() {
	return (
		<Text className="e-kit-library-bottom-promotion" variant="xl">
			{ __( 'Looking for more Kits?', 'elementor' ) } { ' ' }
			<Button
				variant="underlined"
				color="link"
				url="https://go.elementor.com/app-envato-kits/"
				target="_blank"
				rel="noreferrer"
				text={ __( 'Check out Elementor Template Kits on ThemeForest', 'elementor' ) }
			/>
		</Text>
	);
}
