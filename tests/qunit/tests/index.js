export const tests = () => {
	require( './core/common/assets/js/api/modules/command-base.spec.js' );
	require( './core/common/assets/js/api/modules/command-data.spec.js' );

	require( './core/common/assets/js/api/core/commands.spec.js' );
	require( './core/common/assets/js/api/core/components.spec.js' );
	require( './core/common/assets/js/api/core/data.spec.js' );
	require( './core/common/assets/js/api/core/routes.spec.js' );

	require( './core/common/assets/js/api/core/hooks/base.spec.js' );

	require( './core/common/assets/js/api/extras/hash-commands.spec' );

	require( './assets/dev/js/editor/container/container.spec' );

	require( './assets/dev/js/editor/document/commands/base/command-history.spec' );
	require( './assets/dev/js/editor/document/dynamic/commands/base/disable-enable.spec' );
	require( './assets/dev/js/editor/document/globals/commands/base/disable-enable.spec' );

	require( './assets/dev/js/editor/document/component.spec' );

	require( './assets/dev/js/editor/data/globals/component.spec' );

	require( './core/kits/assets/js/manager.spec' );
	require( './core/kits/assets/js/component.spec' );

	require( './modules/dev-tools/assets/js/editor/dev-tools.spec' );
};

// Export for external build.
if ( $e?.devTools?.external && ! $e.devTools.external.tests ) {
	$e.devTools.external.tests = tests;
}

export default tests;
