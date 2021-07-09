import CommandHistoryBase from '../../command-bases/command-history-base';

export class Paste extends CommandHistoryBase {
	validateArgs( args ) {
		this.requireContainer( args );

		// Validate if storage have data.
		const { storageKey = 'clipboard' } = args,
			storageData = elementorCommon.storage.get( storageKey );

		this.requireArgumentType( 'storageData', 'object', { storageData } );
	}

	getHistory() {
		return {
			type: 'paste',
			title: __( 'Elements', 'elementor' ),
		};
	}

	apply( args ) {
		const { at, rebuild = false, storageKey = 'clipboard', containers = [ args.container ], options = {} } = args,
			storageData = elementorCommon.storage.get( storageKey ),
			result = [];

		// Paste on "Add Section" area.
		if ( rebuild ) {
			// Paste at each target.
			containers.forEach( ( targetContainer ) => {
				let index = 'undefined' === typeof at ? targetContainer.view.collection.length : at;

				storageData.forEach( ( model ) => {
					switch ( model.elType ) {
						case 'section': {
							// If is inner create section for `inner-section`.
							if ( model.isInner ) {
								const section = $e.run( 'document/elements/create', {
									container: targetContainer,
									model: {
										elType: 'section',
									},
									columns: 1,
									options: {
										at: index,
										edit: false,
									},
								} );

								// `targetContainer` = first column at `section`.
								targetContainer = section.view.children.findByIndex( 0 ).getContainer();
							}

							// Will be not affected by hook since it always have `model.elements`.
							result.push( this.pasteTo(
								[ targetContainer ],
								[ model ],
								{
									at: index,
									edit: false,
								} )
							);
							index++;
						}
						break;

						case 'column': {
							// Next code changed from original since `_checkIsEmpty()` was removed.
							const section = $e.run( 'document/elements/create', {
								container: targetContainer,
								model: {
									elType: 'section',
								},
								columns: 0, // section with no columns.
								options: {
									at: index,
									edit: false,
								},
							} );

							result.push( this.pasteTo( [ section ], [ model ] ) );
						}
						break;

						default:
							// In case it widget:
							let target;

							// If you trying to paste widget on section, then paste should be at the first column.
							if ( 'section' === targetContainer.model.get( 'elType' ) ) {
								target = [ targetContainer.view.children.findByIndex( 0 ).getContainer() ];
							} else {
								// Else, create section with one column for element.
								const section = $e.run( 'document/elements/create', {
									container: targetContainer,
									model: {
										elType: 'section',
									},
									columns: 1,
									options: {
										at: index,
									},
								} );

								// Create the element in the column that just was created.
								target = [ section.view.children.first().getContainer() ];
							}

							result.push( this.pasteTo( target, [ model ] ) );
					}
				} );
			} );
		} else {
			if ( undefined !== at ) {
				options.at = at;
			}

			result.push( this.pasteTo( containers, storageData, options ) );
		}

		if ( 1 === result.length ) {
			return result[ 0 ];
		}

		return result;
	}

	pasteTo( targetContainers, models, options = {} ) {
		options = Object.assign( { at: null, clone: true }, options );

		const result = [];

		models.forEach( ( model ) => {
			result.push( $e.run( 'document/elements/create', {
				containers: targetContainers,
				model,
				options,
			} ) );

			// On paste sections, increase the `at` for every section.
			if ( null !== options.at ) {
				options.at++;
			}
		} );

		if ( 1 === result.length ) {
			return result[ 0 ];
		}

		return result;
	}
}

export default Paste;
