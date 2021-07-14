import BackwardsCompatibility from 'elementor-document/helper-bc';

// TODO: this file should be deleted.
export default class Helper extends BackwardsCompatibility {
	// TODO: This is not the right place for this function
	static isValidChild( childModel, parentModel ) {
		const parentElType = parentModel.get( 'elType' ),
			draggedElType = childModel.get( 'elType' ),
			parentIsInner = parentModel.get( 'isInner' ),
			draggedIsInner = childModel.get( 'isInner' );

		// Block's inner-section at inner-section column.
		if ( draggedIsInner && 'section' === draggedElType && parentIsInner && 'column' === parentElType ) {
			return false;
		}

		// Allow only nested containers.
		if ( draggedElType === parentElType && 'container' !== draggedElType ) {
			return false;
		}

		if ( 'section' === draggedElType && ! draggedIsInner && 'column' === parentElType ) {
			return false;
		}

		const childTypes = elementor.helpers.getElementChildType( parentElType );

		return childTypes && -1 !== childTypes.indexOf( childModel.get( 'elType' ) );
	}

	// TODO: This is not the right place for this function
	static isValidGrandChild( childModel, targetContainer ) {
		let result;

		const childElType = childModel.get( 'elType' );

		switch ( targetContainer.model.get( 'elType' ) ) {
			case 'document':
				result = true;
				break;

			case 'section':
				result = 'widget' === childElType;
				break;

			default:
				result = false;
		}

		return result;
	}

	// TODO: This is not the right place for this function
	static isSameElement( sourceModel, targetContainer ) {
		const targetElType = targetContainer.model.get( 'elType' ),
			sourceElType = sourceModel.get( 'elType' );

		if ( targetElType !== sourceElType ) {
			return false;
		}

		if ( 'column' === targetElType && 'column' === sourceElType ) {
			return true;
		}

		return targetContainer.model.get( 'isInner' ) === sourceModel.get( 'isInner' );
	}

	// TODO: This is not the right place for this function
	static getPasteOptions( sourceModel, targetContainer ) {
		const result = {};

		result.isValidChild = this.isValidChild( sourceModel, targetContainer.model );
		result.isSameElement = this.isSameElement( sourceModel, targetContainer );
		result.isValidGrandChild = this.isValidGrandChild( sourceModel, targetContainer );

		return result;
	}

	// TODO: This is not the right place for this function
	static isPasteEnabled( targetContainer ) {
		const storage = elementorCommon.storage.get( 'clipboard' );

		// No storage? no paste.
		if ( ! storage || ! storage[ 0 ] ) {
			return false;
		}

		if ( ! ( storage[ 0 ] instanceof Backbone.Model ) ) {
			storage[ 0 ] = new Backbone.Model( storage[ 0 ] );
		}

		const pasteOptions = this.getPasteOptions( storage[ 0 ], targetContainer );

		return Object.values( pasteOptions ).some(
			( opt ) => !! opt
		);
	}
}
