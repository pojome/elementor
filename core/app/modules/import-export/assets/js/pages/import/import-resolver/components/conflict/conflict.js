import { useContext } from 'react';

import { Context } from '../../../../../context/context-provider';

import ConflictCheckbox from './components/conflict-checkbox/conflict-checkbox';
import Heading from 'elementor-app/ui/atoms/heading';
import Text from 'elementor-app/ui/atoms/text';
import Grid from 'elementor-app/ui/grid/grid';
import Button from 'elementor-app/ui/molecules/button';

export default function Conflict( props ) {
	const context = useContext( Context ),
		manifest = context.data.fileResponse?.stage1?.manifest,
		getConflictTitle = ( id ) => {
			const templateType = manifest.templates[ id ].doc_type,
				summaryTitle = elementorAppConfig[ 'import-export' ].summaryTitles.templates?.[ templateType ];

			return summaryTitle?.single || templateType;
		},
		getEditTemplateButton = ( editUrl ) => (
			<Button
				className="e-app-import-resolver-conflicts__edit-template"
				url={ editUrl }
				target="_blank"
				icon="eicon-editor-external-link"
				text={ __( 'Edit Template', 'elementor' ) }
				hideText
			/>
		),
		isImportedAssetSelected = ( importedAssetId ) => context.data.overrideConditions.includes( importedAssetId ),
		getAssetClassName = ( isActive ) => {
			const classes = [ 'e-app-import-resolver-conflicts__asset' ];

			if ( isActive ) {
				classes.push( 'active' );
			}

			return classes.join( ' ' );
		},
		getImportedAssetClasses = ( importedAssetId ) => getAssetClassName( isImportedAssetSelected( importedAssetId ) ),
		getExistingAssetClasses = ( importedAssetId ) => getAssetClassName( ! isImportedAssetSelected( importedAssetId ) );

	return (
		<Grid container noWrap>
			<ConflictCheckbox id={ props.importedId } type="main-type" className="e-app-import-resolver-conflicts__checkbox" />

			<Grid item>
				<Heading variant="h5" tag="h4" className="e-app-import-resolver-conflicts__title">
					{ getConflictTitle( props.importedId ) }
				</Heading>

				<Grid item>
					<Text variant="sm" tag="span" className={ getImportedAssetClasses( props.importedId ) }>
						{ __( 'Imported' ) }: { manifest.templates[ props.importedId ].title }
					</Text>

					<Text style variant="sm" tag="span" className={ getExistingAssetClasses( props.importedId ) }>
						{ __( 'Existing' ) }: { props.conflictData.template_title } { getEditTemplateButton( props.conflictData.edit_url ) }
					</Text>
				</Grid>
			</Grid>
		</Grid>
	);
}

Conflict.propTypes = {
	importedId: PropTypes.number,
	conflictData: PropTypes.object,
};
