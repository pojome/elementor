import Content from '../../../../../../assets/js/layout/content';
import EnvatoPromotion from '../../components/envato-promotion';
import ErrorScreen from '../../components/error-screen';
import FilterIndicationText from '../../components/filter-indication-text';
import IndexHeader from './index-header';
import IndexSidebar from './index-sidebar';
import KitList from '../../components/kit-list';
import Layout from '../../components/layout';
import PageLoader from '../../components/page-loader';
import SearchInput from '../../components/search-input';
import SortSelect from '../../components/sort-select';
import TaxonomiesFilter from '../../components/taxonomies-filter';
import useKits, { defaultQueryParams } from '../../hooks/use-kits';
import useTaxonomies from '../../hooks/use-taxonomies';
import { Grid } from '@elementor/app-ui';
import { useCallback, useMemo, useEffect } from 'react';
import { useLastFilterContext } from '../../context/last-filter-context';
import { useLocation } from '@reach/router';

import './index.scss';

/**
 * Generate select and unselect taxonomy functions.
 *
 * @param setQueryParams
 * @returns {((function(*, *): *)|(function(*=): *))[]}
 */
function useTaxonomiesSelection( setQueryParams ) {
	const selectTaxonomy = useCallback( ( type, callback ) => setQueryParams(
		( prev ) => {
			const taxonomies = { ...prev.taxonomies };

			taxonomies[ type ] = callback( prev.taxonomies[ type ] );

			return { ...prev, taxonomies };
		}
	), [ setQueryParams ] );

	const unselectTaxonomy = useCallback( ( taxonomy ) => setQueryParams( ( prev ) => {
		const taxonomies = Object.entries( prev.taxonomies )
			.reduce( ( current, [ key, groupedTaxonomies ] ) => ( {
				...current,
				[ key ]: groupedTaxonomies.filter( ( item ) => item !== taxonomy ),
			} ), {} );

		return { ...prev, taxonomies };
	} ), [ setQueryParams ] );

	return [ selectTaxonomy, unselectTaxonomy ];
}

/**
 * Generate the menu items for the index page.
 *
 * @param path
 * @returns {array}
 */
function useMenuItems( path ) {
	return useMemo( () => {
		const page = path.replace( '/', '' );

		return [
			{
				label: __( 'All Template Kits', 'elementor' ),
				icon: 'eicon-filter',
				isActive: ! page,
				url: '/kit-library',
			},
			{
				label: __( 'Favorites', 'elementor' ),
				icon: 'eicon-heart-o',
				isActive: 'favorites' === page,
				url: '/kit-library/favorites',
			},
		];
	}, [ path ] );
}

/**
 * Update and read the query param from the url
 *
 * @param queryParams
 * @param setQueryParams
 * @param exclude
 */
function useRouterQueryParams( queryParams, setQueryParams, exclude = [] ) {
	const location = useLocation(),
		{ setLastFilter } = useLastFilterContext();

	useEffect( () => {
		const filteredQueryParams = Object.fromEntries(
			Object.entries( queryParams )
				.filter( ( [ key, item ] ) => ! exclude.includes( key ) && item )
		);

		setLastFilter( filteredQueryParams );

		history.replaceState(
			null,
			'',
			decodeURI(
				`#${ wp.url.addQueryArgs( location.pathname.split( '?' )[ 0 ] || '/', filteredQueryParams ) }`
			)
		);
	}, [ queryParams ] );

	useEffect( () => {
		const routerQueryParams = Object.keys( defaultQueryParams ).reduce( ( current, key ) => {
			// TODO: Replace with `wp.url.getQueryArgs` when WordPress 5.7 is the min version
			const queryArg = wp.url.getQueryArg( location.pathname, key );

			if ( ! queryArg ) {
				return current;
			}

			return {
				...current,
				[ key ]: queryArg,
			};
		}, {} );

		setQueryParams( ( prev ) => ( {
			...prev,
			...routerQueryParams,
			taxonomies: {
				...prev.taxonomies,
				...routerQueryParams.taxonomies,
			},
			ready: true,
		} ) );
	}, [] );
}

export default function Index( props ) {
	const menuItems = useMenuItems( props.path );

	const {
		data,
		isSuccess,
		isLoading,
		isFetching,
		isError,
		queryParams,
		setQueryParams,
		clearQueryParams,
		forceRefetch,
		isFilterActive,
	} = useKits( props.initialQueryParams );

	useRouterQueryParams( queryParams, setQueryParams, [ 'ready', ...Object.keys( props.initialQueryParams ) ] );

	const {
		data: taxonomiesData,
		forceRefetch: forceRefetchTaxonomies,
		isFetching: isFetchingTaxonomies,
	} = useTaxonomies();

	const [ selectTaxonomy, unselectTaxonomy ] = useTaxonomiesSelection( setQueryParams );

	return (
		<Layout
			sidebar={
				<IndexSidebar
					tagsFilterSlot={ <TaxonomiesFilter
						selected={ queryParams.taxonomies }
						onSelect={ selectTaxonomy }
						taxonomies={ taxonomiesData }
					/> }
					menuItems={ menuItems }
				/>
			}
			header={
				<IndexHeader
					refetch={ () => {
						forceRefetch();
						forceRefetchTaxonomies();
					} }
					isFetching={ isFetching || isFetchingTaxonomies }
				/>
			}
		>
			<div className="e-kit-library__index-layout-container">
				<Grid container className="e-kit-library__index-layout-top-area">
					<Grid item className="e-kit-library__index-layout-top-area-search">
						<SearchInput
							placeholder={ __( 'Search all Template Kits...', 'elementor' ) }
							value={ queryParams.search }
							onChange={ ( value ) => setQueryParams( ( prev ) => ( { ...prev, search: value } ) ) }
						/>
						{ isFilterActive && <FilterIndicationText
							queryParams={ queryParams }
							resultCount={ data.length || 0 }
							onClear={ clearQueryParams }
							onRemoveTag={ unselectTaxonomy }
						/> }
					</Grid>
					<Grid
						item
						className="e-kit-library__index-layout-top-area-sort"
					>
						<SortSelect
							options={ [
								{ label: __( 'Featured', 'elementor' ), value: 'featuredIndex' },
								{ label: __( 'New', 'elementor' ), value: 'createdAt' },
								{ label: __( 'Popular', 'elementor' ), value: 'popularityIndex' },
								{ label: __( 'Trending', 'elementor' ), value: 'trendIndex' },
							] }
							value={ queryParams.order }
							onChange={ ( order ) => setQueryParams( ( prev ) => ( { ...prev, order } ) ) }
						/>
					</Grid>
				</Grid>
				<Content className="e-kit-library__index-layout-main">
					<>
						{ isLoading && <PageLoader /> }
						{
							isError && <ErrorScreen
								title={ __( 'Something went wrong.', 'elementor' ) }
								description={ __( 'Nothing to worry about, use 🔄 on the top right to try again. If the problem continues, head over to the Help Center.', 'elementor' ) }
								button={ {
									text: __( 'Learn More', 'elementor' ),
									url: 'http://go.elementor.com/app-kit-library-error',
									target: '_blank',
								} }
							/>
						}
						{ isSuccess && 0 < data.length && queryParams.ready && <KitList data={ data }/> }
						{
							isSuccess && 0 === data.length && queryParams.ready && props.renderNoResultsComponent( {
								defaultComponent: <ErrorScreen
									title={ __( 'No results matched your search.', 'elementor' ) }
									description={ __( 'Try different keywords or ', 'elementor' ) }
									button={ {
										text: __( 'Continue browsing.', 'elementor' ),
										action: clearQueryParams,
									} }
								/>,
								isFilterActive,
							} )
						}
						<EnvatoPromotion />
					</>
				</Content>
			</div>
		</Layout>
	);
}

Index.propTypes = {
	path: PropTypes.string,
	initialQueryParams: PropTypes.object,
	renderNoResultsComponent: PropTypes.func,
};

Index.defaultProps = {
	initialQueryParams: {},
	renderNoResultsComponent: ( { defaultComponent } ) => defaultComponent,
};
