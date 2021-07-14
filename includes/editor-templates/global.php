<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<script type="text/template" id="tmpl-elementor-empty-preview">
	<div class="elementor-first-add">
		<div class="elementor-icon eicon-plus"></div>
	</div>
</script>

<script type="text/template" id="tmpl-e-container-presets">
	<div class="e-container-select-preset--inner">
		<div class="e-container-select-preset--close">
			<i class="eicon-close" aria-hidden="true"></i>
			<span class="elementor-screen-only"><?php echo __( 'Close', 'elementor' ); ?></span>
		</div>
		<div class="e-container-select-preset--title"><?php echo __( 'Add Container Structure', 'elementor' ); ?></div>
		<div class="e-container-select-preset--list">
			<#
				const presets = [
					'100',
					'50',
					'50-50',
					'33',
					'50-25',
				];

				presets.forEach( ( preset ) => {
					#>
					<div class="e-container-preset" data-preset="{{ preset }}">
						{{{ elementor.presetsFactory.getContainerPreset( preset ) }}}
					</div>
					<#
				} );
			#>
		</div>
	</div>
	<div class="elementor-first-add">
		<div class="elementor-icon eicon-plus"></div>
	</div>
</script>

<script type="text/template" id="tmpl-elementor-add-section">
	<div class="elementor-add-section-inner">
		<div class="elementor-add-section-close">
			<i class="eicon-close" aria-hidden="true"></i>
			<span class="elementor-screen-only"><?php echo __( 'Close', 'elementor' ); ?></span>
		</div>
		<div class="elementor-add-new-section">
			<div class="elementor-add-section-area-button elementor-add-section-button" title="<?php echo __( 'Add New Section', 'elementor' ); ?>">
				<i class="eicon-plus"></i>
			</div>
			<div class="elementor-add-section-area-button elementor-add-template-button" title="<?php echo __( 'Add Template', 'elementor' ); ?>">
				<i class="eicon-folder"></i>
			</div>
			<div class="elementor-add-section-drag-title"><?php echo __( 'Drag widget here', 'elementor' ); ?></div>
		</div>
		<div class="elementor-select-preset">
			<div class="elementor-select-preset-title"><?php echo __( 'Select your Structure', 'elementor' ); ?></div>
			<ul class="elementor-select-preset-list">
				<#
					const isContainerActive = elementorCommon.config.experimentalFeatures.container;
					var structures = [ 10, 20, 30, 40, 21, 22, 31, 32, 33, 50, 34 ];

					// Add the removed section structure if the container experiment isn't active.
					if ( ! isContainerActive ) {
						structures.push( 60 );
					}

					_.each( structures, function( structure ) {
					var preset = elementor.presetsFactory.getPresetByStructure( structure ); #>

					<li class="elementor-preset elementor-column elementor-col-16" data-structure="{{ structure }}">
						{{{ elementor.presetsFactory.getPresetSVG( preset.preset ).outerHTML }}}
					</li>
					<# } ); #>

				<# if ( isContainerActive ) { #>
					<li class="elementor-preset elementor-column elementor-col-16" data-structure="flex-container">
						{{{ elementor.presetsFactory.getContainerPreset( 'default', 'FLEX' ) }}}
					</li>
				<# } #>
			</ul>
		</div>
	</div>
</script>

<script type="text/template" id="tmpl-elementor-tag-controls-stack-empty">
	<?php echo __( 'This tag has no settings.', 'elementor' ); ?>
</script>
