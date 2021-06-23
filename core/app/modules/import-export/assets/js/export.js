import ContextProvider from './context/context-provider';
import { LocationProvider, Router } from '@reach/router';
import router from '@elementor/router';

import ExportKit from './pages/export/export-kit/export-kit';
import ExportComplete from './pages/export/export-complete/export-complete';
import ExportProcess from './pages/export/export-process/export-process';

export default function Export() {
	return (
		<ContextProvider>
			<LocationProvider history={ router.appHistory }>
				<Router>
					<ExportComplete path="complete" />
					<ExportProcess path="process" />
					<ExportKit default />
				</Router>
			</LocationProvider>
		</ContextProvider>
	);
}
