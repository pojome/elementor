export class Reset extends $e.modules.CommandBase {
	validateArgs( args ) {
		this.requireArgument( 'type', args );
	}

	async apply( { type } ) {
		await $e.data.delete( 'default-values/index', { type } );

		// Fill the cache
		$e.data.cache.set( `default-values/${ type }`, undefined );
	}
}

export default Reset;
