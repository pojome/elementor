import ContextProvider from './context/context-provider';
import { LocationProvider, Router } from '@reach/router';
import router from '@elementor/router';

import ImportKit from './pages/import/import-kit/import-kit';
import ImportContent from './pages/import/import-content/import-content';
import ImportResolver from './pages/import/import-resolver/import-resolver';
import ImportProcess from './pages/import/import-process/import-process';
import ImportComplete from './pages/import/import-complete/import-complete';

export default function Import() {
	return (
		<ContextProvider>
			<LocationProvider history={ router.appHistory }>
				<Router>
					<ImportComplete path="complete" />
					<ImportProcess path="process" />
					<ImportResolver path="resolver" />
					<ImportContent path="content" />
					<ImportKit default />
				</Router>
			</LocationProvider>
		</ContextProvider>
	);
}
