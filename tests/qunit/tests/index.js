export const tests = () => {
	// JS API modules.
	require( './core/common/assets/js/api/modules/command-infra.spec.js' );
	require( './core/common/assets/js/api/modules/command-base.spec.js' );
	require( './core/common/assets/js/api/modules/command-callback.spec.js' );
	require( './core/common/assets/js/api/modules/command-data.spec.js' );
	require( './core/common/assets/js/api/modules/command-internal-base.spec.js' );

	// JS API core.
	require( './core/common/assets/js/api/core/commands.spec.js' );
	require( './core/common/assets/js/api/core/components.spec.js' );
	require( './core/common/assets/js/api/core/data.spec.js' );
	require( './core/common/assets/js/api/core/routes.spec.js' );

	require( './core/common/assets/js/api/core/hooks/base.spec.js' );

	// JS API extras.
	require( './core/common/assets/js/api/extras/hash-commands.spec' );

	// Editor.
	require( './assets/dev/js/editor/container/container.spec' );

	// JS API editor
	require( './assets/dev/js/editor/command-bases/command-editor-base.spec' );
	require( './assets/dev/js/editor/command-bases/command-editor-internal.spec' );

	// JS API editor document.
	require( './assets/dev/js/editor/document/command-bases/command-history-base.spec' );
	require( './assets/dev/js/editor/document/command-bases/command-history-debounce-base.spec' );
	require( './assets/dev/js/editor/document/command-bases/command-disable-enable.spec' );

	require( './assets/dev/js/editor/document/dynamic/commands/base/disable-enable.spec' );
	require( './assets/dev/js/editor/document/globals/commands/base/disable-enable.spec' );

	// JS API editor document components & commands.
	require( './assets/dev/js/editor/document/component.spec' );

	// JS API editor globals data.
	require( './assets/dev/js/editor/data/globals/component.spec' );

	// Kits.
	require( './core/kits/assets/js/manager.spec' );

	// JS API kits components & commands.
	require( './core/kits/assets/js/component.spec' );

	// Modules.
	require( './modules/dev-tools/assets/js/editor/dev-tools.spec' );
};

// Export for external build.
if ( $e?.devTools?.external && ! $e.devTools.external.tests ) {
	$e.devTools.external.tests = tests;
}

export default tests;
