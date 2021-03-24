import { SearchInput, CssGrid } from '@elementor/app-ui';
import Layout from '../components/layout';
import IndexSidebar from '../components/index-sidebar';
import TagsFilter from '../components/tags-filter';
import Header from '../components/layout/header';
import KitList from '../components/kit-list';
import useKits from '../hooks/use-kits';
import Content from '../../../../../assets/js/layout/content';
import useHeadersButtons from '../hooks/use-headers-buttons';
import FilterIndicationText from '../components/filter-indication-text';
import { IndexNoResults } from '../components/index-no-results';

import './index.scss';

export default function Index() {
	const headerButtons = useHeadersButtons( [ 'info' ] );

	const {
		data,
		isSuccess,
		isLoading,
		isError,
		filter,
		setFilter,
		clearFilter,
	} = useKits();

	return (
		<Layout
			sidebar={
				<IndexSidebar
					tagsFilterSlot={ <TagsFilter
						selected={ filter.tags }
						onSelect={ ( type, callback ) => setFilter(
							( prev ) => {
								const tags = { ...prev.tags };

								tags[ type ] = callback( prev.tags[ type ] );

								return { ...prev, tags };
							}
						) }
					/> }
				/>
			}
			header={ <Header buttons={ headerButtons }/> }
		>
			<div className="e-kit-library__index-layout-container">
				<div className="e-kit-library__index-layout-search-area">
					<CssGrid spacing={ 24 } colMinWidth={ 250 }>
						<div style={ { gridColumn: '1 / span 2' } }>
							<SearchInput
								placeholder={ __( 'Search a kit theme or style', 'elementor' ) }
								value={ filter.search }
								onChange={ ( value ) => setFilter( ( prev ) => ( { ...prev, search: value } ) ) }
							/>
							<FilterIndicationText
								filter={ filter }
								resultCount={ data.length || 0 }
								onClear={ clearFilter }
								onRemoveTag={( tag ) => setFilter( ( prev ) => {
									const tags = Object.entries( prev.tags )
										.reduce( ( current, [ key, groupedTags ] ) => ( {
											...current,
											[ key ]: groupedTags.filter( ( item ) => item !== tag ),
										} ), {} );

									return { ...prev, tags };
								} )}
							/>
						</div>
						<div style={{ background: 'red' }}>
							This is try
						</div>
					</CssGrid>
				</div>
				<Content className="e-kit-library__index-layout-main">
					<>
						{ isLoading && 'Loading...' }
						{ isError && 'Error' }
						{ isSuccess && data.length > 0 && <KitList data={ data }/> }
						{ isSuccess && data.length <= 0 && <IndexNoResults /> }
					</>
				</Content>
			</div>
		</Layout>
	);
}
