import CommandsBase from './base';

export class Toggle extends CommandsBase {
	/**
	 * @inheritDoc
	 */
	apply( args ) {
		const manager = this.component.manager;

		return manager.typeInstance( args.type )?.toggle( args.favorite );
	}
}

export default Toggle;
