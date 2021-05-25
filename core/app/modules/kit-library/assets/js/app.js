import Index from './pages/index';
import Overview from './pages/overview/overview';
import Preview from './pages/preview/preview';
import { QueryClientProvider, QueryClient } from 'react-query';
import { ReactQueryDevtools } from 'react-query/devtools';
import { Router } from '@reach/router';
import { SettingsProvider } from './context/settings-context';

const queryClient = new QueryClient( {
	defaultOptions: {
		queries: {
			refetchOnWindowFocus: false,
			retry: false,
			staleTime: 1000 * 60 * 30, // 30 minutes
		},
	},
} );

const isDebug = elementorCommon.config.isDebug || false;

export default function App() {
	return (
		<div className="e-kit-library">
			<QueryClientProvider client={ queryClient }>
				<SettingsProvider value={ elementorAppConfig[ 'kit-library' ] }>
					<Router>
						<Index path="/"/>
						<Preview path="/preview/:id"/>
						<Overview path="/overview/:id"/>
					</Router>
				</SettingsProvider>
				{ isDebug && <ReactQueryDevtools initialIsOpen={ false }/> }
			</QueryClientProvider>
		</div>
	);
}
