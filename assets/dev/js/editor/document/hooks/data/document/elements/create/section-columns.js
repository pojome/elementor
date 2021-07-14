import After from 'elementor-api/modules/hooks/data/after';
import Helper from '../helper';
import { DEFAULT_INNER_SECTION_COLUMNS } from 'elementor-elements/views/section';

export class SectionColumns extends After {
	getCommand() {
		return 'document/elements/create';
	}

	getId() {
		return 'create-section-columns';
	}

	getContainerType() {
		return 'document';
	}

	getConditions( args ) {
		return ! args.model.elements && 'section' === args.model.elType;
	}

	/**
	 * @inheritDoc
	 *
	 * @param {{}} args
	 * @param {Container||Container[]} containers
	 *
	 * @returns {boolean}
	 */
	apply( args, containers ) {
		const { structure = false, options = {} } = args;

		if ( ! Array.isArray( containers ) ) {
			containers = [ containers ];
		}

		let { columns = 1 } = args;

		if ( args.model.isInner && 1 === columns ) {
			columns = DEFAULT_INNER_SECTION_COLUMNS;
		}

		Helper.createSectionColumns( containers, columns, options, structure );
	}
}

export default SectionColumns;
